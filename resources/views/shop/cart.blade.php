@extends('layouts.app')

@section('title', 'Mi Carrito | Gourmetica')

@section('content')
<div class="bg-brand-bg min-h-screen py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-serif font-bold text-brand-primary mb-10">Tu Carrito Artisanal</h1>

        @if(count($cart) > 0)
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <div class="divide-y divide-gray-100">
                @foreach($cart as $key => $item)
                <div class="p-8 flex items-center">
                    <div class="w-24 h-24 bg-gray-50 rounded-2xl shadow-md overflow-hidden flex items-center justify-center">
                        @if($item['image'])
                            <img src="{{ asset('storage/'.$item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        @endif
                    </div>
                    
                    <div class="ml-8 flex-1">
                        <h3 class="text-xl font-bold text-brand-primary">{{ $item['name'] }}</h3>
                        <p class="text-xs text-gray-400 mt-1 uppercase tracking-wider">
                            {{ implode(' • ', $item['options']) }}
                        </p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm font-bold text-gray-500">Cantidad: {{ $item['quantity'] }}</span>
                            <span class="text-lg font-serif font-bold text-brand-secondary">S/ {{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                    </div>

                    <a href="{{ route('cart.remove', $key) }}" class="ml-8 text-gray-300 hover:text-red-500 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </a>
                </div>
                @endforeach
            </div>

            <div class="p-8 bg-gray-50 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                <div>
                    <p class="text-sm text-gray-500">Total Estimado</p>
                    <h2 class="text-4xl font-serif font-bold text-brand-primary">S/ {{ number_format($total, 2) }}</h2>
                </div>
                <div class="flex gap-4 w-full md:w-auto">
                    <a href="{{ route('shop.index') }}" class="flex-1 md:flex-none px-8 py-4 text-center font-bold text-brand-primary border-2 border-gray-200 rounded-2xl hover:bg-white transition-all">
                        SEGUIR COMPRANDO
                    </a>
                    <a href="{{ route('shop.checkout') }}" class="flex-1 md:flex-none btn-premium px-12 py-4 shadow-xl shadow-brand-primary/10">
                        FINALIZAR PEDIDO
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
            <svg class="w-20 h-20 text-gray-200 mx-auto mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            <h3 class="text-2xl font-bold text-gray-400 mb-6">Tu carrito está vacío</h3>
            <a href="{{ route('shop.index') }}" class="btn-premium px-10 py-4">VER POSTRES</a>
        </div>
        @endif
    </div>
</div>
@endsection
