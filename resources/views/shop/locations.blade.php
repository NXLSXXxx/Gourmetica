@extends('layouts.app')

@section('title', 'Nuestras Sedes | Gourmetica')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map { height: 500px; border-radius: 1.5rem; }
</style>
@endpush

@section('content')
<div class="bg-brand-bg min-h-screen py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <header class="text-center mb-16">
            <h1 class="text-5xl font-serif font-bold text-brand-primary mb-4">Encuéntranos</h1>
            <p class="text-gray-500 text-lg">Visítanos en cualquiera de nuestras sedes y disfruta de la experiencia Gourmetica.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Locations List -->
            <div class="lg:col-span-1 space-y-6 max-h-[500px] overflow-y-auto pr-4">
                @foreach($locations as $location)
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:border-brand-primary transition-all group" onclick="focusLocation({{ $location->id }}, {{ $location->latitude ?? -12.046374 }}, {{ $location->longitude ?? -77.042793 }})">
                    <h3 class="text-xl font-serif font-bold text-brand-primary group-hover:text-brand-secondary transition-colors">{{ $location->name }}</h3>
                    <p class="text-gray-500 text-sm mt-2 flex items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 mt-0.5 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        </svg>
                        {{ $location->address }}
                    </p>
                    <div class="mt-4 flex space-x-4">
                        <span class="text-xs font-bold text-brand-primary bg-brand-primary/10 px-3 py-1 rounded-full">ABIERTO</span>
                        <span class="text-xs text-gray-400">{{ $location->phone }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Map -->
            <div class="lg:col-span-2">
                <div id="map" class="shadow-xl"></div>
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
            .bindPopup("<b>{{ $location->name }}</b><br>{{ $location->address }}");
    @endforeach

    function focusLocation(id, lat, lng) {
        map.setView([lat, lng], 16);
        markers[id].openPopup();
    }
</script>
@endpush
@endsection
