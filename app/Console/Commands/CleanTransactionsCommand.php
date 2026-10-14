<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-transactions {--force : Forzar la ejecución sin confirmación} {--reset-stock : Reiniciar el stock de productos e insumos a cero}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia los datos operativos y de prueba (pedidos, ventas, compras, auditorías, etc.) preservando usuarios, sedes y catálogo.';

    /**
     * Tablas a truncar (datos transaccionales y temporales).
     */
    protected array $transactionalTables = [
        'order_items',
        'orders',
        'sales',
        'purchases',
        'productions',
        'catering_requests',
        'favorites',
        'audits',
        'failed_jobs',
        'jobs',
        'job_batches',
        'password_reset_tokens',
        'sessions',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('ATENCIÓN: Esta acción eliminará permanentemente todos los datos de prueba y transacciones:');
        $this->line('- Pedidos y sus detalles (orders, order_items)');
        $this->line('- Ventas y Comprobantes (sales)');
        $this->line('- Compras de insumos (purchases)');
        $this->line('- Registros de producción (productions)');
        $this->line('- Solicitudes de catering (catering_requests)');
        $this->line('- Favoritos de clientes (favorites)');
        $this->line('- Registros de auditoría (audits)');
        $this->line('- Sesiones y colas de jobs');
        $this->newLine();
        $this->info('SE CONSERVARÁN:');
        $this->line('+ Usuarios (users), Roles (roles), Sedes (headquarters)');
        $this->line('+ Categorías (categories), Productos (products, product_options)');
        $this->line('+ Insumos y Recetas (supplies, recipes)');
        $this->line('+ Configuraciones y Zonas de entrega (settings, delivery_zones, banners)');
        $this->newLine();

        if (!$this->option('force') && !$this->confirm('¿Estás seguro de que deseas continuar con la limpieza?', false)) {
            $this->comment('Operación cancelada.');
            return Command::SUCCESS;
        }

        $this->info('Iniciando limpieza de base de datos...');

        try {
            Schema::disableForeignKeyConstraints();

            foreach ($this->transactionalTables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("  ✓ Tabla <comment>{$table}</comment> truncada.");
                }
            }

            if ($this->option('reset-stock')) {
                if (Schema::hasTable('headquarter_product')) {
                    DB::table('headquarter_product')->update(['stock' => 0]);
                    $this->line("  ✓ Stock de productos en sedes reiniciado a 0.");
                }
                if (Schema::hasTable('headquarter_supply')) {
                    DB::table('headquarter_supply')->update(['stock' => 0]);
                    $this->line("  ✓ Stock de insumos en sedes reiniciado a 0.");
                }
            }

            Schema::enableForeignKeyConstraints();

            // Limpiar caché de Laravel
            $this->callSilently('cache:clear');
            $this->line("  ✓ Caché de la aplicación limpiada.");

            $this->newLine();
            $this->info('¡Limpieza completada exitosamente! La base de datos está lista.');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Schema::enableForeignKeyConstraints();
            $this->error("Ocurrió un error durante la limpieza: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
