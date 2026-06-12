@extends('layouts.intranet')

@section('title', 'Insumos | Gourmetica Intranet')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Insumos (Materia Prima)</h1>
        <p class="text-slate-400 mt-2">Gestiona los ingredientes de producción para tus recetas.</p>
    </div>
    @if(auth('admin')->user()->isAdmin() || auth('admin')->user()->isIngeniero())
    <a href="{{ route('admin.supplies.create') }}" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform">
        + NUEVO INSUMO
    </a>
    @endif
</header>

<div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Nombre</th>
                    <th class="px-6 py-4">Unidad de Medida</th>
                    <th class="px-6 py-4 text-center">Stock por Sede</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($supplies as $supply)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 font-medium text-white">{{ $supply->name }}</td>
                    <td class="px-6 py-4 text-slate-400 text-sm">
                        <span class="px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 font-mono text-xs">
                            {{ $supply->unit }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <a href="{{ route('admin.supplies.stock', $supply->id) }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-brand-primary/10 hover:bg-brand-primary/20 text-brand-primary font-bold text-xs transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Ver / Ajustar Stock
                        </a>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end items-center space-x-3">
                            @if(auth('admin')->user()->isAdmin() || auth('admin')->user()->isIngeniero())
                            <a href="{{ route('admin.supplies.edit', $supply->id) }}" class="text-slate-400 hover:text-brand-secondary transition-colors" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            
                            <form action="{{ route('admin.supplies.destroy', $supply->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este insumo?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-slate-500 italic">Solo lectura</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-10 text-center text-slate-500">No hay insumos registrados aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
