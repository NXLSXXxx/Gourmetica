<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Headquarter;
use App\Models\Order;
use App\Models\Sale;
use Illuminate\Support\Facades\Gate;

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
    protected $description = 'Certifica la matriz de autorización y aislamiento por sede de la Fase P2';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("===============================================================================");
        $this->info("   GOURMETICA - CERTIFICACION DE FASE P2 (ROLES, PERMISOS Y AISLAMIENTO)       ");
        $this->info("===============================================================================");

        // 1. Asegurar rol cajero en caso no exista
        $cajeroRole = Role::firstOrCreate(['slug' => 'cajero'], ['name' => 'Cajero']);

        // 2. Obtener usuarios clave
        $adminGeneral = User::whereHas('role', fn($q) => $q->where('slug', 'admin_general'))->first();
        $ingeniero    = User::whereHas('role', fn($q) => $q->where('slug', 'ingeniero'))->first();
        $adminSede    = User::whereHas('role', fn($q) => $q->where('slug', 'admin_sede'))->first();

        if (!$adminGeneral || !$ingeniero) {
            $this->error("Faltan usuarios base de prueba (Admin General o Ingeniero).");
            return 1;
        }

        // 3. Obtener o crear dos sedes
        $hq1 = Headquarter::find(1) ?: Headquarter::first();
        $hq2 = Headquarter::where('id', '!=', $hq1->id)->first() ?: Headquarter::create([
            'name' => 'Sede Secundaria Certificacion',
            'is_active' => true,
        ]);

        $orderS1 = null;
        $orderS2 = null;
        $saleS1 = null;
        $saleS2 = null;
        $cajeroSede1 = null;

        $passed = 0;
        $total = 0;

        try {
            // Crear usuario cajero asignado a Sede 1 para la prueba
            $cajeroSede1 = User::create([
                'name' => 'Cajero Audit S1',
                'email' => 'cajero_cert_' . time() . '@gourmetica.pe',
                'password' => bcrypt('password123'),
                'role_id' => $cajeroRole->id,
                'headquarter_id' => $hq1->id,
            ]);

            // Asignar Admin Sede a Sede 2 si no tiene sede asignada
            if ($adminSede && !$adminSede->headquarter_id) {
                $adminSede->update(['headquarter_id' => $hq2->id]);
            }

            // Crear pedidos de prueba en cada sede
            $orderS1 = Order::create([
                'user_id' => $adminGeneral->id,
                'headquarter_id' => $hq1->id,
                'customer_name' => 'Cliente Sede 1',
                'total' => 45.00,
                'status' => 'pending',
                'payment_method' => 'Efectivo',
                'payment_status' => 'paid',
            ]);

            $orderS2 = Order::create([
                'user_id' => $adminGeneral->id,
                'headquarter_id' => $hq2->id,
                'customer_name' => 'Cliente Sede 2',
                'total' => 60.00,
                'status' => 'pending',
                'payment_method' => 'Efectivo',
                'payment_status' => 'paid',
            ]);

            // Crear ventas de prueba en cada sede
            $saleS1 = Sale::create([
                'user_id' => $adminGeneral->id,
                'headquarter_id' => $hq1->id,
                'document_type' => '03',
                'series' => 'B001',
                'correlative' => 9991,
                'total' => 45.00,
                'status' => 'completed',
                'sunat_status' => 'pending',
            ]);

            $saleS2 = Sale::create([
                'user_id' => $adminGeneral->id,
                'headquarter_id' => $hq2->id,
                'document_type' => '03',
                'series' => 'B001',
                'correlative' => 9992,
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
            if ($adminSede) {
                $total++;
                if (!$adminSede->can('view', $orderS1)) {
                    $passed++;
                    $this->line(" [OK] Admin Sede 2 NO puede consultar Pedido de Sede 1 -> 403 Forbidden");
                } else {
                    $this->error(" [FALLO] Admin Sede 2 pudo ver Pedido de Sede 1");
                }
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

            // 2.1 Configuracion general
            $total++;
            $cfgAdminOk = $adminGeneral->can('manage-settings');
            $cfgSedeDeny = $adminSede ? !$adminSede->can('manage-settings') : true;
            $cfgCajeroDeny = !$cajeroSede1->can('manage-settings');
            $cfgIngDeny = !$ingeniero->can('manage-settings');

            if ($cfgAdminOk && $cfgSedeDeny && $cfgCajeroDeny && $cfgIngDeny) {
                $passed++;
                $this->line(" [OK] Configuracion General: Solo Admin General (Denegado para Sede, Cajero e Ingeniero)");
            } else {
                $this->error(" [FALLO] Matriz de Configuracion General no cumple");
            }

            // 2.2 Gestion de usuarios
            $total++;
            $usrAdminOk = $adminGeneral->can('manage-users');
            $usrSedeDeny = $adminSede ? !$adminSede->can('manage-users') : true;
            $usrCajeroDeny = !$cajeroSede1->can('manage-users');
            $usrIngDeny = !$ingeniero->can('manage-users');

            if ($usrAdminOk && $usrSedeDeny && $usrCajeroDeny && $usrIngDeny) {
                $passed++;
                $this->line(" [OK] Gestion de Usuarios: Solo Admin General (Denegado para Sede, Cajero e Ingeniero)");
            } else {
                $this->error(" [FALLO] Matriz de Gestion de Usuarios no cumple");
            }

            // 2.3 Gestion de sedes y delivery
            $total++;
            $hqAdminOk = $adminGeneral->can('manage-headquarters') && $adminGeneral->can('manage-delivery-zones');
            $hqSedeDeny = $adminSede ? (!$adminSede->can('manage-headquarters') && !$adminSede->can('manage-delivery-zones')) : true;
            $hqCajeroDeny = !$cajeroSede1->can('manage-headquarters') && !$cajeroSede1->can('manage-delivery-zones');
            $hqIngDeny = !$ingeniero->can('manage-headquarters') && !$ingeniero->can('manage-delivery-zones');

            if ($hqAdminOk && $hqSedeDeny && $hqCajeroDeny && $hqIngDeny) {
                $passed++;
                $this->line(" [OK] Gestion de Sedes y Delivery: Solo Admin General (Denegado para Sede, Cajero e Ingeniero)");
            } else {
                $this->error(" [FALLO] Matriz de Gestion de Sedes y Delivery no cumple");
            }

            // 2.4 Backups y logs tecnicos
            $total++;
            $techIngOk = $ingeniero->can('manage-technical');
            $techAdminDeny = !$adminGeneral->can('manage-technical');
            $techSedeDeny = $adminSede ? !$adminSede->can('manage-technical') : true;
            $techCajeroDeny = !$cajeroSede1->can('manage-technical');

            if ($techIngOk && $techAdminDeny && $techSedeDeny && $techCajeroDeny) {
                $passed++;
                $this->line(" [OK] Backups y Logs Tecnicos: Exclusivo para Ingeniero (Denegado para Admin General, Sede y Cajero)");
            } else {
                $this->error(" [FALLO] Matriz de Backups y Logs no cumple");
            }

            // 2.5 Auditoria tecnica
            $total++;
            $auditIngOk = $ingeniero->can('view-audits');
            $auditAdminDeny = !$adminGeneral->can('view-audits');
            $auditSedeDeny = $adminSede ? !$adminSede->can('view-audits') : true;
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

            $this->newLine();
            $this->info("===============================================================================");
            $this->info("   RESULTADO DE CERTIFICACION: {$passed} / {$total} VERIFICACIONES EXITOSAS     ");
            $this->info("===============================================================================");

        } finally {
            // Limpieza segura de registros temporales creados para la prueba
            if ($orderS1) { $orderS1->delete(); }
            if ($orderS2) { $orderS2->delete(); }
            if ($saleS1) { $saleS1->delete(); }
            if ($saleS2) { $saleS2->delete(); }
            if ($cajeroSede1) { $cajeroSede1->delete(); }
        }

        return $passed === $total ? 0 : 1;
    }
}
