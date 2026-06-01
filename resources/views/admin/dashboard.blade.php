@extends('layouts.intranet')

@section('title', 'Dashboard | Gourmetica Intranet')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Panel de Control</h1>
    <p class="text-slate-400 mt-2">Bienvenido, {{ auth('admin')->user()->name }}. Gestionando {{ auth('admin')->user()->isAdmin() ? 'todas las sedes' : auth('admin')->user()->headquarter->name }}.</p>
</div>
            <div class="flex items-center space-x-4">
                <span class="px-4 py-2 rounded-full bg-brand-primary text-brand-secondary text-sm font-bold border border-brand-secondary/30">
                    {{ auth('admin')->user()->role->name }}
                </span>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <div class="bg-[#1E293B] p-6 rounded-2xl border border-slate-700 shadow-xl">
                <p class="text-slate-400 text-sm font-medium mb-1">Ventas Totales</p>
                <h3 class="text-3xl font-bold text-white">{{ $stats['total_sales'] }}</h3>
                <div class="mt-4 flex items-center text-emerald-400 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <span>En esta sede</span>
                </div>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-2xl border border-slate-700 shadow-xl">
                <p class="text-slate-400 text-sm font-medium mb-1">Productos en Stock</p>
                <h3 class="text-3xl font-bold text-white">{{ $stats['total_products'] }}</h3>
                <div class="mt-4 flex items-center text-blue-400 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Activos</span>
                </div>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-2xl border border-slate-700 shadow-xl">
                <p class="text-slate-400 text-sm font-medium mb-1">Compras Realizadas</p>
                <h3 class="text-3xl font-bold text-white">{{ $stats['total_purchases'] }}</h3>
                <div class="mt-4 flex items-center text-brand-secondary text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Declaradas SUNAT</span>
                </div>
            </div>

            <div class="bg-[#1E293B] p-6 rounded-2xl border border-slate-700 shadow-xl">
                <p class="text-slate-400 text-sm font-medium mb-1">Sedes Activas</p>
                <h3 class="text-3xl font-bold text-white">{{ $stats['headquarters_count'] }}</h3>
                <div class="mt-4 flex items-center text-slate-400 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Registradas</span>
                </div>
            </div>
        </div>

        <!-- Financial & Cash Flow Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            <!-- Left Side: Inflow vs Outflow Cash Balance -->
            <div class="lg:col-span-2 bg-[#1E293B] p-8 rounded-2xl border border-slate-700 shadow-xl relative overflow-hidden">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                    <svg class="w-5 h-5 text-brand-secondary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2z"></path></svg>
                    Resumen de Flujo de Caja (Caja Chica y Ventas)
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                    <div class="p-5 bg-emerald-500/5 border border-emerald-500/10 rounded-xl">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ingresos (Ventas)</p>
                        <p class="text-2xl font-bold text-emerald-400">S/ {{ number_format($stats['total_sales_amount'], 2) }}</p>
                        <span class="text-[10px] text-slate-500 block mt-2">Dinero ingresado por ventas completadas</span>
                    </div>

                    <div class="p-5 bg-red-500/5 border border-red-500/10 rounded-xl">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Egresos (Compras/Gastos)</p>
                        <p class="text-2xl font-bold text-red-400">S/ {{ number_format($stats['total_purchases_amount'], 2) }}</p>
                        <span class="text-[10px] text-slate-500 block mt-2">Compras a proveedores y egresos de caja</span>
                    </div>

                    @php
                        $balance = $stats['total_sales_amount'] - $stats['total_purchases_amount'];
                    @endphp
                    <div class="p-5 bg-brand-secondary/5 border border-brand-secondary/20 rounded-xl">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Balance Neto en Caja</p>
                        <p class="text-2xl font-serif font-bold text-brand-secondary">S/ {{ number_format($balance, 2) }}</p>
                        <span class="text-[10px] text-slate-500 block mt-2">Disponible en caja de {{ auth('admin')->user()->isAdmin() ? 'la empresa' : 'tu sede' }}</span>
                    </div>
                </div>
                <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-brand-secondary/5 rounded-full blur-3xl"></div>
            </div>

            <!-- Right Side: Revenue by Headquarters (Admin Only) or Quick Actions -->
            <div class="lg:col-span-1 bg-[#1E293B] p-6 rounded-2xl border border-slate-700 shadow-xl flex flex-col justify-between">
                @if(auth('admin')->user()->isAdmin() && isset($stats['revenue_by_sede']))
                <div>
                    <h2 class="text-lg font-bold text-white mb-4">Ingresos por Sede</h2>
                    <div class="space-y-3 max-h-[180px] overflow-y-auto no-scrollbar">
                        @foreach($stats['revenue_by_sede'] as $revenue)
                        <div class="flex items-center justify-between p-3 bg-slate-800/40 rounded-xl border border-slate-700/60">
                            <div>
                                <p class="text-white font-bold text-xs">{{ $revenue->headquarter->name }}</p>
                                <p class="text-[9px] text-slate-500 uppercase">Recaudado</p>
                            </div>
                            <span class="text-brand-secondary font-serif font-bold text-sm">S/ {{ number_format($revenue->total, 2) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div>
                    <h2 class="text-lg font-bold text-white mb-4">Acciones de Caja</h2>
                    <div class="space-y-3">
                        <a href="{{ route('admin.purchases.create') }}" class="w-full flex items-center justify-between p-4 bg-red-500/5 hover:bg-red-500/10 transition-colors border border-red-500/20 rounded-xl text-left">
                            <div>
                                <p class="text-white font-bold text-sm">Registrar Egreso</p>
                                <p class="text-[10px] text-slate-500">Caja chica o compra informal/proveedores</p>
                            </div>
                            <span class="text-red-400 text-lg">→</span>
                        </a>
                        <a href="{{ route('admin.sales.index') }}" class="w-full flex items-center justify-between p-4 bg-emerald-500/5 hover:bg-emerald-500/10 transition-colors border border-emerald-500/20 rounded-xl text-left">
                            <div>
                                <p class="text-white font-bold text-sm">Ver Ingresos (Ventas)</p>
                                <p class="text-[10px] text-slate-500">Monitorear facturación de la sede</p>
                            </div>
                            <span class="text-emerald-400 text-lg">→</span>
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Headquarters Table -->
        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-slate-700 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Sedes Bajo tu Cargo</h2>
                @if(auth('admin')->user()->isAdmin())
                <a href="{{ route('admin.headquarters.index') }}" class="px-4 py-2 text-sm rounded-xl bg-brand-secondary text-brand-dark font-bold hover:scale-105 transition-transform">Gestionar Sedes</a>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4">Nombre</th>
                            <th class="px-6 py-4">Dirección</th>
                            <th class="px-6 py-4">Contacto</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @foreach($headquarters as $hq)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 font-medium text-white">{{ $hq->name }}</td>
                            <td class="px-6 py-4 text-slate-400 text-sm">{{ $hq->address }}, {{ $hq->city }}</td>
                            <td class="px-6 py-4 text-slate-400 text-sm">{{ $hq->phone }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">ACTIVO</span>
                            </td>
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.headquarters.show', $hq->id) }}" class="text-brand-secondary hover:underline text-sm font-medium">Ver detalles</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
    </div>
</div>
@endsection
