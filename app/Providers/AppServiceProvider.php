<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Order;
use App\Models\Sale;
use App\Policies\OrderPolicy;
use App\Policies\SalePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // --------------------------------------------------------------------
        // Fase P2: Registro de Policies y Gates con denegación por defecto
        // --------------------------------------------------------------------
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);

        // 1. Configuración general: solo Admin General
        Gate::define('manage-settings', function (?User $user = null) {
            $user = $user ?: auth('admin')->user();
            return $user !== null && $user->isAdmin();
        });

        // 2. Gestión de usuarios: solo Admin General
        Gate::define('manage-users', function (?User $user = null) {
            $user = $user ?: auth('admin')->user();
            return $user !== null && $user->isAdmin();
        });

        // 3. Gestión de sedes y delivery: solo Admin General
        Gate::define('manage-headquarters', function (?User $user = null) {
            $user = $user ?: auth('admin')->user();
            return $user !== null && $user->isAdmin();
        });

        Gate::define('manage-delivery-zones', function (?User $user = null) {
            $user = $user ?: auth('admin')->user();
            return $user !== null && $user->isAdmin();
        });

        // 4. Pedidos y ventas: Admin General (todas), Admin Sede y Cajero (su sede), Ingeniero (No)
        Gate::define('manage-orders-sales', function (?User $user = null) {
            $user = $user ?: auth('admin')->user();
            return $user !== null && ($user->isAdmin() || $user->isSedeAdmin() || $user->isCajero());
        });

        // 5. Operaciones de caja: Admin General (todas), Admin Sede y Cajero (su sede), Ingeniero (No)
        Gate::define('operate-cashier', function (?User $user = null) {
            $user = $user ?: auth('admin')->user();
            return $user !== null && ($user->isAdmin() || $user->isSedeAdmin() || $user->isCajero());
        });

        // 6. Backups y logs técnicos: solo Ingeniero
        Gate::define('manage-technical', function (?User $user = null) {
            $user = $user ?: auth('admin')->user();
            return $user !== null && $user->isIngeniero();
        });

        // 7. Auditoría técnica: solo Ingeniero
        Gate::define('view-audits', function (?User $user = null) {
            $user = $user ?: auth('admin')->user();
            return $user !== null && $user->isIngeniero();
        });

        // 8. Validación de sede específica (aislamiento multisede)
        Gate::define('operate-headquarter', function (?User $user, int $headquarterId) {
            $user = $user ?: auth('admin')->user();
            if (!$user) {
                return false;
            }
            if ($user->isAdmin()) {
                return true;
            }
            if ($user->isSedeAdmin() || $user->isCajero()) {
                return (int) $user->headquarter_id === (int) $headquarterId;
            }
            return false;
        });

        // Auto-heal: asegurar que headquarter_product tenga la columna is_available en producción
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('headquarter_product') && 
                !\Illuminate\Support\Facades\Schema::hasColumn('headquarter_product', 'is_available')) {
                \Illuminate\Support\Facades\Schema::table('headquarter_product', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->boolean('is_available')->default(true)->after('price');
                });
            }
        } catch (\Throwable $e) {
            // Continuar normalmente si no hay permisos de modificación
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $view->with('categories', \App\Models\Category::all());

            // Inyectar sedes activas y sede seleccionada globalmente
            $view->with('global_headquarters', \App\Models\Headquarter::where('is_active', true)->get());

            $selHqId = session('selected_headquarter_id');
            $view->with('selected_headquarter', $selHqId ? \App\Models\Headquarter::find($selHqId) : null);

            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $view->with('contact_whatsapp', \App\Models\Setting::get('contact_whatsapp', '950664655'));
                $view->with('contact_email', \App\Models\Setting::get('contact_email', 'hola@gourmetica.pe'));
                $view->with('contact_phone', \App\Models\Setting::get('contact_phone', '(01) 234-5678'));
            } else {
                $view->with('contact_whatsapp', '950664655');
                $view->with('contact_email', 'hola@gourmetica.pe');
                $view->with('contact_phone', '(01) 234-5678');
            }
        });
    }
}
