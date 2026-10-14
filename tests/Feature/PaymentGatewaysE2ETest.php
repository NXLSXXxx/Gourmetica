<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Headquarter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewaysE2ETest extends TestCase
{
    use RefreshDatabase;

    protected User $cliente;
    protected Headquarter $hq;
    protected Product $product;
    protected string $testHmacKey = 'test_secret_hmac_key_12345';

    protected function setUp(): void
    {
        parent::setUp();

        $clienteRole = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);

        $this->hq = Headquarter::create([
            'name' => 'Sede Sandbox Miraflores',
            'address' => 'Av. Larco 456',
            'city' => 'Lima',
            'is_active' => true,
        ]);

        $this->cliente = User::create([
            'name' => 'Cliente Sandbox',
            'email' => 'cliente.sandbox@test.pe',
            'password' => bcrypt('password123'),
            'role_id' => $clienteRole->id,
            'headquarter_id' => null,
        ]);

        $category = Category::create([
            'name' => 'Platos E2E',
            'slug' => 'platos-e2e',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Plato Gourmet Sandbox',
            'slug' => 'plato-gourmet-sandbox',
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        $this->product->headquarters()->attach([
            $this->hq->id => ['price' => 50.00, 'stock' => 10, 'is_available' => true],
        ]);

        // Configurar credenciales sandbox de Izipay y Nakama en Setting
        Setting::set('izipay_shop_id', '12345678');
        Setting::set('izipay_password', 'testpassword');
        Setting::set('izipay_public_key', '12345678:publickey_test');
        Setting::set('izipay_hmac_sha256', $this->testHmacKey);
        Setting::set('nakama_webhook_secret', 'test_nakama_secret_token_999');
    }

    /**
     * Helper para preparar sesión con carrito de compras
     */
    protected function getCartSession(): array
    {
        return [
            'cart' => [
                $this->product->id => [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'price' => 50.00,
                    'quantity' => 1,
                    'options' => [],
                ]
            ]
        ];
    }

    /**
     * Escenario 1: Izipay Checkout exitoso con firma HMAC auténtica
     * Confirma pedido como PAID, descuenta inventario atómicamente y genera venta consecutiva.
     */
    public function test_izipay_checkout_success_with_valid_hmac_confirms_payment_and_creates_single_sale(): void
    {
        $this->actingAs($this->cliente);

        $order = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente Sandbox',
            'total' => 50.00,
            'status' => 'pending',
            'payment_method' => 'izipay',
            'payment_status' => 'pending',
            'stock_decremented' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 50.00,
        ]);

        $initialStock = $this->product->getStockForHeadquarter($this->hq->id);

        // Generar respuesta JSON simulada de Izipay (PAID, 50.00 PEN = 5000 centavos)
        $rawAnswerArray = [
            'orderStatus' => 'PAID',
            'orderDetails' => [
                'orderId' => 'GOURMETICA-' . $order->id,
                'orderCurrency' => 'PEN',
                'orderTotalAmount' => 5000,
            ],
        ];
        $rawAnswer = json_encode($rawAnswerArray);
        $validHash = hash_hmac('sha256', $rawAnswer, $this->testHmacKey);

        // Simular llamada de checkout que finaliza el pago con Izipay
        $sessionData = array_merge($this->getCartSession(), ['izipay_pending_order_id' => $order->id]);
        $response = $this->withSession($sessionData)->post(route('orders.store'), [
            'order_id' => $order->id,
            'headquarter_id' => $this->hq->id,
            'payment_method' => 'izipay',
            'customer_name' => 'Cliente Sandbox',
            'phone' => '999888777',
            'shipping_type' => 'pickup',
            'izipay_raw_answer' => $rawAnswer,
            'izipay_hash' => $validHash,
        ]);

        $order->refresh();

        // 1. Estado de pedido actualizado a preparando y pagado
        $this->assertEquals('preparing', $order->status);
        $this->assertEquals('paid', $order->payment_status);

        // 2. Inventario descontado atómicamente exactamente una vez (de 10 a 9)
        $this->assertEquals($initialStock - 1, $this->product->getStockForHeadquarter($this->hq->id));
        $this->assertTrue((bool)$order->stock_decremented);

        // 3. Venta registrada con correlativo único
        $this->assertEquals(1, Sale::where('order_id', $order->id)->count());
        $sale = Sale::where('order_id', $order->id)->first();
        $this->assertEquals(50.00, (float)$sale->total);
        $this->assertEquals('completed', $sale->status);
    }

    /**
     * Escenario 2: Izipay Checkout con HMAC alterado o inválido
     * Debe rechazar la confirmación y no generar alteraciones financieras ni descontar stock.
     */
    public function test_izipay_checkout_rejects_tampered_or_invalid_hmac(): void
    {
        $this->actingAs($this->cliente);

        $order = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente Sandbox',
            'total' => 50.00,
            'status' => 'pending',
            'payment_method' => 'izipay',
            'payment_status' => 'pending',
            'stock_decremented' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 50.00,
        ]);

        $initialStock = $this->product->getStockForHeadquarter($this->hq->id);

        $rawAnswerArray = [
            'orderStatus' => 'PAID',
            'orderDetails' => [
                'orderId' => 'GOURMETICA-' . $order->id,
                'orderCurrency' => 'PEN',
                'orderTotalAmount' => 5000,
            ],
        ];
        $rawAnswer = json_encode($rawAnswerArray);
        $fakeHash = 'invalid_tampered_hash_signature_attempt_999999999';

        $sessionData = array_merge($this->getCartSession(), ['izipay_pending_order_id' => $order->id]);
        $response = $this->withSession($sessionData)->post(route('orders.store'), [
            'order_id' => $order->id,
            'headquarter_id' => $this->hq->id,
            'payment_method' => 'izipay',
            'customer_name' => 'Cliente Sandbox',
            'phone' => '999888777',
            'shipping_type' => 'pickup',
            'izipay_raw_answer' => $rawAnswer,
            'izipay_hash' => $fakeHash,
        ]);

        $order->refresh();

        // El pedido NO debe ser marcado como pagado
        $this->assertNotEquals('paid', $order->payment_status);
        $this->assertNotEquals('preparing', $order->status);

        // Cero descuentos de inventario
        $this->assertEquals($initialStock, $this->product->getStockForHeadquarter($this->hq->id));
        $this->assertFalse((bool)$order->stock_decremented);

        // Cero ventas generadas
        $this->assertEquals(0, Sale::where('order_id', $order->id)->count());
    }

    /**
     * Escenario 3: Idempotencia en IPN Webhook de Izipay (Notificaciones repetidas de pasarela)
     * Reintentos de webhook deben responder 200 OK sin duplicar venta ni descontar inventario doble.
     */
    public function test_izipay_webhook_ipn_idempotency_prevents_duplicate_sales_and_stock_reductions(): void
    {
        $order = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente Sandbox',
            'total' => 50.00,
            'status' => 'pending',
            'payment_method' => 'izipay',
            'payment_status' => 'pending',
            'stock_decremented' => false,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 50.00,
        ]);

        $initialStock = $this->product->getStockForHeadquarter($this->hq->id);

        $krAnswerArray = [
            'orderStatus' => 'PAID',
            'orderDetails' => [
                'orderId' => 'GOURMETICA-' . $order->id,
                'orderCurrency' => 'PEN',
                'orderTotalAmount' => 5000,
            ],
        ];
        $krAnswer = json_encode($krAnswerArray);
        $krHash = hash_hmac('sha256', $krAnswer, $this->testHmacKey);

        // 1. Primera notificación de webhook (IPN legítimo)
        $resp1 = $this->post(route('checkout.izipay_webhook'), [
            'kr-answer' => $krAnswer,
            'kr-hash' => $krHash,
        ]);
        $resp1->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals(1, Sale::where('order_id', $order->id)->count());
        $this->assertEquals($initialStock - 1, $this->product->getStockForHeadquarter($this->hq->id));

        // 2. Segunda notificación de webhook (Reintento de Izipay por timeout de red previo)
        $resp2 = $this->post(route('checkout.izipay_webhook'), [
            'kr-answer' => $krAnswer,
            'kr-hash' => $krHash,
        ]);
        $resp2->assertStatus(200);
        $resp2->assertSee('already paid');

        // 3. Tercera notificación idéntica
        $resp3 = $this->post(route('checkout.izipay_webhook'), [
            'kr-answer' => $krAnswer,
            'kr-hash' => $krHash,
        ]);
        $resp3->assertStatus(200);

        // Verificar que la venta sigue siendo estrictamente 1 (Idempotencia garantizada)
        $this->assertEquals(1, Sale::where('order_id', $order->id)->count());

        // Verificar que el stock solo se redujo una unidad (10 a 9) y NO a 8 o 7
        $this->assertEquals($initialStock - 1, $this->product->getStockForHeadquarter($this->hq->id));
    }

    /**
     * Escenario 4: Discrepancia de monto o moneda en Webhook de Izipay
     * Rechaza la confirmación con 400 si el monto o la moneda no coinciden con la orden.
     */
    public function test_izipay_webhook_rejects_currency_or_amount_discrepancy(): void
    {
        $order = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente Sandbox',
            'total' => 50.00,
            'status' => 'pending',
            'payment_method' => 'izipay',
            'payment_status' => 'pending',
            'stock_decremented' => false,
        ]);

        // Intento 1: Discrepancia de monto (reporta 10.00 soles en lugar de 50.00 soles)
        $tamperedAmountAnswer = json_encode([
            'orderStatus' => 'PAID',
            'orderDetails' => [
                'orderId' => 'GOURMETICA-' . $order->id,
                'orderCurrency' => 'PEN',
                'orderTotalAmount' => 1000, // 10.00 PEN en vez de 5000
            ],
        ]);
        $tamperedAmountHash = hash_hmac('sha256', $tamperedAmountAnswer, $this->testHmacKey);

        $resp1 = $this->post(route('checkout.izipay_webhook'), [
            'kr-answer' => $tamperedAmountAnswer,
            'kr-hash' => $tamperedAmountHash,
        ]);
        $resp1->assertStatus(400);

        // Intento 2: Discrepancia de moneda (reporta USD en vez de PEN)
        $tamperedCurrencyAnswer = json_encode([
            'orderStatus' => 'PAID',
            'orderDetails' => [
                'orderId' => 'GOURMETICA-' . $order->id,
                'orderCurrency' => 'USD',
                'orderTotalAmount' => 5000,
            ],
        ]);
        $tamperedCurrencyHash = hash_hmac('sha256', $tamperedCurrencyAnswer, $this->testHmacKey);

        $resp2 = $this->post(route('checkout.izipay_webhook'), [
            'kr-answer' => $tamperedCurrencyAnswer,
            'kr-hash' => $tamperedCurrencyHash,
        ]);
        $resp2->assertStatus(400);

        // Confirmar que la orden sigue pendiente y sin venta emitida
        $order->refresh();
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals(0, Sale::where('order_id', $order->id)->count());
    }

    /**
     * Escenario 5: Webhook de Nakama Delivery con autenticación fail-closed
     * Comprueba token de seguridad y previene alteración no autorizada de pagos en pasarelas electrónicas.
     */
    public function test_nakama_webhook_fail_closed_and_prevents_unauthorized_payment_override(): void
    {
        $order = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq->id,
            'customer_name' => 'Cliente Sandbox Delivery',
            'total' => 50.00,
            'status' => 'preparing',
            'payment_method' => 'izipay',
            'payment_status' => 'pending', // Pago electrónico aún no confirmado por Izipay
            'nakama_id' => 'NK-987654',
        ]);

        // 1. Intento sin token -> 401 Unauthorized
        $respNoToken = $this->postJson(route('webhook.nakama'), [
            'event' => 'order.status_updated',
            'pedido_id' => 'NK-987654',
            'estado' => 'entregado',
        ]);
        $respNoToken->assertStatus(401);

        // 2. Intento con token falso -> 401 Unauthorized
        $respFakeToken = $this->withHeaders(['X-Nakama-Token' => 'fake_secret_token'])
            ->postJson(route('webhook.nakama'), [
                'event' => 'order.status_updated',
                'pedido_id' => 'NK-987654',
                'estado' => 'entregado',
            ]);
        $respFakeToken->assertStatus(401);

        // 3. Intento auténtico pero para orden electrónica no confirmada
        // Al entregarse, Nakama solo marca 'paid' si el pago es contraentrega/efectivo.
        // En pasarelas electrónicas (Izipay), NO debe marcar 'paid' indebidamente.
        $respValid = $this->withHeaders(['X-Nakama-Token' => 'test_nakama_secret_token_999'])
            ->postJson(route('webhook.nakama'), [
                'event' => 'order.status_updated',
                'pedido_id' => 'NK-987654',
                'estado' => 'entregado',
            ]);
        $respValid->assertStatus(200);

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        // payment_status debe seguir siendo 'pending' porque es izipay y la pasarela no lo ha confirmado
        $this->assertEquals('pending', $order->payment_status);
    }
}
