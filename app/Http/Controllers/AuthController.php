<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function showClientLogin()
    {
        return view('auth.login');
    }

    public function showAdminLogin()
    {
        return view('auth.admin-login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role_id' => \App\Models\Role::where('slug', 'cliente')->first()->id,
        ]);

        \Illuminate\Support\Facades\Auth::login($user);

        return redirect('/shop');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $context = $request->input('context');
        $guard = ($context === 'admin') ? 'admin' : 'web';

        if (\Illuminate\Support\Facades\Auth::guard($guard)->attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = \Illuminate\Support\Facades\Auth::guard($guard)->user();

            // Intranet context: Only staff allowed
            if ($context === 'admin') {
                $allowedSlugs = ['admin_general', 'admin_sede', 'ingeniero', 'cajero'];
                if (!in_array($user->role->slug, $allowedSlugs)) {
                    \Illuminate\Support\Facades\Auth::guard($guard)->logout();
                    return back()->withErrors(['email' => 'Esta cuenta no tiene acceso administrativo.']);
                }
                if ($user->isSedeAdmin() || $user->isCajero()) {
                    return redirect()->route('admin.pos.index');
                }
                return redirect()->route('intranet.dashboard');
            }

            // Shop context: Only clients allowed
            if ($user->role->slug !== 'cliente') {
                \Illuminate\Support\Facades\Auth::guard($guard)->logout();
                return back()->withErrors(['email' => 'Los administradores deben ingresar por el portal de intranet.']);
            }
            
            return redirect()->intended('/shop');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden con nuestros registros.',
        ]);
    }

    public function logout(Request $request)
    {
        $isAdmin = auth('admin')->check();
        
        if ($isAdmin) {
            \Illuminate\Support\Facades\Auth::guard('admin')->logout();
        } else {
            \Illuminate\Support\Facades\Auth::guard('web')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if ($isAdmin) {
            return redirect()->route('admin.login');
        }
        
        return redirect('/');
    }
}
