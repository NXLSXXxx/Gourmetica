@extends('layouts.intranet')

@section('title', 'Nuevo Insumo | Gourmetica Intranet')

@section('content')
<div class="max-w-3xl mx-auto" x-data="{ isNewCategory: false }">
    <header class="mb-8">
        <a href="{{ route('admin.supplies.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-3 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver a Insumos
        </a>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Nuevo Insumo</h1>
        <p class="text-slate-400 mt-1 text-sm">Registra una materia prima o ingrediente, asígnale una categoría y establece su stock inicial por sede.</p>
    </header>

    @if ($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-[#1E293B] p-8 rounded-2xl border border-slate-700 shadow-xl">
        <form action="{{ route('admin.supplies.store') }}" method="POST" class="space-y-8">
            @csrf
            
            <!-- Bloque 1: Datos Básicos del Insumo -->
            <div class="space-y-5">
                <h2 class="text-lg font-bold text-white flex items-center gap-2 border-b border-slate-700 pb-3">
                    <span class="w-7 h-7 rounded-lg bg-brand-primary/20 text-brand-primary flex items-center justify-center text-xs font-mono font-bold">1</span>
                    Información Básica
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Nombre -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-300 mb-2">Nombre del Insumo <span class="text-red-400">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-colors placeholder-slate-500 text-sm" placeholder="Ej. Harina Pastelera, Crema de Leche 35%, Huevos">
                    </div>

                    <!-- Categoría de Insumo -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-slate-300">Categoría <span class="text-red-400">*</span></label>
                            <button type="button" @click="isNewCategory = !isNewCategory" class="text-xs text-brand-secondary hover:underline cursor-pointer">
                                <span x-show="!isNewCategory">+ Crear nueva categoría</span>
                                <span x-show="isNewCategory">← Seleccionar existente</span>
                            </button>
                        </div>

                        <!-- Selector existente -->
                        <div x-show="!isNewCategory">
                            <select name="supply_category_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-colors cursor-pointer text-sm">
                                <option value="">Selecciona una categoría...</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('supply_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Input para crear categoría al vuelo -->
                        <div x-show="isNewCategory" style="display: none;">
                            <input type="text" name="new_category" value="{{ old('new_category') }}" placeholder="Nombre de la nueva categoría (Ej. Frutas y Pulpa)" class="w-full bg-slate-900 border border-brand-secondary/50 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-colors placeholder-slate-500 text-sm">
                        </div>
                    </div>

                    <!-- Unidad de Medida -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Unidad de Medida <span class="text-red-400">*</span></label>
                        <select name="unit" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-colors cursor-pointer text-sm">
                            <option value="g" {{ old('unit') === 'g' ? 'selected' : '' }}>Gramos (g) - Sólidos (Harina, Azúcar, Chocolate)</option>
                            <option value="ml" {{ old('unit') === 'ml' ? 'selected' : '' }}>Mililitros (ml) - Líquidos (Leche, Vainilla, Aceite)</option>
                            <option value="und" {{ old('unit') === 'und' ? 'selected' : '' }}>Unidades (und) - Enteros (Huevos, Cajas, Envases)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Bloque 2: Stock Inicial por Sede -->
            <div class="space-y-4 pt-4">
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <span class="w-7 h-7 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-mono font-bold">2</span>
                        Stock Inicial por Sede (Inventario)
                    </h2>
                    <p class="text-slate-400 text-xs mt-1">Indica la cantidad disponible actualmente en el almacén de cada sede. Puedes dejar en 0 si aún no hay existencias.</p>
                </div>

                <div class="space-y-3 bg-slate-900/60 p-4 rounded-xl border border-slate-800">
                    @foreach($headquarters as $hq)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-3 rounded-lg bg-slate-800/40 border border-slate-700/60 gap-3">
                        <div>
                            <span class="font-bold text-white text-sm block">{{ $hq->name }}</span>
                            <span class="text-xs text-slate-500">{{ $hq->address }}</span>
                        </div>
                        <div class="flex items-center gap-2 self-end sm:self-center">
                            <label class="text-xs text-slate-400 font-medium">Stock Inicial:</label>
                            <input type="number" step="0.0001" min="0" name="headquarters[{{ $hq->id }}][stock]" value="{{ old("headquarters.{$hq->id}.stock", 0) }}" class="w-32 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white font-mono font-bold text-right text-sm focus:border-brand-secondary outline-none">
                            <span class="text-xs text-slate-400 font-mono font-bold uppercase w-8"></span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="pt-6 border-t border-slate-700 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.supplies.index') }}" class="px-6 py-3 text-slate-400 hover:text-white transition-colors text-sm font-bold">CANCELAR</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
                    GUARDAR INSUMO Y STOCK
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
