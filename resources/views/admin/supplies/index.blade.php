@extends('layouts.intranet')

@section('title', 'Insumos | Gourmetica Intranet')

@section('content')
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Insumos (Materia Prima)</h1>
        <p class="text-slate-400 mt-1 text-sm">Gestiona los ingredientes de producción clasificados por categorías y su stock por sede.</p>
    </div>
    @if(auth('admin')->user()->isAdmin() || auth('admin')->user()->isIngeniero())
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.supplies.create') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo Insumo
        </a>
    </div>
    @endif
</header>

<!-- Barra de Filtros y Búsqueda -->
<div class="bg-[#1E293B] p-4 rounded-2xl border border-slate-700 shadow-lg mb-6">
    <form method="GET" action="{{ route('admin.supplies.index') }}" class="flex flex-col md:flex-row items-center gap-4">
        <!-- Búsqueda por texto -->
        <div class="relative flex-1 w-full">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar insumo por nombre..." class="w-full pl-10 pr-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:border-brand-secondary outline-none transition-colors placeholder-slate-500">
        </div>

        <!-- Filtro por Categoría -->
        <div class="w-full md:w-64">
            <select name="category_id" onchange="this.form.submit()" class="w-full px-4 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white text-sm focus:border-brand-secondary outline-none transition-colors cursor-pointer">
                <option value="">Todas las Categorías ({{ $categories->count() }})</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ (string)request('category_id') === (string)$category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
                @endforeach
            </select>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center gap-2 w-full md:w-auto">
            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center">
                Filtrar
            </button>
            @if(request()->filled('search') || request()->filled('category_id'))
            <a href="{{ route('admin.supplies.index') }}" class="px-4 py-2.5 bg-slate-800/60 hover:bg-slate-800 text-slate-400 hover:text-white rounded-xl text-sm font-semibold transition-colors flex items-center justify-center" title="Limpiar filtros">
                Limpiar
            </a>
            @endif
        </div>
    </form>
</div>

<!-- Tabla de Insumos -->
<div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Insumo</th>
                    <th class="px-6 py-4">Categoría</th>
                    <th class="px-6 py-4">Unidad</th>
                    <th class="px-6 py-4">Stock Actual por Sede</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($supplies as $supply)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-bold text-white block">{{ $supply->name }}</span>
                        <span class="text-[11px] text-slate-500 font-mono">ID: #{{ $supply->id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($supply->category)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-brand-primary/10 text-brand-primary border border-brand-primary/20">
                            {{ $supply->category->name }}
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs text-slate-400 bg-slate-800 border border-slate-700">
                            Sin categoría
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-sm">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 font-mono text-xs font-bold uppercase">
                            {{ $supply->unit }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-2">
                            @foreach($headquarters as $hq)
                                @php
                                    $hqPivot = $supply->headquarters->firstWhere('id', $hq->id);
                                    $stockVal = $hqPivot ? (float)$hqPivot->pivot->stock : 0.0;
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs {{ $stockVal > 0 ? 'bg-slate-800 text-slate-200 border border-slate-700' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                    <span class="font-medium text-slate-400">{{ $hq->name }}:</span>
                                    <span class="font-mono font-bold {{ $stockVal > 0 ? 'text-brand-secondary' : 'text-red-400' }}">
                                        {{ number_format($stockVal, $stockVal == floor($stockVal) ? 0 : 2) }} {{ $supply->unit }}
                                    </span>
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center space-x-2">
                            <a href="{{ route('admin.supplies.stock', $supply->id) }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-brand-primary/10 hover:bg-brand-primary/20 text-brand-primary font-bold text-xs transition-colors" title="Ajustar Stock">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                Stock
                            </a>

                            @if(auth('admin')->user()->isAdmin() || auth('admin')->user()->isIngeniero())
                            <a href="{{ route('admin.supplies.edit', $supply->id) }}" class="p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            
                            <form action="{{ route('admin.supplies.destroy', $supply->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este insumo?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="max-w-sm mx-auto text-center space-y-3">
                            <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-500 mx-auto flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <p class="text-slate-400 text-sm">No se encontraron insumos con los filtros seleccionados.</p>
                            @if(request()->filled('search') || request()->filled('category_id'))
                            <a href="{{ route('admin.supplies.index') }}" class="inline-block text-xs text-brand-secondary hover:underline">Limpiar filtros</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($supplies->hasPages())
    <div class="p-6 border-t border-slate-700 bg-slate-800/30">
        {{ $supplies->links() }}
    </div>
    @endif
</div>
@endsection
