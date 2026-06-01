<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'payment_method' => 'required|in:culqi,transfer',
            'shipping_type' => 'required|in:pickup,delivery',
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

        foreach ($cart as $item) {
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'options' => $item['options'],
            ]);
        }

        session()->forget('cart');

        return view('shop.success', compact('order'));
    }
}
