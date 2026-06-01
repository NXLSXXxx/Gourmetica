@extends('layouts.app')

@section('title', 'Crear Cuenta | Gourmetica')

@section('content')
<div class="min-h-screen bg-brand-bg flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <div class="text-center mb-10">
            <h2 class="text-4xl font-serif font-bold text-brand-primary tracking-widest">GOURMETICA</h2>
            <p class="text-gray-500 mt-2">Únete a nuestra comunidad exclusiva de amantes del buen pan</p>
        </div>

        <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden">
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-brand-primary/10 rounded-full blur-3xl"></div>
            
            <form action="{{ route('register.post') }}" method="POST" class="space-y-6 relative z-10">
                @csrf
                
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Nombre Completo</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all placeholder-gray-400"
                        placeholder="Juan Pérez">
                    @error('name') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Correo Electrónico</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all placeholder-gray-400"
                        placeholder="tu@correo.com">
                    @error('email') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Contraseña</label>
                    <input type="password" name="password" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all placeholder-gray-400"
                        placeholder="••••••••">
                    @error('password') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-2">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all placeholder-gray-400"
                        placeholder="••••••••">
                </div>

                <button type="submit" class="btn-premium w-full py-4 text-lg font-bold">
                    CREAR MI CUENTA
                </button>
            </form>

            <div class="mt-8 pt-8 border-t border-gray-100 text-center">
                <p class="text-gray-500 text-sm">
                    ¿Ya tienes una cuenta? 
                    <a href="{{ route('login') }}" class="text-brand-primary font-bold hover:underline">Inicia Sesión</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
