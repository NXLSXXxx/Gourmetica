@extends('layouts.app')

@section('title', 'Finalizar Pedido | Gourmetica')

@push('styles')
<!-- Leaflet Map CSS for Premium Address Pinning -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #map {
        height: 320px;
        width: 100%;
        border-radius: 24px;
        border: 2px solid #F1F5F9;
        z-index: 10;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.05), 0 4px 6px -4px rgb(0 0 0 / 0.05);
        transition: all 0.3s ease;
    }
    .leaflet-container {
        font-family: inherit;
    }
    /* Dynamic Address suggestions custom styles */
    .search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        max-height: 250px;
        overflow-y: auto;
        z-index: 999;
        margin-top: 4px;
    }
    .suggestion-item {
        padding: 14px 18px;
        font-size: 13px;
        color: #334155;
        cursor: pointer;
        border-bottom: 1px solid #F1F5F9;
        transition: all 0.2s ease;
    }
    .suggestion-item:last-child {
        border-bottom: none;
    }
    .suggestion-item:hover {
        background-color: #FFFBF7;
        color: #E2B182;
        padding-left: 24px;
    }
</style>
@endpush

@section('content')
<div class="bg-brand-bg min-h-screen py-20" id="checkout-root">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-serif font-bold text-brand-primary mb-10 text-center">Finalizar Pedido</h1>

        <form action="{{ route('orders.store') }}" method="POST" id="checkout-form">
            @csrf
            <input type="hidden" name="culqi_token" id="culqi_token">
            <input type="hidden" name="shipping_type" id="shipping_type" value="pickup">
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Left: Delivery & Sede Info -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Tabs for Shipping Type (Pickup vs. Delivery) -->
                    <div class="bg-white p-4 rounded-2xl shadow-md border border-gray-100 flex gap-2">
                        <button type="button" id="tab-pickup" onclick="setShippingType('pickup')" class="flex-1 py-4 text-center rounded-xl font-bold transition-all flex items-center justify-center gap-2 bg-brand-secondary text-brand-dark shadow-sm">
                            🛍️ Recojo en Tienda
                        </button>
                        <button type="button" id="tab-delivery" onclick="setShippingType('delivery')" class="flex-1 py-4 text-center rounded-xl font-bold transition-all text-gray-500 hover:bg-gray-50 flex items-center justify-center gap-2">
                            🛵 Envío a Domicilio (Delivery)
                        </button>
                    </div>

                    <!-- Pickup (Sede Selection) Card -->
                    <div id="pickup-section" class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 space-y-6">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-brand-secondary/10 rounded-full flex items-center justify-center text-brand-secondary mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                            </div>
                            <h2 class="text-2xl font-serif font-bold text-brand-primary">¿Dónde recoges tu pedido?</h2>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($headquarters as $hq)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="pickup_headquarter_id" value="{{ $hq->id }}" class="peer sr-only" {{ (session('selected_headquarter_id') == $hq->id || (!session('selected_headquarter_id') && $loop->first)) ? 'checked' : '' }} onchange="selectPickupHq(this.value)">
                                <div class="p-6 border-2 border-gray-100 rounded-2xl transition-all peer-checked:border-brand-secondary peer-checked:bg-brand-secondary/5 hover:bg-gray-50">
                                    <h4 class="font-bold text-brand-primary">{{ $hq->name }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $hq->address }}</p>
                                    <p class="text-[10px] text-brand-secondary font-bold mt-2 uppercase tracking-widest">Disponible para hoy</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Delivery Information Card -->
                    <div id="delivery-section" class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 space-y-6 hidden">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-brand-secondary/10 rounded-full flex items-center justify-center text-brand-secondary mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            </div>
                            <h2 class="text-2xl font-serif font-bold text-brand-primary">Dirección de Envío</h2>
                        </div>

                        <!-- City selector to initialize center coordinates -->
                        <div>
                            <label class="block text-xs uppercase font-extrabold text-brand-primary mb-2">Selecciona tu Ciudad</label>
                            <select id="delivery-city" onchange="onCityChange(this.value)" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3.5 text-xs text-brand-primary outline-none focus:border-brand-secondary transition-all">
                                <option value="">Selecciona tu ciudad...</option>
                                <option value="Lima">Lima</option>
                                <option value="Chiclayo">Chiclayo (Torres Paz 567)</option>
                            </select>
                        </div>

                        <!-- Real-time Address Autocomplete -->
                        <div class="relative space-y-2">
                            <label class="block text-xs uppercase font-extrabold text-brand-primary">🔍 Busca tu Dirección Exacta (Calle, Avenida o Lugar)</label>
                            <div class="relative">
                                <input type="text" id="address-search-input" oninput="searchAddress(this.value)" placeholder="Empieza a escribir (ej: Av. Larco, Chiclayo)..." class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3.5 text-xs text-brand-primary placeholder-gray-400 outline-none focus:border-brand-secondary transition-all" disabled>
                                <div id="search-spinner" class="absolute right-4 top-3.5 hidden">
                                    <span class="w-4 h-4 border-2 border-brand-secondary border-t-transparent rounded-full animate-spin block"></span>
                                </div>
                            </div>
                            <div id="suggestions-dropdown" class="search-suggestions hidden"></div>
                        </div>

                        <!-- Interactive Map container with Location Detection -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <label class="block text-xs uppercase font-extrabold text-brand-primary">
                                    📍 Ubica tu casa en el mapa
                                </label>
                                <button type="button" onclick="detectMyLocation()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-brand-primary hover:bg-brand-secondary hover:text-brand-dark text-white text-[10px] font-extrabold shadow-sm transition-all hover:scale-105 cursor-pointer">
                                    🎯 Detectar Mi Ubicación
                                </button>
                            </div>
                            <div id="map"></div>
                        </div>

                        <!-- Dynamic Distance & Pricing Indicator Badge -->
                        <div id="distance-pricing-badge" class="p-5 rounded-2xl border flex items-center justify-between transition-all duration-300 hidden">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl" id="badge-icon">📍</span>
                                <div class="flex-1 pr-4">
                                    <h4 class="font-bold text-brand-primary text-xs uppercase" id="badge-title">Esperando ubicación...</h4>
                                    <p class="text-[11px] text-gray-500 mt-0.5" id="badge-desc">Por favor, escribe tu calle en el buscador o arrastra el pin en el mapa para ubicar tu casa exacta.</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="font-serif font-extrabold text-lg text-amber-600" id="badge-price">S/ --.--</span>
                                <p class="text-[9px] text-amber-500 font-bold uppercase tracking-wider mt-0.5" id="badge-status">Ubicación Pendiente</p>
                            </div>
                        </div>

                        <!-- Manual Address details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs uppercase font-extrabold text-brand-primary mb-2">Dirección de Entrega (Ej: Calle Torres Paz 567, Dpto 201)</label>
                                <input type="text" name="address" id="delivery_address" placeholder="Ej: Calle Torres Paz 567, Dpto 201" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3.5 text-xs text-brand-primary placeholder-gray-400 outline-none focus:border-brand-secondary transition-all">
                            </div>
                            <div>
                                <label class="block text-xs uppercase font-extrabold text-brand-primary mb-2">Referencia de Entrega (Opcional)</label>
                                <input type="text" id="delivery_reference" placeholder="Ej: Frente al parque infantil, reja verde" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3.5 text-xs text-brand-primary placeholder-gray-400 outline-none focus:border-brand-secondary transition-all">
                            </div>
                        </div>

                        <!-- Dynamic delivery zone inputs -->
                        <input type="hidden" name="delivery_zone_id" id="delivery_zone_id">

                        <!-- Headquarters dispatch dispatching notice -->
                        <div id="delivery-dispatch-hq-box" class="p-4 bg-gray-50 rounded-xl border border-gray-100 text-xs text-gray-600 flex items-center gap-2.5 hidden">
                            <span class="text-lg">🏬</span>
                            <div>
                                <p class="font-bold text-brand-primary">Sede Despachadora:</p>
                                <p id="delivery-dispatch-hq-name" class="font-medium text-brand-secondary"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Card -->
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-brand-secondary/10 rounded-full flex items-center justify-center text-brand-secondary mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                            <h2 class="text-2xl font-serif font-bold text-brand-primary">Método de Pago</h2>
                        </div>

                        <div class="space-y-4">
                            <label class="flex items-center p-4 border-2 border-brand-secondary bg-brand-secondary/5 rounded-2xl cursor-pointer">
                                <input type="radio" name="payment_method" value="culqi" class="w-5 h-5 text-brand-secondary" checked>
                                <div class="ml-4">
                                    <span class="block font-bold text-brand-primary">Tarjeta de Crédito / Débito (Culqi)</span>
                                    <span class="text-xs text-gray-500">Pago seguro procesado por Culqi</span>
                                </div>
                                <div class="ml-auto flex gap-2">
                                    <img src="https://vignette.wikia.nocookie.net/logopedia/images/b/b5/Visa_2014.svg/revision/latest?cb=20151124115124" class="h-4">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" class="h-4">
                                </div>
                            </label>
                            
                            <label class="flex items-center p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50">
                                <input type="radio" name="payment_method" value="transfer" class="w-5 h-5 text-brand-secondary">
                                <div class="ml-4">
                                    <span class="block font-bold text-brand-primary">Transferencia Bancaria / Yape</span>
                                    <span class="text-xs text-gray-500">Adjunta tu constancia después de pedir</span>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Right: Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100 sticky top-24">
                        <h3 class="text-xl font-serif font-bold mb-8 border-b border-gray-100 pb-4 text-brand-primary">Resumen del Pedido</h3>
                        
                        <div class="space-y-4 mb-8">
                            @php $cartTotal = 0; @endphp
                            @forelse(session('cart', []) as $item)
                                @php $cartTotal += $item['price'] * $item['quantity']; @endphp
                                <div class="flex justify-between items-start text-sm">
                                    <div class="flex-1 pr-4">
                                        <p class="font-bold text-brand-secondary">{{ $item['quantity'] }}x {{ $item['name'] }}</p>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-widest">{{ implode(', ', $item['options']) }}</p>
                                    </div>
                                    <span class="font-bold text-brand-secondary">S/ {{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                </div>
                            @empty
                                <p class="text-gray-400 italic text-center">Tu carrito está vacío</p>
                            @endforelse
                        </div>

                        <!-- Real-time price breakdown -->
                        <div class="space-y-3 border-t border-gray-100 pt-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Subtotal Productos</span>
                                <span class="text-brand-secondary font-medium" id="summary-subtotal">S/ {{ number_format($cartTotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Costo de Envío</span>
                                <span class="text-emerald-500 font-bold uppercase text-[10px]" id="summary-delivery">Gratis</span>
                            </div>
                            <div class="flex justify-between text-xl font-serif font-bold pt-4 border-t border-dashed border-gray-100">
                                <span class="text-brand-secondary">Total</span>
                                <span class="text-brand-primary" id="summary-total">S/ {{ number_format($cartTotal, 2) }}</span>
                            </div>
                        </div>

                        <!-- Form hidden fields to pass active headquarter -->
                        <input type="hidden" name="headquarter_id" id="form-headquarter-id" value="">

                        <button type="submit" id="submit-order-btn" class="w-full mt-8 btn-premium py-5 text-lg shadow-xl shadow-brand-primary/20">
                            PAGAR AHORA
                        </button>
                        
                        <p class="text-[10px] text-gray-400 text-center mt-6 uppercase tracking-widest">
                            Pago 100% Seguro • Gourmetica Artisanal
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.culqi.com/js/v4"></script>
<!-- Leaflet Map JS for Premium Address Pinning -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    const headquarters = @json($headquarters);
    const deliveryZones = @json($deliveryZones);
    const cartTotal = parseFloat("{{ $cartTotal }}");
    
    // Coordinates mapping for Physical Headquarters
    const hqCoordinates = {
        'Lima': [-12.0945, -77.0341], // Sede Central Lima (San Isidro/Miraflores)
        'Chiclayo': [-6.77137, -79.84411] // Sede Chiclayo (Torres Paz 567)
    };

    // Track selected state
    let selectedShippingType = 'pickup';
    let selectedPickupHqId = '';
    let selectedDeliveryPrice = 0;
    let isWithinCoverage = false;

    // Leaflet map variables
    let map = null;
    let marker = null;
    let autocompleteTimeout = null;
    let activePolygonsOnMap = [];

    // Initialize pickup headquarter selection from default checked element
    window.addEventListener('DOMContentLoaded', () => {
        const checkedHq = document.querySelector('input[name="pickup_headquarter_id"]:checked');
        if (checkedHq) {
            selectPickupHq(checkedHq.value);
        }
        initMap();
    });

    function initMap() {
        // Initialize Map centered on Lima
        map = L.map('map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView(hqCoordinates['Lima'], 13);

        // Highly modern and clean map style (CartoDB Voyager)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        // Beautiful custom icon
        const markerIcon = L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        marker = L.marker(hqCoordinates['Lima'], {
            draggable: true,
            icon: markerIcon
        }).addTo(map);

        // Calculate distance immediately after dragging marker
        marker.on('dragend', function (event) {
            const position = marker.getLatLng();
            document.getElementById('latitude').value = position.lat;
            document.getElementById('longitude').value = position.lng;
            calculateAndApplyPolygonPricing(position.lat, position.lng);
            reverseGeocode(position.lat, position.lng);
        });

        // Crucial fix: Recalculate map sizes when window or layouts change size
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    }

    function setShippingType(type) {
        selectedShippingType = type;
        document.getElementById('shipping_type').value = type;

        const tabPickup = document.getElementById('tab-pickup');
        const tabDelivery = document.getElementById('tab-delivery');
        const pickupSection = document.getElementById('pickup-section');
        const deliverySection = document.getElementById('delivery-section');

        if (type === 'pickup') {
            tabPickup.className = "flex-1 py-4 text-center rounded-xl font-bold transition-all flex items-center justify-center gap-2 bg-brand-secondary text-brand-dark shadow-sm";
            tabDelivery.className = "flex-1 py-4 text-center rounded-xl font-bold transition-all text-gray-500 hover:bg-gray-50 flex items-center justify-center gap-2";
            pickupSection.style.display = "block";
            deliverySection.style.display = "none";
            
            // Set fields
            const checkedHq = document.querySelector('input[name="pickup_headquarter_id"]:checked');
            if (checkedHq) {
                selectPickupHq(checkedHq.value);
            }
            
            selectedDeliveryPrice = 0;
            isWithinCoverage = true;
            document.getElementById('submit-order-btn').disabled = false;
            updatePrices();
        } else {
            tabPickup.className = "flex-1 py-4 text-center rounded-xl font-bold transition-all text-gray-500 hover:bg-gray-50 flex items-center justify-center gap-2";
            tabDelivery.className = "flex-1 py-4 text-center rounded-xl font-bold transition-all bg-brand-secondary text-brand-dark shadow-sm";
            pickupSection.style.display = "none";
            deliverySection.style.display = "block";

            // CRITICAL FIX: Re-render and resize Leaflet maps instantly to prevent grey grid visual glitch!
            setTimeout(() => {
                map.invalidateSize(true);
            }, 50);

            // Check if coordinates represent raw Hq center coordinates
            const lat = parseFloat(document.getElementById('latitude').value);
            const lng = parseFloat(document.getElementById('longitude').value);
            const city = document.getElementById('delivery-city').value;
            
            if (city && hqCoordinates[city] && lat === hqCoordinates[city][0] && lng === hqCoordinates[city][1]) {
                resetPricingToPending();
            } else if (lat && lng) {
                calculateAndApplyPolygonPricing(lat, lng);
            } else {
                resetPricingToPending();
            }
        }
    }

    function selectPickupHq(hqId) {
        selectedPickupHqId = hqId;
        if (selectedShippingType === 'pickup') {
            document.getElementById('form-headquarter-id').value = hqId;
        }
    }

    // Handle City dropdown selection
    function onCityChange(city) {
        const searchInput = document.getElementById('address-search-input');
        const dispatchBox = document.getElementById('delivery-dispatch-hq-box');
        const dispatchHqName = document.getElementById('delivery-dispatch-hq-name');
        
        searchInput.disabled = true;
        searchInput.value = '';
        dispatchBox.style.display = 'none';
        
        selectedDeliveryPrice = 0;
        updatePrices();

        // Clear existing polygons from map
        activePolygonsOnMap.forEach(p => map.removeLayer(p));
        activePolygonsOnMap = [];

        if (!city) {
            document.getElementById('distance-pricing-badge').style.display = 'none';
            return;
        }

        // Center map to selected city coords (Base Center position)
        if (hqCoordinates[city]) {
            map.setView(hqCoordinates[city], 13);
            marker.setLatLng(hqCoordinates[city]);
            document.getElementById('latitude').value = hqCoordinates[city][0];
            document.getElementById('longitude').value = hqCoordinates[city][1];
            
            // Re-draw tiles safely
            setTimeout(() => {
                map.invalidateSize(true);
            }, 100);

            // Draw coverage areas for this specific city on the checkout map live!
            const matchingHq = headquarters.find(hq => hq.city.toLowerCase() === city.toLowerCase() || hq.name.toLowerCase().includes(city.toLowerCase()));
            if (matchingHq) {
                // Find all delivery zones for this headquarter
                const zones = deliveryZones.filter(z => z.headquarter_id == matchingHq.id);
                zones.forEach(zone => {
                    if (zone.coordinates) {
                        try {
                            const coords = JSON.parse(zone.coordinates);
                            if (coords && coords.length > 2) {
                                const poly = L.polygon(coords, {
                                    color: '#E9A171',
                                    fillColor: '#E9A171',
                                    fillOpacity: 0.15,
                                    weight: 1.5,
                                    dashArray: '4, 4'
                                }).addTo(map);
                                
                                poly.bindTooltip(zone.name, {
                                    permanent: true,
                                    direction: 'center',
                                    className: 'text-[9px] font-bold text-brand-secondary bg-white/80 border border-brand-secondary/20 px-1 py-0.5 rounded shadow-sm opacity-80'
                                });

                                activePolygonsOnMap.push(poly);
                            }
                        } catch(e) {
                            console.error('Error drawing checkout polygon preview:', e);
                        }
                    }
                });
            }

            resetPricingToPending();
        }

        // Enable search input
        searchInput.disabled = false;

        // Find the headquarter matching this city
        const matchingHq = headquarters.find(hq => hq.city.toLowerCase() === city.toLowerCase() || hq.name.toLowerCase().includes(city.toLowerCase()));
        
        if (matchingHq) {
            document.getElementById('form-headquarter-id').value = matchingHq.id;
            dispatchHqName.textContent = `${matchingHq.name} (${matchingHq.address})`;
            dispatchBox.style.display = 'flex';
        }
    }

    function resetPricingToPending() {
        const badge = document.getElementById('distance-pricing-badge');
        const badgeTitle = document.getElementById('badge-title');
        const badgeDesc = document.getElementById('badge-desc');
        const badgePrice = document.getElementById('badge-price');
        const badgeStatus = document.getElementById('badge-status');
        const badgeIcon = document.getElementById('badge-icon');
        const submitBtn = document.getElementById('submit-order-btn');

        badge.style.display = 'flex';
        badgeTitle.textContent = "Esperando ubicación...";
        badgeDesc.innerHTML = "Hemos sombreado nuestra <b>Zona de Cobertura en color naranja</b> en el mapa. Por favor, escribe tu calle o arrastra el pin dentro del área sombreada.";
        badgePrice.textContent = "S/ --.--";
        badgePrice.className = "font-serif font-extrabold text-lg text-amber-600";
        badgeStatus.textContent = "Ubicación Pendiente";
        badgeStatus.className = "text-[9px] text-amber-500 font-bold uppercase tracking-wider mt-0.5";
        badgeIcon.textContent = "📍";
        badge.className = "p-5 rounded-2xl border border-amber-100 bg-amber-50/20 flex items-center justify-between transition-all duration-300";

        selectedDeliveryPrice = 0;
        isWithinCoverage = false;
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        
        updatePrices();
    }

    // GPS Detector function
    function detectMyLocation() {
        if (!navigator.geolocation) {
            alert('Tu navegador o dispositivo no soporta la geolocalización.');
            return;
        }

        const submitBtn = document.getElementById('submit-order-btn');
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';

        navigator.geolocation.getCurrentPosition(position => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lon;

            // Instantly center map and move marker
            map.setView([lat, lon], 16);
            marker.setLatLng([lat, lon]);

            // Detect closest Gourmetica headquarters (Lima or Chiclayo)
            let closestHqKey = 'Lima';
            let closestDistance = Infinity;

            for (const key in hqCoordinates) {
                const dist = calculateHaversineDistance(lat, lon, hqCoordinates[key][0], hqCoordinates[key][1]);
                if (dist < closestDistance) {
                    closestDistance = dist;
                    closestHqKey = key;
                }
            }

            // Select closest city dropdown option
            document.getElementById('delivery-city').value = closestHqKey;
            
            // Trigger city change visual updates but bypass the centering reset so map stays on user GPS
            const searchInput = document.getElementById('address-search-input');
            const dispatchBox = document.getElementById('delivery-dispatch-hq-box');
            const dispatchHqName = document.getElementById('delivery-dispatch-hq-name');
            
            searchInput.disabled = false;
            searchInput.value = '';
            
            // Draw coverage polygons for the closest city
            activePolygonsOnMap.forEach(p => map.removeLayer(p));
            activePolygonsOnMap = [];

            const matchingHq = headquarters.find(hq => hq.city.toLowerCase() === closestHqKey.toLowerCase() || hq.name.toLowerCase().includes(closestHqKey.toLowerCase()));
            if (matchingHq) {
                document.getElementById('form-headquarter-id').value = matchingHq.id;
                dispatchHqName.textContent = `${matchingHq.name} (${matchingHq.address})`;
                dispatchBox.style.display = 'flex';

                const zones = deliveryZones.filter(z => z.headquarter_id == matchingHq.id);
                zones.forEach(zone => {
                    if (zone.coordinates) {
                        try {
                            const coords = JSON.parse(zone.coordinates);
                            if (coords && coords.length > 2) {
                                const poly = L.polygon(coords, {
                                    color: '#E9A171',
                                    fillColor: '#E9A171',
                                    fillOpacity: 0.15,
                                    weight: 1.5,
                                    dashArray: '4, 4'
                                }).addTo(map);
                                
                                poly.bindTooltip(zone.name, {
                                    permanent: true,
                                    direction: 'center',
                                    className: 'text-[9px] font-bold text-brand-secondary bg-white/80 border border-brand-secondary/20 px-1 py-0.5 rounded shadow-sm opacity-80'
                                });
                                activePolygonsOnMap.push(poly);
                            }
                        } catch(e) {
                            console.error(e);
                        }
                    }
                });
            }

            // Calculate distance and geocode address text from GPS coordinates
            calculateAndApplyPolygonPricing(lat, lon);
            reverseGeocode(lat, lon);

            setTimeout(() => {
                map.invalidateSize(true);
            }, 100);

        }, error => {
            console.error('Error detecting location:', error);
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            alert('No pudimos acceder a tu GPS. Por favor, selecciona tu ciudad y escribe tu dirección en el buscador.');
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
    }

    // Ray-Casting Algorithm for Point-in-Polygon validation (100% accurate!)
    function isPointInPolygon(point, polygon) {
        const x = point[0], y = point[1];
        let inside = false;
        for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
            const xi = polygon[i][0], yi = polygon[i][1];
            const xj = polygon[j][0], yj = polygon[j][1];
            
            const intersect = ((yi > y) !== (yj > y))
                && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    }

    // Point-in-Polygon dynamic validation
    function calculateAndApplyPolygonPricing(lat, lng) {
        const city = document.getElementById('delivery-city').value;
        const badge = document.getElementById('distance-pricing-badge');
        const badgeTitle = document.getElementById('badge-title');
        const badgeDesc = document.getElementById('badge-desc');
        const badgePrice = document.getElementById('badge-price');
        const badgeStatus = document.getElementById('badge-status');
        const badgeIcon = document.getElementById('badge-icon');
        const submitBtn = document.getElementById('submit-order-btn');

        if (!city) {
            badge.style.display = 'none';
            return;
        }

        const matchingHq = headquarters.find(hq => hq.city.toLowerCase() === city.toLowerCase() || hq.name.toLowerCase().includes(city.toLowerCase()));
        if (!matchingHq) {
            badge.style.display = 'none';
            return;
        }

        // Find active delivery zones for this headquarter
        const activeZones = deliveryZones.filter(z => z.headquarter_id == matchingHq.id);
        let matchedZone = null;

        // Check if point falls inside any custom polygon zone
        for (const zone of activeZones) {
            if (zone.coordinates) {
                try {
                    const polygonVertices = JSON.parse(zone.coordinates);
                    if (polygonVertices && polygonVertices.length > 2) {
                        if (isPointInPolygon([lat, lng], polygonVertices)) {
                            matchedZone = zone;
                            break; // First match found wins!
                        }
                    }
                } catch (e) {
                    console.error('Error parsing coordinates for zone:', zone.name, e);
                }
            }
        }

        badge.style.display = 'flex';

        if (matchedZone) {
            selectedDeliveryPrice = parseFloat(matchedZone.price);
            document.getElementById('delivery_zone_id').value = matchedZone.id;
            isWithinCoverage = true;
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';

            badgeTitle.textContent = `Zona Encontrada: ${matchedZone.name}`;
            badgeDesc.textContent = `Tu dirección se encuentra dentro de nuestra zona de reparto autorizada.`;
            badgePrice.textContent = `S/ ${selectedDeliveryPrice.toFixed(2)}`;
            badgePrice.className = "font-serif font-extrabold text-lg text-emerald-600";
            badgeStatus.textContent = "Cobertura Activa";
            badgeStatus.className = "text-[9px] text-emerald-500 font-bold uppercase tracking-wider mt-0.5";
            badgeIcon.textContent = "🛵";
            badge.className = "p-5 rounded-2xl border border-emerald-100 bg-emerald-50/20 flex items-center justify-between transition-all duration-300";
        } else {
            // Not inside any drawn custom polygon zone
            selectedDeliveryPrice = 0;
            document.getElementById('delivery_zone_id').value = '';
            isWithinCoverage = false;
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';

            badgeTitle.textContent = "FUERA DE COBERTURA";
            badgeDesc.textContent = "Tu ubicación está fuera de nuestras zonas de reparto dibujadas. Arrastra el pin dentro del área sombreada naranja o contáctanos.";
            badgePrice.textContent = "NO DISPONIBLE";
            badgePrice.className = "font-serif font-bold text-xs text-red-600 uppercase";
            badgeStatus.textContent = "Sin Cobertura";
            badgeStatus.className = "text-[9px] text-red-500 font-bold uppercase tracking-wider mt-0.5";
            badgeIcon.textContent = "⚠️";
            badge.className = "p-5 rounded-2xl border border-red-100 bg-red-50/20 flex items-center justify-between transition-all duration-300";
        }

        updatePrices();
    }

    // Haversine Formula for generic calculations if needed
    function calculateHaversineDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth radius in km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = 
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Recalculate and update interface values
    function updatePrices() {
        const finalTotal = cartTotal + selectedDeliveryPrice;

        // Update elements
        document.getElementById('summary-subtotal').textContent = `S/ ${cartTotal.toFixed(2)}`;
        
        const deliveryEl = document.getElementById('summary-delivery');
        if (selectedShippingType === 'delivery' && selectedDeliveryPrice > 0) {
            deliveryEl.textContent = `S/ ${selectedDeliveryPrice.toFixed(2)}`;
            deliveryEl.className = "text-brand-secondary font-bold";
        } else {
            deliveryEl.textContent = "Gratis";
            deliveryEl.className = "text-emerald-500 font-bold uppercase text-[10px]";
        }

        document.getElementById('summary-total').textContent = `S/ ${finalTotal.toFixed(2)}`;
    }

    // Real-Time Nominatim Geocoding Autocomplete Search
    function searchAddress(query) {
        const city = document.getElementById('delivery-city').value;
        const spinner = document.getElementById('search-spinner');
        const dropdown = document.getElementById('suggestions-dropdown');

        clearTimeout(autocompleteTimeout);
        
        if (query.trim().length < 3) {
            dropdown.style.display = 'none';
            return;
        }

        spinner.style.display = 'block';

        autocompleteTimeout = setTimeout(() => {
            const refinedQuery = `${query}, ${city}, Peru`;
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(refinedQuery)}&limit=5&addressdetails=1`)
                .then(res => res.json())
                .then(data => {
                    spinner.style.display = 'none';
                    dropdown.innerHTML = '';

                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'suggestion-item';
                            div.textContent = item.display_name;
                            div.onclick = () => selectSuggestion(item);
                            dropdown.appendChild(div);
                        });
                        dropdown.style.display = 'block';
                    } else {
                        dropdown.style.display = 'none';
                    }
                })
                .catch(err => {
                    spinner.style.display = 'none';
                    console.error('Nominatim autocomplete error:', err);
                });
        }, 600);
    }

    function selectSuggestion(item) {
        document.getElementById('suggestions-dropdown').style.display = 'none';
        document.getElementById('address-search-input').value = item.display_name;
        document.getElementById('delivery_address').value = item.display_name;

        const lat = parseFloat(item.lat);
        const lon = parseFloat(item.lon);
        
        map.setView([lat, lon], 16);
        marker.setLatLng([lat, lon]);

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lon;

        // Recalculate price instantly using Point-in-Polygon geocerca
        calculateAndApplyPolygonPricing(lat, lon);
    }

    // Reverse Geocoding when pinning manually
    function reverseGeocode(lat, lon) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&addressdetails=1`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('delivery_address').value = data.display_name;
                    document.getElementById('address-search-input').value = data.display_name;
                }
            })
            .catch(err => console.error('Nominatim reverse geocode error:', err));
    }

    // Close dropdowns if click outside
    document.addEventListener('click', (e) => {
        const dropdown = document.getElementById('suggestions-dropdown');
        if (dropdown && !document.getElementById('address-search-input').contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Culqi Configuration
    Culqi.publicKey = '{{ $culqiPublicKey }}';
    
    const checkoutForm = document.getElementById('checkout-form');
    
    checkoutForm.addEventListener('submit', function(e) {
        if (selectedShippingType === 'delivery') {
            const city = document.getElementById('delivery-city').value;
            const address = document.getElementById('delivery_address').value.trim();

            if (!city || !address) {
                e.preventDefault();
                alert('Por favor, completa la ciudad y dirección completa en el mapa.');
                return;
            }

            if (!isWithinCoverage) {
                e.preventDefault();
                alert('La ubicación seleccionada está fuera de nuestras geocercas de cobertura express.');
                return;
            }
        }

        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (paymentMethod === 'culqi' && !document.getElementById('culqi_token').value) {
            e.preventDefault();
            
            const finalTotal = cartTotal + selectedDeliveryPrice;
            
            Culqi.settings({
                title: 'Gourmetica',
                currency: 'PEN',
                description: 'Pedido Gourmetica',
                amount: Math.round(finalTotal * 100) // Correct total charged!
            });

            Culqi.options({
                lang: 'auto',
                installments: false,
                paymentMethods: {
                    tarjeta: true,
                    yape: true,
                    banca_movil: true,
                    agente: true,
                    cuotealo: false
                }
            });

            Culqi.open();
        }
    });

    function culqi() {
        if (Culqi.token) {
            const token = Culqi.token.id;
            document.getElementById('culqi_token').value = token;
            checkoutForm.submit();
        } else {
            console.log(Culqi.error);
            alert(Culqi.error.user_message);
        }
    }
</script>
@endpush

<style>
    /* Styling adjustments for tabs */
    #tab-pickup, #tab-delivery {
        cursor: pointer;
        border: none;
        outline: none;
    }
</style>
