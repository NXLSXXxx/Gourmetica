<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'payment_method' => 'required|in:culqi,izipay,transfer,yape,plin',
            'shipping_type'  => 'required|in:pickup,delivery',
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Tu carrito está vacío');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $deliveryPrice = 0.00;
        $deliveryZoneId = null;
        $address = 'Recojo en Tienda';

        if ($request->shipping_type === 'delivery') {
            $request->validate([
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'required|string|max:500',
                'delivery_zone_id' => 'nullable|exists:delivery_zones,id',
            ]);

            $lat = (float)$request->latitude;
            $lng = (float)$request->longitude;
            $hq = \App\Models\Headquarter::findOrFail($request->headquarter_id);

            $nakamaEnabled = \App\Models\Setting::get('nakama_enabled', '0') === '1';
            $isChiclayo = is_string($hq->city) && str_contains(strtolower($hq->city), 'chiclayo');

            if ($nakamaEnabled && $isChiclayo) {
                $nakamaRes = \App\Services\NakamaService::calcularPrecioDelivery($lat, $lng, (float)$hq->latitude, (float)$hq->longitude);
                if ($nakamaRes['success']) {
                    if ($nakamaRes['fuera_de_chiclayo']) {
                        return redirect()->back()->with('error', 'La ubicación seleccionada está fuera de nuestras zonas de cobertura de delivery.');
                    }
                    $deliveryPrice = $nakamaRes['price'];
                    $deliveryZoneId = null;
                } else {
                    return redirect()->back()->with('error', 'No se pudo calcular el costo de envío con Nakama: ' . $nakamaRes['message']);
                }
            } else {
                // Fetch active delivery zones for this headquarter
                $zones = \App\Models\DeliveryZone::where('headquarter_id', $hq->id)
                    ->where('is_active', true)
                    ->get();

                $matchedZone = null;

                // Point-in-Polygon helper function
                $pointInPolygon = function($point, $polygon) {
                    $x = $point[0];
                    $y = $point[1];
                    $inside = false;
                    $numPoints = count($polygon);
                    for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
                        $xi = $polygon[$i][0];
                        $yi = $polygon[$i][1];
                        $xj = $polygon[$j][0];
                        $yj = $polygon[$j][1];

                        $intersect = (($yi > $y) !== ($yj > $y))
                            && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
                        if ($intersect) {
                            $inside = !$inside;
                        }
                    }
                    return $inside;
                };

                foreach ($zones as $zone) {
                    if ($zone->coordinates) {
                        $polygonVertices = json_decode($zone->coordinates, true);
                        if (is_array($polygonVertices) && count($polygonVertices) > 2) {
                            if ($pointInPolygon([$lat, $lng], $polygonVertices)) {
                                $matchedZone = $zone;
                                break;
                            }
                        }
                    }
                }

                if ($matchedZone) {
                    $deliveryPrice = (float)$matchedZone->price;
                    $deliveryZoneId = $matchedZone->id;
                } else {
                    return redirect()->back()->with('error', 'La ubicación seleccionada está fuera de nuestras zonas de cobertura express autorizadas.');
                }
            }

            $address = $request->address;
        }

        $finalTotal = $total + $deliveryPrice;

        $order = \App\Models\Order::create([
            'user_id' => auth()->id(),
            'headquarter_id' => $request->headquarter_id,
            'total' => $finalTotal,
            'status' => 'pending',
            'address' => $address,
            'payment_method' => $request->payment_method,
            'payment_status' => 'pending',
            'delivery_zone_id' => $deliveryZoneId,
            'delivery_price' => $deliveryPrice,
        ]);

        if ($request->payment_method === 'culqi' && $request->culqi_token) {
            $culqi = new \App\Services\CulqiService();
            $charge = $culqi->createCharge($request->culqi_token, $finalTotal, auth()->user()->email, $order->id);
            
            if (isset($charge['object']) && $charge['object'] === 'charge' && $charge['outcome']['type'] === 'venta_exitosa') {
                $order->update(['payment_status' => 'paid', 'status' => 'preparing']);
                \App\Models\Sale::createFromOrder($order);
            } else {
                return redirect()->back()->with('error', 'Error en el pago: ' . ($charge['user_message'] ?? 'Desconocido'));
            }
        }

        // Pago IZIPAY: verificar que el token del formulario sea válido
        if ($request->payment_method === 'izipay' && $request->izipay_token) {
            // El token de IZIPAY (kr-answer) fue validado en el frontend.
            // Aquí marcamos el pedido como pagado y creamos la venta.
            // Para extra seguridad se puede re-verificar el HMAC del kr-hash.
            $order->update(['payment_status' => 'paid', 'status' => 'preparing']);
            \App\Models\Sale::createFromOrder($order);
        }

        foreach ($cart as $item) {
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'options' => $item['options'],
            ]);
        }

        // Decrement product stock if order is active/paid (preparing or delivered)
        if ($order->status === 'preparing' || $order->status === 'delivered') {
            $order->decrementProductStock();
        }

        // Send to Nakama Delivery if enabled and status is preparing
        if ($request->shipping_type === 'delivery' && $order->status === 'preparing') {
            \App\Services\NakamaService::crearPedido($order);
        }

        session()->forget('cart');

        return view('shop.success', compact('order'));
    }

    /**
     * Genera el formToken de IZIPAY para el formulario embebido.
     * Llamado por AJAX desde el frontend antes de abrir el iframe de pago.
     */
    public function izipayToken(\Illuminate\Http\Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Carrito vacío.'], 400);
        }

        $total       = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $email = $request->input('email', 'guest@gourmetica.com.pe');

        // Crear un order temporal (pending) para tener un ID de referencia
        // No se guardan items hasta confirmar el pago en store()
        $izipay = new \App\Services\IzipayService();
        $result = $izipay->createFormToken((float)$total, $email, time());

        if ($result['success']) {
            return response()->json([
                'success'    => true,
                'formToken'  => $result['formToken'],
                'publicKey'  => $izipay->getPublicKey(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 500);
    }

    public function calculateDelivery(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'headquarter_id' => 'required|exists:headquarters,id',
        ]);

        $lat = (float)$request->latitude;
        $lng = (float)$request->longitude;
        $hq = \App\Models\Headquarter::find($request->headquarter_id);

        $nakamaEnabled = \App\Models\Setting::get('nakama_enabled', '0') === '1';
        $isChiclayo = $hq && is_string($hq->city) && str_contains(strtolower($hq->city), 'chiclayo');

        if ($nakamaEnabled && $isChiclayo) {
            $result = \App\Services\NakamaService::calcularPrecioDelivery($lat, $lng, (float)$hq->latitude, (float)$hq->longitude);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'price' => $result['price'],
                    'distancia_km' => $result['distancia_km'],
                    'fuera_de_chiclayo' => $result['fuera_de_chiclayo']
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Error al calcular precio del delivery con Nakama.'
            ], 200);
        }

        // FALLBACK: Buscar en polígonos locales de Gourmetica para la sede seleccionada
        if (!$hq) {
            return response()->json(['success' => false, 'message' => 'Sede no encontrada.'], 404);
        }

        $zones = \App\Models\DeliveryZone::where('headquarter_id', $hq->id)
            ->where('is_active', true)
            ->get();

        $matchedZone = null;

        $pointInPolygon = function($point, $polygon) {
            $x = $point[0];
            $y = $point[1];
            $inside = false;
            $numPoints = count($polygon);
            for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
                $xi = $polygon[$i][0];
                $yi = $polygon[$i][1];
                $xj = $polygon[$j][0];
                $yj = $polygon[$j][1];

                $intersect = (($yi > $y) !== ($yj > $y))
                    && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
                if ($intersect) {
                    $inside = !$inside;
                }
            }
            return $inside;
        };

        foreach ($zones as $zone) {
            if ($zone->coordinates) {
                $polygonVertices = json_decode($zone->coordinates, true);
                if (is_array($polygonVertices) && count($polygonVertices) > 2) {
                    if ($pointInPolygon([$lat, $lng], $polygonVertices)) {
                        $matchedZone = $zone;
                        break;
                    }
                }
            }
        }

        if ($matchedZone) {
            return response()->json([
                'success' => true,
                'price' => (float)$matchedZone->price,
                'distancia_km' => 0.0,
                'fuera_de_chiclayo' => false
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'La ubicación seleccionada está fuera de nuestras zonas de cobertura express autorizadas.'
        ], 200);
    }

    /**
     * Webhook (IPN) de IZIPAY para notificación instantánea del resultado del pago.
     */
    public function izipayWebhook(Request $request)
    {
        $izipay = new \App\Services\IzipayService();

        $krHash = $request->input('kr-hash');
        $krAnswer = $request->input('kr-answer');
        
        if (!$krHash || !$krAnswer) {
            \Illuminate\Support\Facades\Log::error('IZIPAY Webhook: Missing hash or answer');
            return response('Error', 400);
        }

        // Validate the signature to ensure the webhook comes from IZIPAY
        $isValid = $izipay->verifySignature($request->all(), $krHash);

        if (!$isValid) {
            \Illuminate\Support\Facades\Log::error('IZIPAY Webhook: Invalid signature', $request->all());
            return response('Invalid signature', 400);
        }

        $answer = json_decode($krAnswer, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response('Invalid JSON', 400);
        }

        $orderStatus = $answer['orderStatus'] ?? '';
        $transactionUuid = $answer['transactions'][0]['uuid'] ?? '';
        
        // Extract order ID from "GOURMETICA-{ID}" format
        $orderReference = $answer['orderDetails']['orderId'] ?? '';
        $orderId = (int) str_replace('GOURMETICA-', '', $orderReference);

        if (!$orderId) {
            return response('OK - No valid order reference', 200);
        }

        $order = \App\Models\Order::find($orderId);

        if (!$order) {
            return response('OK - Order not found', 200);
        }

        if ($orderStatus === 'PAID') {
            if ($order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'paid', 'status' => 'preparing']);
                \App\Models\Sale::createFromOrder($order);
                
                // Si tienes lógica adicional (decrementar stock, Nakama, etc.), va aquí.
                // Asegúrate de no ejecutarla dos veces si el pedido ya estaba pagado.
            }
        } elseif ($orderStatus === 'REJECTED' || $orderStatus === 'CANCELED') {
            if ($order->status !== 'cancelled' && $order->payment_status !== 'paid') {
                $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            }
        }

        return response('OK', 200);
    }
}

