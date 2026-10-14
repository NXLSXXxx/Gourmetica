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

        // ---------------------------------------------------------------------
        // 1. CONCILIACION PEDIDO PAGADO -> VENTA EMITIDA
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
        }

        // ---------------------------------------------------------------------
        // 2. AUDITORIA DE DUPLICADOS EN CORRELATIVOS FISCALES (SERIE + CORRELATIVO)
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
        // 3. REPORTE DE RESULTADOS DE CONCILIACION
        // ---------------------------------------------------------------------
        $this->line("<fg=yellow;options=bold>--- RESUMEN DE CONCILIACION FINANCIERA ---</>");
        $this->table(
            ['Métrica de Conciliación', 'Valor Auditado'],
            [
                ['Pedidos Pagados Conciliados', "{$matchedPaidOrders} / {$paidOrders->count()}"],
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
        $this->info("   CONCILIACION EXITOSA: CERO DISCREPANCIAS ENTRE PEDIDOS, PAGOS Y VENTAS      ");
        $this->info("===============================================================================");

        return 0;
    }
}
