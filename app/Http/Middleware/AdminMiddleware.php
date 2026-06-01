<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Debes iniciar sesión para acceder a la intranet.');
        }

        $user = auth('admin')->user();
        
        // Allow Admin General, Admin de Sede, Ingeniero, and Cajero
        $allowedSlugs = ['admin_general', 'admin_sede', 'ingeniero', 'cajero'];
        
        if (!in_array($user->role->slug, $allowedSlugs)) {
            auth('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'No tienes permisos para acceder a esta área.');
        }

        return $next($request);
    }
}
