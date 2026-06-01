@extends('layouts.app')

@section('title', 'Nuestra Carta | Gourmetica')

@section('content')
<div class="bg-brand-bg min-h-screen pt-4 pb-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Sticky Category Bar -->
        <div class="sticky top-20 z-40 bg-brand-bg/95 backdrop-blur-md -mx-4 px-4 py-6 mb-12 overflow-x-auto no-scrollbar flex items-center gap-4 scroll-smooth">
            <a href="{{ route('shop.index') }}" class="pill-category {{ !request('category') ? 'active' : '' }}">
                Todos
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('shop.index', ['category' => $cat->slug]) }}" class="pill-category {{ request('category') == $cat->slug ? 'active' : '' }}">
                {{ $cat->name }}
            </a>
            @endforeach
        </div>

        <header class="mb-12">
            <h1 class="text-4xl font-serif font-bold text-brand-secondary mb-2">
                {{ request('category') ? $categories->where('slug', request('category'))->first()->name : 'Nuestra Carta' }}
            </h1>
            <p class="text-gray-500 text-sm italic">Experiencias dulces creadas para compartir.</p>
        </header>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
            @foreach($products as $product)
            <div class="card-premium group">
                <div class="aspect-square relative overflow-hidden rounded-[2.5rem] bg-gray-50 shadow-sm flex items-center justify-center">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                    @else
                        <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    @endif
                    
                    @auth
                    <form action="{{ route('favorites.toggle', $product) }}" method="POST" class="absolute top-6 right-6 z-10">
                        @csrf
                        <button type="submit" class="w-11 h-11 bg-white/90 backdrop-blur rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition-transform {{ auth()->user()->favorites->contains($product->id) ? 'text-brand-primary' : 'text-gray-300' }}">
                            <svg class="w-6 h-6" fill="{{ auth()->user()->favorites->contains($product->id) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </button>
                    </form>
                    @endauth
                </div>

                <div class="mt-6 px-2">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="text-lg font-bold text-brand-secondary group-hover:text-brand-primary transition-colors leading-tight">{{ $product->name }}</h3>
                            <p class="text-xs text-gray-400 mt-1 uppercase tracking-widest font-bold">{{ $product->category->name }}</p>
                        </div>
                    </div>
                    
                    <p class="text-gray-500 text-xs line-clamp-2 mb-6 h-8">{{ $product->description }}</p>
                    
                    <div class="flex items-center justify-between mt-auto pt-2 border-t border-gray-100">
                        <span class="text-xl font-bold text-brand-primary">S/ {{ number_format($product->base_price, 2) }}</span>
                        <a href="{{ route('shop.product', $product->slug) }}" class="btn-pill">
                            VER MÁS
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-20">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection
