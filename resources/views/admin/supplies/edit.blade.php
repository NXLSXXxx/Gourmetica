@extends('layouts.intranet')

@section('title', 'Editar Insumo | Gourmetica Intranet')

@section('content')
<div class="max-w-2xl mx-auto">
    <header class="mb-10">
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Editar Insumo</h1>
        <p class="text-slate-400 mt-2">Modifica los detalles del ingrediente: {{ $supply->name }}</p>
    </header>

    <div class="bg-[#1E293B] p-8 rounded-2xl border border-slate-700 shadow-xl">
        <form action="{{ route('admin.supplies.update', $supply->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Nombre del Insumo</label>
                    <input type="text" name="name" value="{{ $supply->name }}" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Unidad de Medida</label>
                    <select name="unit" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all cursor-pointer">
                        <option value="g" {{ $supply->unit === 'g' ? 'selected' : '' }}>Gramos (g)</option>
                        <option value="ml" {{ $supply->unit === 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
                        <option value="und" {{ $supply->unit === 'und' ? 'selected' : '' }}>Unidades (und)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.supplies.index') }}" class="px-6 py-3 text-slate-400 hover:text-white transition-colors text-sm font-bold">CANCELAR</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
                    ACTUALIZAR INSUMO
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
