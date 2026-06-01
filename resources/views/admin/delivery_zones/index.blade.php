@extends('layouts.intranet')

@section('title', 'Zonas de Delivery | Gourmetica Intranet')

@section('styles')
<!-- Leaflet Map CSS for Premium Address Pinning -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #modal-map {
        height: 380px;
        width: 100%;
        border-radius: 20px;
        border: 2px solid #334155;
        z-index: 10;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.3);
    }
    #preview-map {
        height: 280px;
        width: 100%;
        border-radius: 20px;
        border: 2px solid #334155;
        z-index: 10;
        box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.3);
    }
    .leaflet-container {
        font-family: inherit;
    }
</style>
@endsection

@section('content')
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Zonas de Delivery (Geocercas)</h1>
        <p class="text-slate-400 mt-2">Dibuja polígonos personalizados en el mapa para delimitar tus áreas de despacho y tarifas de envío exactas.</p>
    </div>
    
    <button onclick="openCreateModal()" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-extrabold text-sm hover:scale-105 transition-transform flex items-center gap-2 shadow-lg cursor-pointer">
        🗺️ DIBUJAR NUEVA ZONA
    </button>
</header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left Column: Zones List Table -->
    <div class="lg:col-span-2 bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4">Sede / Ubicación</th>
                        <th class="px-6 py-4">Nombre de la Zona</th>
                        <th class="px-6 py-4">Tarifa de Envío</th>
                        <th class="px-6 py-4">Geocerca</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700 text-sm">
                    @forelse($zones as $zone)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <!-- Headquarter Sede info -->
                        <td class="px-6 py-4 font-bold text-white">
                            <span class="px-2.5 py-1 rounded bg-slate-800 text-brand-secondary border border-slate-700 text-[10px] uppercase font-mono tracking-wider">
                                🏬 {{ $zone->headquarter->name }}
                            </span>
                        </td>
                        
                        <!-- District name -->
                        <td class="px-6 py-4 font-bold text-slate-200">
                            {{ $zone->name }}
                        </td>
                        
                        <!-- Cost of shipping -->
                        <td class="px-6 py-4 font-serif font-bold text-brand-secondary text-base">
                            S/ {{ number_format($zone->price, 2) }}
                        </td>

                        <!-- Coordinates presence badge -->
                        <td class="px-6 py-4">
                            @if($zone->coordinates)
                                @php
                                    $verticesCount = count(json_decode($zone->coordinates, true) ?? []);
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold uppercase flex items-center gap-1 w-max">
                                    🟢 {{ $verticesCount }} Vértices
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-[10px] rounded bg-red-500/10 text-red-400 border border-red-500/20 font-bold uppercase flex items-center gap-1 w-max">
                                    🔴 Sin Polígono
                                </span>
                            @endif
                        </td>

                        <!-- Active indicator status badge -->
                        <td class="px-6 py-4 text-center">
                            @if($zone->is_active)
                                <span class="px-2.5 py-0.5 text-[9px] uppercase font-bold rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Activo</span>
                            @else
                                <span class="px-2.5 py-0.5 text-[9px] uppercase font-bold rounded-full bg-slate-800 text-slate-500 border border-slate-700">Inactivo</span>
                            @endif
                        </td>

                        <!-- Action controls -->
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center space-x-3">
                                <!-- Edit trigger -->
                                <button onclick="openEditModal({{ json_encode($zone) }})" class="text-slate-400 hover:text-brand-secondary transition-colors cursor-pointer" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                
                                <!-- Delete control -->
                                <form action="{{ route('admin.delivery_zones.destroy', $zone->id) }}" method="POST" data-confirm="¿Estás seguro de que deseas eliminar esta zona de reparto? Los pedidos históricos no se verán afectados." class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer" title="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            No hay distritos de delivery configurados aún. ¡Dibuja tu primera zona!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column: Interactive Map Preview -->
    <div class="bg-[#1E293B] rounded-2xl border border-slate-700 p-6 shadow-xl space-y-6 self-start">
        <h3 class="font-serif font-bold text-white text-lg flex items-center gap-2">
            🗺️ Mapa General de Cobertura
        </h3>
        <p class="text-xs text-slate-400 leading-relaxed">
            Abajo puedes ver cómo están delimitadas tus geocercas actuales en Chiclayo y Lima.
        </p>

        <!-- Preview Map -->
        <div id="preview-map" class="h-[280px] w-full rounded-xl border-2 border-slate-700 shadow-inner"></div>

        <div class="p-4 bg-slate-800/40 border border-slate-700/60 rounded-xl space-y-2 text-xs">
            <p class="font-bold text-brand-secondary uppercase">💡 ¿Cómo funciona la delimitación manual?</p>
            <ul class="list-disc pl-4 text-slate-400 space-y-1.5">
                <li>Haz clic en <strong>NUEVA ZONA</strong>.</li>
                <li>Activa la herramienta de dibujo y haz clic en el mapa para marcar los vértices de la geocerca.</li>
                <li><strong>Cierra el polígono</strong> haciendo clic en el primer punto para guardar el área.</li>
                <li>Asigna la tarifa de envío justa para esa región delimitada.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Modal: Create / Edit Zone (Dynamic Map Layout) -->
