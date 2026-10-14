<?php

namespace Tests\Unit;

use App\Models\Headquarter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\SunatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SunatServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que montoLetras convierta números a su representación literal en español con centavos.
     */
    public function test_monto_letras_converts_amounts_accurately(): void
    {
        $service = new SunatService();

        $this->assertEquals('CERO CON 00/100', $service->montoLetras(0));
        $this->assertEquals('UN CON 00/100', $service->montoLetras(1));
        $this->assertEquals('QUINCE CON 50/100', $service->montoLetras(15.50));
        $this->assertEquals('VEINTICINCO CON 20/100', $service->montoLetras(25.20));
        $this->assertEquals('CIEN CON 00/100', $service->montoLetras(100));
        $this->assertEquals('CIENTO CINCUENTA CON 00/100', $service->montoLetras(150));
        $this->assertEquals('CIENTO OCHENTA Y CINCO CON 50/100', $service->montoLetras(185.50));
        $this->assertEquals('MIL CON 00/100', $service->montoLetras(1000));
        $this->assertEquals('MIL DOSCIENTOS CINCUENTA CON 75/100', $service->montoLetras(1250.75));
    }

    /**
     * Prueba que buildInvoice construya un comprobante electrónico UBL 2.1 con productos reales,
     * desglose de IGV, datos del cliente y leyendas tributarias correctas.
     */
    public function test_build_invoice_creates_greenter_invoice_with_real_items_and_taxes(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $hq = Headquarter::create(['name' => 'Sede Central', 'address' => 'Av. Larco 500', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create([
            'name' => 'Juan Perez',
            'email' => 'juan@test.local',
            'phone' => '45678912', // DNI 8 dígitos
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
        ]);

        $cat = \App\Models\Category::create(['name' => 'Pastelería', 'slug' => 'pasteleria']);

        $product1 = Product::create([
            'category_id' => $cat->id,
            'name' => 'Torta de Chocolate Gourmet',
            'sku' => 'TORT-CHOC-01',
            'base_price' => 50.00,
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'category_id' => $cat->id,
            'name' => 'Cheesecake de Frutos Rojos',
            'sku' => 'CHEESE-FR-02',
            'base_price' => 30.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Juan Perez Garcia',
            'headquarter_id' => $hq->id,
            'total' => 90.00, // 50 + 30 + 10 delivery
            'status' => 'preparing',
            'address' => 'Calle Los Pinos 123',
            'payment_method' => 'izipay',
            'payment_status' => 'paid',
            'delivery_price' => 10.00,
            'invoice_type' => 'boleta',
            'invoice_document' => '45678912',
            'stock_decremented' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'price' => 50.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 30.00,
        ]);

        $sale = Sale::createFromOrder($order);

        $service = new SunatService();
        $invoice = $service->buildInvoice($sale);

        // 1. Cabecera fiscal
        $this->assertEquals('2.1', $invoice->getUblVersion());
        $this->assertEquals('03', $invoice->getTipoDoc()); // Boleta
        $this->assertEquals('B001', $invoice->getSerie());
        $this->assertEquals('PEN', $invoice->getTipoMoneda());

        // 2. Cliente real
        $client = $invoice->getClient();
        $this->assertNotNull($client);
        $this->assertEquals('1', $client->getTipoDoc()); // DNI
        $this->assertEquals('45678912', $client->getNumDoc());
        $this->assertEquals('Juan Perez Garcia', $client->getRznSocial());

        // 3. Detalle de ítems reales (2 productos + 1 servicio de delivery)
        $details = $invoice->getDetails();
        $this->assertCount(3, $details);

        // Ítem 1: Torta de Chocolate
        $this->assertEquals('P' . str_pad((string)$product1->id, 4, '0', STR_PAD_LEFT), $details[0]->getCodProducto());
        $this->assertEquals('Torta de Chocolate Gourmet', $details[0]->getDescripcion());
        $this->assertEquals(1, $details[0]->getCantidad());
        $this->assertEquals(50.00, $details[0]->getMtoPrecioUnitario());

        // Ítem 2: Cheesecake
        $this->assertEquals('P' . str_pad((string)$product2->id, 4, '0', STR_PAD_LEFT), $details[1]->getCodProducto());
        $this->assertEquals('Cheesecake de Frutos Rojos', $details[1]->getDescripcion());
        $this->assertEquals(1, $details[1]->getCantidad());
        $this->assertEquals(30.00, $details[1]->getMtoPrecioUnitario());

        // Ítem 3: Delivery
        $this->assertEquals('SERV-DELIVERY', $details[2]->getCodProducto());
        $this->assertEquals('Servicio de Delivery', $details[2]->getDescripcion());
        $this->assertEquals(10.00, $details[2]->getMtoPrecioUnitario());

        // 4. Totales tributarios consistentes (Suma de líneas conforme a UBL 2.1)
        $expectedGravada = round(round(50.00 / 1.18, 2) + round(30.00 / 1.18, 2) + round(10.00 / 1.18, 2), 2);
        $expectedIgv = round(90.00 - $expectedGravada, 2);

        $this->assertEquals(90.00, $invoice->getMtoImpVenta());
        $this->assertEquals(90.00, $invoice->getSubTotal());
        $this->assertEquals($expectedGravada, $invoice->getMtoOperGravadas());
        $this->assertEquals($expectedIgv, $invoice->getMtoIGV());

        // 5. Leyenda en letras dinámica
        $legends = $invoice->getLegends();
        $this->assertCount(1, $legends);
        $this->assertEquals('1000', $legends[0]->getCode());
        $this->assertEquals('SON NOVENTA CON 00/100 SOLES', $legends[0]->getValue());
    }

    /**
     * Prueba que buildInvoice soporte Factura (01/F001) con RUC de empresa compradora.
     */
    public function test_build_invoice_creates_factura_with_ruc_for_business_clients(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $hq = Headquarter::create(['name' => 'Sede Central', 'address' => 'Av. Larco 500', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create([
            'name' => 'Corporacion Andina S.A.C.',
            'email' => 'corp@andina.local',
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
        ]);

        $cat = \App\Models\Category::create(['name' => 'Catering', 'slug' => 'catering']);
        $product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Banquete Empresarial',
            'sku' => 'BANQ-001',
            'base_price' => 200.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Corporacion Andina S.A.C.',
            'headquarter_id' => $hq->id,
            'total' => 200.00,
            'status' => 'preparing',
            'payment_method' => 'izipay',
            'payment_status' => 'paid',
            'invoice_type' => 'factura',
            'invoice_document' => '20601234567', // RUC válido
            'stock_decremented' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 200.00,
        ]);

        $sale = Sale::createFromOrder($order);

        $service = new SunatService();
        $invoice = $service->buildInvoice($sale);

        $this->assertEquals('01', $invoice->getTipoDoc()); // Factura
        $this->assertEquals('F001', $invoice->getSerie());
        $this->assertEquals('6', $invoice->getClient()->getTipoDoc()); // RUC
        $this->assertEquals('20601234567', $invoice->getClient()->getNumDoc());
        $this->assertEquals('Corporacion Andina S.A.C.', $invoice->getClient()->getRznSocial());
        $this->assertEquals('SON DOSCIENTOS CON 00/100 SOLES', $invoice->getLegends()[0]->getValue());
    }

    /**
     * Prueba que intentar generar una Factura (01) sin un RUC válido de 11 dígitos
     * lance InvalidArgumentException y NUNCA use valores de respaldo ficticios.
     */
    public function test_factura_without_valid_11_digit_ruc_throws_exception_without_fictive_fallback(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $hq = Headquarter::create(['name' => 'Sede Central', 'address' => 'Av. Larco 500', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create([
            'name' => 'Empresa Sin RUC',
            'email' => 'sinruc@test.local',
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
        ]);

        $cat = \App\Models\Category::create(['name' => 'Catering', 'slug' => 'catering']);
        $product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Coffee Break',
            'sku' => 'CB-001',
            'base_price' => 150.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Empresa Sin RUC',
            'headquarter_id' => $hq->id,
            'total' => 150.00,
            'status' => 'preparing',
            'payment_method' => 'izipay',
            'payment_status' => 'paid',
            'invoice_type' => 'factura',
            'invoice_document' => '12345678', // 8 dígitos (DNI, inválido para Factura tipo 01)
            'stock_decremented' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 150.00,
        ]);

        $sale = Sale::createFromOrder($order);

        $service = new SunatService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No se puede emitir una Factura electrónica (tipo 01) sin un RUC válido de 11 dígitos");

        $service->buildInvoice($sale);
    }

    /**
     * Prueba que intentar generar una Factura (01) sin Razón Social lance InvalidArgumentException.
     */
    public function test_factura_without_business_name_throws_exception(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $hq = Headquarter::create(['name' => 'Sede Central', 'address' => 'Av. Larco 500', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create([
            'name' => '   ',
            'email' => 'noname@test.local',
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
        ]);

        $cat = \App\Models\Category::create(['name' => 'Catering', 'slug' => 'catering']);
        $product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Coffee Break',
            'sku' => 'CB-002',
            'base_price' => 150.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => '   ',
            'headquarter_id' => $hq->id,
            'total' => 150.00,
            'status' => 'preparing',
            'payment_method' => 'izipay',
            'payment_status' => 'paid',
            'invoice_type' => 'factura',
            'invoice_document' => '20601234567',
            'stock_decremented' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 150.00,
        ]);

        $sale = Sale::createFromOrder($order);

        $service = new SunatService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("No se puede emitir una Factura electrónica sin la Razón Social");

        $service->buildInvoice($sale);
    }

    /**
     * Prueba la regla tributaria SUNAT para Boleta de Venta:
     * Si el importe es igual o mayor a S/ 700.00, es obligatoria la identificación del comprador.
     */
    public function test_boleta_equal_or_greater_than_700_without_identification_throws_exception(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $hq = Headquarter::create(['name' => 'Sede Central', 'address' => 'Av. Larco 500', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create([
            'name' => 'Cliente Anonimo',
            'email' => 'anon@test.local',
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
        ]);

        $cat = \App\Models\Category::create(['name' => 'Eventos', 'slug' => 'eventos']);
        $product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Buffet Completo Premium',
            'sku' => 'BUF-001',
            'base_price' => 750.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Cliente Anonimo',
            'headquarter_id' => $hq->id,
            'total' => 750.00,
            'status' => 'preparing',
            'payment_method' => 'izipay',
            'payment_status' => 'paid',
            'invoice_type' => 'boleta',
            'invoice_document' => null, // Sin documento
            'stock_decremented' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 750.00,
        ]);

        $sale = Sale::createFromOrder($order);

        $service = new SunatService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("SUNAT exige identificación obligatoria (DNI o RUC) para boletas de venta con importe igual o superior a S/ 700.00");

        $service->buildInvoice($sale);
    }

    /**
     * Prueba que una boleta menor a S/ 700.00 sin documento sea aceptada legalmente con tipo 0 'CLIENTES VARIOS'.
     */
    public function test_boleta_under_700_allows_clientes_varios_without_document(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $hq = Headquarter::create(['name' => 'Sede Central', 'address' => 'Av. Larco 500', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create([
            'name' => 'Comensal en Mesa',
            'email' => 'comensal@test.local',
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
        ]);

        $cat = \App\Models\Category::create(['name' => 'Pastelería', 'slug' => 'pasteleria']);
        $product = Product::create([
            'category_id' => $cat->id,
            'name' => 'Tarta de Fresa',
            'sku' => 'TF-001',
            'base_price' => 45.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Comensal en Mesa',
            'headquarter_id' => $hq->id,
            'total' => 45.00,
            'status' => 'preparing',
            'payment_method' => 'efectivo',
            'payment_status' => 'paid',
            'invoice_type' => 'boleta',
            'invoice_document' => null,
            'stock_decremented' => true,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 45.00,
        ]);

        $sale = Sale::createFromOrder($order);

        $service = new SunatService();
        $invoice = $service->buildInvoice($sale);

        $this->assertEquals('03', $invoice->getTipoDoc());
        $this->assertEquals('0', $invoice->getClient()->getTipoDoc());
        $this->assertEquals('00000000', $invoice->getClient()->getNumDoc());
        $this->assertEquals('Comensal en Mesa', $invoice->getClient()->getRznSocial());
    }

    /**
     * Prueba que intentar generar un comprobante para una venta sin ítems lance InvalidArgumentException,
     * impidiendo líneas genéricas ficticias como 'SERV-001'.
     */
    public function test_sale_without_items_throws_exception_preventing_fictive_line(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $hq = Headquarter::create(['name' => 'Sede Central', 'address' => 'Av. Larco 500', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create([
            'name' => 'Cliente Directo',
            'email' => 'direct@test.local',
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
        ]);

        $sale = Sale::create([
            'user_id' => $user->id,
            'headquarter_id' => $hq->id,
            'document_type' => '03',
            'series' => 'B001',
            'correlative' => 999,
            'total' => 50.00,
            'status' => 'completed',
            'sunat_status' => 'pending',
            'order_id' => null, // Sin pedido asociado ni ítems
        ]);

        $service = new SunatService();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("no tiene productos o ítems registrados. SUNAT prohíbe emitir comprobantes sin líneas de detalle de productos reales");

        $service->buildInvoice($sale);
    }

    /**
     * Prueba que declareToSunat preserve la venta en estado PENDIENTE_CORRECCION
     * con el detalle de la infracción y sin enviar datos corruptos o ficticios a SUNAT.
     */
    public function test_declare_to_sunat_preserves_sale_in_pendiente_correccion_on_validation_failure(): void
    {
        $role = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);
        $hq = Headquarter::create(['name' => 'Sede Central', 'address' => 'Av. Larco 500', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create([
            'name' => 'Empresa RUC Trunco',
            'email' => 'trunco@test.local',
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'customer_name' => 'Empresa RUC Trunco',
            'headquarter_id' => $hq->id,
            'total' => 100.00,
            'status' => 'preparing',
            'payment_method' => 'izipay',
            'payment_status' => 'paid',
            'invoice_type' => 'factura',
            'invoice_document' => '123', // RUC trunco (inválido para Factura)
            'stock_decremented' => true,
        ]);

        $sale = Sale::createFromOrder($order);

        $adminRole = Role::create(['name' => 'Admin General', 'slug' => 'admin_general']);
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admintest@gourmetica.local',
            'password' => bcrypt('secret'),
            'role_id' => $adminRole->id,
        ]);

        $this->actingAs($admin, 'admin');

        $controller = new \App\Http\Controllers\Admin\SaleController();
        $controller->declareToSunat($sale->id, new SunatService());

        $sale->refresh();
        $this->assertEquals('PENDIENTE_CORRECCION', $sale->sunat_status);
        $this->assertStringContainsString('Rechazo de validación tributaria', $sale->sunat_response);
        $this->assertStringContainsString('sin un RUC válido de 11 dígitos', $sale->sunat_response);
    }
}
