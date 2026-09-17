@extends('layouts.intranet')

@section('title', 'Historial de ' . $client->name . ' | Gourmetica Intranet')

@section('content')
<!-- Barra Superior y Navegación -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.clients') }}" 
           class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 transition-colors border border-slate-700" 
           title="Volver a la lista de clientes">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-2xl sm:text-3xl font-serif font-bold text-white tracking-tight">{{ $client->name }}</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Cliente</span>
            </div>
            <p class="text-slate-400 text-xs sm:text-sm mt-0.5">Historial consolidado de pedidos web y ventas en tienda.</p>
        </div>
    </div>
    <div class="flex items-center gap-2 text-xs text-slate-400 bg-slate-800/60 px-3.5 py-2 rounded-xl border border-slate-700">
        <svg class="w-4 h-4 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Registrado el: <strong class="text-white">{{ $client->created_at ? $client->created_at->format('d/m/Y H:i') : 'N/A' }}</strong>
    </div>
</div>

<!-- Ficha de Datos del Cliente & Métricas -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <!-- Perfil del Cliente -->
    <div class="bg-gradient-to-br from-[#1E293B] to-slate-900 rounded-2xl border border-slate-700 p-5 shadow-lg md:col-span-1 flex flex-col justify-between">
        <div>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-secondary/20 text-brand-secondary border border-brand-secondary/30 flex items-center justify-center font-serif font-bold text-lg">
                    {{ strtoupper(substr($client->name, 0, 2)) }}
                </div>
                <div>
                    <h3 class="font-bold text-white text-sm line-clamp-1">{{ $client->name }}</h3>
                    <span class="text-[11px] text-slate-400">ID: #{{ str_pad($client->id, 5, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
            <div class="space-y-2.5 text-xs">
                <div class="flex items-start gap-2 text-slate-300">
                    <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="break-all">{{ $client->email }}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-300">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ $client->phone ?? 'Sin teléfono' }}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-300">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Sede: <strong class="text-brand-secondary">{{ $client->headquarter->name ?? 'Cualquiera' }}</strong></span>
                </div>
            </div>
        </div>
        @if($client->phone)
        <div class="mt-4 pt-4 border-t border-slate-700/60">
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $client->phone) }}" target="_blank" class="w-full py-2 px-3 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                Contactar por WhatsApp
            </a>
        </div>
        @endif
    </div>

    <!-- KPIs Consumo -->
    <div class="md:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Total Gastado -->
        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 p-5 shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Consumido</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold">
                    S/
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl sm:text-3xl font-bold text-white">S/ {{ number_format($totalSpent, 2) }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Suma de pedidos web y ventas directas</p>
            </div>
        </div>

        <!-- Pedidos Online -->
        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 p-5 shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pedidos Online</span>
                <div class="w-8 h-8 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl sm:text-3xl font-bold text-white">{{ $totalOrdersCount }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Compras en la tienda web</p>
            </div>
        </div>

        <!-- Ventas Directas / POS -->
        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 p-5 shadow-lg flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ventas en Tienda / POS</span>
                <div class="w-8 h-8 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-4">
                <div class="text-2xl sm:text-3xl font-bold text-white">{{ $totalSalesCount }}</div>
                <p class="text-[11px] text-slate-400 mt-1">Comprobantes emitidos en caja</p>
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN 1: Pedidos Web -->
<div class="mb-10">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
            Historial de Pedidos Web ({{ $orders->total() }})
        </h2>
    </div>

    <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-800/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-700/60">
                        <th class="px-5 py-3.5">Pedido</th>
                        <th class="px-5 py-3.5">Fecha</th>
                        <th class="px-5 py-3.5">Sede</th>
                        <th class="px-5 py-3.5">Productos</th>
                        <th class="px-5 py-3.5">Pago</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5 text-right">Total</th>
                        <th class="px-5 py-3.5 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($orders as $order)
                    @php
                        $statusColors = [
                            'pending' => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                            'processing' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                            'ready' => 'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
                            'shipped' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                            'delivered' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                            'cancelled' => 'bg-red-500/20 text-red-400 border-red-500/30',
                        ];
                        $statusLabels = [
                            'pending' => 'Pendiente',
                            'processing' => 'En Preparación',
                            'ready' => 'Listo',
                            'shipped' => 'En Camino',
                            'delivered' => 'Entregado',
                            'cancelled' => 'Cancelado',
                        ];
                    @endphp
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-4 font-bold text-white text-sm">
                            #{{ $order->id }}
                        </td>
                        <td class="px-5 py-4 text-slate-300 text-xs">
                            <div>{{ $order->created_at->format('d/m/Y') }}</div>
                            <div class="text-slate-500 text-[10px]">{{ $order->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-300">
                            {{ $order->headquarter->name ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-300 max-w-xs">
                            @if($order->items && $order->items->count() > 0)
                                <div class="space-y-0.5">
                                    @foreach($order->items->take(2) as $item)
                                    <div class="truncate" title="{{ $item->product->name ?? 'Producto' }}">
                                        <span class="font-bold text-slate-400">{{ $item->quantity }}x</span> {{ $item->product->name ?? 'Producto' }}
                                    </div>
                                    @endforeach
                                    @if($order->items->count() > 2)
                                    <div class="text-[10px] text-slate-500 italic">+{{ $order->items->count() - 2 }} producto(s) más</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-500 italic">Sin ítems</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs">
                            <div class="font-semibold text-slate-200 capitalize">{{ $order->payment_method ?? 'N/A' }}</div>
                            <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded {{ $order->payment_status === 'paid' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-700 text-slate-400' }}">
                                {{ $order->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $statusColors[$order->status] ?? 'bg-slate-800 text-slate-400 border-slate-700' }}">
                                {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right font-bold text-white text-sm">
                            S/ {{ number_format($order->total, 2) }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.orders.show', $order->id) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-brand-secondary hover:text-brand-dark text-slate-300 rounded-lg text-xs font-bold transition-all border border-slate-700">
                                Ver Detalle
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center text-slate-500 text-sm">
                            Este cliente aún no tiene pedidos realizados en la tienda online.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-5 py-3 border-t border-slate-700 bg-slate-800/30">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</div>

<!-- SECCIÓN 2: Ventas en Tienda / Comprobantes POS -->
<div class="mb-10">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-white flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
            Ventas en Tienda / Comprobantes ({{ $sales->total() }})
        </h2>
    </div>

    <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-800/60 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-700/60">
                        <th class="px-5 py-3.5">Comprobante</th>
                        <th class="px-5 py-3.5">Fecha</th>
                        <th class="px-5 py-3.5">Sede</th>
                        <th class="px-5 py-3.5">Mesa / Origen</th>
                        <th class="px-5 py-3.5">Estado</th>
                        <th class="px-5 py-3.5">Estado SUNAT</th>
                        <th class="px-5 py-3.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-bold text-white text-sm">
                                {{ strtoupper($sale->document_type ?? 'Ticket') }} {{ $sale->series }}-{{ $sale->correlative }}
                            </div>
                            @if($sale->order_id)
                            <div class="text-[10px] text-blue-400">Origen: Pedido #{{ $sale->order_id }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-300">
                            <div>{{ $sale->created_at->format('d/m/Y') }}</div>
                            <div class="text-slate-500 text-[10px]">{{ $sale->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-300">
                            {{ $sale->headquarter->name ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-4 text-xs text-slate-300">
                            {{ $sale->table_number ? 'Mesa ' . $sale->table_number : ($sale->order_id ? 'Delivery Web' : 'Caja Directa') }}
                        </td>
                        <td class="px-5 py-4">
                            @if($sale->status === 'anulado' || $sale->status === 'cancelado')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-500/20 text-red-400 border border-red-500/30">Anulado</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Completado</span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            @if($sale->sunat_status === 'aceptado')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Aceptado</span>
                            @elseif($sale->sunat_status === 'rechazado')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/20 text-red-400 border border-red-500/30">Rechazado</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-700 text-slate-400">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right font-bold text-white text-sm">
                            S/ {{ number_format($sale->total, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-slate-500 text-sm">
                            No hay comprobantes de venta emitidos para este cliente en el punto de venta.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="px-5 py-3 border-t border-slate-700 bg-slate-800/30">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>

<!-- SECCIÓN 3: Productos Favoritos -->
@if(isset($favorites) && $favorites->count() > 0)
<div class="mb-8">
    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-red-400 fill-current" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        Productos Favoritos del Cliente ({{ $favorites->count() }})
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach($favorites as $fav)
        <div class="bg-slate-800/60 rounded-xl p-3 border border-slate-700 flex items-center gap-3">
            @if($fav->image)
            <img src="{{ asset('storage/' . $fav->image) }}" class="w-12 h-12 rounded-lg object-cover flex-shrink-0">
            @else
            <div class="w-12 h-12 rounded-lg bg-slate-700 flex items-center justify-center text-slate-400 flex-shrink-0 font-bold text-xs">
                G
            </div>
            @endif
            <div class="min-w-0 flex-1">
                <h4 class="text-xs font-bold text-white truncate">{{ $fav->name }}</h4>
                <p class="text-[11px] text-amber-400 font-extrabold mt-0.5">S/ {{ number_format($fav->base_price, 2) }}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
