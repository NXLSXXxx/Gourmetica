<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NakamaWebhookController extends Controller
{
    /**
     * Procesa los eventos de actualización de estado enviados por Nakama Delivery.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('NakamaWebhook: Recibido payload:', $payload);

        $event = $payload['event'] ?? '';
        $nakamaId = $payload['pedido_id'] ?? null;
        $estado = $payload['estado'] ?? '';

        if ($event !== 'order.status_updated' || empty($nakamaId) || empty($estado)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payload inválido o evento no soportado.'
            ], 400);
        }

        // Buscar el pedido en Gourmetica por el ID de Nakama
        $order = Order::where('nakama_id', $nakamaId)->first();

        if (!$order) {
            Log::warning("NakamaWebhook: Pedido con Nakama ID {$nakamaId} no encontrado en Gourmetica.");
            return response()->json([
                'status' => 'error',
                'message' => 'Pedido no encontrado en Gourmetica.'
            ], 404);
        }

        // Mapear los estados de Nakama a Gourmetica
        $nuevoEstado = null;
        $paymentStatus = $order->payment_status;

        switch ($estado) {
            case 'recogiendo':
            case 'llevando':
                $nuevoEstado = 'shipped';
                break;
            case 'entregado':
                $nuevoEstado = 'delivered';
                $paymentStatus = 'paid';
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

            // Si se entrega, registrar la venta en Gourmetica (si no se ha registrado antes)
            if ($nuevoEstado === 'delivered' && $oldStatus !== 'delivered') {
                try {
                    // Evitar duplicados de venta
                    $saleExists = Sale::where('order_id', $order->id)->exists();
                    if (!$saleExists) {
                        Sale::createFromOrder($order);
                        Log::info("NakamaWebhook: Venta registrada en caja para el Pedido #{$order->id}.");
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
