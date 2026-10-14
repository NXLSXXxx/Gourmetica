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
}
