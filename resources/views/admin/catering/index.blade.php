@extends('layouts.intranet')

@section('title', 'Solicitudes de Catering | Gourmetica Intranet')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Solicitudes de Catering</h1>
        <p class="text-slate-400 mt-2">Gestiona las cotizaciones de eventos de tus clientes.</p>
    </div>
</header>

<div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">ID / Fecha</th>
                    <th class="px-6 py-4">Cliente</th>
                    <th class="px-6 py-4">Evento</th>
                    <th class="px-6 py-4">Invitados</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($requests as $req)
                <tr class="hover:bg-slate-800/30 transition-colors cursor-pointer" onclick="window.location='{{ route('admin.catering.show', $req) }}'">
                    <td class="px-6 py-4">
                        <div class="text-white font-bold">#{{ str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</div>
                        <div class="text-[10px] text-slate-500 uppercase">{{ $req->created_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-white font-medium">{{ $req->name }}</div>
                        <div class="text-xs text-slate-400">{{ $req->phone }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-white">{{ $req->event_type }}</div>
                        <div class="text-xs text-brand-secondary font-bold">{{ \Carbon\Carbon::parse($req->event_date)->format('d M, Y') }}</div>
                    </td>
                    <td class="px-6 py-4 text-white">
                        <span class="px-3 py-1 bg-slate-800 rounded-lg text-sm font-bold">{{ $req->guests }}</span>
                    </td>
                    <td class="px-6 py-4">
                        @if($req->status === 'pending')
                            <span class="px-3 py-1 bg-yellow-500/20 text-yellow-500 rounded-full text-xs font-bold uppercase tracking-wider border border-yellow-500/20">Pendiente</span>
                        @elseif($req->status === 'reviewed')
                            <span class="px-3 py-1 bg-blue-500/20 text-blue-500 rounded-full text-xs font-bold uppercase tracking-wider border border-blue-500/20">Revisado</span>
                        @elseif($req->status === 'contacted')
                            <span class="px-3 py-1 bg-brand-secondary/20 text-brand-secondary rounded-full text-xs font-bold uppercase tracking-wider border border-brand-secondary/20">Contactado</span>
                        @else
                            <span class="px-3 py-1 bg-emerald-500/20 text-emerald-500 rounded-full text-xs font-bold uppercase tracking-wider border border-emerald-500/20">Completado</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.catering.show', $req) }}" class="inline-flex items-center text-brand-secondary hover:text-white transition-colors text-sm font-bold">
                            Ver Detalles
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-500">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path></svg>
                        </div>
                        <p class="text-slate-400 font-medium">Aún no hay solicitudes de catering.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($requests->hasPages())
    <div class="px-6 py-4 border-t border-slate-700">
        {{ $requests->links() }}
    </div>
    @endif
</div>
@endsection
