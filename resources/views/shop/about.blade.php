@extends('layouts.app')

@section('title', 'Nuestra Historia | Gourmetica')

@section('content')

@php
    $heroBanner = \App\Models\Banner::where('type', 'about_hero')->where('is_active', true)->first();
    $heroImage = $heroBanner ? asset('storage/' . $heroBanner->image_path) : asset('images/hero.png');
@endphp
<!-- Hero Banner with background image -->
<section class="banner-hero relative min-h-[80vh] flex items-center justify-center overflow-hidden"
         style="background-image: url('{{ $heroImage }}'); background-size: cover; background-position: center;">
    <!-- Layered overlays for depth -->
    <div class="absolute inset-0 bg-gradient-to-br from-brand-secondary/95 via-brand-secondary/80 to-brand-primary/60"></div>
    
    <!-- Decorative elements -->
    <div class="absolute top-20 left-10 w-64 h-64 bg-brand-primary/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
    
    <!-- Vertical text accent -->
    <div class="absolute left-8 top-1/2 -translate-y-1/2 hidden lg:block">
        <span class="writing-mode-vertical text-white/20 font-black tracking-[0.3em] uppercase text-xs"
              style="writing-mode: vertical-lr; transform: rotate(180deg); letter-spacing: 0.3em;">GOURMETICA · DESDE EL CORAZÓN</span>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Eyebrow badge -->
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-5 py-2 mb-8">
            <span class="w-2 h-2 bg-brand-primary rounded-full animate-pulse"></span>
            <span class="text-white/80 text-xs font-bold uppercase tracking-widest">Desde el Corazón</span>
        </div>

        <h1 class="text-6xl md:text-8xl font-serif font-black text-white mb-8 leading-none tracking-tight">
            Nuestra<br>
            <span class="text-brand-primary italic">Historia</span>
        </h1>
        <p class="text-xl md:text-2xl text-white/70 max-w-3xl mx-auto leading-relaxed font-medium">
            Gourmetica nació de un sueño: elevar la pastelería tradicional peruana a un nivel de arte, fusionando ingredientes locales con técnicas europeas de vanguardia.
        </p>

        <!-- Scroll indicator -->
        <div class="mt-16 flex flex-col items-center gap-2 animate-bounce">
            <span class="text-white/30 text-[10px] uppercase tracking-widest font-bold">Conoce más</span>
            <svg class="w-5 h-5 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </div>
</section>

