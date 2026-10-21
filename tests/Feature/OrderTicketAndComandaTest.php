<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Headquarter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTicketAndComandaTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Headquarter $hq;
    protected Order $order;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create([
            'name' => 'Administrador General',
            'slug' => 'admin_general',
            'permissions' => ['*'],
        ]);

        $this->hq = Headquarter::create([
            'name' => 'Chiclayo Central',
            'address' => 'Torres Paz 567 Chiclayo',
            'city' => 'Chiclayo',
            'phone' => '950664655',
            'email' => 'chiclayo@gourmetica.pe',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'headquarter_id' => $this->hq->id,
        ]);

        $category = Category::create([
            'name' => 'Tortas Saludables',
            'slug' => 'tortas-saludables',
            'description' => 'Tortas sin azúcar refinada',
        ]);

        $this->product = Product::create([
            'name' => 'Torta de Chocolate Saludable',
            'slug' => 'torta-de-chocolate-saludable',
            'description' => 'Sin gluten y sin azúcar',
            'base_price' => 75.00,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $this->order = Order::create([
            'user_id' => $this->admin->id,
            'customer_name' => 'Mariana Dávila',
            'headquarter_id' => $this->hq->id,
            'total' => 85.00,
            'status' => 'pending',
            'address' => 'Av. Balta 1234, Chiclayo',
            'phone' => '979123456',
            'delivery_price' => 10.00,
            'delivery_date' => '2026-10-22',
            'delivery_time' => '04:00 PM',
            'invoice_type' => 'boleta',
            'invoice_document' => '72345678',
            'is_gift' => true,
            'has_card' => true,
            'has_dedication' => true,
        ]);

        OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $this->product->id,
            'quantity' => 1,
            'price' => 75.00,
            'options' => ['Dedicatoria' => 'Feliz Día Mamá', 'Tamaño' => 'Mediana'],
        ]);
    }

    public function test_order_formatted_comanda_text_matches_user_template(): void
    {
        $text = $this->order->getFormattedComandaText();

        $this->assertStringContainsString('Comanda: #' . str_pad($this->order->id, 5, '0', STR_PAD_LEFT), $text);
        $this->assertStringContainsString('Nombre Cliente: Mariana Dávila', $text);
        $this->assertStringContainsString('Detalle Pedido:', $text);
        $this->assertStringContainsString('Torta de Chocolate Saludable', $text);
        $this->assertStringContainsString('Dedicatoria: Feliz Día Mamá', $text);
        $this->assertStringContainsString('Fecha/Hora de entrega programada: 22/10/2026 - 04:00 PM', $text);
        $this->assertStringContainsString('Direccion y celular: Av. Balta 1234, Chiclayo | Cel: 979123456', $text);
        $this->assertStringContainsString('Regalo', $text);
    }

    public function test_order_formatted_invoice_text_matches_user_template(): void
    {
        $text = $this->order->getFormattedInvoiceText();

        $this->assertStringContainsString('Gourmetica', $text);
        $this->assertStringContainsString('Pastelería Saludable', $text);
        $this->assertStringContainsString('RUC: 10737725648', $text);
        $this->assertStringContainsString('Torres Paz 567 Chiclayo', $text);
        $this->assertStringContainsString('Fecha y Hora de emision:', $text);
        $this->assertStringContainsString('Boleta:', $text);
        $this->assertStringContainsString('Dni/RUC/ Nombre Cliente: 72345678 / Mariana Dávila', $text);
        $this->assertStringContainsString('Direccion: Av. Balta 1234, Chiclayo', $text);
        $this->assertStringContainsString('Detalle Pedido:', $text);
        $this->assertStringContainsString('Torta de Chocolate Saludable', $text);
        $this->assertStringContainsString('Total S/. 85.00', $text);
    }

    public function test_admin_can_view_ticket_and_comanda_views(): void
    {
        // 1. Ticket de Boleta (Formato Boleta/Factura con Logo)
        $responseTicket = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.ticket', ['id' => $this->order->id, 'type' => 'boleta']));

        $responseTicket->assertStatus(200);
        $responseTicket->assertSee('ticket_logo.png');
        $responseTicket->assertSee('GOURMETICA');
        $responseTicket->assertSee('Pastelería Saludable');
        $responseTicket->assertSee('10737725648');
        $responseTicket->assertSee('Torres Paz 567 Chiclayo');
        $responseTicket->assertSee('BOLETA DE VENTA');
        $responseTicket->assertSee('Mariana Dávila');
        $responseTicket->assertSee('85.00');

        // 2. Ticket de Comanda (Formato Comanda Cocina)
        $responseComanda = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.comanda', $this->order->id));

        $responseComanda->assertStatus(200);
        $responseComanda->assertSee('COMANDA DE PRODUCCIÓN');
        $responseComanda->assertSee('Nombre Cliente:');
        $responseComanda->assertSee('Mariana Dávila');
        $responseComanda->assertSee('Fecha/Hora de entrega programada:');
        $responseComanda->assertSee('22/10/2026 - 04:00 PM');
        $responseComanda->assertSee('Direccion y celular:');
        $responseComanda->assertSee('979123456');

        // 3. Detalle de Pedido (Show view con opciones de Comanda y Boleta y botones WhatsApp)
        $responseShow = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.orders.show', $this->order->id));

        $responseShow->assertStatus(200);
        $responseShow->assertSee('IMPRIMIR BOLETA');
        $responseShow->assertSee('IMPRIMIR COMANDA');
        $responseShow->assertSee('Formatos Comanda y Boleta / Factura');
        $responseShow->assertSee('Copiar Comanda');
        $responseShow->assertSee('Copiar Boleta');
    }
}
