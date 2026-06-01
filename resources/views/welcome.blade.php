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
    </div>
</section>
@else
<!-- Fallback Hero -->
@php
    $fallbackBanner = \App\Models\Banner::where('type', 'fallback_hero')->where('is_active', true)->first();
    $fallbackImage = $fallbackBanner ? asset('storage/' . $fallbackBanner->image_path) : asset('images/hero.png');
@endphp
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6 relative">
    <div class="banner-hero relative h-[500px] flex items-center justify-center overflow-hidden rounded-[2rem] shadow-2xl"
         style="background-image: url('{{ $fallbackImage }}'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-gradient-to-br from-brand-secondary/95 via-brand-secondary/80 to-brand-primary/60"></div>
        <div class="absolute top-20 left-10 w-64 h-64 bg-brand-primary/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
        <div class="relative z-10 text-center max-w-4xl px-4">
            <h1 class="text-5xl md:text-7xl font-serif font-black text-white mb-6 leading-none tracking-tight">
                El Arte de la<br>
                <span class="text-brand-primary italic">Pastelería Fina</span>
            </h1>
            <p class="text-xl text-white/70 font-medium mb-10">Descubre nuestra selección de postres artesanales, elaborados con pasión y los mejores ingredientes.</p>
            <a href="/shop" class="btn-pide shadow-xl inline-block">Nuestra Carta</a>
        </div>
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
        <h2 class="text-4xl font-serif font-black text-brand-secondary tracking-tight">
            Nuestros servicios,<br>
            <span class="text-brand-primary italic">a tu medida</span>
        </h2>
    </div>
    
    @php
        $cateringBanner = \App\Models\Banner::where('type', 'service_catering')->where('is_active', true)->first();
        $corporateBanner = \App\Models\Banner::where('type', 'service_corporate')->where('is_active', true)->first();
        $locationsBanner = \App\Models\Banner::where('type', 'section_locations')->where('is_active', true)->first();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-10">
        <!-- Catering -->
        <a href="/catering" class="group relative overflow-hidden rounded-[2.5rem] shadow-xl min-h-[450px] flex items-end block">
            <div class="absolute inset-0 bg-brand-secondary/10 group-hover:scale-105 transition-transform duration-700 z-0" 
                 {!! $cateringBanner ? 'style="background-image: url('.asset('storage/'.$cateringBanner->image_path).'); background-size: cover; background-position: center;"' : 'style="background-image: url('.asset('images/cakes.png').'); background-size: cover; background-position: center;"' !!}></div>
            <div class="absolute inset-0 bg-gradient-to-t from-brand-secondary via-brand-secondary/60 to-transparent z-10"></div>
            <div class="relative z-20 p-10 md:p-14 w-full">
                <span class="text-white text-[10px] font-black uppercase tracking-[0.2em] mb-3 block">Servicio Especial</span>
                <h3 class="text-4xl font-serif font-black text-white mb-4 leading-tight">Catering</h3>
                <p class="text-white/80 font-medium leading-relaxed mb-8 max-w-sm">Haz de tus celebraciones momentos inolvidables con nuestra selección exclusiva de bocaditos y tortas.</p>
                <span class="inline-flex items-center gap-2 text-white font-bold uppercase tracking-widest text-xs group-hover:text-white/80 transition-colors">
                    Coordina tu evento 
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </span>
            </div>
        </a>

        <!-- Corporate -->
        <div class="group relative overflow-hidden rounded-[2.5rem] shadow-xl min-h-[450px] flex items-end">
            <div class="absolute inset-0 bg-brand-secondary/10 group-hover:scale-105 transition-transform duration-700 z-0" 
                 {!! $corporateBanner ? 'style="background-image: url('.asset('storage/'.$corporateBanner->image_path).'); background-size: cover; background-position: center;"' : 'style="background-image: url('.asset('images/hero.png').'); background-size: cover; background-position: center;"' !!}></div>
            <div class="absolute inset-0 bg-gradient-to-t from-brand-secondary via-brand-secondary/60 to-transparent z-10"></div>
            <div class="relative z-20 p-10 md:p-14 w-full">
                <span class="text-white text-[10px] font-black uppercase tracking-[0.2em] mb-3 block">Para Empresas</span>
                <h3 class="text-4xl font-serif font-black text-white mb-4 leading-tight">Pedidos Corporativos</h3>
                <p class="text-white/80 font-medium leading-relaxed max-w-sm">Sorprende a tu equipo y clientes con el detalle más dulce. Soluciones personalizadas para empresas.</p>
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

