@extends('layouts.intranet')

@section('title', 'Detalles del Pedido #' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . ' | Gourmetica Intranet')

@section('content')
<div class="max-w-5xl mx-auto">
    <header class="flex flex-col md:flex-row md:justify-between md:items-center mb-12 gap-4">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-4 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Pedidos
            </a>
            <h1 class="text-3xl font-serif font-bold text-white tracking-tight flex items-center">
                Pedido #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                <span class="ml-4 text-xs px-3 py-1.5 rounded-full font-sans font-bold tracking-wide uppercase 
                    @if($order->status === 'delivered') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                    @elseif($order->status === 'cancelled') bg-red-500/10 text-red-400 border border-red-500/20
                    @elseif($order->status === 'pending') bg-amber-500/10 text-amber-400 border border-amber-500/20
                    @else bg-blue-500/10 text-blue-400 border border-blue-500/20 @endif">
                    @if($order->status === 'pending') Pendiente
                    @elseif($order->status === 'preparing') Preparando
                    @elseif($order->status === 'shipped') Enviado
                    @elseif($order->status === 'delivered') Entregado
                    @else Cancelado @endif
                </span>
            </h1>
            <p class="text-slate-400 mt-2">Registrado el {{ $order->created_at->format('d/m/Y H:i A') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.ticket', $order->id) }}" target="_blank" class="px-6 py-3.5 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm flex items-center hover:scale-105 transition-transform shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                IMPRIMIR TICKET
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-8 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Order Stats and Controls (left 2 cols on big screen) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Products Card -->
            <div class="bg-brand-primary border border-slate-700 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 bg-slate-800/40 border-b border-slate-700">
                    <h2 class="text-lg font-serif font-bold text-white">Detalle de Productos</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-800/20 text-slate-400 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4">Producto</th>
                                <th class="px-6 py-4 text-center">Cant.</th>
                                <th class="px-6 py-4 text-right">Unitario</th>
                                <th class="px-6 py-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700 text-slate-300">
                            @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-5">
                                    <span class="font-medium text-white block">{{ $item->product->name }}</span>
                                    @if($item->options)
                                        <div class="mt-1.5 space-y-0.5 text-xs text-slate-500 font-mono">
                                            @foreach($item->options as $key => $val)
                                                <span>• {{ strtoupper($key) }}: {{ strtoupper($val) }}</span><br>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center font-bold text-white">{{ $item->quantity }}</td>
                                <td class="px-6 py-5 text-right">S/ {{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-5 text-right font-bold text-white">S/ {{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="bg-slate-800/40 px-6 py-5 border-t border-slate-700 flex justify-between items-center">
                    <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Monto Total</span>
                    <span class="text-2xl font-bold text-white">S/ {{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <!-- Notes or Info -->
            @if($order->notes)
            <div class="bg-brand-primary border border-slate-700 p-6 rounded-2xl shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-3">Notas Especiales del Cliente</h3>
                <p class="text-slate-200 leading-relaxed italic bg-slate-800/45 p-4 rounded-xl border border-slate-700">
                    "{{ $order->notes }}"
                </p>
            </div>
            @endif

        </div>

        <!-- Sidebar Information Details (right 1 col) -->
        <div class="space-y-8">
            
            <!-- Update Status Card -->
            <div class="bg-brand-primary border border-slate-700 p-6 rounded-2xl shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Actualizar Estado</h3>
                <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    
                    <select name="status" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all cursor-pointer">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>Preparando</option>
                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Enviado</option>
                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>

                    <button type="submit" class="w-full py-3 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white font-semibold text-xs uppercase tracking-wider transition-colors">
                        Guardar Estado
                    </button>
                </form>
            </div>

            <!-- Customer Info -->
            <div class="bg-brand-primary border border-slate-700 p-6 rounded-2xl shadow-xl space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Detalles del Cliente</h3>
                
                <div class="border-t border-slate-700/60 pt-4 space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 block">Nombre</span>
                        <span class="text-white font-semibold">{{ $order->user->name }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Correo electrónico</span>
                        <span class="text-slate-300 font-mono text-xs">{{ $order->user->email }}</span>
                    </div>
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="bg-brand-primary border border-slate-700 p-6 rounded-2xl shadow-xl space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Detalles de Entrega</h3>
                
                <div class="border-t border-slate-700/60 pt-4 space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 block">Sede Preparadora</span>
                        <span class="text-white font-semibold flex items-center">
                            <span class="w-2 h-2 rounded-full bg-brand-secondary mr-2"></span>
                            {{ $order->headquarter->name }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Dirección de Envío</span>
                        <span class="text-slate-300">{{ $order->address }}</span>
                    </div>
                    @if($order->payment_method)
                    <div>
                        <span class="text-xs text-slate-500 block">Método de Pago</span>
                        <span class="text-slate-300 font-semibold uppercase tracking-wide">{{ $order->payment_method }}</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
@endsection
