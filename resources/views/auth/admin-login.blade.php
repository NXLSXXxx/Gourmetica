@extends('layouts.auth-admin')

@section('title', 'Iniciar Sesión | Gourmetica')

@section('content')
<div class="min-h-screen bg-[#0F172A] flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-serif font-bold text-brand-secondary tracking-widest">GOURMETICA</h2>
            <p class="text-slate-400 mt-2">Acceso a la Intranet Administrativa</p>
        </div>

        <div class="bg-[#1E293B] p-8 rounded-3xl shadow-2xl border border-slate-700 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand-primary/10 rounded-full blur-3xl"></div>
            
            <form action="{{ route('login.post') }}" method="POST" class="space-y-6 relative z-10">
                @csrf
                <input type="hidden" name="context" value="admin">
                
                @if($errors->any())
                <div class="bg-red-500/10 border border-red-500/50 text-red-500 p-4 rounded-xl text-sm">
                    {{ $errors->first() }}
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Correo Electrónico</label>
                    <input type="email" name="email" required class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all" placeholder="admin@gourmetica.pe">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Contraseña</label>
                    <input type="password" name="password" required class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all" placeholder="••••••••">
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
            </form>
        </div>
        
        <p class="text-center text-slate-500 mt-8 text-sm">
            ¿Problemas de acceso? Contacta al <a href="#" class="text-brand-secondary hover:underline">Ingeniero de Sistemas</a>.
        </p>
    </div>
</div>
@endsection
