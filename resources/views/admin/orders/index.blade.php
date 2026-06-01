@extends('layouts.intranet')

@section('title', 'Pedidos | Gourmetica Intranet')

@section('content')
<header class="mb-10">
    <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Gestión de Pedidos</h1>
    <p class="text-slate-400 mt-2">Monitorea y actualiza el estado de los pedidos entrantes.</p>
</header>

        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4">ID / Fecha</th>
                            <th class="px-6 py-4">Cliente</th>
                            <th class="px-6 py-4">Sede</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($orders as $order)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-bold text-white block">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <span class="text-xs text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-slate-300 block">{{ $order->user->name }}</span>
                                <span class="text-xs text-slate-500">{{ $order->address }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded bg-slate-700 text-xs text-slate-400">{{ $order->headquarter->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-white font-bold">S/ {{ number_format($order->total, 2) }}</td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs text-white focus:border-brand-primary outline-none">
                                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>Preparando</option>
                                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Enviado</option>
                                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                </form>
                            </td>
                            <td class="px-6 py-4 flex gap-3">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-brand-secondary hover:text-white transition-colors text-sm font-medium" title="Ver detalles">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('admin.orders.ticket', $order->id) }}" target="_blank" class="text-brand-secondary hover:text-white transition-colors text-sm font-medium" title="Imprimir Ticket">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">No hay pedidos registrados aún.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-700">
                {{ $orders->links() }}
            </div>
        </div>
@endsection
