@extends('layouts.intranet')

@section('title', 'Detalle de Solicitud | Gourmetica Intranet')

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div class="flex items-center">
        <a href="{{ route('admin.catering.index') }}" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 transition-colors mr-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Solicitud #{{ str_pad($cateringRequest->id, 5, '0', STR_PAD_LEFT) }}</h1>
            <p class="text-slate-400 mt-1">Recibida el {{ $cateringRequest->created_at->format('d de M, Y a las H:i') }}</p>
        </div>
    </div>
    
    <div class="bg-slate-800 px-6 py-2 rounded-xl border border-slate-700">
        <span class="text-xs text-slate-400 uppercase font-bold tracking-widest mr-3">Estado Actual:</span>
        @if($cateringRequest->status === 'pending')
            <span class="text-yellow-500 font-bold uppercase tracking-wider text-sm">Pendiente</span>
        @elseif($cateringRequest->status === 'reviewed')
            <span class="text-blue-500 font-bold uppercase tracking-wider text-sm">Revisado</span>
        @elseif($cateringRequest->status === 'contacted')
            <span class="text-brand-secondary font-bold uppercase tracking-wider text-sm">Contactado</span>
        @else
            <span class="text-emerald-500 font-bold uppercase tracking-wider text-sm">Completado</span>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-8">
        <!-- Request Details -->
        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl p-8">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Detalles del Evento
            </h2>
            
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50">
                    <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-500 mb-1">Tipo de Evento</span>
                    <span class="text-lg font-bold text-white">{{ $cateringRequest->event_type }}</span>
                </div>
                <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50">
                    <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-500 mb-1">Fecha del Evento</span>
                    <span class="text-lg font-bold text-brand-secondary">{{ \Carbon\Carbon::parse($cateringRequest->event_date)->format('d M, Y') }}</span>
                </div>
                <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50">
                    <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-500 mb-1">Nº Invitados</span>
                    <span class="text-lg font-bold text-white">{{ $cateringRequest->guests }} personas</span>
                </div>
                <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700/50">
                    <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-500 mb-1">Días Faltantes</span>
                    @php
                        $eventDate = \Carbon\Carbon::parse($cateringRequest->event_date)->startOfDay();
                        $today = now()->startOfDay();
                        $isPast = $eventDate->isBefore($today);
                        $daysLeft = (int) $today->diffInDays($eventDate);
                    @endphp
                    @if($isPast)
                        <span class="text-lg font-bold text-red-400">Evento Pasado</span>
                    @else
                        <span class="text-lg font-bold text-white">{{ $daysLeft }} días</span>
                    @endif
                </div>
            </div>

            @if($cateringRequest->message)
            <div>
                <span class="block text-xs uppercase font-bold tracking-widest text-slate-500 mb-3">Mensaje / Requerimientos Específicos</span>
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-700/50 text-slate-300 leading-relaxed italic">
                    "{{ $cateringRequest->message }}"
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="space-y-8">
        <!-- Client Info -->
        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl p-8">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Datos del Cliente
            </h2>
            
            <div class="space-y-6">
                <div>
                    <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-500 mb-1">Nombre Completo</span>
                    <span class="text-base font-medium text-white">{{ $cateringRequest->name }}</span>
                </div>
                <div>
                    <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-500 mb-1">Teléfono</span>
                    <a href="tel:{{ $cateringRequest->phone }}" class="text-base font-bold text-brand-secondary hover:underline flex items-center">
                        {{ $cateringRequest->phone }}
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $cateringRequest->phone) }}" target="_blank" class="mt-2 inline-flex items-center px-3 py-1 bg-green-500/10 text-green-500 border border-green-500/20 rounded-lg text-xs font-bold hover:bg-green-500/20 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Contactar por WhatsApp
                    </a>
                </div>
                <div>
                    <span class="block text-[10px] uppercase font-bold tracking-widest text-slate-500 mb-1">Correo Electrónico</span>
                    <a href="mailto:{{ $cateringRequest->email }}" class="text-base font-medium text-white hover:text-brand-secondary transition-colors">
                        {{ $cateringRequest->email }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Update Status -->
        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl p-8">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                <svg class="w-6 h-6 mr-3 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                Actualizar Estado
            </h2>
            
            <form action="{{ route('admin.catering.update_status', $cateringRequest) }}" method="POST">
                @csrf
                @method('PATCH')
                
                <div class="space-y-4">
                    <label class="flex items-center p-4 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-800/50 transition-colors {{ $cateringRequest->status === 'pending' ? 'bg-slate-800 border-brand-secondary' : '' }}">
                        <input type="radio" name="status" value="pending" {{ $cateringRequest->status === 'pending' ? 'checked' : '' }} class="text-brand-secondary focus:ring-brand-secondary bg-slate-900 border-slate-600">
                        <span class="ml-3 text-sm font-bold text-white">Pendiente</span>
                    </label>
                    
                    <label class="flex items-center p-4 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-800/50 transition-colors {{ $cateringRequest->status === 'reviewed' ? 'bg-slate-800 border-brand-secondary' : '' }}">
                        <input type="radio" name="status" value="reviewed" {{ $cateringRequest->status === 'reviewed' ? 'checked' : '' }} class="text-brand-secondary focus:ring-brand-secondary bg-slate-900 border-slate-600">
                        <span class="ml-3 text-sm font-bold text-white">Revisado (Evaluando)</span>
                    </label>
                    
                    <label class="flex items-center p-4 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-800/50 transition-colors {{ $cateringRequest->status === 'contacted' ? 'bg-slate-800 border-brand-secondary' : '' }}">
                        <input type="radio" name="status" value="contacted" {{ $cateringRequest->status === 'contacted' ? 'checked' : '' }} class="text-brand-secondary focus:ring-brand-secondary bg-slate-900 border-slate-600">
                        <span class="ml-3 text-sm font-bold text-white">Contactado (Cotización Enviada)</span>
                    </label>
                    
                    <label class="flex items-center p-4 border border-slate-700 rounded-xl cursor-pointer hover:bg-slate-800/50 transition-colors {{ $cateringRequest->status === 'completed' ? 'bg-slate-800 border-brand-secondary' : '' }}">
                        <input type="radio" name="status" value="completed" {{ $cateringRequest->status === 'completed' ? 'checked' : '' }} class="text-brand-secondary focus:ring-brand-secondary bg-slate-900 border-slate-600">
                        <span class="ml-3 text-sm font-bold text-white">Completado (Venta Cerrada)</span>
                    </label>
                </div>

                <button type="submit" class="w-full mt-6 bg-brand-secondary text-brand-dark font-bold py-3 rounded-xl hover:bg-white hover:scale-105 transition-all shadow-lg">
                    GUARDAR CAMBIOS
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
