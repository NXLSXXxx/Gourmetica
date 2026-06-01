@extends('layouts.app')

@section('title', 'Nuestras Sedes | Gourmetica')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 500px; border-radius: 1.5rem; }
    .leaflet-container { border-radius: 1.5rem; }
</style>
@endpush

@section('content')

<!-- Hero Banner -->
<section class="banner-hero relative min-h-[60vh] flex items-center justify-center overflow-hidden"
         style="background-image: url('{{ asset('images/hero.png') }}'); background-size: cover; background-position: center;">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-brand-secondary/96 via-brand-secondary/85 to-brand-primary/60"></div>

    <!-- Decorative blobs -->
    <div class="absolute top-10 right-10 w-80 h-80 bg-brand-primary/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Grid pattern overlay -->
    <div class="absolute inset-0 opacity-5" 
         style="background-image: radial-gradient(circle, rgba(255,255,255,0.15) 1px, transparent 1px); background-size: 40px 40px;">
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-5 py-2 mb-8">
            <svg class="w-3.5 h-3.5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="text-white/80 text-xs font-bold uppercase tracking-widest">Siempre cerca de ti</span>
        </div>

        <h1 class="text-6xl md:text-8xl font-serif font-black text-white mb-6 leading-none tracking-tight">
            Nuestras<br>
            <span class="text-brand-primary italic">Casas</span>
        </h1>
        <p class="text-xl text-white/60 max-w-2xl mx-auto font-medium leading-relaxed">
            Visítanos en cualquiera de nuestras sedes y disfruta de la experiencia Gourmetica. Siempre recién horneado, siempre con amor.
        </p>

        <!-- Stats -->
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 mt-12">
            <div class="text-center">
                <span class="block text-4xl font-black text-brand-primary">10+</span>
                <span class="text-white/40 text-[10px] uppercase tracking-widest font-bold">Sedes</span>
            </div>
            <div class="w-px h-10 bg-white/20 hidden md:block"></div>
            <div class="text-center">
                <span class="block text-4xl font-black text-brand-primary">7</span>
                <span class="text-white/40 text-[10px] uppercase tracking-widest font-bold">Días</span>
            </div>
            <div class="w-px h-10 bg-white/20 hidden md:block"></div>
            <div class="text-center">
                <span class="block text-4xl font-black text-brand-primary">Lima</span>
                <span class="text-white/40 text-[10px] uppercase tracking-widest font-bold">Ciudad</span>
            </div>
        </div>
    </div>
</section>

<!-- Locations List + Map -->
<div class="bg-brand-bg py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Section header -->
        <div class="text-center mb-12">
            <span class="inline-block text-brand-primary font-bold uppercase tracking-widest text-xs mb-3">Encuentra tu tienda</span>
            <h2 class="text-3xl font-serif font-black text-brand-secondary">Selecciona una sede</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Locations List -->
            <div class="lg:col-span-1 space-y-4 max-h-[600px] overflow-y-auto pr-2" style="scrollbar-width: thin; scrollbar-color: #E9A171 transparent;">
                @foreach($locations as $location)
                <div class="group bg-white p-6 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:border-brand-primary hover:shadow-lg transition-all duration-300 hover:-translate-y-1" 
                     onclick="focusLocation({{ $location->id }}, {{ $location->latitude ?? -12.046374 }}, {{ $location->longitude ?? -77.042793 }})">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 bg-brand-primary/10 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-brand-primary transition-colors duration-300 mt-0.5">
                            <svg class="w-5 h-5 text-brand-primary group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg font-bold text-brand-secondary group-hover:text-brand-primary transition-colors leading-tight">{{ $location->name }}</h3>
                            <p class="text-gray-400 text-sm mt-1 leading-relaxed">{{ $location->address }}</p>
                            <div class="flex items-center gap-3 mt-3">
                                <span class="text-[10px] font-black text-brand-primary bg-brand-primary/10 px-3 py-1 rounded-full uppercase tracking-widest">ABIERTO</span>
                                @if($location->phone)
                                <span class="text-xs text-gray-400">{{ $location->phone }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Map -->
            <div class="lg:col-span-2">
                <div class="sticky top-28">
                    <div id="map" class="shadow-2xl border border-gray-100"></div>
                    <p class="text-center text-xs text-gray-400 mt-4 font-medium">Haz clic en una sede para centrar el mapa</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([-12.046374, -77.042793], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markers = {};

    @foreach($locations as $location)
        markers[{{ $location->id }}] = L.marker([{{ $location->latitude ?? -12.046374 }}, {{ $location->longitude ?? -77.042793 }}])
            .addTo(map)
            .bindPopup("<b style='color:#E9A171;font-family:serif;'>{{ $location->name }}</b><br><span style='color:#666;font-size:12px;'>{{ $location->address }}</span>");
    @endforeach

    function focusLocation(id, lat, lng) {
        map.setView([lat, lng], 16);
        markers[id].openPopup();
    }
</script>
@endpush

@endsection
