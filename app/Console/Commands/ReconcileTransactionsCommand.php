<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Sale;
use App\Models\Headquarter;
use Illuminate\Support\Facades\DB;

class ReconcileTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:reconcile {--headquarter_id= : Filtrar conciliación por sede específica}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta una conciliación financiera y operativa integral entre pedidos, pagos, ventas y correlativos fiscales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("===============================================================================");
        $this->info("   GOURMETICA - CONCILIACION FINANCIERA Y OPERATIVA (P3.4 RECONCILIATION)       ");
        $this->info("===============================================================================");

        $hqId = $this->option('headquarter_id');
        $orderQuery = Order::query();
        $saleQuery = Sale::query();

        if ($hqId) {
            $orderQuery->where('headquarter_id', $hqId);
            $saleQuery->where('headquarter_id', $hqId);
            $this->line(" Filtrando conciliación para la sede ID: <fg=cyan>{$hqId}</>");
        }

        $totalOrders = (clone $orderQuery)->count();
        $paidOrders = (clone $orderQuery)->where('payment_status', 'paid')->get();
        $totalSales = (clone $saleQuery)->get();

        $this->line(" Total de pedidos analizados: <fg=yellow>{$totalOrders}</>");
        $this->line(" Pedidos confirmados como PAGADOS: <fg=yellow>{$paidOrders->count()}</>");
        $this->line(" Total de ventas registradas: <fg=yellow>{$totalSales->count()}</>");
        $this->newLine();

        $anomalies = [];
        $matchedPaidOrders = 0;
        $totalVolumePaid = 0;
        $totalVolumeSales = 0;
        $matchedGateways = 0;
        $totalElectronicOrders = 0;

        // ---------------------------------------------------------------------
        // 1. CONCILIACION INTERNA: PEDIDO PAGADO -> VENTA EMITIDA
        // ---------------------------------------------------------------------
        foreach ($paidOrders as $order) {
            $totalVolumePaid += (float)$order->total;
            $associatedSales = Sale::where('order_id', $order->id)->get();

            if ($associatedSales->isEmpty()) {
                $anomalies[] = [
                    'tipo' => 'PEDIDO_PAGADO_SIN_VENTA',
                    'referencia' => "Pedido #{$order->id}",
                    'detalle' => "Pedido pagado de S/. {$order->total} sin registro en tabla sales.",
                    'severidad' => 'ALTA',
                ];
            } elseif ($associatedSales->count() > 1) {
                $anomalies[] = [
                    'tipo' => 'VENTAS_DUPLICADAS',
                    'referencia' => "Pedido #{$order->id}",
                    'detalle' => "Pedido asociado a {$associatedSales->count()} registros de venta.",
                    'severidad' => 'CRÍTICA',
                ];
            } else {
                $sale = $associatedSales->first();
                $totalVolumeSales += (float)$sale->total;

                // Verificar discrepancia de monto
                if (abs((float)$order->total - (float)$sale->total) > 0.01) {
                    $anomalies[] = [
                        'tipo' => 'DISCREPANCIA_MONTO',
                        'referencia' => "Pedido #{$order->id} / Venta #{$sale->id}",
                        'detalle' => "Total pedido (S/. {$order->total}) != Total venta (S/. {$sale->total}).",
                        'severidad' => 'ALTA',
                    ];
                } else {
                    $matchedPaidOrders++;
                }

                // Verificar descuento de inventario
                if (!$order->stock_decremented && $order->items()->count() > 0) {
                    $anomalies[] = [
                        'tipo' => 'INVENTARIO_NO_DESCONTADO',
                        'referencia' => "Pedido #{$order->id}",
                        'detalle' => "Pedido pagado sin flag stock_decremented activado.",
                        'severidad' => 'MEDIA',
                    ];
                }
            }

            // -----------------------------------------------------------------
            // 2. CONCILIACION DE PASARELAS EXTERNAS (IZIPAY / CULQI)
            // -----------------------------------------------------------------
            $electronicGateways = ['izipay', 'culqi'];
            if (in_array(strtolower((string)$order->payment_method), $electronicGateways, true)) {
                $totalElectronicOrders++;

                if (empty($order->transaction_id)) {
                    $anomalies[] = [
                        'tipo' => 'PAGO_EXTERNO_SIN_REFERENCIA',
                        'referencia' => "Pedido #{$order->id} ({$order->payment_method})",
                        'detalle' => "Pedido pagado vía {$order->payment_method} sin identificador de transacción externa.",
                        'severidad' => 'ALTA',
                    ];
                } else {
                    $matchedGateways++;

                    // Verificar consistencia de monto en el payload registrado
                    if (!empty($order->payment_payload) && is_array($order->payment_payload)) {
                        $payload = $order->payment_payload;
                        $gatewayAmountCents = null;

                        if (isset($payload['orderDetails']['orderTotalAmount'])) {
                            $gatewayAmountCents = (int)$payload['orderDetails']['orderTotalAmount'];
                        } elseif (isset($payload['amount'])) {
                            $gatewayAmountCents = (int)$payload['amount'];
                        }

                        if ($gatewayAmountCents !== null) {
                            $expectedCents = (int)round($order->total * 100);
                            if (abs($gatewayAmountCents - $expectedCents) > 1) {
                                $anomalies[] = [
                                    'tipo' => 'DISCREPANCIA_MONTO_PASARELA',
                                    'referencia' => "Pedido #{$order->id} / Tx: {$order->transaction_id}",
                                    'detalle' => "Monto pasarela (" . ($gatewayAmountCents / 100) . ") no coincide con pedido ({$order->total}).",
                                    'severidad' => 'CRÍTICA',
                                ];
                            }
                        }
                    }
                }
            }
        }

        // ---------------------------------------------------------------------
        // 3. AUDITORIA DE TRANSACCIONES EXTERNAS DUPLICADAS (ANTI-REPLAY / FRAUDE)
        // ---------------------------------------------------------------------
        $dupTransactions = DB::table('orders')
            ->select('transaction_id', DB::raw('COUNT(*) as total_count'))
            ->whereNotNull('transaction_id')
            ->where('transaction_id', '!=', '')
            ->groupBy('transaction_id')
            ->having('total_count', '>', 1)
            ->get();

        foreach ($dupTransactions as $dupTx) {
            $anomalies[] = [
                'tipo' => 'TRANSACCION_EXTERNA_DUPLICADA',
                'referencia' => "Tx: {$dupTx->transaction_id}",
                'detalle' => "Mismo ID de transacción externa asignado a {$dupTx->total_count} pedidos distintos.",
                'severidad' => 'CRÍTICA',
            ];
        }

        // ---------------------------------------------------------------------
        // 4. AUDITORIA DE DUPLICADOS EN CORRELATIVOS FISCALES (SERIE + CORRELATIVO)
        // ---------------------------------------------------------------------
        $duplicates = DB::table('sales')
            ->select('series', 'correlative', 'document_type', DB::raw('COUNT(*) as total_count'))
            ->groupBy('series', 'correlative', 'document_type')
            ->having('total_count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $anomalies[] = [
                'tipo' => 'CORRELATIVO_DUPLICADO',
                'referencia' => "{$dup->series}-{$dup->correlative}",
                'detalle' => "Documento {$dup->document_type} repetido {$dup->total_count} veces en la misma serie.",
                'severidad' => 'CRÍTICA',
            ];
        }

        // ---------------------------------------------------------------------
        // 5. REPORTE DE RESULTADOS DE CONCILIACION
        // ---------------------------------------------------------------------
        $this->line("<fg=yellow;options=bold>--- RESUMEN DE CONCILIACION FINANCIERA Y OPERATIVA ---</>");
        $this->table(
            ['Métrica de Conciliación', 'Valor Auditado'],
            [
                ['Pedidos Pagados Conciliados (Interno)', "{$matchedPaidOrders} / {$paidOrders->count()}"],
                ['Transacciones Pasarelas Validadas (Izipay/Culqi)', "{$matchedGateways} / {$totalElectronicOrders}"],
                ['Volumen Monetario Pagado (Orders)', 'S/. ' . number_format($totalVolumePaid, 2)],
                ['Volumen Monetario Emitido (Sales)', 'S/. ' . number_format($totalVolumeSales, 2)],
                ['Discrepancia Neta de Volumen', 'S/. ' . number_format(abs($totalVolumePaid - $totalVolumeSales), 2)],
                ['Anomalías Detectadas', count($anomalies) === 0 ? '<fg=green>0 (Conciliación perfecta)</>' : '<fg=red>' . count($anomalies) . '</>'],
            ]
        );

        if (!empty($anomalies)) {
            $this->newLine();
            $this->error("DETALLE DE ANOMALIAS DETECTADAS:");
            $this->table(['Tipo', 'Referencia', 'Detalle', 'Severidad'], $anomalies);
            return 1;
        }

        $this->newLine();
        $this->info("===============================================================================");
        $this->info("   CONCILIACION EXITOSA: CERO DISCREPANCIAS ENTRE PEDIDOS, PASARELAS Y VENTAS   ");
        $this->info("===============================================================================");

        return 0;
    }
}
