@extends('layouts.app')

@section('title', 'Gourmetica | El Arte de la Pastelería Fina')

@section('content')
<!-- 1. Hero Split Section -->
@php
    $heroBanners = \App\Models\Banner::hero()->get();
    $promoBanner = \App\Models\Banner::promo()->first();
@endphp

@if($heroBanners->count() > 0)
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 relative" 
         x-data="{ activeSlide: 0, totalSlides: {{ $heroBanners->count() }} }" 
         x-init="setInterval(() => { activeSlide = (activeSlide + 1) % totalSlides }, 6000)">
    
    <!-- Outer Wrapper (Clips overflow and provides border radius) -->
    <div class="relative overflow-hidden rounded-[2rem] shadow-2xl">
        <!-- Inner Sliding Container -->
        <div class="flex transition-transform duration-700 ease-in-out" 
             :style="'width: ' + (totalSlides * 100) + '%; transform: translateX(-' + (activeSlide * (100 / totalSlides)) + '%);'">
            
            @foreach($heroBanners as $index => $banner)
            <!-- Individual Slide -->
            <div class="hero-split shadow-none" 
                 style="margin: 0; flex: 1; flex-shrink: 0; min-width: 0; width: 100%; border-radius: 0; box-shadow: none;">
                
                <div class="hero-image relative" style="background-image: url('{{ asset('storage/' . $banner->image_path) }}')">
                    <!-- Navigation Arrows -->
                    <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex items-center justify-between px-4 z-30">
                        <button type="button" 
                                @click.stop="activeSlide = (activeSlide === 0) ? totalSlides - 1 : activeSlide - 1" 
                                class="w-12 h-12 bg-white/95 text-brand-secondary rounded-full flex items-center justify-center shadow-lg hover:scale-115 transition-all hover:bg-white cursor-pointer focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <button type="button" 
                                @click.stop="activeSlide = (activeSlide === totalSlides - 1) ? 0 : activeSlide + 1" 
                                class="w-12 h-12 bg-white/95 text-brand-secondary rounded-full flex items-center justify-center shadow-lg hover:scale-115 transition-all hover:bg-white cursor-pointer focus:outline-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="hero-content relative">
                    <div class="text-white text-right">
                        <h1 class="font-black leading-[0.9] mb-4 tracking-tighter uppercase italic">{!! nl2br(e($banner->title)) !!}</h1>
                        <p class="text-xl opacity-90 mb-10 font-bold tracking-tight">{!! nl2br(e($banner->subtitle)) !!}</p>
                        
                        @if($banner->button_text)
                        <div class="flex justify-end">
                            <a href="{{ $banner->button_url ?? '/shop' }}" class="btn-pide shadow-xl">
                                {{ $banner->button_text }}
                            </a>
                        </div>
                        @endif
                    </div>
                    <div class="absolute bottom-10 right-10 w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Dots Indicators -->
    <div class="absolute bottom-6 left-10 flex space-x-2 z-30">
        <template x-for="i in Array.from({length: totalSlides}, (_, i) => i)">
            <button type="button" 
                    @click="activeSlide = i" 
                    class="w-2.5 h-2.5 rounded-full transition-all duration-300 focus:outline-none cursor-pointer"
                    :class="activeSlide === i ? 'bg-white scale-125 w-6' : 'bg-white/40 hover:bg-white/60'"></button>
        </template>
    </div>
</section>
@endif

<!-- 2. Category Shortcuts (Floating Bubbles) -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-20">
    <div class="category-bubbles no-scrollbar">
        @foreach($categories->take(6) as $cat)
        <a href="/shop?category={{ $cat->slug }}" class="bubble-item">
            <div class="bubble-circle {{ !$cat->image ? 'bg-brand-secondary/5 flex items-center justify-center' : '' }}">
                @if($cat->image)
                    <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-8 h-8 text-brand-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                @endif
            </div>
            <span>{{ $cat->name }}</span>
        </a>
        @endforeach
    </div>
</section>

<!-- 3. Services Grid -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-serif font-bold text-brand-secondary tracking-widest uppercase italic">Nuestros servicios, a tu medida</h2>
    </div>
    
    @php
        $cateringBanner = \App\Models\Banner::where('type', 'service_catering')->where('is_active', true)->first();
        $corporateBanner = \App\Models\Banner::where('type', 'service_corporate')->where('is_active', true)->first();
        $locationsBanner = \App\Models\Banner::where('type', 'section_locations')->where('is_active', true)->first();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
        <!-- Catering -->
        <div class="service-card group">
            <div class="service-image group-hover:scale-105 transition-transform duration-700 bg-brand-secondary/10" {!! $cateringBanner ? 'style="background-image: url('.asset('storage/'.$cateringBanner->image_path).');"' : '' !!}></div>
            <div class="service-content">
                <h3 class="text-brand-secondary">Catering</h3>
                <p>Haz de tus celebraciones momentos inolvidables con nuestra selección exclusiva de bocaditos y tortas.</p>
                <div class="flex justify-center">
                    <a href="/catering" class="btn-pill px-10 py-4 bg-brand-primary text-white">Coordina tu evento</a>
                </div>
            </div>
        </div>

        <!-- Corporate -->
        <div class="service-card group">
            <div class="service-image group-hover:scale-105 transition-transform duration-700 bg-brand-secondary/10" {!! $corporateBanner ? 'style="background-image: url('.asset('storage/'.$corporateBanner->image_path).');"' : '' !!}></div>
            <div class="service-content">
                <h3 class="text-brand-secondary">Pedidos Corporativos</h3>
                <p>Sorprende a tu equipo y clientes con el detalle más dulce. Soluciones personalizadas para empresas.</p>
                <div class="flex justify-center">
                    <a href="/corporate" class="btn-pill px-10 py-4 bg-brand-primary text-white">Coordina tu pedido</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 4. Promo Banner -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-24">
    @if($promoBanner)
    <div class="promo-banner" style="background-image: url('{{ asset('storage/' . $promoBanner->image_path) }}')">
        <h2 class="italic uppercase tracking-tighter">{{ $promoBanner->title }}</h2>
    </div>
    @endif
</section>

<!-- 5. Locations Shortcut -->
<section class="py-20 bg-brand-primary text-white overflow-hidden relative mb-24 rounded-[3rem] mx-4 sm:mx-8 lg:mx-12" {!! $locationsBanner ? 'style="background-image: url('.asset('storage/'.$locationsBanner->image_path).'); background-size: cover; background-position: center;"' : '' !!}>
    @if($locationsBanner)
    <div class="absolute inset-0 bg-brand-primary/80"></div>
    @endif
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <h2 class="text-4xl md:text-6xl font-black mb-8 italic uppercase tracking-tighter">Encuéntranos cerca de ti</h2>
        <p class="text-xl opacity-90 mb-12 max-w-2xl mx-auto">
            Tenemos más de 10 casas listas para recibirte y endulzar tu día.
        </p>
        <div class="flex justify-center">
            <a href="/locations" class="btn-pill bg-white text-brand-primary px-12 py-4 hover:bg-brand-secondary hover:text-white border-none">VER NUESTRAS CASAS</a>
        </div>
    </div>
</section>
@endsection