<!-- Misión y Visión -->
<section class="py-24 bg-brand-bg relative overflow-hidden">
    <!-- Decorative background shape -->
    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-brand-primary to-transparent opacity-40"></div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section header -->
        <div class="text-center mb-16">
            <span class="inline-block text-brand-primary font-bold uppercase tracking-widest text-xs mb-4">Quiénes somos</span>
            <h2 class="text-4xl md:text-5xl font-serif font-black text-brand-secondary">Misión & Visión</h2>
            <div class="mt-4 flex items-center justify-center gap-4">
                <div class="h-px w-16 bg-brand-primary/40"></div>
                <div class="w-2 h-2 bg-brand-primary rounded-full"></div>
                <div class="h-px w-16 bg-brand-primary/40"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">
            <!-- Misión Card -->
            <div class="group relative bg-white rounded-[2.5rem] p-10 lg:p-14 shadow-xl border border-gray-50 overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <!-- Accent corner -->
                <div class="absolute top-0 left-0 w-24 h-24 bg-brand-primary/10 rounded-br-[3rem]"></div>
                <div class="absolute -bottom-8 -right-8 w-40 h-40 bg-brand-primary/5 rounded-full"></div>
                
                <!-- Icon -->
                <div class="relative z-10 w-16 h-16 bg-brand-primary/10 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-brand-primary transition-colors duration-300">
                    <svg class="w-8 h-8 text-brand-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <div class="relative z-10">
                    <span class="text-[10px] uppercase tracking-[0.3em] text-brand-primary font-black block mb-3">01 — Misión</span>
                    <h3 class="text-3xl font-serif font-black text-brand-secondary mb-6 leading-tight">Nuestra Misión</h3>
                    <p class="text-gray-500 leading-relaxed text-base">
                        Crear experiencias gastronómicas únicas a través de la pastelería artesanal de alta calidad, utilizando ingredientes locales de primera selección y técnicas que preservan la autenticidad del sabor. Nos comprometemos a llevar alegría, emoción y momentos memorables a cada cliente, evento y rincón donde Gourmetica esté presente.
                    </p>
                    <div class="mt-8 flex items-center gap-3">
                        <div class="h-px flex-1 bg-gray-100"></div>
                        <span class="w-2 h-2 bg-brand-primary rounded-full flex-shrink-0"></span>
                        <div class="h-px flex-1 bg-gray-100"></div>
                    </div>
                </div>
            </div>

            <!-- Visión Card -->
            <div class="group relative bg-brand-secondary rounded-[2.5rem] p-10 lg:p-14 shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2">
                <!-- Accent corner -->
                <div class="absolute top-0 right-0 w-24 h-24 bg-brand-primary/20 rounded-bl-[3rem]"></div>
                <div class="absolute -bottom-8 -left-8 w-40 h-40 bg-white/5 rounded-full"></div>
                
                <!-- Icon -->
                <div class="relative z-10 w-16 h-16 bg-brand-primary/20 rounded-2xl flex items-center justify-center mb-8 group-hover:bg-brand-primary transition-colors duration-300">
                    <svg class="w-8 h-8 text-brand-primary group-hover:text-white transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                
                <div class="relative z-10">
                    <span class="text-[10px] uppercase tracking-[0.3em] text-brand-primary font-black block mb-3">02 — Visión</span>
                    <h3 class="text-3xl font-serif font-black text-white mb-6 leading-tight">Nuestra Visión</h3>
                    <p class="text-white/60 leading-relaxed text-base">
                        Ser reconocida como la marca de pastelería artesanal más querida del Perú, expandiendo nuestra presencia en todo el país y convirtiéndonos en sinónimo de excelencia, innovación y tradición. Aspiramos a ser referentes latinoamericanos en la fusión de sabores locales con las más altas técnicas de la confitería internacional.
                    </p>
                    <div class="mt-8 flex items-center gap-3">
                        <div class="h-px flex-1 bg-white/10"></div>
                        <span class="w-2 h-2 bg-brand-primary rounded-full flex-shrink-0"></span>
                        <div class="h-px flex-1 bg-white/10"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block text-brand-primary font-bold uppercase tracking-widest text-xs mb-4">Lo que nos define</span>
            <h2 class="text-4xl md:text-5xl font-serif font-black text-brand-secondary">El Compromiso con la Calidad</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Value 1 -->
            <div class="text-center p-8 rounded-3xl bg-brand-bg hover:bg-brand-primary/5 transition-colors group">
                <div class="w-20 h-20 bg-brand-primary/10 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-brand-primary/20 transition-colors">
                    <span class="text-3xl font-serif font-black text-brand-primary">01</span>
                </div>
                <h3 class="text-xl font-bold text-brand-secondary mb-4">Ingredientes Honestos</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Seleccionamos cada fruto, grano de cacao y harina de pequeños productores locales que comparten nuestra visión de respeto por la tierra.</p>
            </div>
            <!-- Value 2 -->
            <div class="text-center p-8 rounded-3xl bg-brand-bg hover:bg-brand-primary/5 transition-colors group">
                <div class="w-20 h-20 bg-brand-primary/10 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-brand-primary/20 transition-colors">
                    <span class="text-3xl font-serif font-black text-brand-primary">02</span>
                </div>
                <h3 class="text-xl font-bold text-brand-secondary mb-4">Procesos Lentos</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Respetamos los tiempos de fermentación natural y los procesos de horneado tradicionales para garantizar texturas y sabores únicos.</p>
            </div>
            <!-- Value 3 -->
            <div class="text-center p-8 rounded-3xl bg-brand-bg hover:bg-brand-primary/5 transition-colors group">
                <div class="w-20 h-20 bg-brand-primary/10 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-brand-primary/20 transition-colors">
                    <span class="text-3xl font-serif font-black text-brand-primary">03</span>
                </div>
                <h3 class="text-xl font-bold text-brand-secondary mb-4">Arte en Cada Pieza</h3>
                <p class="text-gray-500 text-sm leading-relaxed">Cada creación es tratada como una obra de arte: decoración meticulosa, presentación impecable y atención a cada detalle visual y sensorial.</p>
            </div>
        </div>
    </div>
</section>

<!-- Philosophy Section -->
<section class="py-24 bg-brand-bg relative overflow-hidden">
    <div class="absolute inset-0 opacity-5" style="background-image: url('{{ asset('images/cakes.png') }}'); background-size: cover; background-position: center;"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="bg-white/80 backdrop-blur-sm rounded-[3rem] p-12 md:p-20 shadow-2xl border border-white">
            <svg class="w-10 h-10 text-brand-primary/30 mx-auto mb-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
            </svg>
            <h2 class="text-4xl font-serif font-bold text-brand-primary mb-8">Nuestra Filosofía</h2>
            <p class="text-2xl font-serif italic text-gray-500 leading-relaxed">
                "No solo hacemos postres; creamos momentos de felicidad pura. Creemos que la pastelería saludable es posible sin sacrificar el placer absoluto del sabor."
            </p>
            <div class="mt-10 h-px w-24 bg-brand-secondary mx-auto"></div>
            <p class="mt-6 font-black text-brand-secondary uppercase tracking-widest text-sm">— El Equipo Gourmetica</p>
        </div>
    </div>
</section>

@endsection
