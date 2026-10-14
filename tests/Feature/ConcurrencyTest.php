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

class ConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Headquarter $hq;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $this->hq = Headquarter::create([
            'name' => 'Sede Miraflores',
            'address' => 'Av. Larco 123',
            'city' => 'Lima',
            'phone' => '1234567',
            'email' => 'miraflores@test.pe',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Juan Pérez',
            'email' => 'juan@test.pe',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'headquarter_id' => $this->hq->id,
        ]);

        $category = \App\Models\Category::create(['name' => 'Tortas', 'slug' => 'tortas']);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Torta de Chocolate',
            'description' => 'Deliciosa torta',
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        $this->product->headquarters()->attach($this->hq->id, [
            'stock' => 10,
            'price' => 50.00,
            'is_available' => true,
        ]);
    }

    /**
     * Prueba que descuentos sucesivos o concurrentes respetan idempotencia y atomicidad.
     */
    public function test_concurrent_stock_decrements_are_atomic_and_idempotent(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Juan Pérez',
            'total' => 100.00,
            'status' => 'preparing',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
            'stock_decremented' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
            'price' => 50.00,
        ]);

        // Simular 5 llamadas concurrentes al descuento de inventario del mismo pedido
        for ($i = 0; $i < 5; $i++) {
            $order->decrementProductStock();
        }

        $stockFinal = $this->product->fresh()->getStockForHeadquarter($this->hq->id);

        // El stock inicial era 10, se compraron 2, por idempotencia debe ser exactamente 8 (no 0 ni negativo)
        $this->assertEquals(8, $stockFinal);
        $this->assertTrue((bool)$order->fresh()->stock_decremented);
    }

    /**
     * Prueba que la generación de correlativos y comprobantes previene duplicados.
     */
    public function test_concurrent_sale_correlative_generation_prevents_duplicates(): void
    {
        $order1 = Order::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente 1',
            'total' => 50.00,
            'status' => 'preparing',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
        ]);

        $order2 = Order::create([
            'user_id' => $this->user->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente 2',
            'total' => 80.00,
            'status' => 'preparing',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
        ]);

        // Múltiples intentos sobre order1 deben retornar exactamente la misma venta
        $sale1A = Sale::createFromOrder($order1);
        $sale1B = Sale::createFromOrder($order1);

        $this->assertEquals($sale1A->id, $sale1B->id);
        $this->assertEquals($sale1A->correlative, $sale1B->correlative);

        // Venta para order2 debe obtener correlativo correlativo consecutivo
        $sale2 = Sale::createFromOrder($order2);

        $this->assertNotEquals($sale1A->id, $sale2->id);
        $this->assertEquals($sale1A->correlative + 1, $sale2->correlative);
    }

    /**
     * Prueba que el recálculo de delivery en el servidor previene la inyección de precios arbitrarios del cliente.
     */
    public function test_server_delivery_calculation_rejects_client_tampering(): void
    {
        $this->actingAs($this->user);

        // Carrito con 1 producto
        session(['cart' => [
            'prod_1' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'price' => 50.00,
                'quantity' => 1,
                'options' => [],
            ]
        ]]);

        // El cliente intenta enviar un delivery de -10.00 o 1.00 falso en tipo pickup
        $response = $this->postJson(route('checkout.izipay_token'), [
            'shipping_type' => 'pickup',
            'headquarter_id' => $this->hq->id,
            'delivery_price' => -50.00, // Intento de manipulación
            'email' => 'juan@test.pe',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // En pickup el delivery calculado por el servidor debe ser exactamente 0.00
        $this->assertEquals(0.00, $data['deliveryPrice']);
        $this->assertEquals(50.00, $data['finalTotal']);

        // Se verifica que la orden persistida en BD tenga total exacto de 50.00 y no 0
        $order = Order::find($data['orderId']);
        $this->assertNotNull($order);
        $this->assertEquals(50.00, (float)$order->total);
        $this->assertEquals(0.00, (float)$order->delivery_price);
    }
}
