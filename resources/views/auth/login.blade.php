@extends('layouts.app')

@section('title', 'Mi Cuenta | Gourmetica')

@section('content')
<div class="min-h-screen bg-brand-bg flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-serif font-bold text-brand-primary tracking-widest">GOURMETICA</h2>
            <p class="text-gray-500 mt-2">Bienvenido de nuevo a tu pastelería favorita</p>
        </div>

        <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand-primary/10 rounded-full blur-3xl"></div>
            
            <form action="{{ route('login.post') }}" method="POST" class="space-y-6 relative z-10">
                @csrf
                <input type="hidden" name="context" value="client">
                
                @if($errors->any())
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl text-sm">
                    {{ $errors->first() }}
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Correo Electrónico</label>
                    <input type="email" name="email" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all" placeholder="cliente@ejemplo.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Contraseña</label>
                    <input type="password" name="password" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all" placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-slate-400">
                        <input type="checkbox" class="mr-2 rounded bg-slate-800 border-slate-600 text-brand-primary focus:ring-brand-primary">
                        Recordarme
                    </label>
                    <a href="#" class="text-brand-secondary hover:underline">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="btn-premium w-full py-4 text-lg font-bold">
                    INICIAR SESIÓN
                </button>

                <div class="relative py-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-100"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="bg-white px-4 text-gray-400">O continuar con</span>
                    </div>
                </div>

                <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-200 py-3 rounded-xl font-bold text-gray-700 hover:bg-gray-50 transition-all shadow-sm">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    INGRESAR CON GOOGLE
                </a>
            </form>
        </div>
        
        <p class="text-center text-slate-500 mt-8 text-sm">
            ¿No tienes una cuenta? <a href="{{ route('register') }}" class="text-brand-primary font-bold hover:underline">Regístrate aquí</a>.
        </p>
    </div>
</div>
@endsection
