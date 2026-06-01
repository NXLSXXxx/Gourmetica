@extends('layouts.intranet')

@section('title', 'Detalle de Sede | Gourmetica Intranet')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <a href="{{ route('intranet.dashboard') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-2 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al Dashboard
        </a>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">{{ $headquarter->name }}</h1>
        <p class="text-slate-400 mt-1">{{ $headquarter->address }}, {{ $headquarter->city }}</p>
    </div>
    @if(auth('admin')->user()->isAdmin())
    <a href="{{ route('admin.headquarters.edit', $headquarter->id) }}" class="px-6 py-3 rounded-xl bg-slate-800 text-white font-bold text-sm hover:bg-slate-700 transition-colors border border-slate-700">
        EDITAR SEDE
    </a>
    @endif
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Info & Staff -->
    <div class="lg:col-span-1 space-y-8">
        <div class="bg-brand-primary p-6 rounded-2xl border border-slate-700 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-6 border-b border-slate-700 pb-4">Información de Contacto</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Teléfono</p>
                    <p class="text-slate-300">{{ $headquarter->phone ?? 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Email</p>
                    <p class="text-slate-300">{{ $headquarter->email ?? 'No registrado' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Estado</p>
                    <span class="px-2 py-1 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                        {{ $headquarter->is_active ? 'ACTIVO' : 'INACTIVO' }}
                    </span>
                </div>
            </div>
        </div>

        @if(auth('admin')->user()->isAdmin() || auth('admin')->user()->isIngeniero())
        <div class="bg-brand-primary p-6 rounded-2xl border border-slate-700 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-6 border-b border-slate-700 pb-4">Personal Asignado</h2>
            <div class="space-y-4">
                @php
                    $staffList = auth('admin')->user()->isIngeniero() 
                        ? $headquarter->users 
                        : $headquarter->users->where('role.slug', 'admin_sede');
                @endphp

                @forelse($staffList as $staff)
                <div class="flex items-center p-3 bg-slate-800/30 rounded-xl border border-slate-700/50">
                    <div class="w-8 h-8 rounded-full bg-brand-secondary/20 flex items-center justify-center text-brand-secondary text-xs font-bold mr-3">
                        {{ substr($staff->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-white text-sm font-semibold">{{ $staff->name }}</p>
                        <p class="text-[10px] text-slate-500 uppercase">{{ $staff->role->name }}</p>
                    </div>
                </div>
                @empty
                <p class="text-slate-500 text-sm italic text-center">No hay personal asignado</p>
                @endforelse
            </div>
        </div>
        @endif
    </div>

    <!-- Inventory -->
    <div class="lg:col-span-2">
        <div class="bg-brand-primary rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-slate-700">
                <h2 class="text-lg font-bold text-white">Inventario en Sede</h2>
                <p class="text-xs text-slate-500 mt-1">Stock y precios actuales para este local.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-slate-800/50 text-slate-400 text-[10px] uppercase tracking-wider">
                            <th class="px-6 py-4">Producto</th>
                            <th class="px-6 py-4 text-center">Stock Disponible</th>
                            <th class="px-6 py-4 text-right">Precio en Sede</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($headquarter->products as $product)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 font-medium text-white">{{ $product->name }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-3 py-1 rounded-full {{ $product->pivot->stock > 5 ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500' }} font-bold text-xs">
                                    {{ $product->pivot->stock }} uds.
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-brand-secondary font-bold font-serif">
                                S/ {{ number_format($product->pivot->price ?? $product->base_price, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-slate-500">No hay productos en inventario para esta sede.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
