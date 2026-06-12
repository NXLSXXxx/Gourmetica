@extends('layouts.app')

@section('title', '¡Pedido Confirmado! | Gourmetica')

@section('content')
<div class="bg-brand-bg min-h-screen py-20 flex items-center">
    <div class="max-w-2xl mx-auto px-4 text-center">
        <div class="w-24 h-24 bg-emerald-500 rounded-full flex items-center justify-center text-white mx-auto mb-10 shadow-2xl shadow-emerald-500/20">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
        </div>
        
        <h1 class="text-5xl font-serif font-bold text-brand-primary mb-4">¡Gracias por tu pedido!</h1>
        <p class="text-xl text-gray-500 mb-10">Tu pedido <span class="font-bold text-brand-secondary">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span> ha sido recibido con éxito.</p>

        <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 text-left mb-10">
            <h3 class="font-bold text-brand-primary mb-4 uppercase tracking-widest text-xs">Detalle de Recojo</h3>
            <div class="flex items-start mb-6">
                <div class="w-10 h-10 bg-brand-secondary/10 rounded-full flex items-center justify-center text-brand-secondary mr-4 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                </div>
                <div>
                    <p class="font-bold text-brand-primary">{{ $order->headquarter->name }}</p>
                    <p class="text-sm text-gray-500">{{ $order->headquarter->address }}</p>
                </div>
            </div>
            
            <div class="pt-6 border-t border-gray-100 flex justify-between items-center">
                <span class="text-gray-500">Método de Pago:</span>
                <span class="font-bold text-brand-primary uppercase text-sm">{{ $order->payment_method }}</span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('shop.index') }}" class="btn-premium px-10 py-4">VOLVER A LA TIENDA</a>
            <a href="{{ route('shop.tracking', $order->id) }}" class="px-10 py-4 font-bold text-brand-primary border-2 border-gray-200 rounded-2xl hover:bg-white transition-all text-center">SEGUIR MI PEDIDO</a>
        </div>
    </div>
</div>
@endsection
