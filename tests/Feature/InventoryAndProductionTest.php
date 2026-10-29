<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Headquarter;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Role;
use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventoryAndProductionTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected Headquarter $hq;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::create(['name' => 'Admin General', 'slug' => 'admin_general']);
        $clientRole = Role::create(['name' => 'Cliente', 'slug' => 'cliente']);

        $this->hq = Headquarter::create([
            'name' => 'Sede Central Miraflores',
            'address' => 'Av. Larco 456',
            'city' => 'Lima',
            'phone' => '999888777',
            'email' => 'miraflores@gourmetica.pe',
            'is_active' => true,
        ]);

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@gourmetica.pe',
            'password' => bcrypt('password123'),
            'role_id' => $adminRole->id,
            'headquarter_id' => null,
        ]);

        $this->category = Category::create([
            'name' => 'Tortas Finas',
            'slug' => 'tortas-finas',
        ]);
    }

    /**
     * Requerimiento 1: Categorías para insumos
     * Requerimiento 3: Poder añadir stock inicial de insumos
     */
    public function test_supply_categories_and_supplies_with_initial_stock()
    {
        $this->actingAs($this->adminUser, 'admin');

        // 1. Crear categoría de insumo
        $cat = SupplyCategory::create([
            'name' => 'Harinas y Secos',
            'slug' => 'harinas-y-secos',
            'description' => 'Harinas, polvos y almidones'
        ]);

        $this->assertDatabaseHas('supply_categories', [
            'name' => 'Harinas y Secos',
            'slug' => 'harinas-y-secos',
        ]);

        // 2. Crear insumo con categoría y stock inicial por sede
        $response = $this->post(route('admin.supplies.store'), [
            'name' => 'Harina Pastelera Especial',
            'unit' => 'g',
            'supply_category_id' => $cat->id,
            'headquarters' => [
                $this->hq->id => [
                    'stock' => 5000.5,
                ]
            ]
        ]);

        $response->assertRedirect(route('admin.supplies.index'));
        $response->assertSessionHas('success');

        $supply = Supply::where('name', 'Harina Pastelera Especial')->first();
        $this->assertNotNull($supply);
        $this->assertEquals($cat->id, $supply->supply_category_id);
        $this->assertEquals('g', $supply->unit);

        // Verificar relación
        $this->assertEquals('Harinas y Secos', $supply->category->name);

        // Verificar stock inicial en headquarter_supply
        $stockPivot = DB::table('headquarter_supply')
            ->where('supply_id', $supply->id)
            ->where('headquarter_id', $this->hq->id)
            ->first();

        $this->assertNotNull($stockPivot);
        $this->assertEquals(5000.5, (float)$stockPivot->stock);
    }

    /**
     * Requerimiento 3: Poder añadir stock inicial de producto terminado
     */
    public function test_finished_product_initial_stock()
    {
        $this->actingAs($this->adminUser, 'admin');

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Cheesecake de Frutos Rojos',
            'category_id' => $this->category->id,
            'base_price' => 65.00,
            'description' => 'Cheesecake artesanal horneado',
            'headquarters' => [
                $this->hq->id => [
                    'is_available' => '1',
                    'stock' => 15,
                    'price' => 65.00,
                ]
            ]
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Cheesecake de Frutos Rojos')->first();
        $this->assertNotNull($product);

        $pivot = DB::table('headquarter_product')
            ->where('product_id', $product->id)
            ->where('headquarter_id', $this->hq->id)
            ->first();

        $this->assertNotNull($pivot);
        $this->assertEquals(15, (int)$pivot->stock);
        $this->assertEquals(65.00, (float)$pivot->price);
    }

    /**
     * Requerimiento 2: Cuando aumento la producción del día, se incrementa el stock de producto terminado
     * y se descuentan los insumos según la receta.
     */
    public function test_production_increments_product_stock_and_decrements_supplies()
    {
        $this->actingAs($this->adminUser, 'admin');

        // Insumos
        $harina = Supply::create(['name' => 'Harina', 'unit' => 'g']);
        $chocolate = Supply::create(['name' => 'Chocolate Bitter', 'unit' => 'g']);

        // Stock inicial de insumos: 2000g de harina y 1000g de chocolate
        $harina->headquarters()->attach($this->hq->id, ['stock' => 2000.0]);
        $chocolate->headquarters()->attach($this->hq->id, ['stock' => 1000.0]);

        // Producto terminado con stock inicial = 4 unidades
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Torta Selva Negra',
            'base_price' => 55.00,
            'is_active' => true,
        ]);
        $product->headquarters()->attach($this->hq->id, [
            'stock' => 4,
            'price' => 55.00,
            'is_available' => true,
        ]);

        // Receta: 1 torta requiere 250g harina y 150g chocolate
        Recipe::create([
            'product_id' => $product->id,
            'supply_id' => $harina->id,
            'quantity' => 250.0,
        ]);
        Recipe::create([
            'product_id' => $product->id,
            'supply_id' => $chocolate->id,
            'quantity' => 150.0,
        ]);

        // Registrar producción de 3 tortas
        $response = $this->post(route('admin.productions.store'), [
            'headquarter_id' => $this->hq->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $response->assertRedirect(route('admin.productions.index'));
        $response->assertSessionHas('success');

        // 1. Stock de producto terminado debe subir de 4 a 7 (4 + 3)
        $productStock = DB::table('headquarter_product')
            ->where('product_id', $product->id)
            ->where('headquarter_id', $this->hq->id)
            ->value('stock');
        $this->assertEquals(7, (int)$productStock);

        // 2. Harina descontada: 250 * 3 = 750g -> Stock restante = 2000 - 750 = 1250g
        $harinaStock = DB::table('headquarter_supply')
            ->where('supply_id', $harina->id)
            ->where('headquarter_id', $this->hq->id)
            ->value('stock');
        $this->assertEquals(1250.0, (float)$harinaStock);

        // 3. Chocolate descontado: 150 * 3 = 450g -> Stock restante = 1000 - 450 = 550g
        $chocolateStock = DB::table('headquarter_supply')
            ->where('supply_id', $chocolate->id)
            ->where('headquarter_id', $this->hq->id)
            ->value('stock');
        $this->assertEquals(550.0, (float)$chocolateStock);

        // 4. Registro de producción creado
        $this->assertDatabaseHas('productions', [
            'headquarter_id' => $this->hq->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    /**
     * Requerimiento 2: Producción rechazada si los insumos son insuficientes
     */
    public function test_production_rejected_when_insufficient_supplies()
    {
        $this->actingAs($this->adminUser, 'admin');

        $harina = Supply::create(['name' => 'Harina', 'unit' => 'g']);
        $harina->headquarters()->attach($this->hq->id, ['stock' => 100.0]); // Solo 100g

        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Pan Baguette',
            'base_price' => 5.00,
            'is_active' => true,
        ]);
        $product->headquarters()->attach($this->hq->id, ['stock' => 0, 'price' => 5.00]);

        Recipe::create([
            'product_id' => $product->id,
            'supply_id' => $harina->id,
            'quantity' => 200.0, // Requiere 200g por unidad
        ]);

        // Intentar producir 2 unidades (requiere 400g, solo hay 100g)
        $response = $this->post(route('admin.productions.store'), [
            'headquarter_id' => $this->hq->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertSessionHas('error');

        // Los stocks deben permanecer intactos
        $harinaStock = DB::table('headquarter_supply')
            ->where('supply_id', $harina->id)
            ->where('headquarter_id', $this->hq->id)
            ->value('stock');
        $this->assertEquals(100.0, (float)$harinaStock);

        $productStock = DB::table('headquarter_product')
            ->where('product_id', $product->id)
            ->where('headquarter_id', $this->hq->id)
            ->value('stock');
        $this->assertEquals(0, (int)$productStock);
    }

    /**
     * Requerimiento 4: Cuando realizo una venta se descuenta del stock de producto terminado
     */
    public function test_direct_sale_decrements_finished_product_stock()
    {
        $this->actingAs($this->adminUser, 'admin');

        $customer = User::create([
            'name' => 'Cliente Mostrador',
            'email' => 'cliente@test.pe',
            'password' => bcrypt('123456'),
            'role_id' => Role::where('slug', 'cliente')->first()->id,
        ]);

        // Stock inicial del producto terminado = 12 unidades
        $product = Product::create([
            'category_id' => $this->category->id,
            'name' => 'Pastel de Choclo',
            'base_price' => 18.00,
            'is_active' => true,
        ]);
        $product->headquarters()->attach($this->hq->id, [
            'stock' => 12,
            'price' => 18.00,
            'is_available' => true,
        ]);

        // Registrar venta directa de 4 unidades
        $response = $this->post(route('admin.sales.store_direct'), [
            'user_id' => $customer->id,
            'customer_name' => 'Cliente Mostrador',
            'headquarter_id' => $this->hq->id,
            'document_type' => '03', // Boleta
            'invoice_document' => '71234567',
            'products' => [
                [
                    'id' => $product->id,
                    'quantity' => 4,
                ]
            ]
        ]);

        $response->assertSessionHas('success');

        // El stock de producto terminado debe haber bajado de 12 a 8 (12 - 4)
        $productStock = DB::table('headquarter_product')
            ->where('product_id', $product->id)
            ->where('headquarter_id', $this->hq->id)
            ->value('stock');
        $this->assertEquals(8, (int)$productStock);

        // Verificar que la orden y la venta quedaron registradas con stock_decremented = true
        $order = \App\Models\Order::latest('id')->first();
        $this->assertNotNull($order);
        $this->assertTrue((bool)$order->stock_decremented);
        $this->assertEquals(4 * 18.00, (float)$order->total);

        $sale = \App\Models\Sale::where('order_id', $order->id)->first();
        $this->assertNotNull($sale);
        $this->assertEquals('03', $sale->document_type);
        $this->assertEquals('B001', $sale->series);
    }
}
