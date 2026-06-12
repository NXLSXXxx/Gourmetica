@extends('layouts.intranet')

@section('title', 'Editar Sede | Gourmetica Intranet')

@section('styles')
<style>
    #map { height: 400px; width: 100%; border-radius: 0.75rem; }
</style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <header class="mb-10">
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Editar Sede</h1>
        <p class="text-slate-400 mt-2">Modificando la información de: {{ $headquarter->name }}</p>
    </header>

    <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
        <form action="{{ route('admin.headquarters.update', $headquarter->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Nombre de la Sede</label>
                    <input type="text" name="name" value="{{ $headquarter->name }}" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Ciudad</label>
                    <input type="text" name="city" value="{{ $headquarter->city }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Teléfono</label>
                    <input type="text" name="phone" value="{{ $headquarter->phone }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Dirección Completa</label>
                    <input type="text" name="address" value="{{ $headquarter->address }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Email de Contacto</label>
                    <input type="email" name="email" value="{{ $headquarter->email }}" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Ubicación en el Mapa</label>
                    <div class="mb-4">
                        <input type="text" id="map-search" value="{{ $headquarter->address }}" autocomplete="off" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all" placeholder="Buscar dirección en Google Maps...">
                    </div>
                    <div id="map" class="border border-slate-700"></div>
                    <p class="text-xs text-slate-500 mt-2">Arrastra el marcador o haz clic en el mapa para ajustar la ubicación exacta.</p>
                    
                    <input type="hidden" name="latitude" id="latitude" value="{{ $headquarter->latitude }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $headquarter->longitude }}">
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center text-slate-400 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ $headquarter->is_active ? 'checked' : '' }} class="mr-3 w-5 h-5 rounded bg-slate-800 border-slate-700 text-brand-secondary focus:ring-brand-secondary transition-all">
                        <span class="text-sm">Sede Activa (Disponible para ventas y asignación)</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.headquarters.index') }}" class="px-6 py-3 text-slate-400 hover:text-white transition-colors text-sm font-bold">CANCELAR</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
                    ACTUALIZAR SEDE
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXo-Lpvk7_PQ6KnL4XjxA5ux1pHsopYTk&libraries=places&callback=initGoogleMap&loading=async" async defer></script>
<script>
    let map = null;
    let marker = null;
    let autocomplete = null;
    let geocoder = null;

    function initGoogleMap() {
        const initialLat = parseFloat("{{ $headquarter->latitude ?? -12.0464 }}");
        const initialLng = parseFloat("{{ $headquarter->longitude ?? -77.0428 }}");

        const initialLatLng = { lat: initialLat, lng: initialLng };

        // Initialize Map
        map = new google.maps.Map(document.getElementById('map'), {
            center: initialLatLng,
            zoom: 14,
            disableDefaultUI: false
        });

        geocoder = new google.maps.Geocoder();

        // Initialize Marker
        marker = new google.maps.Marker({
            position: initialLatLng,
            map: map,
            draggable: true,
            title: "Ubicación de la sede"
        });

        // Initialize Places Autocomplete
        const searchInput = document.getElementById('map-search');
        autocomplete = new google.maps.places.Autocomplete(searchInput, {
            fields: ['geometry', 'formatted_address', 'name', 'address_components'],
        });

        // Bind Autocomplete to Map bounds
        autocomplete.bindTo('bounds', map);

        // When a place is selected from Autocomplete
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                return;
            }

            // Update Map view
            if (place.geometry.viewport) {
                map.fitBounds(place.geometry.viewport);
            } else {
                map.setCenter(place.geometry.location);
                map.setZoom(16);
            }

            // Update Marker position
            marker.setPosition(place.geometry.location);

            // Update hidden coordinate inputs
            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Fill address input field
            const addressInput = document.querySelector('input[name="address"]');
            if (addressInput) {
                addressInput.value = place.formatted_address || place.name;
            }

            // Deduce city if possible
            deduceCityFromComponents(place.address_components);
        });

        // Marker dragend event
        marker.addListener('dragend', function() {
            const position = marker.getPosition();
            const lat = position.lat();
            const lng = position.lng();

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            geocodePosition(position);
        });

        // Map click event
        map.addListener('click', function(event) {
            const latLng = event.latLng;
            marker.setPosition(latLng);

            document.getElementById('latitude').value = latLng.lat();
            document.getElementById('longitude').value = latLng.lng();

            geocodePosition(latLng);
        });
    }

    function geocodePosition(latLng) {
        geocoder.geocode({ location: latLng }, function(results, status) {
            if (status === 'OK') {
                if (results[0]) {
                    // Update search input
                    document.getElementById('map-search').value = results[0].formatted_address;
                    
                    // Update address field
                    const addressInput = document.querySelector('input[name="address"]');
                    if (addressInput) {
                        addressInput.value = results[0].formatted_address;
                    }

                    // Deduce city
                    deduceCityFromComponents(results[0].address_components);
                }
            } else {
                console.error('Geocoder failed due to: ' + status);
            }
        });
    }

    function deduceCityFromComponents(components) {
        if (!components) return;
        
        let city = '';
        for (let i = 0; i < components.length; i++) {
            const types = components[i].types;
            if (types.includes('locality')) {
                city = components[i].long_name;
                break;
            } else if (types.includes('administrative_area_level_2')) {
                city = components[i].long_name;
            }
        }

        if (city) {
            const cityInput = document.querySelector('input[name="city"]');
            if (cityInput) {
                cityInput.value = city;
            }
        }
    }
</script>
@endsection