<div id="zone-modal" class="fixed inset-0 z-50 bg-black/75 backdrop-blur-md hidden items-center justify-center p-4">
    <div class="w-full max-w-4xl bg-[#1E293B] rounded-3xl border border-slate-700 shadow-2xl overflow-hidden flex flex-col md:flex-row">
        
        <!-- Left: Form Controls -->
        <div class="w-full md:w-5/12 p-8 border-b md:border-b-0 md:border-r border-slate-700 flex flex-col justify-between space-y-6 max-h-[90vh] overflow-y-auto">
            <div>
                <h3 id="modal-title" class="font-serif font-bold text-xl text-white">Nueva Zona de Delivery</h3>
                <p class="text-xs text-slate-400 mt-1">Dibuja y delimita el área en el mapa a la derecha.</p>
            </div>

            <form id="modal-form" action="" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                <!-- Coordinates JSON array hidden input -->
                <input type="hidden" name="coordinates" id="modal-coordinates">

                <!-- Headquarter Sede selector -->
                <div>
                    <label class="block text-xs uppercase font-extrabold text-slate-400 mb-2">🏬 Sede Asociada</label>
                    <select name="headquarter_id" id="modal-hq-id" onchange="onModalHqChange(this.value)" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3.5 text-xs text-white outline-none focus:border-brand-primary">
                        @foreach($headquarters as $hq)
                            <option value="{{ $hq->id }}">{{ $hq->name }} ({{ $hq->city }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Zone Name -->
                <div>
                    <label class="block text-xs uppercase font-extrabold text-slate-400 mb-2">📍 Nombre de la Zona (Ej: Chiclayo Centro, Zona Pimentel)</label>
                    <input type="text" name="name" id="modal-name" required placeholder="Ej: Chiclayo Centro" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3.5 text-xs text-white placeholder-slate-500 outline-none focus:border-brand-primary">
                </div>

                <!-- Pricing -->
                <div>
                    <label class="block text-xs uppercase font-extrabold text-slate-400 mb-2">💵 Tarifa de Envío (S/)</label>
                    <input type="number" name="price" id="modal-price" required min="0" step="0.10" placeholder="Ej: 5.00" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3.5 text-xs text-white placeholder-slate-500 outline-none focus:border-brand-primary font-bold">
                </div>

                <!-- Active Checkbox -->
                <div class="flex items-center gap-3 py-2 bg-slate-800/40 border border-slate-700/60 rounded-xl px-4">
                    <input type="checkbox" name="is_active" id="modal-active" value="1" checked class="w-4 h-4 rounded text-brand-secondary focus:ring-brand-primary focus:ring-offset-slate-900 bg-slate-900 border-slate-700">
                    <div>
                        <label for="modal-active" class="block text-xs font-bold text-white cursor-pointer">Activo y Disponible</label>
                        <span class="text-[10px] text-slate-400">Si se desmarca, los clientes del e-commerce no podrán elegir esta zona.</span>
                    </div>
                </div>

                <div class="border-t border-slate-700 my-4 pt-4 flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 py-3.5 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 py-3.5 bg-brand-secondary text-brand-dark font-extrabold rounded-xl text-xs hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20 cursor-pointer">
                        GUARDAR ZONA
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Drawing Map Panel -->
        <div class="w-full md:w-7/12 p-8 flex flex-col justify-between space-y-4">
            <div class="flex justify-between items-center">
                <label class="block text-xs uppercase font-extrabold text-slate-400">
                    ✏️ Delineado de Geocerca
                </label>
                <div class="flex gap-2">
                    <button type="button" id="btn-start-draw" onclick="startDrawing()" class="px-3 py-1.5 rounded-lg bg-brand-primary hover:bg-brand-primary/80 text-white text-[10px] font-extrabold shadow-sm transition-all flex items-center gap-1 cursor-pointer">
                        ✏️ Delinear Área
                    </button>
                    <button type="button" id="btn-clear-draw" onclick="clearDrawing()" class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-[10px] font-extrabold shadow-sm transition-all flex items-center gap-1 cursor-pointer">
                        🗑️ Limpiar Dibujo
                    </button>
                </div>
            </div>
            
            <div id="modal-map"></div>
            
            <p class="text-[10px] text-slate-400 italic">
                * Haz clic en el botón "Delinear Área" y luego haz clics sucesivos en el mapa para trazar el contorno de tu zona de reparto. Haz clic en el primer punto para cerrar el polígono.
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    // Coordinates mapping for Physical Headquarters
    const hqCoordinates = {
        1: [-12.0945, -77.0341], // Sede Central Lima
        2: [-6.77137, -79.84411]  // Sede Chiclayo
    };

    const zonesData = @json($zones);

    // Maps variables
    let previewMap = null;
    let modalMap = null;
    let activeDrawingPolygon = null;
    let drawingVertices = [];
    let drawingMarkers = [];
    let isDrawingActive = false;
    let activePolygonLayer = null;

    window.addEventListener('DOMContentLoaded', () => {
        initPreviewMap();
    });

    // 1. Initialize Preview Map to show all active custom zones
    function initPreviewMap() {
        const defaultCenter = hqCoordinates[2] || [-6.77137, -79.84411]; // default Chiclayo
        
        previewMap = L.map('preview-map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView(defaultCenter, 12);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 20
        }).addTo(previewMap);

        // Draw all saved polygon geocercas
        zonesData.forEach(zone => {
            if (zone.coordinates) {
                try {
                    const coords = JSON.parse(zone.coordinates);
                    if (coords && coords.length > 2) {
                        const polygon = L.polygon(coords, {
                            color: zone.headquarter_id === 1 ? '#E9A171' : '#3D2B1F',
                            fillColor: zone.headquarter_id === 1 ? '#E9A171' : '#3D2B1F',
                            fillOpacity: 0.25,
                            weight: 2
                        }).addTo(previewMap);

                        polygon.bindPopup(`
                            <b class="text-brand-secondary">${zone.name}</b><br>
                            Sede: ${zone.headquarter.name}<br>
                            Costo de Envío: <b>S/ ${parseFloat(zone.price).toFixed(2)}</b>
                        `);
                    }
                } catch (e) {
                    console.error('Error drawing preview polygon:', e);
                }
            }
        });
    }

    // 2. Initialize and Open Modal Map
    function initModalMap() {
        if (modalMap) {
            modalMap.remove();
        }

        const hqId = document.getElementById('modal-hq-id').value;
        const center = hqCoordinates[hqId] || [-6.77137, -79.84411];

        modalMap = L.map('modal-map', {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView(center, 14);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            maxZoom: 20
        }).addTo(modalMap);

        // Handle clicks on map when drawing
        modalMap.on('click', handleMapClick);
    }

    function onModalHqChange(hqId) {
        if (modalMap && hqCoordinates[hqId]) {
            modalMap.setView(hqCoordinates[hqId], 14);
        }
        clearDrawing();
    }

    function startDrawing() {
        clearDrawing();
        isDrawingActive = true;
        document.getElementById('btn-start-draw').classList.add('bg-emerald-600');
        document.getElementById('btn-start-draw').textContent = "✏️ Delineando...";
    }

    function clearDrawing() {
        isDrawingActive = false;
        drawingVertices = [];
        
        // Remove markers
        drawingMarkers.forEach(m => modalMap.removeLayer(m));
        drawingMarkers = [];

        // Remove active line/polygon
        if (activeDrawingPolygon) {
            modalMap.removeLayer(activeDrawingPolygon);
            activeDrawingPolygon = null;
        }

        if (activePolygonLayer) {
            modalMap.removeLayer(activePolygonLayer);
            activePolygonLayer = null;
        }

        document.getElementById('modal-coordinates').value = '';
        document.getElementById('btn-start-draw').classList.remove('bg-emerald-600');
        document.getElementById('btn-start-draw').textContent = "✏️ Delinear Área";
    }

    function handleMapClick(e) {
        if (!isDrawingActive) return;

        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        const point = [lat, lng];

        drawingVertices.push(point);

        // Customize marker style
        const marker = L.circleMarker(e.latlng, {
            radius: 6,
            fillColor: '#E9A171',
            color: '#3D2B1F',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.8
        }).addTo(modalMap);

        // If clicking the first point, close polygon
        if (drawingVertices.length > 2) {
            marker.on('click', () => {
                closePolygonDrawing();
            });
        }

        drawingMarkers.push(marker);

        // Redraw polyline/polygon in real time
        if (activeDrawingPolygon) {
            modalMap.removeLayer(activeDrawingPolygon);
        }

        activeDrawingPolygon = L.polyline(drawingVertices, {
            color: '#E9A171',
            weight: 3
        }).addTo(modalMap);
    }

    function closePolygonDrawing() {
        if (drawingVertices.length < 3) return;

        isDrawingActive = false;
        document.getElementById('btn-start-draw').classList.remove('bg-emerald-600');
        document.getElementById('btn-start-draw').textContent = "✏️ Delinear Área";

        // Convert line to final polygon
        if (activeDrawingPolygon) {
            modalMap.removeLayer(activeDrawingPolygon);
        }

        activePolygonLayer = L.polygon(drawingVertices, {
            color: '#E9A171',
            fillColor: '#E9A171',
            fillOpacity: 0.3,
            weight: 3
        }).addTo(modalMap);

        // Save coordinate JSON data
        document.getElementById('modal-coordinates').value = JSON.stringify(drawingVertices);
        alert('¡Geocerca delineada con éxito! Ya puedes guardar la zona.');
    }

    // Modal view triggers
    function openCreateModal() {
        document.getElementById('modal-title').textContent = "Dibuja Nueva Geocerca";
        
        const form = document.getElementById('modal-form');
        form.action = "{{ route('admin.delivery_zones.store') }}";
        document.getElementById('form-method').value = "POST";
        
        // Reset inputs
        document.getElementById('modal-name').value = '';
        document.getElementById('modal-price').value = '';
        document.getElementById('modal-active').checked = true;
        document.getElementById('modal-coordinates').value = '';
        
        // Open modal
        const modal = document.getElementById('zone-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Render map
        setTimeout(() => {
            initModalMap();
        }, 150);
    }

    function openEditModal(zone) {
        document.getElementById('modal-title').textContent = "Editar Geocerca";
        
        const form = document.getElementById('modal-form');
        form.action = `/intranet/delivery-zones/${zone.id}`;
        document.getElementById('form-method').value = "PATCH";
        
        // Load inputs
        document.getElementById('modal-hq-id').value = zone.headquarter_id;
        document.getElementById('modal-name').value = zone.name;
        document.getElementById('modal-price').value = parseFloat(zone.price).toFixed(2);
        document.getElementById('modal-active').checked = !!zone.is_active;
        document.getElementById('modal-coordinates').value = zone.coordinates || '';
        
        // Open modal
        const modal = document.getElementById('zone-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Render map and draw existing polygon
        setTimeout(() => {
            initModalMap();
            if (zone.coordinates) {
                try {
                    const coords = JSON.parse(zone.coordinates);
                    if (coords && coords.length > 2) {
                        activePolygonLayer = L.polygon(coords, {
                            color: '#E9A171',
                            fillColor: '#E9A171',
                            fillOpacity: 0.35,
                            weight: 3
                        }).addTo(modalMap);
                        modalMap.fitBounds(activePolygonLayer.getBounds());
                    }
                } catch(e) {
                    console.error('Error rendering edited polygon:', e);
                }
            }
        }, 150);
    }

    function closeModal() {
        const modal = document.getElementById('zone-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        clearDrawing();
    }
</script>
@endsection
