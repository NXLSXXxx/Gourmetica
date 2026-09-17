@extends('layouts.intranet')

@section('title', 'Clientes | Gourmetica Intranet')

@section('content')
<header class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Gestión de Clientes</h1>
        <p class="text-slate-400 mt-1">Base de datos centralizada de clientes registrados y su historial de consumo.</p>
    </div>
    <div class="flex items-center gap-3">
        <span class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs font-semibold text-slate-300">
            Total Clientes: <strong class="text-brand-secondary">{{ $totalClients ?? $clients->total() }}</strong>
        </span>
    </div>
</header>

<!-- Buscador y Filtros -->
<div class="mb-6 bg-slate-800/40 p-4 rounded-2xl border border-slate-700/60">
    <form action="{{ route('admin.clients') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <input type="text" 
                   name="search" 
                   value="{{ request('search') }}" 
                   placeholder="Buscar por nombre, correo electrónico o teléfono..." 
                   class="w-full pl-10 pr-4 py-2.5 bg-slate-900/90 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:border-brand-secondary transition-colors">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-5 py-2.5 bg-brand-secondary text-brand-dark rounded-xl font-bold text-sm hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                Buscar
            </button>
            @if(request('search'))
            <a href="{{ route('admin.clients') }}" class="px-4 py-2.5 bg-slate-800 text-slate-300 rounded-xl font-semibold text-sm hover:bg-slate-700 transition-colors flex items-center justify-center">
                Limpiar
            </a>
            @endif
        </div>
    </form>
</div>

<!-- Tabla de Clientes -->
<div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-700/60">
                    <th class="px-6 py-4">Cliente</th>
                    <th class="px-6 py-4">Teléfono</th>
                    <th class="px-6 py-4">Sede Asignada</th>
                    <th class="px-6 py-4">Fecha Registro</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($clients as $client)
                <tr class="hover:bg-slate-800/40 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-brand-secondary/30 to-amber-500/20 border border-brand-secondary/30 flex items-center justify-center text-brand-secondary font-bold text-sm flex-shrink-0">
                                {{ strtoupper(substr($client->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-semibold text-white text-sm">{{ $client->name }}</div>
                                <div class="text-xs text-slate-400">{{ $client->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-slate-300 text-sm">
                        {{ $client->phone ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($client->headquarter)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-800 text-slate-200 border border-slate-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                {{ $client->headquarter->name }}
                            </span>
                        @else
                            <span class="text-xs text-slate-500 italic">No especificada</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-sm">
                        {{ $client->created_at ? $client->created_at->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.clients.show', $client->id) }}" 
                           class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-brand-secondary/10 hover:bg-brand-secondary text-brand-secondary hover:text-brand-dark transition-all text-xs font-bold border border-brand-secondary/20 hover:border-transparent shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Ver Historial
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                        <div class="flex flex-col items-center justify-center">
                            <svg class="w-12 h-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p class="text-base font-medium text-slate-400">No se encontraron clientes</p>
                            <p class="text-xs text-slate-500 mt-1">Intenta con otro término de búsqueda o limpia los filtros.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())
    <div class="px-6 py-4 border-t border-slate-700 bg-slate-800/30">
        {{ $clients->links() }}
    </div>
    @endif
</div>
@endsection
