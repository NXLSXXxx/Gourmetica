<?php

namespace Tests\Feature;

use App\Models\Headquarter;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DisasterRecoveryDrillTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que el comando bloquee tajantemente cualquier intento de usar la BD activa como destino.
     */
    public function test_dr_drill_blocks_execution_against_active_or_protected_database(): void
    {
        $defaultConn = config('database.default');
        $activeDb = config("database.connections.{$defaultConn}.database");

        // Intento 1: usar la misma base de datos activa
        $this->artisan('audit:dr-drill', ['--target-db' => $activeDb])
            ->expectsOutputToContain('[BLOQUEO DE SEGURIDAD]')
            ->assertExitCode(1);

        // Intento 2: usar nombres reservados del sistema
        $this->artisan('audit:dr-drill', ['--target-db' => 'mysql'])
            ->expectsOutputToContain('[BLOQUEO DE SEGURIDAD]')
            ->assertExitCode(1);

        $this->artisan('audit:dr-drill', ['--target-db' => 'information_schema'])
            ->expectsOutputToContain('[BLOQUEO DE SEGURIDAD]')
            ->assertExitCode(1);
    }

    /**
     * Prueba la ejecución completa del simulacro DR: volcado, restauración, RTO, RPO y verificación de consistencia.
     */
    public function test_dr_drill_executes_successfully_and_verifies_data_consistency(): void
    {
        // 1. Sembrar registros de prueba
        $role = Role::create(['name' => 'Admin Test', 'slug' => 'admin_test']);
        $hq = Headquarter::create(['name' => 'Sede DR Test', 'address' => 'Av. Test', 'city' => 'Lima', 'is_active' => true]);
        $user = User::create(['name' => 'User DR Test', 'email' => 'dr_test@test.local', 'password' => bcrypt('secret'), 'role_id' => $role->id]);

        $order = Order::create([
            'user_id' => $user->id,
            'headquarter_id' => $hq->id,
            'customer_name' => 'Cliente DR Test',
            'total' => 85.00,
            'status' => 'completed',
            'payment_method' => 'Efectivo',
            'payment_status' => 'paid',
        ]);

        $sale = Sale::create([
            'user_id' => $user->id,
            'headquarter_id' => $hq->id,
            'document_type' => '03',
            'series' => 'B001',
            'correlative' => 5001,
            'total' => 85.00,
            'status' => 'completed',
            'order_id' => $order->id,
        ]);

        // 2. Ejecutar el simulacro
        $this->artisan('audit:dr-drill')
            ->expectsOutputToContain('SIMULACRO DR-DRILL COMPLETADO CON EXITO')
            ->expectsOutputToContain('RPO Medido:')
            ->expectsOutputToContain('RTO Medido')
            ->expectsOutputToContain('100% de tablas y registros verificados idénticos')
            ->assertExitCode(0);
    }
}
