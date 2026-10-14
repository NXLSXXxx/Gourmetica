<?php

namespace Tests\Feature;

use App\Models\Headquarter;
use App\Models\Order;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VerifyP2CommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Verifica que el comando audit:verify-p2 se ejecute con éxito y garantice
     * aislamiento total con cero modificaciones persistentes antes y después.
     */
    public function test_verify_p2_command_runs_successfully_and_preserves_database_state(): void
    {
        // 1. Crear datos base previos para simular un entorno en producción
        $adminRole = Role::create(['name' => 'Admin General', 'slug' => 'admin_general']);
        $sedeRole  = Role::create(['name' => 'Admin de Sede', 'slug' => 'admin_sede']);
        $cajeroRole = Role::create(['name' => 'Cajero', 'slug' => 'cajero']);
        $ingRole   = Role::create(['name' => 'Ingeniero', 'slug' => 'ingeniero']);

        $realHq = Headquarter::create([
            'name' => 'Sede Real Gourmetica Miraflores',
            'address' => 'Av. Larco 123',
            'city' => 'Lima',
            'is_active' => true,
        ]);

        $realAdmin = User::create([
            'name' => 'Administrador Real Produccion',
            'email' => 'admin.real@gourmetica.pe',
            'password' => bcrypt('secret123'),
            'role_id' => $adminRole->id,
            'headquarter_id' => null,
        ]);

        $realAdminSede = User::create([
            'name' => 'Admin Sede Real Produccion',
            'email' => 'admin.sede.real@gourmetica.pe',
            'password' => bcrypt('secret123'),
            'role_id' => $sedeRole->id,
            'headquarter_id' => $realHq->id,
        ]);

        $realOrder = Order::create([
            'user_id' => $realAdmin->id,
            'headquarter_id' => $realHq->id,
            'customer_name' => 'Cliente Comercial Real',
            'total' => 120.00,
            'status' => 'completed',
            'payment_method' => 'Tarjeta',
            'payment_status' => 'paid',
        ]);

        $realSale = Sale::create([
            'user_id' => $realAdmin->id,
            'headquarter_id' => $realHq->id,
            'document_type' => '03',
            'series' => 'B001',
            'correlative' => 101,
            'total' => 120.00,
            'status' => 'completed',
            'sunat_status' => 'pending',
            'order_id' => $realOrder->id,
        ]);

        // 2. Capturar snapshot de conteos y datos antes de ejecutar la certificación
        $tables = ['roles', 'users', 'headquarters', 'orders', 'sales', 'order_items', 'audits'];
        $beforeSnapshot = [];
        foreach ($tables as $table) {
            $metrics = DB::table($table)
                ->selectRaw('COUNT(*) as total_rows, COALESCE(SUM(id), 0) as id_sum, COALESCE(MAX(id), 0) as max_id')
                ->first();
            $beforeSnapshot[$table] = [
                'count'  => (int) ($metrics->total_rows ?? 0),
                'id_sum' => (int) ($metrics->id_sum ?? 0),
                'max_id' => (int) ($metrics->max_id ?? 0),
            ];
        }

        // 3. Ejecutar comando de certificación
        $this->artisan('audit:verify-p2')
            ->expectsOutputToContain('17 / 17 VERIFICACIONES EXITOSAS')
            ->assertExitCode(0);

        // 4. Capturar snapshot posterior a la ejecución
        $afterSnapshot = [];
        foreach ($tables as $table) {
            $metrics = DB::table($table)
                ->selectRaw('COUNT(*) as total_rows, COALESCE(SUM(id), 0) as id_sum, COALESCE(MAX(id), 0) as max_id')
                ->first();
            $afterSnapshot[$table] = [
                'count'  => (int) ($metrics->total_rows ?? 0),
                'id_sum' => (int) ($metrics->id_sum ?? 0),
                'max_id' => (int) ($metrics->max_id ?? 0),
            ];
        }

        // 5. Aserción de integridad: la BD previa y posterior son idénticas
        $this->assertSame(
            $beforeSnapshot,
            $afterSnapshot,
            'El comando audit:verify-p2 no garantizó el rollback completo y dejó registros residuales.'
        );

        // 6. Verificar que ningún registro comercial fue alterado
        $this->assertEquals($realHq->id, $realAdminSede->fresh()->headquarter_id);
        $this->assertEquals('admin.sede.real@gourmetica.pe', $realAdminSede->fresh()->email);
        $this->assertEquals('Cliente Comercial Real', $realOrder->fresh()->customer_name);
        $this->assertEquals(101, $realSale->fresh()->correlative);

        // 7. Confirmar ausencia total de entidades efímeras de auditoría
        $this->assertEquals(0, User::where('email', 'like', '%@audit.test')->count());
        $this->assertEquals(0, Headquarter::where('name', 'like', 'Audit Sede%')->count());
        $this->assertEquals(0, Sale::where('correlative', '>=', 99000)->count());
    }
}
