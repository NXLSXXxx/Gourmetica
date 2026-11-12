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
        <div class="inline-flex flex-wrap items-center justify-center gap-3 mb-8">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-5 py-2">
                <span class="w-2 h-2 bg-brand-primary rounded-full animate-pulse"></span>
                <span class="text-white/80 text-xs font-bold uppercase tracking-widest">
                    {{ $products->total() }} {{ $products->total() === 1 ? 'producto disponible' : 'productos disponibles' }}
                </span>
            </div>
            @if($selected_headquarter)
            <button onclick="openSelectHeadquarterModal()" class="inline-flex items-center gap-1.5 bg-brand-primary/20 hover:bg-brand-primary/30 border border-brand-primary/40 backdrop-blur-sm rounded-full px-4 py-2 text-white text-xs font-bold transition-all cursor-pointer">
                <span>📍 {{ $selected_headquarter->name }}</span>
                <span class="text-brand-primary underline text-[11px] ml-1">Cambiar</span>
            </button>
            @endif
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

        @php
            $hasShopFilters = request()->filled('search')
                || request()->filled('category')
                || request()->filled('min_price')
                || request()->filled('max_price')
                || request()->boolean('in_stock')
                || (request()->filled('sort') && request('sort') !== 'category');
        @endphp

        <!-- Sticky Category Bar -->
        <div class="sticky top-[126px] z-30 bg-brand-bg/98 backdrop-blur-md -mx-4 px-4 py-4 mb-6 overflow-x-auto no-scrollbar border-b border-brand-secondary/10">
            <div class="flex items-center gap-3">
                <a href="{{ route('shop.index', request()->except(['category', 'page'])) }}"
                   class="pill-category flex items-center gap-2 {{ !request('category') ? 'active' : '' }}">
                    <span>Todos</span>
                    <span class="text-[10px] opacity-75">({{ $categories->sum('products_count') }})</span>
                </a>
                @foreach($categories as $cat)
                <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => $cat->slug])) }}"
                   class="pill-category flex items-center gap-2 {{ request('category') == $cat->slug || (string)request('category') === (string)$cat->id ? 'active' : '' }}">
                    <span>{{ $cat->name }}</span>
                    <span class="text-[10px] opacity-75">({{ $cat->products_count ?? $cat->products()->count() }})</span>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Barra de Filtros y Ordenamiento -->
        <div class="bg-white rounded-3xl p-5 shadow-sm border border-brand-secondary/10 mb-10">
            <form action="{{ route('shop.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Búsqueda -->
                    <div class="relative">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-brand-secondary/60 mb-1.5">Buscar producto</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o ingrediente..."
                                   class="w-full pl-9 pr-4 py-2.5 bg-brand-bg border border-brand-secondary/15 rounded-2xl text-xs font-medium text-brand-secondary focus:border-brand-primary outline-none transition-colors">
                            <svg class="w-4 h-4 text-brand-secondary/40 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Categoría -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-brand-secondary/60 mb-1.5">Categoría</label>
                        <select name="category" onchange="this.form.submit()"
                                class="w-full px-4 py-2.5 bg-brand-bg border border-brand-secondary/15 rounded-2xl text-xs font-bold text-brand-secondary focus:border-brand-primary outline-none cursor-pointer">
                            <option value="">Todas las categorías</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ (request('category') == $cat->slug || (string)request('category') === (string)$cat->id) ? 'selected' : '' }}>
                                    {{ $cat->name }} ({{ $cat->products_count ?? 0 }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Rango de Precio -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-brand-secondary/60 mb-1.5">Rango de Precio (S/)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.50" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="Mín"
                                   class="w-full px-3 py-2.5 bg-brand-bg border border-brand-secondary/15 rounded-2xl text-xs font-medium text-brand-secondary focus:border-brand-primary outline-none text-center">
                            <span class="text-brand-secondary/40 font-bold">-</span>
                            <input type="number" step="0.50" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="Máx"
                                   class="w-full px-3 py-2.5 bg-brand-bg border border-brand-secondary/15 rounded-2xl text-xs font-medium text-brand-secondary focus:border-brand-primary outline-none text-center">
                        </div>
                    </div>

                    <!-- Ordenar por -->
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-brand-secondary/60 mb-1.5">Ordenar por</label>
                        <select name="sort" onchange="this.form.submit()"
                                class="w-full px-4 py-2.5 bg-brand-bg border border-brand-secondary/15 rounded-2xl text-xs font-bold text-brand-secondary focus:border-brand-primary outline-none cursor-pointer">
                            <option value="category" {{ request('sort', 'category') === 'category' ? 'selected' : '' }}>Organizados por Categoría</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Precio: Menor a Mayor</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Precio: Mayor a Menor</option>
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nombre: A - Z</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nombre: Z - A</option>
                            <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Más recientes</option>
                        </select>
                    </div>
                </div>

                <!-- Acciones inferiores del filtro -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-gray-100">
                    <label class="inline-flex items-center gap-2 text-xs font-bold text-brand-secondary cursor-pointer select-none">
                        <input type="checkbox" name="in_stock" value="1" {{ request()->boolean('in_stock') ? 'checked' : '' }} onchange="this.form.submit()"
                               class="rounded border-brand-secondary/30 text-brand-primary focus:ring-brand-primary">
                        <span>Mostrar solo con stock inmediato en sede</span>
                    </label>

                    <div class="flex items-center gap-2 ml-auto">
                        @if($hasShopFilters)
                        <a href="{{ route('shop.index') }}"
                           class="px-4 py-2 rounded-full border border-brand-secondary/20 text-brand-secondary hover:bg-brand-secondary/5 text-[11px] font-bold uppercase tracking-wider transition-colors">
                            Limpiar filtros
                        </a>
                        @endif
                        <button type="submit"
                                class="px-6 py-2 rounded-full bg-brand-secondary hover:bg-brand-primary text-white text-[11px] font-black uppercase tracking-widest transition-colors cursor-pointer">
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Productos Organizados por Categorías -->
        @php
            $groupedByCategory = $products->getCollection()->groupBy(fn($product) => $product->category ? $product->category->name : 'Otros Productos');
        @endphp

        @forelse($groupedByCategory as $categoryName => $categoryProducts)
            @php
                $firstProductCat = $categoryProducts->first()?->category;
            @endphp
            <section class="mb-14">
                <!-- Encabezado de la Categoría -->
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-3 border-b-2 border-brand-primary/30">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full bg-brand-primary"></span>
                        <h2 class="text-2xl md:text-3xl font-serif font-black text-brand-secondary tracking-tight">
                            {{ $categoryName }}
                        </h2>
                        <span class="px-3 py-1 rounded-full bg-brand-primary/15 text-brand-secondary text-xs font-extrabold">
                            {{ $categoryProducts->count() }} {{ $categoryProducts->count() === 1 ? 'producto' : 'productos' }}
                        </span>
                    </div>

                    @if(!request('category') && $firstProductCat)
                        <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => $firstProductCat->slug])) }}"
                           class="inline-flex items-center gap-1 text-xs font-black uppercase tracking-widest text-brand-primary hover:text-brand-secondary transition-colors">
                            Ver solo {{ $categoryName }}
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    @endif
                </div>

                <!-- Grid de Productos de esta Categoría -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-5 lg:gap-7">
                    @foreach($categoryProducts as $product)
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
                                        S/ {{ number_format($product->getPriceForHeadquarter($selectedHqId ?? session('selected_headquarter_id')), 2) }}
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
            </section>
        @empty
            <div class="bg-white rounded-3xl p-12 text-center border border-brand-secondary/10 shadow-sm">
                <p class="text-lg font-serif font-bold text-brand-secondary mb-2">No encontramos productos con esos filtros</p>
                <p class="text-xs text-gray-400 mb-6">Intenta cambiar la categoría, el rango de precios o el término de búsqueda.</p>
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-brand-secondary hover:bg-brand-primary text-white text-xs font-black uppercase tracking-widest px-6 py-3 rounded-full transition-all">
                    Ver toda la carta
                </a>
            </div>
        @endforelse

        <!-- Pagination -->
        <div class="mt-16">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
