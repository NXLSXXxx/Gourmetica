<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Headquarter;
use App\Models\Order;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VerifyP2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:verify-p2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Certifica la matriz de autorización y aislamiento por sede de la Fase P2 con aislamiento transaccional estricto';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("===============================================================================");
        $this->info("   GOURMETICA - CERTIFICACION DE FASE P2 (AISLAMIENTO TRANSACCIONAL)           ");
        $this->info("===============================================================================");

        $passed = 0;
        $total = 0;

        // 1. Snapshot del estado de la base de datos previo a la ejecución
        $beforeSnapshot = $this->captureDatabaseState();

        // 2. Iniciar transacción aislada: cualquier inserción o cambio será revertido (rollback)
        DB::beginTransaction();

        try {
            // 3. Obtener o crear roles transaccionales (se descartarán en el rollback)
            $roleAdmin = Role::where('slug', 'admin_general')->first() 
                ?: Role::create(['name' => 'Admin General Audit', 'slug' => 'admin_general']);

            $roleSede = Role::where('slug', 'admin_sede')->first() 
                ?: Role::create(['name' => 'Admin Sede Audit', 'slug' => 'admin_sede']);

            $roleCajero = Role::where('slug', 'cajero')->first() 
                ?: Role::create(['name' => 'Cajero Audit', 'slug' => 'cajero']);

            $roleIngeniero = Role::where('slug', 'ingeniero')->first() 
                ?: Role::create(['name' => 'Ingeniero Audit', 'slug' => 'ingeniero']);

            // 4. Crear sedes de prueba estrictamente efímeras dentro de la transacción
            $hq1 = Headquarter::create([
                'name' => 'Audit Sede 1 ' . uniqid(),
                'address' => 'Calle Auditoria 1',
                'city' => 'Lima',
                'is_active' => true,
            ]);

            $hq2 = Headquarter::create([
                'name' => 'Audit Sede 2 ' . uniqid(),
                'address' => 'Calle Auditoria 2',
                'city' => 'Lima',
                'is_active' => true,
            ]);

            // 5. Crear usuarios de prueba estrictamente efímeros (NUNCA reutiliza ni modifica usuarios reales)
            $adminGeneral = User::create([
                'name' => 'Audit Admin General',
                'email' => 'audit_admingen_' . uniqid() . '@audit.test',
                'password' => bcrypt('password123'),
                'role_id' => $roleAdmin->id,
                'headquarter_id' => null,
            ]);

            $adminSede = User::create([
                'name' => 'Audit Admin Sede 2',
                'email' => 'audit_adminsede2_' . uniqid() . '@audit.test',
                'password' => bcrypt('password123'),
                'role_id' => $roleSede->id,
                'headquarter_id' => $hq2->id,
            ]);

            $cajeroSede1 = User::create([
                'name' => 'Audit Cajero Sede 1',
                'email' => 'audit_cajero1_' . uniqid() . '@audit.test',
                'password' => bcrypt('password123'),
                'role_id' => $roleCajero->id,
                'headquarter_id' => $hq1->id,
            ]);

            $ingeniero = User::create([
                'name' => 'Audit Ingeniero',
                'email' => 'audit_ing_' . uniqid() . '@audit.test',
                'password' => bcrypt('password123'),
                'role_id' => $roleIngeniero->id,
                'headquarter_id' => null,
            ]);

            // 6. Crear pedidos efímeros dentro de la transacción
            $orderS1 = Order::create([
                'user_id' => $adminGeneral->id,
                'headquarter_id' => $hq1->id,
                'customer_name' => 'Cliente Audit Sede 1',
                'total' => 45.00,
                'status' => 'pending',
                'payment_method' => 'Efectivo',
                'payment_status' => 'paid',
            ]);

            $orderS2 = Order::create([
                'user_id' => $adminGeneral->id,
                'headquarter_id' => $hq2->id,
                'customer_name' => 'Cliente Audit Sede 2',
                'total' => 60.00,
                'status' => 'pending',
                'payment_method' => 'Efectivo',
                'payment_status' => 'paid',
            ]);

            // 7. Crear ventas efímeras dentro de la transacción
            $saleS1 = Sale::create([
                'user_id' => $adminGeneral->id,
                'headquarter_id' => $hq1->id,
                'document_type' => '03',
                'series' => 'B001',
                'correlative' => 99991,
                'total' => 45.00,
                'status' => 'completed',
                'sunat_status' => 'pending',
            ]);

            $saleS2 = Sale::create([
                'user_id' => $adminGeneral->id,
                'headquarter_id' => $hq2->id,
                'document_type' => '03',
                'series' => 'B001',
                'correlative' => 99992,
                'total' => 60.00,
                'status' => 'completed',
                'sunat_status' => 'pending',
            ]);

            $this->newLine();
            $this->line("<fg=yellow;options=bold>--- 1. AISLAMIENTO MULTISEDE (PEDIDOS, VENTAS Y CAJA) ---</>");

            // 1.1 Cajero Sede 1 consulta pedido Sede 1
            $total++;
            if ($cajeroSede1->can('view', $orderS1)) {
                $passed++;
                $this->line(" [OK] Cajero Sede 1 puede consultar Pedido de su propia Sede (Sede 1)");
            } else {
                $this->error(" [FALLO] Cajero Sede 1 no pudo consultar Pedido de Sede 1");
            }

            // 1.2 Cajero Sede 1 intenta consultar pedido Sede 2
            $total++;
            if (!$cajeroSede1->can('view', $orderS2)) {
                $passed++;
                $this->line(" [OK] Cajero Sede 1 NO puede consultar Pedido de otra Sede (Sede 2) -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Cajero Sede 1 pudo ver Pedido de Sede 2");
            }

            // 1.3 Cajero Sede 1 intenta modificar estado de pedido Sede 2
            $total++;
            if (!$cajeroSede1->can('update', $orderS2)) {
                $passed++;
                $this->line(" [OK] Cajero Sede 1 NO puede modificar estado de Pedido de otra Sede -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Cajero Sede 1 pudo modificar Pedido de Sede 2");
            }

            // 1.4 Cajero Sede 1 intenta cancelar pedido Sede 2
            $total++;
            if (!$cajeroSede1->can('cancel', $orderS2)) {
                $passed++;
                $this->line(" [OK] Cajero Sede 1 NO puede cancelar Pedido de otra Sede -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Cajero Sede 1 pudo cancelar Pedido de Sede 2");
            }

            // 1.5 Cajero Sede 1 intenta imprimir ticket de Sede 2
            $total++;
            if (!$cajeroSede1->can('printTicket', $orderS2)) {
                $passed++;
                $this->line(" [OK] Cajero Sede 1 NO puede imprimir Ticket de otra Sede -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Cajero Sede 1 pudo imprimir Ticket de Sede 2");
            }

            // 1.6 Cajero Sede 1 intenta consultar venta de Sede 2
            $total++;
            if (!$cajeroSede1->can('view', $saleS2)) {
                $passed++;
                $this->line(" [OK] Cajero Sede 1 NO puede consultar Venta de otra Sede -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Cajero Sede 1 pudo ver Venta de Sede 2");
            }

            // 1.7 Cajero Sede 1 intenta registrar venta directa en Sede 2
            $total++;
            if (!$cajeroSede1->can('create', [Sale::class, $hq2->id])) {
                $passed++;
                $this->line(" [OK] Cajero Sede 1 NO puede registrar Venta en otra Sede -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Cajero Sede 1 pudo registrar Venta en Sede 2");
            }

            // 1.8 Fail-Closed: Cajero Sede 1 intenta registrar venta sin sede (sede null) vs interfaz (createAny)
            $total++;
            $canOpenForm = $cajeroSede1->can('createAny', Sale::class);
            $denyNullHqWrite = !$cajeroSede1->can('create', [Sale::class, null]);
            $denyZeroHqWrite = !$cajeroSede1->can('create', [Sale::class, 0]);
            if ($canOpenForm && $denyNullHqWrite && $denyZeroHqWrite) {
                $passed++;
                $this->line(" [OK] Fail-Closed: Apertura permitida (createAny), pero registro sin sede DENEGADO (create sede null) -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Cajero Sede 1 pudo autorizar escritura sin sede o falló createAny");
            }

            // 1.9 Aislamiento de Admin Sede 2 respecto a Sede 1
            $total++;
            if (!$adminSede->can('view', $orderS1)) {
                $passed++;
                $this->line(" [OK] Admin Sede 2 NO puede consultar Pedido de Sede 1 -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Admin Sede 2 pudo ver Pedido de Sede 1");
            }

            // 1.10 Admin General tiene visibilidad de todas las sedes
            $total++;
            if ($adminGeneral->can('view', $orderS1) && $adminGeneral->can('view', $orderS2)) {
                $passed++;
                $this->line(" [OK] Admin General puede consultar pedidos de Todas las Sedes (Sede 1 y Sede 2)");
            } else {
                $this->error(" [FALLO] Admin General no pudo consultar todas las sedes");
            }

            $this->newLine();
            $this->line("<fg=yellow;options=bold>--- 2. MATRIZ DE AUTORIZACION POR ROL (FAIL-CLOSED) ---</>");

            // 2.1 Configuración general
            $total++;
            $cfgAdminOk = $adminGeneral->can('manage-settings');
            $cfgSedeDeny = !$adminSede->can('manage-settings');
            $cfgCajeroDeny = !$cajeroSede1->can('manage-settings');
            $cfgIngDeny = !$ingeniero->can('manage-settings');

            if ($cfgAdminOk && $cfgSedeDeny && $cfgCajeroDeny && $cfgIngDeny) {
                $passed++;
                $this->line(" [OK] Configuracion General: Solo Admin General (Denegado para Sede, Cajero e Ingeniero)");
            } else {
                $this->error(" [FALLO] Matriz de Configuracion General no cumple");
            }

            // 2.2 Gestión de usuarios
            $total++;
            $usrAdminOk = $adminGeneral->can('manage-users');
            $usrSedeDeny = !$adminSede->can('manage-users');
            $usrCajeroDeny = !$cajeroSede1->can('manage-users');
            $usrIngDeny = !$ingeniero->can('manage-users');

            if ($usrAdminOk && $usrSedeDeny && $usrCajeroDeny && $usrIngDeny) {
                $passed++;
                $this->line(" [OK] Gestion de Usuarios: Solo Admin General (Denegado para Sede, Cajero e Ingeniero)");
            } else {
                $this->error(" [FALLO] Matriz de Gestion de Usuarios no cumple");
            }

            // 2.3 Gestión de sedes y delivery
            $total++;
            $hqAdminOk = $adminGeneral->can('manage-headquarters') && $adminGeneral->can('manage-delivery-zones');
            $hqSedeDeny = !$adminSede->can('manage-headquarters') && !$adminSede->can('manage-delivery-zones');
            $hqCajeroDeny = !$cajeroSede1->can('manage-headquarters') && !$cajeroSede1->can('manage-delivery-zones');
            $hqIngDeny = !$ingeniero->can('manage-headquarters') && !$ingeniero->can('manage-delivery-zones');

            if ($hqAdminOk && $hqSedeDeny && $hqCajeroDeny && $hqIngDeny) {
                $passed++;
                $this->line(" [OK] Gestion de Sedes y Delivery: Solo Admin General (Denegado para Sede, Cajero e Ingeniero)");
            } else {
                $this->error(" [FALLO] Matriz de Gestion de Sedes y Delivery no cumple");
            }

            // 2.4 Backups y logs técnicos
            $total++;
            $techIngOk = $ingeniero->can('manage-technical');
            $techAdminDeny = !$adminGeneral->can('manage-technical');
            $techSedeDeny = !$adminSede->can('manage-technical');
            $techCajeroDeny = !$cajeroSede1->can('manage-technical');

            if ($techIngOk && $techAdminDeny && $techSedeDeny && $techCajeroDeny) {
                $passed++;
                $this->line(" [OK] Backups y Logs Tecnicos: Exclusivo para Ingeniero (Denegado para Admin General, Sede y Cajero)");
            } else {
                $this->error(" [FALLO] Matriz de Backups y Logs no cumple");
            }

            // 2.5 Auditoría técnica
            $total++;
            $auditIngOk = $ingeniero->can('view-audits');
            $auditAdminDeny = !$adminGeneral->can('view-audits');
            $auditSedeDeny = !$adminSede->can('view-audits');
            $auditCajeroDeny = !$cajeroSede1->can('view-audits');

            if ($auditIngOk && $auditAdminDeny && $auditSedeDeny && $auditCajeroDeny) {
                $passed++;
                $this->line(" [OK] Auditoria Tecnica: Exclusivo para Ingeniero (Denegado para Admin General, Sede y Cajero)");
            } else {
                $this->error(" [FALLO] Matriz de Auditoria Tecnica no cumple");
            }

            // 2.6 Ingeniero no opera pedidos ni caja
            $total++;
            $ingOrdersDeny = !$ingeniero->can('manage-orders-sales');
            $ingCashierDeny = !$ingeniero->can('operate-cashier');

            if ($ingOrdersDeny && $ingCashierDeny) {
                $passed++;
                $this->line(" [OK] Operaciones Comerciales y de Caja: Denegadas para Ingeniero -> 403 Forbidden");
            } else {
                $this->error(" [FALLO] Ingeniero tiene acceso indebido a pedidos o caja");
            }

        } finally {
            // Revertir absolutamente todas las inserciones y cambios dentro de la transacción
            DB::rollBack();
        }

        // 8. Verificación de Integridad: Comparar Snapshot post-ejecución con el pre-ejecución
        $this->newLine();
        $this->line("<fg=yellow;options=bold>--- 3. VERIFICACION DE AISLAMIENTO E INTEGRIDAD DE BD ---</>");
        $afterSnapshot = $this->captureDatabaseState();
        $isClean = ($beforeSnapshot === $afterSnapshot);

        $total++;
        if ($isClean) {
            $passed++;
            $this->line(" [OK] Cero Modificaciones Permanentes: Snapshot pre/post identico al 100% (Rollback verificado)");
        } else {
            $this->error(" [FALLO] Se detectaron alteraciones residuales en las tablas tras la ejecucion");
        }

        $this->newLine();
        $this->info("===============================================================================");
        $this->info("   RESULTADO DE CERTIFICACION: {$passed} / {$total} VERIFICACIONES EXITOSAS     ");
        $this->info("===============================================================================");

        return $passed === $total ? 0 : 1;
    }

    /**
     * Captura el estado y conteo de filas de las tablas clave para auditar integridad.
     */
    protected function captureDatabaseState(): array
    {
        $tables = ['roles', 'users', 'headquarters', 'orders', 'sales', 'order_items', 'audits'];
        $state = [];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $maxId = DB::table($table)->max('id') ?? 0;
                $state[$table] = [
                    'count' => (int) $count,
                    'max_id' => (int) $maxId,
                ];
            }
        }

        return $state;
    }
}
