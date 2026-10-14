<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NakamaWebhookController extends Controller
{
    /**
     * Procesa los eventos de actualización de estado enviados por Nakama Delivery de forma autenticada.
     */
    public function handle(Request $request)
    {
        // 1. Verificación de Autenticación
        $configuredSecret = Setting::get('nakama_webhook_secret') 
            ?: Setting::get('nakama_api_key') 
            ?: env('NAKAMA_WEBHOOK_SECRET');

        $providedToken = $request->header('X-Nakama-Token')
            ?: $request->header('X-Webhook-Secret')
            ?: $request->bearerToken()
            ?: $request->query('token')
            ?: $request->input('token');

        // Principio Fail-Closed: Si no hay secreto configurado en el servidor, rechazar todo intento
        if (empty($configuredSecret)) {
            Log::error('NakamaWebhook: Webhook inhabilitado. Falta configurar secreto o API Key en el servidor.');
            return response()->json([
                'status' => 'error',
                'message' => 'Servicio no configurado: Falta clave secreta de webhook en el servidor.'
            ], 503);
        }

        if (empty($providedToken) || !hash_equals((string)$configuredSecret, (string)$providedToken)) {
            Log::warning('NakamaWebhook: Intento no autorizado recibido.', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado: Credenciales de webhook no válidas.'
            ], 401);
        }

        $payload = $request->all();
        Log::info('NakamaWebhook: Payload autenticado recibido:', $payload);

        $event = $payload['event'] ?? '';
        $nakamaId = $payload['pedido_id'] ?? null;
        $estado = strtolower($payload['estado'] ?? '');

        if ($event !== 'order.status_updated' || empty($nakamaId) || empty($estado)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payload inválido o evento no soportado.'
            ], 400);
        }

        // 2. Buscar el pedido en Gourmetica por el ID de Nakama
        $order = Order::where('nakama_id', $nakamaId)->first();

        if (!$order) {
            Log::warning("NakamaWebhook: Pedido con Nakama ID {$nakamaId} no encontrado en Gourmetica.");
            return response()->json([
                'status' => 'error',
                'message' => 'Pedido no encontrado en Gourmetica.'
            ], 404);
        }

        // 3. Mapear los estados de Nakama a Gourmetica con control de transiciones
        $nuevoEstado = null;
        $paymentStatus = $order->payment_status;

        switch ($estado) {
            case 'recogiendo':
            case 'llevando':
                $nuevoEstado = 'shipped';
                break;
            case 'entregado':
                $nuevoEstado = 'delivered';
                // Solo marcar como pagado en la entrega si el método de pago es contraentrega/efectivo.
                // En pasarelas electrónicas (Izipay, Culqi), el pago debió confirmarse con el procesador.
                $isContraentrega = in_array(strtolower((string)$order->payment_method), ['cash', 'efectivo', 'contraentrega', 'pago_contraentrega']);
                if ($isContraentrega) {
                    $paymentStatus = 'paid';
                }
                break;
            case 'cancelado':
            case 'eliminado':
                $nuevoEstado = 'cancelled';
                break;
        }

        if ($nuevoEstado) {
            $oldStatus = $order->status;
            
            $order->update([
                'status' => $nuevoEstado,
                'payment_status' => $paymentStatus,
                'nakama_status' => $estado
            ]);

            Log::info("NakamaWebhook: Pedido #{$order->id} actualizado de {$oldStatus} a {$nuevoEstado}.");

            if (in_array($nuevoEstado, ['preparing', 'shipped', 'delivered'])) {
                $order->decrementProductStock();
            }

            // Registrar venta únicamente si el pedido fue entregado Y está pagado legítimamente
            if ($nuevoEstado === 'delivered' && $order->payment_status === 'paid') {
                try {
                    $saleExists = Sale::where('order_id', $order->id)->exists();
                    if (!$saleExists) {
                        Sale::createFromOrder($order);
                        Log::info("NakamaWebhook: Venta registrada para el Pedido #{$order->id}.");
                    }
                } catch (\Exception $e) {
                    Log::error("NakamaWebhook Error al registrar venta: " . $e->getMessage());
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook procesado correctamente.'
        ]);
    }
}
