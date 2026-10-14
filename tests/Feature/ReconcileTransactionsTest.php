<?php

namespace Tests\Feature;

use App\Models\Headquarter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReconcileTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Headquarter $hq;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $this->hq = Headquarter::create(['name' => 'Sede Conciliación', 'address' => 'Av. Central 100', 'city' => 'Lima', 'is_active' => true]);
        $this->user = User::create(['name' => 'User Conciliacion', 'email' => 'concil@test.local', 'password' => bcrypt('secret'), 'role_id' => $role->id]);
    }

    /**
     * Prueba que la conciliación reporte 0 anomalías cuando los pedidos pagados coinciden con sus ventas.
     */
    public function test_reconciliation_passes_when_all_orders_and_sales_match_perfectly(): void
    {
        $order1 = Order::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente A',
            'total' => 45.00,
            'status' => 'preparing',
            'payment_method' => 'izipay',
            'payment_status' => 'paid',
            'transaction_id' => 'izipay_tx_test_001',
            'payment_payload' => ['orderDetails' => ['orderTotalAmount' => 4500]],
            'stock_decremented' => true,
        ]);

        $sale1 = Sale::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'document_type' => '03',
            'series' => 'B001',
            'correlative' => 1001,
            'total' => 45.00,
            'status' => 'completed',
            'order_id' => $order1->id,
        ]);

        $order2 = Order::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente B',
            'total' => 60.00,
            'status' => 'preparing',
            'payment_method' => 'efectivo',
            'payment_status' => 'paid',
            'stock_decremented' => true,
        ]);

        $sale2 = Sale::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'document_type' => '03',
            'series' => 'B001',
            'correlative' => 1002,
            'total' => 60.00,
            'status' => 'completed',
            'order_id' => $order2->id,
        ]);

        $this->artisan('audit:reconcile')
            ->expectsOutputToContain('CONCILIACION INTERNA EXITOSA')
            ->expectsOutputToContain('0 (Conciliación perfecta)')
            ->assertExitCode(0);
    }

    /**
     * Prueba que la conciliación detecte discrepancias cuando existen pedidos pagados sin venta o montos alterados.
     */
    public function test_reconciliation_detects_anomalies_for_unmatched_or_tampered_sales(): void
    {
        // 1. Pedido pagado pero sin venta emitida
        $orphanedOrder = Order::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente Huerfano',
            'total' => 99.00,
            'status' => 'preparing',
            'payment_method' => 'efectivo',
            'payment_status' => 'paid',
            'stock_decremented' => false,
        ]);

        // 2. Pedido con discrepancia de monto respecto a la venta
        $mismatchedOrder = Order::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente Discrepancia',
            'total' => 100.00,
            'status' => 'preparing',
            'payment_method' => 'efectivo',
            'payment_status' => 'paid',
            'stock_decremented' => true,
        ]);

        Sale::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'document_type' => '03',
            'series' => 'B001',
            'correlative' => 2001,
            'total' => 80.00, // Discrepancia: 80 != 100
            'status' => 'completed',
            'order_id' => $mismatchedOrder->id,
        ]);

        $this->artisan('audit:reconcile')
            ->expectsOutputToContain('DETALLE DE ANOMALIAS DETECTADAS')
            ->expectsOutputToContain('PEDIDO_PAGADO_SIN_VENTA')
            ->expectsOutputToContain('DISCREPANCIA_MONTO')
            ->assertExitCode(1);
    }

    /**
     * Prueba que la conciliación detecte anomalías en pasarelas externas (falta de transaction_id o duplicados).
     */
    public function test_reconciliation_detects_external_gateway_anomalies(): void
    {
        // Pedido izipay pagado pero sin transaction_id
        $orderMissingTx = Order::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente Izipay Sin TX',
            'total' => 50.00,
            'status' => 'preparing',
            'payment_method' => 'izipay',
            'payment_status' => 'paid',
            'transaction_id' => null,
            'stock_decremented' => true,
        ]);

        Sale::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'document_type' => '03',
            'series' => 'B001',
            'correlative' => 3001,
            'total' => 50.00,
            'status' => 'completed',
            'order_id' => $orderMissingTx->id,
        ]);

        $this->artisan('audit:reconcile')
            ->expectsOutputToContain('PAGO_EXTERNO_SIN_REFERENCIA')
            ->assertExitCode(1);
    }
}
