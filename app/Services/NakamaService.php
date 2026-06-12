<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NakamaService
{
    /**
     * Envía un pedido de Gourmetica a la API de Nakama Delivery.
     */
    public static function crearPedido($order): bool
    {
        $enabled = Setting::get('nakama_enabled', '0');
        $hq = $order->headquarter;
        $isChiclayo = $hq && is_string($hq->city) && str_contains(strtolower($hq->city), 'chiclayo');

        if ($enabled !== '1' || !$isChiclayo) {
            return false;
        }

        $apiUrl = rtrim(Setting::get('nakama_api_url'), '/');
        $apiKey = Setting::get('nakama_api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            Log::error('NakamaService: Configuración de API incompleta.');
            return false;
        }

        // Formatear items del pedido
        $items = [];
        foreach ($order->items as $item) {
            $items[] = [
                'nombre_plato' => $item->product->name,
                'precio_unitario' => (float)$item->price,
                'cantidad' => (int)$item->quantity
            ];
        }

        // Obtener lat/lng si están disponibles en la dirección (o si se guardaron en la orden)
        $latDestino = (float)$order->latitude;
        $lngDestino = (float)$order->longitude;

        // Limpiar teléfono
        $telefono = preg_replace('/\D+/', '', $order->user->phone ?? '');
        if (empty($telefono)) {
            $telefono = '999999999'; // Fallback
        }

        $payload = [
            'cliente_nombre' => $order->user->name,
            'cliente_telefono' => $telefono,
            'direccion_entrega' => $order->address,
            'lat_origen' => $hq ? (float)$hq->latitude : null,
            'lng_origen' => $hq ? (float)$hq->longitude : null,
            'lat_destino' => $latDestino ?: null,
            'lng_destino' => $lngDestino ?: null,
            'descripcion' => implode(', ', array_map(fn($it) => $it['cantidad'] . 'x ' . $it['nombre_plato'], $items)),
            'metodo_pago' => $order->payment_method === 'transfer' ? 'transferencia' : 'yape',
            'monto_carrera' => (float)$order->delivery_price,
            'notes' => $order->notes ?? '',
            'items' => $items
        ];

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->timeout(5)
            ->post($apiUrl . '/api/external/pedidos/nuevo', $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    $order->update([
                        'nakama_id' => $data['data']['pedido_id'],
                        'nakama_token' => $data['data']['token_seguimiento']
                    ]);
                    Log::info('NakamaService: Pedido #' . $order->id . ' enviado exitosamente a Nakama. ID Nakama: ' . $data['data']['pedido_id']);
                    return true;
                }
            }

            Log::error('NakamaService: Error al crear pedido en Nakama. HTTP Code: ' . $response->status() . ' | Res: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('NakamaService Exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Notifica a Nakama que el pedido ya fue preparado en la cocina de Gourmetica.
     */
    public static function marcarPreparado($order): bool
    {
        $enabled = Setting::get('nakama_enabled', '0');
        if ($enabled !== '1' || empty($order->nakama_id)) {
            return false;
        }

        $apiUrl = rtrim(Setting::get('nakama_api_url'), '/');
        $apiKey = Setting::get('nakama_api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            Log::error('NakamaService: Configuración de API incompleta.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json'
            ])
            ->timeout(5)
            ->post($apiUrl . '/api/external/pedidos/' . $order->nakama_id . '/preparado');

            if ($response->successful()) {
                Log::info('NakamaService: Pedido #' . $order->id . ' (Nakama ID: ' . $order->nakama_id . ') marcado como PREPARADO en Nakama.');
                return true;
            }

            Log::error('NakamaService: Error al marcar como preparado en Nakama. HTTP Code: ' . $response->status() . ' | Res: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('NakamaService Exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Calcula el precio del delivery llamando a la API de Nakama.
     */
    public static function calcularPrecioDelivery(float $latDestino, float $lngDestino, ?float $latOrigen = null, ?float $lngOrigen = null): array
    {
        $enabled = Setting::get('nakama_enabled', '0');
        if ($enabled !== '1') {
            return ['success' => false, 'message' => 'Integración con Nakama deshabilitada.'];
        }

        $apiUrl = rtrim(Setting::get('nakama_api_url'), '/');
        $apiKey = Setting::get('nakama_api_key');

        if (empty($apiUrl) || empty($apiKey)) {
            return ['success' => false, 'message' => 'Configuración de API incompleta.'];
        }

        $payload = [
            'lat_destino' => $latDestino,
            'lng_destino' => $lngDestino
        ];

        if ($latOrigen !== null && $lngOrigen !== null) {
            $payload['lat_origen'] = $latOrigen;
            $payload['lng_origen'] = $lngOrigen;
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])
            ->timeout(5)
            ->post($apiUrl . '/api/external/pricing/calculate', $payload);

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'success' => true,
                        'price' => (float)($data['data']['precio'] ?? 0.0),
                        'distancia_km' => (float)($data['data']['distancia_km'] ?? 0.0),
                        'fuera_de_chiclayo' => (bool)($data['data']['fuera_de_chiclayo'] ?? false)
                    ];
                }
            }

            return ['success' => false, 'message' => 'Error al calcular precio en Nakama: ' . $response->body()];
        } catch (\Exception $e) {
            Log::error('NakamaService calculatePricing Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Excepción de red: ' . $e->getMessage()];
        }
    }
}
