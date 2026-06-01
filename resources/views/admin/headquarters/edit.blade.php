@extends('layouts.intranet')

@section('title', 'Editar Sede | Gourmetica Intranet')

@section('content')
<div class="max-w-2xl mx-auto">
    <header class="mb-10">
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Editar Sede</h1>
        <p class="text-slate-400 mt-2">Modificando la información de: {{ $headquarter->name }}</p>
    </header>

    <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
        <form action="{{ route('admin.headquarters.update', $headquarter->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Nombre de la Sede</label>
                    <input type="text" name="name" value="{{ $headquarter->name }}" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Ciudad</label>
                    <input type="text" name="city" value="{{ $headquarter->city }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Teléfono</label>
                    <input type="text" name="phone" value="{{ $headquarter->phone }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Dirección Completa</label>
                    <input type="text" name="address" value="{{ $headquarter->address }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Email de Contacto</label>
                    <input type="email" name="email" value="{{ $headquarter->email }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center text-slate-400 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $headquarter->is_active ? 'checked' : '' }} class="mr-3 w-5 h-5 rounded bg-slate-800 border-slate-700 text-brand-secondary focus:ring-brand-secondary transition-all">
                        <span class="text-sm">Sede Activa (Disponible para ventas y asignación)</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.headquarters.index') }}" class="px-6 py-3 text-slate-400 hover:text-white transition-colors text-sm font-bold">CANCELAR</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
                    ACTUALIZAR SEDE
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
