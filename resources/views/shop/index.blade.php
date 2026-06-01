@extends('layouts.app')

@section('title', 'Nuestra Carta | Gourmetica')

@section('content')
@php
    $heroBanner = \App\Models\Banner::where('type', 'shop_hero')->where('is_active', true)->first();
    $heroImage = $heroBanner ? asset('storage/' . $heroBanner->image_path) : asset('images/hero.png');
@endphp
<!-- Hero Banner with background image -->
<section class="banner-hero relative min-h-[70vh] flex items-center justify-center overflow-hidden"
         style="background-image: url('{{ $heroImage }}'); background-size: cover; background-position: center;">
    <!-- Layered overlays for depth -->
    <div class="absolute inset-0 bg-gradient-to-br from-brand-secondary/95 via-brand-secondary/80 to-brand-primary/60"></div>
    
    <!-- Decorative elements -->
    <div class="absolute top-20 left-10 w-64 h-64 bg-brand-primary/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
    
    <!-- Vertical text accent -->
    <div class="absolute left-8 top-1/2 -translate-y-1/2 hidden lg:block">
        <span class="writing-mode-vertical text-white/20 font-black tracking-[0.3em] uppercase text-xs"
              style="writing-mode: vertical-lr; transform: rotate(180deg); letter-spacing: 0.3em;">GOURMETICA · SABOR ARTESANAL</span>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center mt-10">
        <!-- Eyebrow badge -->
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-5 py-2 mb-8">
            <span class="w-2 h-2 bg-brand-primary rounded-full animate-pulse"></span>
            <span class="text-white/80 text-xs font-bold uppercase tracking-widest">
                {{ $products->total() }} {{ $products->total() === 1 ? 'producto disponible' : 'productos disponibles' }}
            </span>
        </div>

        <h1 class="text-6xl md:text-8xl font-serif font-black text-white mb-8 leading-none tracking-tight">
            @if(request('category') && $categories->where('slug', request('category'))->first())
                <span class="text-brand-primary italic">{{ $categories->where('slug', request('category'))->first()->name }}</span>
            @else
                Nuestra<br>
                <span class="text-brand-primary italic">Carta</span>
            @endif
        </h1>
        <p class="text-xl md:text-2xl text-white/70 max-w-3xl mx-auto leading-relaxed font-medium">
            Elaborado con amor y horneado a diario. Descubre nuestra selección de postres, panes y delicias artesanales preparadas con los mejores ingredientes.
        </p>
    </div>
</section>

<div class="bg-brand-bg min-h-screen pb-20 pt-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Sticky Category Bar -->
        <div class="sticky top-[126px] z-30 bg-brand-bg/98 backdrop-blur-md -mx-4 px-4 py-4 mb-10 overflow-x-auto no-scrollbar">
            <div class="flex items-center gap-3">
                <a href="{{ route('shop.index') }}" class="pill-category {{ !request('category') ? 'active' : '' }}">
                    Todos
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('shop.index', ['category' => $cat->slug]) }}" class="pill-category {{ request('category') == $cat->slug ? 'active' : '' }}">
                    {{ $cat->name }}
                </a>
                @endforeach
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-5 lg:gap-7">
            @foreach($products as $product)
            <div class="card-premium group flex flex-col">

                <!-- Image Container -->
                <div class="relative overflow-hidden rounded-2xl bg-gray-50 aspect-square flex-shrink-0">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}"
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-700">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif

                    <!-- Category badge (top-left) -->
                    <div class="absolute top-3 left-3">
                        <span class="bg-white/90 backdrop-blur-sm text-brand-secondary text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full shadow-sm">
                            {{ $product->category->name }}
                        </span>
                    </div>

                    <!-- Favorite button (top-right) -->
                    @auth
                    <form action="{{ route('favorites.toggle', $product) }}" method="POST" class="absolute top-3 right-3 z-10">
                        @csrf
                        <button type="submit"
                                class="w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-md hover:scale-110 transition-transform {{ auth()->user()->favorites->contains($product->id) ? 'text-brand-primary' : 'text-gray-300' }}">
                            <svg class="w-4 h-4" fill="{{ auth()->user()->favorites->contains($product->id) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                    </form>
                    @endauth
                </div>

                <!-- Card Body — flex-col grow so price+button always at bottom -->
                <div class="flex flex-col flex-1 pt-4 px-1 pb-1">

                    <!-- Name & Description -->
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-brand-secondary group-hover:text-brand-primary transition-colors leading-snug line-clamp-2">
                            {{ $product->name }}
                        </h3>
                        <p class="text-gray-400 text-xs mt-1.5 line-clamp-2 leading-relaxed">
                            {{ $product->description }}
                        </p>
                    </div>

                    <!-- Price & CTA — always anchored at bottom -->
                    <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-y-3 gap-x-2">
                        <div class="min-w-0">
                            <span class="text-base font-black text-brand-primary leading-none block">
                                S/ {{ number_format($product->base_price, 2) }}
                            </span>
                        </div>
                        <a href="{{ route('shop.product', $product->slug) }}"
                           class="inline-flex items-center gap-1 bg-brand-secondary hover:bg-brand-primary text-white text-[10px] font-black uppercase tracking-widest px-3 py-2 rounded-full transition-all duration-300 hover:shadow-md whitespace-nowrap">
                            Ver más
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-16">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