<!-- 5. Locations Shortcut - Premium CTA -->
<section class="relative mb-24 mx-4 sm:mx-8 lg:mx-12 overflow-hidden rounded-[3rem]" 
         {!! $locationsBanner ? 'style="background-image: url('.asset('storage/'.$locationsBanner->image_path).'); background-size: cover; background-position: center;"' : 'style="background-color: #3D2B1F;"' !!}>
    <!-- Overlay gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-brand-secondary/95 via-brand-secondary/85 to-brand-primary/70"></div>
    
    <!-- Decorative pulsing circles -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
        <div class="w-[600px] h-[600px] rounded-full border border-white/5 animate-ping" style="animation-duration: 4s;"></div>
    </div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 pointer-events-none">
        <div class="w-[400px] h-[400px] rounded-full border border-white/8 animate-ping" style="animation-duration: 3s; animation-delay: 0.5s;"></div>
    </div>
    
    <!-- Floating decorative blobs -->
    <div class="absolute -top-20 -left-20 w-72 h-72 bg-brand-primary/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-brand-primary/15 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Content -->
    <div class="relative z-10 py-24 px-4 sm:px-8 lg:px-16">
        <div class="max-w-5xl mx-auto text-center">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-5 py-2 mb-8">
                <span class="w-2 h-2 bg-brand-primary rounded-full animate-pulse"></span>
                <span class="text-white/80 text-xs font-bold uppercase tracking-widest">Siempre cerca de ti</span>
            </div>
            
            <!-- Heading -->
            <h2 class="text-5xl md:text-7xl lg:text-8xl font-black uppercase italic tracking-tighter text-white leading-none mb-6">
                Encuéntranos<br>
                <span class="text-brand-primary">cerca de ti</span>
            </h2>
            
            <p class="text-lg md:text-xl text-white/70 mb-12 max-w-2xl mx-auto font-medium leading-relaxed">
                Más de <strong class="text-white">10 casas Gourmetica</strong> en la ciudad, listas para recibirte con el aroma del pan recién horneado y el sabor que enamora.
            </p>

            <!-- Stats Row -->
            <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 mb-14">
                <div class="text-center">
                    <span class="block text-4xl md:text-5xl font-black text-brand-primary">10+</span>
                    <span class="text-white/50 text-xs uppercase tracking-widest font-bold mt-1 block">Sedes</span>
                </div>
                <div class="w-px h-12 bg-white/20 hidden md:block"></div>
                <div class="text-center">
                    <span class="block text-4xl md:text-5xl font-black text-brand-primary">7</span>
                    <span class="text-white/50 text-xs uppercase tracking-widest font-bold mt-1 block">Días a la semana</span>
                </div>
                <div class="w-px h-12 bg-white/20 hidden md:block"></div>
                <div class="text-center">
                    <span class="block text-4xl md:text-5xl font-black text-brand-primary">100%</span>
                    <span class="text-white/50 text-xs uppercase tracking-widest font-bold mt-1 block">Artesanal</span>
                </div>
            </div>
            
            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/locations" 
                   class="group relative inline-flex items-center gap-3 bg-brand-primary hover:bg-white text-white hover:text-brand-secondary font-black uppercase tracking-widest text-sm px-10 py-5 rounded-full transition-all duration-300 shadow-2xl shadow-brand-primary/40 hover:shadow-brand-primary/20 hover:scale-105">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Ver Nuestras Casas
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <a href="https://wa.me/{{ $contact_whatsapp }}" target="_blank"
                   class="inline-flex items-center gap-3 bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/20 text-white font-bold uppercase tracking-widest text-sm px-10 py-5 rounded-full transition-all duration-300 hover:scale-105">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" class="w-5 h-5" alt="WhatsApp">
                    Escríbenos
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
