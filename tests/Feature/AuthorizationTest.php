<?php

namespace Tests\Feature;

use App\Models\Headquarter;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected Role $adminRole;
    protected Role $sedeAdminRole;
    protected Role $cajeroRole;
    protected Role $ingenieroRole;
    protected Role $clienteRole;

    protected Headquarter $hq1;
    protected Headquarter $hq2;

    protected User $adminGeneral;
    protected User $adminSede1;
    protected User $cajero1;
    protected User $cajero2;
    protected User $ingeniero;
    protected User $cliente;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear roles del sistema
        $this->adminRole = Role::create(['name' => 'Admin General', 'slug' => 'admin_general']);
        $this->sedeAdminRole = Role::create(['name' => 'Admin Sede', 'slug' => 'admin_sede']);
        $this->cajeroRole = Role::create(['name' => 'Cajero', 'slug' => 'cajero']);
        $this->ingenieroRole = Role::create(['name' => 'Ingeniero', 'slug' => 'ingeniero']);
        $this->clienteRole = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);

        // 2. Crear sedes aisladas
        $this->hq1 = Headquarter::create([
            'name' => 'Sede Miraflores',
            'address' => 'Av. Larco 100',
            'city' => 'Lima',
            'is_active' => true,
        ]);

        $this->hq2 = Headquarter::create([
            'name' => 'Sede San Isidro',
            'address' => 'Av. Javier Prado 200',
            'city' => 'Lima',
            'is_active' => true,
        ]);

        // 3. Crear usuarios por rol
        $this->adminGeneral = User::create([
            'name' => 'Admin General',
            'email' => 'admin@gourmetica.pe',
            'password' => bcrypt('password123'),
            'role_id' => $this->adminRole->id,
            'headquarter_id' => null,
        ]);

        $this->adminSede1 = User::create([
            'name' => 'Admin Sede 1',
            'email' => 'adminsede1@gourmetica.pe',
            'password' => bcrypt('password123'),
            'role_id' => $this->sedeAdminRole->id,
            'headquarter_id' => $this->hq1->id,
        ]);

        $this->cajero1 = User::create([
            'name' => 'Cajero Sede 1',
            'email' => 'cajero1@gourmetica.pe',
            'password' => bcrypt('password123'),
            'role_id' => $this->cajeroRole->id,
            'headquarter_id' => $this->hq1->id,
        ]);

        $this->cajero2 = User::create([
            'name' => 'Cajero Sede 2',
            'email' => 'cajero2@gourmetica.pe',
            'password' => bcrypt('password123'),
            'role_id' => $this->cajeroRole->id,
            'headquarter_id' => $this->hq2->id,
        ]);

        $this->ingeniero = User::create([
            'name' => 'Ingeniero Sistemas',
            'email' => 'ingeniero@gourmetica.pe',
            'password' => bcrypt('password123'),
            'role_id' => $this->ingenieroRole->id,
            'headquarter_id' => null,
        ]);

        $this->cliente = User::create([
            'name' => 'Cliente Regular',
            'email' => 'cliente@gourmetica.pe',
            'password' => bcrypt('password123'),
            'role_id' => $this->clienteRole->id,
            'headquarter_id' => null,
        ]);

        // 4. Crear categoría y producto de prueba
        $category = \App\Models\Category::create([
            'name' => 'Platos de Fondo',
            'slug' => 'platos-de-fondo',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Lomo Saltado',
            'slug' => 'lomo-saltado',
            'description' => 'Plato criollo',
            'base_price' => 45.00,
            'is_active' => true,
        ]);

        $this->product->headquarters()->attach([
            $this->hq1->id => ['price' => 45.00, 'stock' => 10, 'is_available' => true],
            $this->hq2->id => ['price' => 50.00, 'stock' => 10, 'is_available' => true],
        ]);
    }

    /**
     * Matriz: Configuración, Usuarios y Sedes solo accesibles por Admin General.
     */
    public function test_admin_general_can_access_management_modules(): void
    {
        $this->actingAs($this->adminGeneral, 'admin');

        $this->get(route('admin.settings.index'))->assertStatus(200);
        $this->get(route('admin.users.index'))->assertStatus(200);
        $this->get(route('admin.headquarters.index'))->assertStatus(200);
        $this->get(route('admin.delivery_zones.index'))->assertStatus(200);
    }

    /**
     * Matriz: Admin Sede, Cajero e Ingeniero tienen prohibido acceder a Configuración, Usuarios y Sedes (403 Forbidden).
     */
    public function test_non_admins_forbidden_from_configuration_users_and_headquarters(): void
    {
        $unauthorizedUsers = [$this->adminSede1, $this->cajero1, $this->ingeniero];

        foreach ($unauthorizedUsers as $user) {
            $this->actingAs($user, 'admin');

            $this->get(route('admin.settings.index'))->assertStatus(403);
            $this->get(route('admin.users.index'))->assertStatus(403);
            $this->get(route('admin.headquarters.index'))->assertStatus(403);
            $this->get(route('admin.delivery_zones.index'))->assertStatus(403);
        }
    }

    /**
     * Matriz: Solo el Ingeniero puede acceder a Backups, Logs y Auditorías.
     */
    public function test_only_engineer_can_access_backups_logs_and_audits(): void
    {
        // Ingeniero tiene acceso permitido
        $this->actingAs($this->ingeniero, 'admin');
        $this->get(route('admin.logs.index'))->assertStatus(200);
        $this->get(route('admin.backups.index'))->assertStatus(200);
        $this->get(route('admin.audits.index'))->assertStatus(200);

        // Admin General, Admin Sede y Cajero deben ser rechazados con 403 Forbidden
        $forbiddenUsers = [$this->adminGeneral, $this->adminSede1, $this->cajero1];
        foreach ($forbiddenUsers as $user) {
            $this->actingAs($user, 'admin');

            $this->get(route('admin.logs.index'))->assertStatus(403);
            $this->get(route('admin.backups.index'))->assertStatus(403);
            $this->get(route('admin.audits.index'))->assertStatus(403);
        }
    }

    /**
     * Matriz: El Ingeniero tiene prohibido operar ventas, pedidos y operaciones de caja (403 Forbidden).
     */
    public function test_engineer_cannot_access_orders_sales_or_cashier(): void
    {
        $this->actingAs($this->ingeniero, 'admin');

        $this->get(route('admin.orders.index'))->assertStatus(403);
        $this->get(route('admin.sales.index'))->assertStatus(403);
        $this->get(route('admin.live_orders.index'))->assertStatus(403);
    }

    /**
     * Aislamiento por Sede: Cajero y Admin Sede solo pueden ver pedidos de su propia sede.
     */
    public function test_headquarter_isolation_prevents_viewing_other_sede_orders(): void
    {
        $orderHq1 = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq1->id,
            'total' => 45.00,
            'status' => 'pending',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
        ]);

        $orderHq2 = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq2->id,
            'total' => 50.00,
            'status' => 'pending',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
        ]);

        // Cajero 1 puede ver pedido de su sede
        $this->actingAs($this->cajero1, 'admin');
        $this->get(route('admin.orders.show', $orderHq1->id))->assertStatus(200);

        // Cajero 1 recibe 403 Forbidden al intentar ver pedido de Sede 2 por URL directa
        $this->get(route('admin.orders.show', $orderHq2->id))->assertStatus(403);

        // Cajero 1 recibe 403 Forbidden al intentar imprimir ticket de Sede 2
        $this->get(route('admin.orders.ticket', $orderHq2->id))->assertStatus(403);
    }

    /**
     * Aislamiento por Sede: Intento de modificar estado o cancelar pedido de otra sede devuelve 403 Forbidden.
     */
    public function test_headquarter_isolation_prevents_modifying_or_cancelling_other_sede_orders(): void
    {
        $orderHq2 = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq2->id,
            'total' => 50.00,
            'status' => 'pending',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
        ]);

        $this->actingAs($this->cajero1, 'admin');

        // Intento directo vía patch
        $response1 = $this->patch(route('admin.orders.update_status', $orderHq2->id), [
            'status' => 'delivered'
        ]);
        $response1->assertStatus(403);

        // Intento vía endpoint AJAX de live-orders update status
        $response2 = $this->postJson(route('admin.live_orders.update_status'), [
            'order_id' => $orderHq2->id,
            'status' => 'preparing'
        ]);
        $response2->assertStatus(403);

        // Intento vía endpoint AJAX de live-orders cancel
        $response3 = $this->postJson(route('admin.live_orders.cancel'), [
            'order_id' => $orderHq2->id,
        ]);
        $response3->assertStatus(403);

        // Asegurar que el pedido en Sede 2 permanezca intacto en pending
        $this->assertEquals('pending', $orderHq2->fresh()->status);
    }

    /**
     * Aislamiento por Sede: Manipulación de parámetro hq_id en consultas AJAX de live-orders devuelve 403 Forbidden.
     */
    public function test_live_orders_rejects_manipulated_hq_id_parameter(): void
    {
        $this->actingAs($this->cajero1, 'admin');

        // Petición legítima a su propia sede
        $okResponse = $this->get(route('admin.live_orders.index'));
        $okResponse->assertStatus(200);

        // Intento de evadir restricciones pasando ?hq_id={hq2}
        $tamperedResponse = $this->get(route('admin.live_orders.index', ['hq_id' => $this->hq2->id]));
        $tamperedResponse->assertStatus(403);

        // Intento de evadir restricciones vía JSON AJAX
        $tamperedAjax = $this->getJson(route('admin.live_orders.index', ['hq_id' => $this->hq2->id]));
        $tamperedAjax->assertStatus(403);
    }

    /**
     * Aislamiento por Sede: Registro de venta directa o rápida en otra sede devuelve 403 Forbidden.
     */
    public function test_cashier_cannot_store_sales_in_another_headquarter(): void
    {
        $this->actingAs($this->cajero1, 'admin');

        // Intento de venta directa alterando headquarter_id a Sede 2
        $response1 = $this->post(route('admin.sales.store_direct'), [
            'headquarter_id' => $this->hq2->id,
            'user_id' => $this->cliente->id,
            'document_type' => '03',
            'products' => [
                ['id' => $this->product->id, 'quantity' => 1]
            ]
        ]);
        $response1->assertStatus(403);

        // Intento de compra rápida en vivo alterando headquarter_id a Sede 2
        $response2 = $this->postJson(route('admin.live_orders.store'), [
            'headquarter_id' => $this->hq2->id,
            'user_id' => $this->cliente->id,
            'document_type' => '03',
            'total' => 50.00,
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1, 'price' => 50.00]
            ]
        ]);
        $response2->assertStatus(403);
    }

    /**
     * Admin General puede acceder y gestionar todas las sedes sin restricciones de aislamiento.
     */
    public function test_admin_general_can_operate_across_all_headquarters(): void
    {
        $this->actingAs($this->adminGeneral, 'admin');

        $orderHq1 = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq1->id,
            'total' => 45.00,
            'status' => 'pending',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
        ]);

        $orderHq2 = Order::create([
            'user_id' => $this->cliente->id,
            'headquarter_id' => $this->hq2->id,
            'total' => 50.00,
            'status' => 'pending',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
        ]);

        // Puede ver y consultar pedidos de ambas sedes
        $this->get(route('admin.orders.show', $orderHq1->id))->assertStatus(200);
        $this->get(route('admin.orders.show', $orderHq2->id))->assertStatus(200);

        // Puede consultar live-orders de cualquier sede vía parámetro hq_id
        $this->get(route('admin.live_orders.index', ['hq_id' => $this->hq1->id]))->assertStatus(200);
        $this->get(route('admin.live_orders.index', ['hq_id' => $this->hq2->id]))->assertStatus(200);
    }

    /**
     * Regresión Observación P2.A:
     * SalePolicy separa la apertura de vista/formulario (createAny) de la autorización
     * efectiva de escritura (create), denegando explícitamente cualquier creación con headquarter_id nulo (Fail-Closed).
     */
    public function test_sale_policy_fails_closed_on_null_headquarter_and_separates_create_any(): void
    {
        // 1. Apertura de interfaz (createAny): permitido a Admin General, Admin Sede y Cajero
        $this->assertTrue($this->adminGeneral->can('createAny', Sale::class));
        $this->assertTrue($this->adminSede1->can('createAny', Sale::class));
        $this->assertTrue($this->cajero1->can('createAny', Sale::class));
        $this->assertFalse($this->ingeniero->can('createAny', Sale::class));
        $this->assertFalse($this->cliente->can('createAny', Sale::class));

        // 2. Escritura efectiva (create): con headquarter_id = null o <= 0 debe denegar estrictamente (Fail-Closed)
        // Ni el Cajero, ni el Admin de Sede, ni el Admin General pueden registrar una venta sin sede válida asignada
        $this->assertFalse($this->cajero1->can('create', [Sale::class, null]));
        $this->assertFalse($this->cajero1->can('create', [Sale::class, 0]));
        $this->assertFalse($this->cajero1->can('create', [Sale::class, -1]));
        $this->assertFalse($this->adminSede1->can('create', [Sale::class, null]));
        $this->assertFalse($this->adminGeneral->can('create', [Sale::class, null]));

        // 3. Escritura con sede válida asignada: permitido solo para su propia sede o admin general
        $this->assertTrue($this->cajero1->can('create', [Sale::class, $this->hq1->id]));
        $this->assertFalse($this->cajero1->can('create', [Sale::class, $this->hq2->id]));
        $this->assertTrue($this->adminGeneral->can('create', [Sale::class, $this->hq1->id]));
        $this->assertTrue($this->adminGeneral->can('create', [Sale::class, $this->hq2->id]));
    }
}
