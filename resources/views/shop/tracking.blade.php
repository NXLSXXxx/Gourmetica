@extends('layouts.app')

@section('title', 'Seguimiento de Pedido #' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . ' | Gourmetica')

@section('content')
<div class="bg-brand-bg min-h-screen py-12">
    <div class="max-w-3xl mx-auto px-4">
        
        <!-- Cabecera -->
        <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-gray-100 pb-6 mb-6">
                <div>
                    <h1 class="text-3xl font-serif font-bold text-brand-primary mb-1">Pedido #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h1>
                    <p class="text-gray-500">Realizado el {{ $order->created_at->format('d/m/Y \a \l\a\s H:i') }}</p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-sm text-gray-500 mb-1">Monto Total</p>
                    <p class="text-2xl font-bold text-brand-secondary">S/ {{ number_format($order->total, 2) }}</p>
                </div>
            </div>

            <!-- Stepper de Estados -->
            @php
                $isDelivery = $order->delivery_zone_id || $order->delivery_price > 0;
                
                // Determinamos en qué paso vamos (1 a 5)
                $step = 1; // pending
                if ($order->status === 'preparing') $step = 2;
                if ($order->status === 'shipped') {
                    $step = 3; // Listo
                    if ($isDelivery) {
                        if ($order->nakama_status === 'recogiendo') $step = 3;
                        if ($order->nakama_status === 'llevando') $step = 4;
                    }
                }
                if ($order->status === 'delivered') $step = 5;
                if ($order->status === 'cancelled') $step = -1;
            @endphp

            @if($step === -1)
                <div class="bg-red-50 text-red-500 p-6 rounded-2xl text-center font-bold text-lg">
                    Tu pedido ha sido cancelado.
                </div>
            @else
                <div class="relative mt-10 mb-6">
                    <!-- Línea conectora base -->
                    <div class="absolute left-6 top-0 bottom-0 w-1 bg-gray-100 rounded-full sm:left-0 sm:right-0 sm:top-6 sm:bottom-auto sm:h-1 sm:w-full"></div>
                    
                    <div class="flex flex-col sm:flex-row justify-between relative z-10 gap-8 sm:gap-4">
                        
                        <!-- Paso 1: Recibido -->
                        <div class="flex sm:flex-col items-center sm:w-1/5 gap-4 sm:gap-2">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md shrink-0 transition-colors {{ $step >= 1 ? 'bg-brand-primary text-white shadow-brand-primary/30' : 'bg-white text-gray-300 border-2 border-gray-100' }}">
                                1
                            </div>
                            <div class="text-left sm:text-center">
                                <h4 class="font-bold {{ $step >= 1 ? 'text-brand-primary' : 'text-gray-400' }}">Recibido</h4>
                                <p class="text-xs text-gray-400">Orden confirmada</p>
                            </div>
                        </div>

                        <!-- Paso 2: Preparando -->
                        <div class="flex sm:flex-col items-center sm:w-1/5 gap-4 sm:gap-2">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md shrink-0 transition-colors {{ $step >= 2 ? 'bg-brand-primary text-white shadow-brand-primary/30' : 'bg-white text-gray-300 border-2 border-gray-100' }}">
                                2
                            </div>
                            <div class="text-left sm:text-center">
                                <h4 class="font-bold {{ $step >= 2 ? 'text-brand-primary' : 'text-gray-400' }}">En Cocina</h4>
                                <p class="text-xs text-gray-400">Preparando tu pedido</p>
                            </div>
                        </div>

                        @if($isDelivery)
                            <!-- Paso 3: Motorizado Asignado -->
                            <div class="flex sm:flex-col items-center sm:w-1/5 gap-4 sm:gap-2">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md shrink-0 transition-colors {{ $step >= 3 ? 'bg-brand-primary text-white shadow-brand-primary/30' : 'bg-white text-gray-300 border-2 border-gray-100' }}">
                                    3
                                </div>
                                <div class="text-left sm:text-center">
                                    <h4 class="font-bold {{ $step >= 3 ? 'text-brand-primary' : 'text-gray-400' }}">Motorizado</h4>
                                    <p class="text-xs text-gray-400">Recogiendo tu pedido</p>
                                </div>
                            </div>

                            <!-- Paso 4: En Camino -->
                            <div class="flex sm:flex-col items-center sm:w-1/5 gap-4 sm:gap-2">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md shrink-0 transition-colors {{ $step >= 4 ? 'bg-brand-primary text-white shadow-brand-primary/30' : 'bg-white text-gray-300 border-2 border-gray-100' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <div class="text-left sm:text-center">
                                    <h4 class="font-bold {{ $step >= 4 ? 'text-brand-primary' : 'text-gray-400' }}">En Camino</h4>
                                    <p class="text-xs text-gray-400">Yendo hacia ti</p>
                                </div>
                            </div>
                        @else
                            <!-- Paso 3: Listo para Recojo -->
                            <div class="flex sm:flex-col items-center sm:w-1/5 gap-4 sm:gap-2">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md shrink-0 transition-colors {{ $step >= 3 ? 'bg-brand-primary text-white shadow-brand-primary/30' : 'bg-white text-gray-300 border-2 border-gray-100' }}">
                                    3
                                </div>
                                <div class="text-left sm:text-center">
                                    <h4 class="font-bold {{ $step >= 3 ? 'text-brand-primary' : 'text-gray-400' }}">Listo</h4>
                                    <p class="text-xs text-gray-400">Puedes recogerlo</p>
                                </div>
                            </div>
                        @endif

                        <!-- Paso Final: Entregado -->
                        <div class="flex sm:flex-col items-center sm:w-1/5 gap-4 sm:gap-2">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-bold shadow-md shrink-0 transition-colors {{ $step >= 5 ? 'bg-emerald-500 text-white shadow-emerald-500/30' : 'bg-white text-gray-300 border-2 border-gray-100' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div class="text-left sm:text-center">
                                <h4 class="font-bold {{ $step >= 5 ? 'text-emerald-500' : 'text-gray-400' }}">Entregado</h4>
                                <p class="text-xs text-gray-400">¡Que lo disfrutes!</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="text-center">
            <a href="{{ route('shop.index') }}" class="inline-block px-8 py-3 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50 transition-colors">Volver a la Tienda</a>
            <button onclick="window.location.reload()" class="inline-block ml-4 px-8 py-3 rounded-xl bg-brand-primary text-white font-bold hover:bg-brand-primary/90 transition-colors shadow-lg shadow-brand-primary/30">Actualizar Estado</button>
        </div>

    </div>
</div>
@endsection
