@extends('layouts.app')

@section('title', 'Mi Perfil | Gourmetica')

@section('content')
<div class="bg-brand-bg min-h-screen py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-12" x-data="{ tab: 'orders' }">
            <!-- Sidebar: User Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 sticky top-24">
                    <div class="text-center mb-8">
                        <div class="w-24 h-24 rounded-full bg-brand-secondary/20 flex items-center justify-center text-brand-secondary text-3xl font-bold mx-auto mb-4 overflow-hidden border-4 border-white shadow-lg">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                {{ substr($user->name, 0, 1) }}
                            @endif
                        </div>
                        <h2 class="text-xl font-serif font-bold text-brand-primary">{{ $user->name }}</h2>
                        <p class="text-xs text-gray-400 uppercase tracking-widest mt-1">{{ $user->role->name }}</p>
                    </div>

                    <nav class="space-y-2">
                        <button @click="tab = 'orders'" :class="tab === 'orders' ? 'bg-brand-secondary/10 text-brand-secondary font-bold' : 'text-gray-500 hover:bg-gray-50'" class="w-full flex items-center px-4 py-3 rounded-xl transition-all text-sm">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            Mis Pedidos
                        </button>
                        <button @click="tab = 'favorites'" :class="tab === 'favorites' ? 'bg-brand-secondary/10 text-brand-secondary font-bold' : 'text-gray-500 hover:bg-gray-50'" class="w-full flex items-center px-4 py-3 rounded-xl transition-all text-sm">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            Favoritos
                        </button>
                    </nav>

                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-3 text-red-400 hover:text-red-500 text-sm font-medium transition-colors flex items-center">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-3">
                <!-- Orders Tab -->
                <div x-show="tab === 'orders'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <header class="mb-10">
                        <h1 class="text-3xl font-serif font-bold text-brand-primary">Historial de Pedidos</h1>
                        <p class="text-gray-500 mt-1">Gestiona tus compras y sigue el estado de tus delicias.</p>
                    </header>

                    <div class="space-y-6">
                        @forelse($orders as $order)
                        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden group hover:shadow-2xl transition-all duration-500">
                            <div class="p-6 md:p-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                <div class="flex items-center">
                                    <div class="w-16 h-16 bg-brand-bg rounded-2xl flex items-center justify-center text-brand-secondary mr-6 flex-shrink-0">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-serif font-bold text-brand-primary">Pedido #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h4>
                                        <p class="text-xs text-gray-400 mt-1 uppercase tracking-widest">{{ $order->created_at->format('d M, Y • H:i') }}</p>
                                    </div>
                                </div>

                                <div class="flex flex-wrap gap-3">
                                    <span class="px-4 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest shadow-sm
                                        {{ $order->status === 'delivered' ? 'bg-emerald-500 text-white' : 
                                           ($order->status === 'cancelled' ? 'bg-red-500 text-white' : 'bg-brand-secondary text-brand-dark') }}">
                                        {{ strtoupper($order->status) }}
                                    </span>
                                    <span class="px-4 py-1 rounded-full bg-slate-100 text-slate-500 text-[10px] font-bold uppercase tracking-widest">
                                        {{ $order->payment_status === 'paid' ? 'PAGADO' : 'PENDIENTE' }}
                                    </span>
                                </div>

                                <div class="text-right">
                                    <p class="text-sm text-gray-400">Total Pagado</p>
                                    <p class="text-2xl font-serif font-bold text-brand-primary">S/ {{ number_format($order->total, 2) }}</p>
                                </div>
                            </div>

                            <!-- Order Items Mini List -->
                            <div class="bg-gray-50/50 p-6 md:px-8 border-t border-gray-100 flex flex-wrap gap-4">
                                @foreach($order->items as $item)
                                    <div class="flex items-center bg-white px-4 py-2 rounded-xl border border-gray-100 shadow-sm">
                                        <span class="text-xs font-bold text-brand-secondary mr-2">{{ $item->quantity }}x</span>
                                        <span class="text-xs font-medium text-gray-600">{{ $item->product->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-400 mb-6">Aún no has realizado pedidos</h3>
                            <a href="{{ route('shop.index') }}" class="btn-premium px-10 py-4">EXPLORAR CARTA</a>
                        </div>
                        @endforelse

                        <div class="mt-8">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>

                <!-- Favorites Tab -->
                <div x-show="tab === 'favorites'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="display: none;">
                    <header class="mb-10">
                        <h1 class="text-3xl font-serif font-bold text-brand-primary">Mis Favoritos</h1>
                        <p class="text-gray-500 mt-1">Tus delicias preferidas, a un solo clic de distancia.</p>
                    </header>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @forelse($favorites as $favorite)
                        <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden group">
                            <div class="aspect-square relative overflow-hidden bg-gray-50 flex items-center justify-center">
                                @if($favorite->image)
                                    <img src="{{ asset('storage/'.$favorite->image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <svg class="w-16 h-16 text-gray-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                @endif
                                
                                <form action="{{ route('favorites.toggle', $favorite) }}" method="POST" class="absolute top-4 right-4 z-10">
                                    @csrf
                                    <button type="submit" class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-md text-red-500 hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </button>
                                </form>

                                <a href="{{ route('shop.product', $favorite->slug) }}" class="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur text-brand-primary font-bold py-3 rounded-xl opacity-0 translate-y-4 group-hover:opacity-100 group-hover:translate-y-0 transition-all duration-300 hover:bg-brand-primary hover:text-white text-center">
                                    PEDIR AHORA
                                </a>
                            </div>
                            <div class="p-6 text-center">
                                <span class="text-[10px] uppercase tracking-widest text-brand-secondary font-bold mb-2 block">{{ $favorite->category->name }}</span>
                                <h3 class="text-lg font-bold text-brand-primary">{{ $favorite->name }}</h3>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-full text-center py-20 bg-white rounded-3xl border border-dashed border-gray-200">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-400 mb-6">Aún no tienes favoritos</h3>
                            <a href="{{ route('shop.index') }}" class="btn-premium px-10 py-4">VER CARTA</a>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
