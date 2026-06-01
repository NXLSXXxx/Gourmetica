@extends('layouts.intranet')

@section('title', 'Nueva Sede | Gourmetica Intranet')

@section('content')
<div class="max-w-2xl mx-auto">
    <header class="mb-10">
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Nueva Sede</h1>
        <p class="text-slate-400 mt-2">Registra un nuevo local para la cadena Gourmetica.</p>
    </header>

    <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
        <form action="{{ route('admin.headquarters.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Nombre de la Sede</label>
                    <input type="text" name="name" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all" placeholder="Ej. Sede Miraflores">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Ciudad</label>
                    <input type="text" name="city" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all" placeholder="Ej. Lima">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Teléfono</label>
                    <input type="text" name="phone" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all" placeholder="Ej. 01 4445556">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Dirección Completa</label>
                    <input type="text" name="address" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all" placeholder="Av. Principal 123">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Email de Contacto</label>
                    <input type="email" name="email" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all" placeholder="sede@gourmetica.pe">
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.headquarters.index') }}" class="px-6 py-3 text-slate-400 hover:text-white transition-colors text-sm font-bold">CANCELAR</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
                    GUARDAR SEDE
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
