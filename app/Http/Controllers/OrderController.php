<?php

namespace App\Http\Controllers;

use App\Models\Headquarter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Sale;
use App\Models\Setting;
use App\Services\CulqiService;
use App\Services\IzipayService;
use App\Services\NakamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Calcula y valida de forma estricta el costo y cobertura de envío en el servidor.
     */
    protected function resolveServerDelivery(Request $request, Headquarter $hq): array
    {
        if ($request->input('shipping_type') !== 'delivery') {
            return [
                'success' => true,
                'price' => 0.00,
                'zone_id' => null,
                'address' => 'Recojo en Tienda',
                'error' => null,
            ];
        }

        $lat = (float) $request->input('latitude');
        $lng = (float) $request->input('longitude');
        $address = (string) $request->input('address', 'Delivery a Domicilio');

        if (!$lat || !$lng) {
            return [
                'success' => false,
                'price' => 0.00,
                'zone_id' => null,
                'address' => $address,
                'error' => 'Coordenadas de entrega inválidas o no proporcionadas.',
            ];
        }

        $nakamaEnabled = Setting::get('nakama_enabled', '0') === '1';
        $isChiclayo = is_string($hq->city) && str_contains(strtolower($hq->city), 'chiclayo');

        if ($nakamaEnabled && $isChiclayo) {
            $nakamaRes = NakamaService::calcularPrecioDelivery($lat, $lng, (float)$hq->latitude, (float)$hq->longitude);
            if ($nakamaRes['success']) {
                if ($nakamaRes['fuera_de_chiclayo']) {
                    return [
                        'success' => false,
                        'price' => 0.00,
                        'zone_id' => null,
                        'address' => $address,
                        'error' => 'La ubicación seleccionada está fuera de nuestras zonas de cobertura de delivery.',
                    ];
                }
                return [
                    'success' => true,
                    'price' => (float)$nakamaRes['price'],
                    'zone_id' => null,
                    'address' => $address,
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'price' => 0.00,
                'zone_id' => null,
                'address' => $address,
                'error' => 'No se pudo calcular el costo de envío: ' . ($nakamaRes['message'] ?? 'Error desconocido'),
            ];
        }

        // Validación de polígonos locales en base de datos
        $zones = \App\Models\DeliveryZone::where('headquarter_id', $hq->id)
            ->where('is_active', true)
            ->get();

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
                        return [
                            'success' => true,
                            'price' => (float)$zone->price,
                            'zone_id' => $zone->id,
                            'address' => $address,
                            'error' => null,
                        ];
                    }
                }
            }
        }

        return [
            'success' => false,
            'price' => 0.00,
            'zone_id' => null,
            'address' => $address,
            'error' => 'La ubicación seleccionada está fuera de nuestras zonas de cobertura express autorizadas.',
        ];
    }

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

        $hq = Headquarter::findOrFail($request->headquarter_id);
        $delivery = $this->resolveServerDelivery($request, $hq);

        if (!$delivery['success']) {
            return redirect()->back()->with('error', $delivery['error']);
        }

        $deliveryPrice = $delivery['price'];
        $deliveryZoneId = $delivery['zone_id'];
        $address = $delivery['address'];
        $finalTotal = $total + $deliveryPrice;

        // Comprobar si ya existe una orden pendiente de esta sesión para Izipay
        $existingOrderId = $request->input('order_id') ?: session('izipay_pending_order_id');
        $order = null;

        if ($existingOrderId && $request->payment_method === 'izipay') {
            $order = Order::where('id', $existingOrderId)
                ->where('status', 'pending')
                ->where('payment_status', 'pending')
                ->first();
        }

        if (!$order) {
            $order = Order::create([
                'user_id' => auth()->id(),
                'customer_name' => $request->first_name . ' ' . $request->last_name,
                'headquarter_id' => $request->headquarter_id,
                'total' => $finalTotal,
                'status' => 'pending',
                'address' => $address,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'delivery_zone_id' => $deliveryZoneId,
                'delivery_price' => $deliveryPrice,
                'delivery_date' => $request->delivery_date,
                'delivery_time' => $request->delivery_time,
                'phone' => $request->phone,
                'email' => $request->email,
                'invoice_type' => $request->invoice_type,
                'invoice_document' => $request->invoice_type === 'factura' ? $request->factura_ruc : $request->boleta_doc,
                'is_gift' => $request->has('is_gift') || $request->input('is_gift') == '1' || $request->input('is_gift') === 'on',
                'has_card' => $request->has('has_tarjeta') || $request->input('has_tarjeta') == '1' || $request->input('has_tarjeta') === 'on',
                'has_dedication' => $request->has('has_dedicatoria_torta') || $request->input('has_dedicatoria_torta') == '1' || $request->input('has_dedicatoria_torta') === 'on',
            ]);

            foreach ($cart as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'options' => $item['options'] ?? null,
                ]);
            }
        } else {
            // Actualizar orden pendiente con los datos finales del formulario
            $order->update([
                'customer_name' => $request->first_name . ' ' . $request->last_name,
                'address' => $address,
                'delivery_date' => $request->delivery_date,
                'delivery_time' => $request->delivery_time,
                'phone' => $request->phone,
                'email' => $request->email,
                'invoice_type' => $request->invoice_type,
                'invoice_document' => $request->invoice_type === 'factura' ? $request->factura_ruc : $request->boleta_doc,
                'is_gift' => $request->has('is_gift') || $request->input('is_gift') == '1' || $request->input('is_gift') === 'on',
                'has_card' => $request->has('has_tarjeta') || $request->input('has_tarjeta') == '1' || $request->input('has_tarjeta') === 'on',
                'has_dedication' => $request->has('has_dedicatoria_torta') || $request->input('has_dedicatoria_torta') == '1' || $request->input('has_dedicatoria_torta') === 'on',
            ]);
        }

        // Pago con CULQI
        if ($request->payment_method === 'culqi' && $request->culqi_token) {
            $culqi = new CulqiService();
            $charge = $culqi->createCharge($request->culqi_token, $finalTotal, auth()->user()->email, $order->id);
            
            if (isset($charge['object']) && $charge['object'] === 'charge' && $charge['outcome']['type'] === 'venta_exitosa') {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'preparing',
                    'transaction_id' => $charge['id'] ?? ('culqi_' . time()),
                    'payment_payload' => $charge,
                ]);
                Sale::createFromOrder($order);
                $order->decrementProductStock();
            } else {
                return redirect()->back()->with('error', 'Error en el pago: ' . ($charge['user_message'] ?? 'Desconocido'));
            }
        }

        // Pago IZIPAY: Verificación criptográfica obligatoria, moneda y monto exacto
        if ($request->payment_method === 'izipay') {
            $rawAnswer = $request->input('izipay_raw_answer');
            $hash = $request->input('izipay_hash');

            if (!empty($rawAnswer) && !empty($hash)) {
                $izipay = new IzipayService();
                $isSignatureValid = $izipay->verifyHmacAnswer($rawAnswer, $hash);

                if ($isSignatureValid) {
                    $answerData = json_decode($rawAnswer, true);
                    $orderStatus = $answerData['orderStatus'] ?? '';
                    $currency = $answerData['orderDetails']['orderCurrency'] ?? '';
                    $paidAmountInCents = (int) ($answerData['orderDetails']['orderTotalAmount'] ?? 0);
                    $expectedAmountInCents = (int) round($order->total * 100);

                    // Validar estado PAID, moneda PEN y monto exacto
                    if ($orderStatus === 'PAID' && $currency === 'PEN' && abs($paidAmountInCents - $expectedAmountInCents) <= 1) {
                        $transactionId = $answerData['transactions'][0]['transactionId'] ?? $answerData['orderDetails']['orderId'] ?? ('izipay_' . $order->id . '_' . time());
                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'preparing',
                            'transaction_id' => $transactionId,
                            'payment_payload' => $answerData,
                        ]);
                        Sale::createFromOrder($order);
                        $order->decrementProductStock();
                    } else {
                        Log::warning('IZIPAY: Monto, moneda o estado inconsistente en checkout.', [
                            'order_id' => $order->id,
                            'status' => $orderStatus,
                            'currency' => $currency,
                            'paid_cents' => $paidAmountInCents,
                            'expected_cents' => $expectedAmountInCents
                        ]);
                    }
                } else {
                    Log::error('IZIPAY: Firma HMAC inválida en checkout.', [
                        'order_id' => $order->id,
                        'user_id' => auth()->id()
                    ]);
                }
            } else {
                Log::warning('IZIPAY: Pedido recibido sin datos criptográficos de verificación.', [
                    'order_id' => $order->id,
                    'user_id' => auth()->id()
                ]);
            }
        }

        // Send to Nakama Delivery if enabled, delivery mode, and status is preparing
        if ($request->shipping_type === 'delivery' && $order->status === 'preparing') {
            NakamaService::crearPedido($order);
        }

        session()->forget(['cart', 'izipay_pending_order_id']);

        return view('shop.success', compact('order'));
    }

    /**
     * Genera el formToken de IZIPAY vinculando la transacción a una Orden persistida
     * y recalculando el costo de envío estrictamente en el servidor.
     */
    public function izipayToken(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Carrito vacío.'], 400);
        }

        $cartTotal = 0;
        foreach ($cart as $item) {
            $cartTotal += $item['price'] * $item['quantity'];
        }

        $hqId = $request->input('headquarter_id') ?: session('selected_headquarter_id');
        $hq = Headquarter::find($hqId) ?: Headquarter::where('is_active', true)->first();

        if (!$hq) {
            return response()->json(['success' => false, 'message' => 'Sede no seleccionada.'], 400);
        }

        // Recálculo del delivery en el servidor (no se confía en delivery_price del cliente)
        $delivery = $this->resolveServerDelivery($request, $hq);
        if (!$delivery['success']) {
            return response()->json(['success' => false, 'message' => $delivery['error']], 422);
        }

        $deliveryPrice = $delivery['price'];
        $finalTotal = $cartTotal + $deliveryPrice;
        $email = $request->input('email', auth()->user()->email ?? 'cliente@gourmetica.pe');
        $customerName = $request->input('customer_name') ?: (auth()->user()->name ?? 'Cliente Web');

        // Persistir la orden como pendiente vinculada al ID real
        $order = Order::create([
            'user_id' => auth()->id() ?? 1,
            'customer_name' => $customerName,
            'headquarter_id' => $hq->id,
            'total' => $finalTotal,
            'status' => 'pending',
            'address' => $delivery['address'],
            'payment_method' => 'izipay',
            'payment_status' => 'pending',
            'delivery_zone_id' => $delivery['zone_id'],
            'delivery_price' => $deliveryPrice,
            'email' => $email,
            'phone' => $request->input('phone'),
        ]);

        foreach ($cart as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'options' => $item['options'] ?? null,
            ]);
        }

        session(['izipay_pending_order_id' => $order->id]);

        $izipay = new IzipayService();
        $result = $izipay->createFormToken((float)$finalTotal, $email, $order->id);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'formToken' => $result['formToken'],
                'publicKey' => $izipay->getPublicKey(),
                'orderId' => $order->id,
                'deliveryPrice' => $deliveryPrice,
                'finalTotal' => $finalTotal,
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

        $hq = Headquarter::find($request->headquarter_id);
        if (!$hq) {
            return response()->json(['success' => false, 'message' => 'Sede no encontrada.'], 404);
        }

        $request->merge(['shipping_type' => 'delivery']);
        $delivery = $this->resolveServerDelivery($request, $hq);

        if ($delivery['success']) {
            return response()->json([
                'success' => true,
                'price' => $delivery['price'],
                'delivery_zone_id' => $delivery['zone_id'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $delivery['error'] ?? 'Ubicación fuera de cobertura.',
        ], 422);
    }

    /**
     * Webhook (IPN) de IZIPAY para notificación instantánea del resultado del pago.
     * Con validación criptográfica, comprobación de monto y moneda, e idempotencia estricta.
     */
    public function izipayWebhook(Request $request)
    {
        $izipay = new IzipayService();

        $krHash = $request->input('kr-hash');
        $krAnswer = $request->input('kr-answer');
        
        if (!$krHash || !$krAnswer) {
            Log::error('IZIPAY Webhook: Missing hash or answer');
            return response('Error: Missing hash or answer', 400);
        }

        // 1. Validar la firma HMAC-SHA256
        $isValid = $izipay->verifySignature($krAnswer, $krHash);

        if (!$isValid) {
            Log::error('IZIPAY Webhook: Invalid signature', $request->all());
            return response('Invalid signature', 400);
        }

        $answer = json_decode($krAnswer, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response('Invalid JSON', 400);
        }

        $orderStatus = $answer['orderStatus'] ?? '';
        
        // 2. Extraer ID del pedido del formato "GOURMETICA-{ID}"
        $orderReference = $answer['orderDetails']['orderId'] ?? '';
        $orderId = (int) str_replace('GOURMETICA-', '', $orderReference);

        if (!$orderId) {
            return response('OK - No valid order reference', 200);
        }

        $order = Order::find($orderId);
        if (!$order) {
            return response('OK - Order not found', 200);
        }

        // 3. Procesar estado con idempotencia estricta
        if ($orderStatus === 'PAID') {
            // Idempotencia: Si ya fue procesado, no repetir venta ni descuento de stock
            if ($order->payment_status === 'paid') {
                return response('OK - Order already paid and processed', 200);
            }

            // Validar moneda y monto
            $currency = $answer['orderDetails']['orderCurrency'] ?? '';
            $paidCents = (int)($answer['orderDetails']['orderTotalAmount'] ?? 0);
            $expectedCents = (int)round($order->total * 100);

            if ($currency !== 'PEN' || abs($paidCents - $expectedCents) > 1) {
                Log::error('IZIPAY Webhook: Discrepancia de monto o moneda', [
                    'order_id' => $order->id,
                    'currency' => $currency,
                    'paid_cents' => $paidCents,
                    'expected_cents' => $expectedCents,
                ]);
                return response('Discrepancy in amount or currency', 400);
            }

            $transactionId = $answer['transactions'][0]['transactionId'] ?? $answer['orderDetails']['orderId'] ?? ('izipay_' . $order->id . '_' . time());
            $order->update([
                'payment_status' => 'paid',
                'status' => 'preparing',
                'transaction_id' => $transactionId,
                'payment_payload' => $answer,
            ]);
            Sale::createFromOrder($order);
            $order->decrementProductStock();

            if ($order->deliveryZone && $order->status === 'preparing') {
                NakamaService::crearPedido($order);
            }

            Log::info("IZIPAY Webhook: Pedido #{$order->id} confirmado como PAID exitosamente.");
        } elseif ($orderStatus === 'REJECTED' || $orderStatus === 'CANCELED') {
            if ($order->status !== 'cancelled' && $order->payment_status !== 'paid') {
                $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            }
        }

        return response('OK', 200);
    }
}
