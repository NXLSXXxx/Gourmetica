<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
