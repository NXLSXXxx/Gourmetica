@extends('layouts.app')

@section('title', 'Checkout | Gourmetica')

@push('styles')
<style>
    body {
        background-color: #E5E5E5; /* Light grey background similar to image */
    }
    #map {
        height: 220px;
        width: 100%;
        z-index: 10;
        border-radius: 12px;
        margin-top: 10px;
    }
    .suggestion-item {
        padding: 12px 16px;
        font-size: 13px;
        cursor: pointer;
        border-bottom: 1px solid #F3F4F6;
    }
    .suggestion-item:hover { background-color: #F9FAFB; }

    /* Custom Switch Toggle */
    .toggle-checkbox:checked {
        right: 0;
        border-color: #111827; 
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #111827;
    }

    /* Modal animations */
    .modal-enter {
        opacity: 0;
        transform: scale(0.95);
        transition: all 0.2s ease-out;
    }
    .modal-enter-active {
        opacity: 1;
        transform: scale(1);
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
@php
    $cartTotal = 0;
    foreach(session('cart', []) as $item) {
        $cartTotal += $item['price'] * $item['quantity'];
    }
@endphp

<!-- Modals Overlay Container -->
<div id="modal-overlay" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4 overflow-y-auto">
    
    <!-- Modal 1: Forma de Compra (Location) -->
    <div id="modal-location" class="modal-box hidden bg-white w-full max-w-xl rounded-3xl shadow-2xl relative flex flex-col max-h-[90vh]">
        <div class="flex justify-between items-center p-5 border-b border-gray-100 shrink-0">
            <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-900"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg></button>
            <h3 class="font-bold text-gray-900 text-lg">Selecciona tu forma de compra</h3>
            <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-900 bg-gray-100 rounded-full p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        
        <div class="p-6 overflow-y-auto no-scrollbar flex-1">
            <!-- Tabs -->
            <div class="flex gap-2 mb-6 justify-center max-w-sm mx-auto">
                <button type="button" id="tab-pickup" onclick="setShippingType('pickup')" class="flex-1 py-2.5 text-center rounded-full font-bold border-2 border-black bg-white text-black text-sm transition-all shadow-sm">Recojo en tienda</button>
                <button type="button" id="tab-delivery" onclick="setShippingType('delivery')" class="flex-1 py-2.5 text-center rounded-full font-bold border-2 border-black bg-black text-white text-sm transition-all shadow-sm">Delivery</button>
            </div>

            <!-- Content Pickup -->
            <div id="modal-pickup-content" class="space-y-4">
                <p class="text-center text-xs font-bold text-gray-900 mb-4">Selecciona el local para recoger</p>
                @foreach($headquarters as $hq)
                <label class="flex items-start p-4 border border-gray-200 rounded-2xl cursor-pointer hover:bg-gray-50 [&:has(:checked)]:border-black [&:has(:checked)]:bg-gray-50">
                    <input type="radio" name="modal_pickup_hq" value="{{ $hq->id }}" class="mt-0.5 text-black focus:ring-black" {{ $loop->first ? 'checked' : '' }} data-name="{{ $hq->name }}" data-address="{{ $hq->address }}">
                    <div class="ml-3">
                        <span class="block font-bold text-sm text-gray-900">{{ $hq->name }}</span>
                        <span class="block text-xs text-gray-500 mt-1">{{ $hq->address }}</span>
                    </div>
                </label>
                @endforeach
            </div>

            <!-- Content Delivery -->
            <div id="modal-delivery-content" class="hidden space-y-4">
                <p class="text-center text-xs font-bold text-gray-900 mb-4">Establece una dirección de envío</p>
                <div class="relative">
                    <input type="text" id="address-search-input" placeholder="Escribe la dirección de entrega" class="w-full border-gray-200 rounded-xl text-sm pl-10 focus:ring-black focus:border-black bg-gray-50">
                    <div class="absolute left-3 top-3"><svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path></svg></div>
                    <div id="suggestions-dropdown" class="search-suggestions hidden bg-white absolute w-full border border-gray-200 shadow-lg rounded-xl mt-1 z-50"></div>
                </div>

                <div class="mt-6 border border-gray-100 rounded-2xl p-4 bg-gray-50 shadow-inner">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-bold text-sm text-gray-900">Detalle de la dirección actual</h4>
                        <button type="button" class="text-gray-500" onclick="detectMyLocation()"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg></button>
                    </div>
                    <p id="modal-address-preview" class="text-xs text-gray-600 mb-3">Dirección no seleccionada</p>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="text" id="modal_delivery_reference" placeholder="Piso/Oficina/Dpto" class="w-full border-gray-200 rounded-lg text-xs bg-white">
                        <input type="text" id="modal_delivery_city" placeholder="Referencia de la dirección" class="w-full border-gray-200 rounded-lg text-xs bg-white">
                    </div>
                    <div class="mt-3 border border-gray-200 rounded-xl overflow-hidden p-1 bg-white">
                        <div id="map"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-5 border-t border-gray-100 shrink-0">
            <button type="button" onclick="saveLocationModal()" class="w-full bg-[#E50000] hover:bg-red-700 text-white font-bold py-3 rounded-full text-sm shadow-md transition-all">Confirmar Ubicación</button>
        </div>
    </div>

    <!-- Modal 2: Teléfono -->
    <div id="modal-phone" class="modal-box hidden bg-white w-full max-w-md rounded-3xl shadow-2xl relative flex flex-col">
        <div class="flex justify-between items-center p-5 border-b border-gray-100">
            <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-900"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg></button>
            <h3 class="font-bold text-gray-900 text-lg">Editar número de teléfono</h3>
            <div class="w-6"></div> <!-- Spacer for center alignment -->
        </div>
        <div class="p-6">
            <div class="flex gap-3">
                <div class="w-1/3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">País</label>
                    <select class="w-full border-gray-200 rounded-xl text-sm bg-gray-50 focus:ring-black">
                        <option>+51 (PE)</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Número de teléfono</label>
                    <input type="tel" id="modal-input-phone" placeholder="987654321" class="w-full border-gray-200 rounded-xl text-sm bg-gray-50 focus:ring-black">
                </div>
            </div>
        </div>
        <div class="p-5 border-t border-gray-100">
            <button type="button" onclick="savePhoneModal()" class="w-full bg-[#E50000] hover:bg-red-700 text-white font-bold py-3 rounded-full text-sm shadow-md transition-all">Guardar</button>
        </div>
    </div>

    <!-- Modal 3: Fecha -->
    <div id="modal-date" class="modal-box hidden bg-white w-full max-w-md rounded-3xl shadow-2xl relative flex flex-col">
        <div class="flex items-center p-5 border-b border-gray-100">
            <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-900 mr-4"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg></button>
            <div>
                <h3 class="font-bold text-gray-900 text-lg">Programa tu entrega</h3>
                <p class="text-xs text-gray-500">Selecciona fecha y horario según disponibilidad.</p>
            </div>
        </div>
        <div class="p-6">
            <div class="flex gap-2">
                <div class="w-1/2">
                    <select id="modal-input-date" class="w-full border-gray-200 rounded-xl text-sm bg-gray-50 font-bold focus:ring-black">
                        <option value="Hoy">Hoy</option>
                        <option value="Mañana">Mañana</option>
                    </select>
                </div>
                <div class="w-1/2">
                    <select id="modal-input-time" class="w-full border-gray-200 rounded-xl text-sm bg-gray-50 font-bold focus:ring-black">
                        <option value="Lo antes posible">Lo antes posible</option>
                        <option value="Tarde (2:00pm - 6:00pm)">Tarde (2:00pm - 6:00pm)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="p-5 border-t border-gray-100">
            <button type="button" onclick="saveDateModal()" class="w-full bg-[#E50000] hover:bg-red-700 text-white font-bold py-3 rounded-full text-sm shadow-md transition-all">Actualizar</button>
        </div>
    </div>

    <!-- Modal 4: Facturación -->
    <div id="modal-billing" class="modal-box hidden bg-white w-full max-w-lg rounded-3xl shadow-2xl relative flex flex-col">
        <div class="flex items-center p-5 border-b border-gray-100">
            <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-900 mr-4"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg></button>
            <h3 class="font-bold text-gray-900 text-lg">Opciones de facturación</h3>
        </div>
        <div class="p-6">
            <label class="block text-xs font-medium text-gray-500 mb-1">Tipo de comprobante</label>
            <select id="modal-invoice-type" onchange="toggleInvoiceModalFields(this.value)" class="w-full border-gray-200 rounded-xl text-sm mb-4 focus:ring-black">
                <option value="boleta">Boleta con DNI</option>
                <option value="factura">Factura</option>
            </select>

            <!-- Boleta Fields -->
            <div id="modal-fields-boleta" class="space-y-4">
                <div class="flex gap-4">
                    <div class="w-1/3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tipo de documento</label>
                        <select class="w-full border-gray-200 rounded-xl text-sm focus:ring-black"><option>DNI</option><option>CE</option></select>
                    </div>
                    <div class="w-2/3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Documento</label>
                        <input type="text" id="modal-boleta-doc" class="w-full border-gray-200 rounded-xl text-sm focus:ring-black">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Apellido paterno</label>
                        <input type="text" id="modal-boleta-ap" class="w-full border-gray-200 rounded-xl text-sm focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Apellido materno</label>
                        <input type="text" id="modal-boleta-am" class="w-full border-gray-200 rounded-xl text-sm focus:ring-black">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nombres</label>
                        <input type="text" id="modal-boleta-name" class="w-full border-gray-200 rounded-xl text-sm focus:ring-black">
                    </div>
                </div>
            </div>

            <!-- Factura Fields -->
            <div id="modal-fields-factura" class="hidden space-y-4">
                <div class="flex gap-4">
                    <div class="w-1/3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">RUC</label>
                        <input type="text" id="modal-factura-ruc" class="w-full border-gray-200 rounded-xl text-sm focus:ring-black">
                    </div>
                    <div class="w-2/3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Razón social</label>
                        <input type="text" id="modal-factura-rs" class="w-full border-gray-200 rounded-xl text-sm focus:ring-black">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dirección</label>
                    <input type="text" id="modal-factura-dir" class="w-full border-gray-200 rounded-xl text-sm focus:ring-black">
                </div>
            </div>
        </div>
        <div class="p-5 border-t border-gray-100">
            <button type="button" onclick="saveBillingModal()" class="w-full bg-[#E50000] hover:bg-red-700 text-white font-bold py-3 rounded-full text-sm shadow-md transition-all">Actualizar</button>
        </div>
    </div>

    <!-- Modal 5: Fuera de Cobertura -->
    <div id="modal-no-coverage" class="modal-box hidden bg-white w-full max-w-md rounded-3xl shadow-2xl relative flex flex-col p-6 text-center animate-fade">
        <div class="flex justify-end">
            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-900 bg-gray-100 rounded-full p-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
        
        <div class="flex flex-col items-center my-4">
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mb-4 text-[#E50000]">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <h3 class="font-bold text-gray-900 text-xl mb-2">¡Fuera de Cobertura!</h3>
            <p class="text-xs text-gray-500 max-w-xs leading-relaxed">
                Lo sentimos, la dirección elegida está fuera del rango de reparto express para esta sede. ¡Pero no te quedes sin tu pedido! Te sugerimos estas opciones:
            </p>
        </div>
        
        <div class="space-y-3 mt-2">
            <!-- Opción 1: Cambiar a Recojo -->
            <button type="button" onclick="switchToPickup()" class="w-full bg-[#E50000] hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl text-sm transition-all shadow-sm flex items-center justify-center gap-2">
                🛍️ Cambiar a Recojo en Tienda
            </button>
            
            <!-- Opción 2: Intentar otra ubicación -->
            <button type="button" onclick="tryDifferentAddress()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-bold py-3 px-4 rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                📍 Elegir otra ubicación
            </button>
            
            <!-- Opción 3: Coordinar por WhatsApp -->
            <a href="https://wa.me/{{ $contact_whatsapp ?? '950664655' }}?text=Hola,%20mi%20dirección%20está%20fuera%20de%20cobertura%20en%20la%20web%20y%20quisiera%20coordinar%20un%20envío%20personalizado." target="_blank" class="w-full bg-[#25D366] hover:bg-[#128C7E] text-white font-bold py-3 px-4 rounded-xl text-sm transition-all flex items-center justify-center gap-2">
                💬 Coordinar envío por WhatsApp
            </a>
        </div>
    </div>

</div>

<!-- MAIN PAGE -->
<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 py-8" id="checkout-root">
    
    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 flex justify-between items-center">
        <div class="flex items-center gap-4">
            <button class="text-gray-900"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg></button>
            <h1 class="text-2xl font-bold text-gray-900">Checkout</h1>
        </div>
        <!-- Logo placeholder -->
        <h2 class="hidden md:block text-red-600 font-extrabold text-xl tracking-tighter">GOURMETICA</h2>
    </div>

    <form action="{{ route('orders.store') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="culqi_token" id="culqi_token">
        
        <!-- Hidden Inputs for Syncing Modal Data -->
        <input type="hidden" name="shipping_type" id="form_shipping_type" value="pickup">
        <input type="hidden" name="headquarter_id" id="form_pickup_hq" value="{{ session('selected_headquarter_id') ?? $headquarters->first()->id ?? '' }}">
        <input type="hidden" name="latitude" id="form_latitude">
        <input type="hidden" name="longitude" id="form_longitude">
        <input type="hidden" name="address" id="form_delivery_address">
        <input type="hidden" name="phone" id="form_phone">
        <input type="hidden" name="delivery_date" id="form_date">
        <input type="hidden" name="delivery_time" id="form_time">
        <input type="hidden" name="invoice_type" id="form_invoice_type" value="boleta">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Column: Preview Blocks -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Badge: Tiempo de entrega -->
                <div class="bg-gray-100 rounded-t-2xl px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <h2 class="font-bold text-gray-900 text-base">Tiempo de entrega</h2>
                    </div>
                    <span class="text-gray-900 text-sm">Programado</span>
                </div>

                <!-- Block 1: Ubicación / Entrega -->
                <div class="bg-white px-6 py-5 rounded-2xl shadow-sm relative group">
                    <div class="flex gap-4">
                        <div class="mt-1"><svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg></div>
                        <div class="flex-1 pr-16">
                            <p class="text-sm font-bold text-gray-900" id="main-preview-shipping-title">Recojo en Tienda: {{ $headquarters->first()->name ?? 'SELECCIONE' }}</p>
                            <p class="text-xs text-gray-500 mt-1" id="main-preview-shipping-desc">{{ $headquarters->first()->address ?? 'Elige local' }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="openModal('modal-location')" class="absolute top-5 right-6 text-sm font-bold text-red-600 hover:underline">Cambiar</button>
                </div>

                <!-- Block 2: Teléfono -->
                <div class="bg-white px-6 py-5 rounded-2xl shadow-sm relative group">
                    <div class="flex gap-4 items-center">
                        <div><svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path></svg></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900" id="main-preview-phone">Teléfono del comprador</p>
                        </div>
                    </div>
                    <button type="button" onclick="openModal('modal-phone')" class="absolute top-5 right-6 text-sm font-bold text-red-600 hover:underline" id="btn-phone">Agregar</button>
                </div>

                <!-- Block 3: Fecha y Hora -->
                <div class="bg-white px-6 py-5 rounded-2xl shadow-sm relative group">
                    <div class="flex gap-4 items-start">
                        <div class="mt-0.5"><svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Fecha y hora de entrega</p>
                            <p class="text-xs text-gray-500 mt-1" id="main-preview-date">Agrega fecha</p>
                        </div>
                    </div>
                    <button type="button" onclick="openModal('modal-date')" class="absolute top-5 right-6 text-sm font-bold text-red-600 hover:underline">Agregar</button>
                </div>

                <!-- Block 4: Datos Personales (Inline as per image) -->
                <div class="bg-gray-100 px-6 py-5 rounded-2xl mt-4">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                        <h2 class="font-bold text-gray-900 text-base">Datos personales</h2>
                    </div>
                    <div class="space-y-3 pl-7">
                        <div class="flex items-center gap-3">
                            <label class="w-20 text-xs font-medium text-gray-700 text-right">Nombres:</label>
                            <input type="text" name="first_name" value="{{ auth()->user()->name ?? '' }}" class="flex-1 border-gray-300 rounded text-sm bg-transparent border-0 border-b focus:ring-0 focus:border-black p-1" required>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="w-20 text-xs font-medium text-gray-700 text-right">Apellidos:</label>
                            <input type="text" name="last_name" class="flex-1 border-gray-300 rounded text-sm bg-transparent border-0 border-b focus:ring-0 focus:border-black p-1" required>
                        </div>
                        <div class="flex items-center gap-3">
                            <label class="w-20 text-xs font-medium text-gray-700 text-right">Email:</label>
                            <input type="email" name="email" value="{{ auth()->user()->email ?? '' }}" class="flex-1 border-gray-300 rounded text-sm bg-transparent border-0 border-b focus:ring-0 focus:border-black p-1" required>
                        </div>
                    </div>
                </div>

                <!-- Block 5: Facturación -->
                <div class="bg-white px-6 py-5 rounded-2xl shadow-sm relative group mt-2 border border-gray-100">
                    <div class="flex gap-4 items-start">
                        <div class="mt-0.5"><svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Facturación</p>
                            <p class="text-xs text-gray-500 mt-1" id="main-preview-billing">Seleccione un tipo de factura</p>
                        </div>
                    </div>
                    <button type="button" onclick="openModal('modal-billing')" class="absolute top-5 right-6 text-sm font-bold text-red-600 hover:underline">Cambiar</button>
                </div>

                <!-- Block 6: Extras de la torta -->
                <div class="space-y-4 pt-4">
                    <!-- Breve Dedicatoria -->
                    <div class="bg-gray-100 px-6 py-4 rounded-xl flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path></svg>
                            <span class="text-sm font-bold text-gray-900">¿Deseas una breve dedicatoria sobre la torta?</span>
                        </div>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input type="checkbox" name="has_dedicatoria_torta" id="main-toggle-torta" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 border-gray-300 appearance-none cursor-pointer z-10"/>
                            <label for="main-toggle-torta" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                    </div>
                    <div id="main-dedicatoria-torta-box" class="hidden px-6">
                        <input type="text" name="dedicatoria_torta" placeholder="Mensaje..." class="w-full border-gray-200 rounded-xl text-sm focus:ring-black focus:border-black">
                    </div>

                    <!-- Tarjeta -->
                    <div class="bg-gray-100 px-6 py-4 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4zm7 5a1 1 0 10-2 0v1H8a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd"></path></svg>
                                <span class="text-sm font-bold text-gray-900">¿Deseas tarjeta de dedicatoria?</span>
                            </div>
                            <p class="text-[10px] text-gray-500 ml-8" id="text-tarjeta">El pedido se entregará con la tarjeta de dedicatoria.</p>
                        </div>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input type="checkbox" name="has_tarjeta" id="main-toggle-tarjeta" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 border-gray-300 appearance-none cursor-pointer z-10" checked/>
                            <label for="main-toggle-tarjeta" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-900 cursor-pointer"></label>
                        </div>
                    </div>

                    <!-- Es un Regalo -->
                    <div class="bg-gray-100 px-6 py-4 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <svg class="w-5 h-5 text-gray-700" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 5a3 3 0 015-2.236A3 3 0 0114.83 6H16a2 2 0 110 4h-5V9a1 1 0 10-2 0v1H4a2 2 0 110-4h1.17C5.06 5.687 5 5.35 5 5zm4 1V5a1 1 0 10-1 1h1zm3 0a1 1 0 10-1-1v1h1z" clip-rule="evenodd"></path><path d="M9 11H3v5a2 2 0 002 2h4v-7zM11 18h4a2 2 0 002-2v-5h-6v7z"></path></svg>
                                <span class="text-sm font-bold text-gray-900">¿Es un regalo?</span>
                            </div>
                            <p class="text-[10px] text-gray-500 ml-8" id="text-gift">El pedido se entregará sin boleta / factura</p>
                        </div>
                        <div class="relative inline-block w-10 align-middle select-none">
                            <input type="checkbox" name="is_gift" id="main-toggle-gift" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 border-gray-300 appearance-none cursor-pointer z-10"/>
                            <label for="main-toggle-gift" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer"></label>
                        </div>
                    </div>
                </div>

                <!-- Block 7: Información de pago -->
                <div class="pt-6">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <h2 class="font-bold text-gray-900 text-base">Información de pago</h2>
                    </div>
                    
                    <div class="bg-white rounded-2xl p-2 shadow-sm border border-gray-100">
                        <label class="flex items-center p-3 border-b border-gray-50 cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="culqi" class="text-black focus:ring-black" checked>
                            <span class="ml-3 font-medium text-sm text-gray-900 flex-1">Pago con Tarjeta de Crédito / Débito</span>
                        </label>
                        
                        <label class="flex items-center p-3 border-b border-gray-50 cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="yape" class="text-black focus:ring-black">
                            <span class="ml-3 font-medium text-sm text-gray-900 flex-1">Pago con Yape</span>
                        </label>
                        
                        <label class="flex items-center p-3 cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="payment_method" value="plin" class="text-black focus:ring-black">
                            <span class="ml-3 font-medium text-sm text-gray-900 flex-1">Pago con Plin</span>
                        </label>
                    </div>

                    <div class="mt-4 px-2">
                        <p class="text-sm font-bold text-gray-700 hover:text-black cursor-pointer">Tengo un cupón de descuento</p>
                    </div>

                    <div class="space-y-3 mt-6 px-2">
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" class="mt-1 border-gray-300 rounded text-black focus:ring-black" required>
                            <span class="ml-2 text-xs text-gray-600">Acepto los términos y condiciones</span>
                        </label>
                        <label class="flex items-start cursor-pointer">
                            <input type="checkbox" class="mt-1 border-gray-300 rounded text-black focus:ring-black">
                            <span class="ml-2 text-xs text-gray-600">Quisiera recibir promociones y descuentos</span>
                        </label>
                    </div>

                    <button type="submit" id="submit-order-btn" class="w-full bg-[#E50000] hover:bg-red-700 text-white font-bold py-4 rounded-full shadow-lg transition-all text-center mt-6">
                        Finalizar pedido <span id="btn-total">S/ {{ number_format($cartTotal, 2) }}</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Sidebar (Detalle del pedido) -->
            <div class="lg:col-span-5">
                <div class="bg-gray-100 p-6 md:p-8 rounded-3xl sticky top-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-6">Detalle del pedido</h3>
                    
                    <div class="space-y-6 mb-6">
                        @forelse(session('cart', []) as $item)
                            <div class="flex gap-4 items-center">
                                <div class="w-16 h-16 bg-white rounded-lg flex-shrink-0 border border-gray-200 overflow-hidden">
                                    <img src="{{ $item['image_url'] ?? 'https://placehold.co/150x150' }}" alt="Img" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-sm text-gray-900 pr-2">{{ $item['name'] }}</h4>
                                        <span class="font-bold text-sm text-red-600 whitespace-nowrap">S/ {{ number_format($item['price'], 2) }}</span>
                                    </div>
                                    <p class="text-[10px] text-gray-500 mt-1 line-clamp-2">Torta rellena con ingredientes premium. Rinde aprox porciones.</p>
                                    <div class="mt-2 text-xs font-bold text-gray-500">Cantidad: {{ $item['quantity'] }}</div>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400 text-sm text-center">Tu carrito está vacío</p>
                        @endforelse
                    </div>

                    <div class="space-y-2 pt-4 border-t border-gray-200">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="text-gray-900 font-bold" id="summary-subtotal">S/ {{ number_format($cartTotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Envío:</span>
                            <span class="text-gray-900 font-bold" id="summary-delivery">S/ 0.00</span>
                        </div>
                        <div class="flex justify-between items-end pt-3 mt-3 border-t border-gray-300">
                            <span class="text-base font-bold text-gray-900">Total:</span>
                            <span class="text-2xl font-bold text-gray-900" id="summary-total">S/ {{ number_format($cartTotal, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://checkout.culqi.com/js/v4"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXo-Lpvk7_PQ6KnL4XjxA5ux1pHsopYTk&libraries=places&callback=initGoogleMap&loading=async" async defer></script>
<script>
    const cartTotal = parseFloat("{{ $cartTotal }}");
    let selectedShippingType = 'pickup';
    let selectedDeliveryPrice = 0;
    
    let map = null;
    let marker = null;
    let autocomplete = null;
    let geocoder = null;
    let currentModal = null;

    // Callback called when Google Maps script finishes loading
    async function initGoogleMap() {
        const input = document.getElementById('address-search-input');
        if (!input) return;

        const { PlaceAutocompleteElement } = await google.maps.importLibrary("places");

        autocomplete = new PlaceAutocompleteElement({
            componentRestrictions: { country: ['pe'] }
        });
        
        autocomplete.id = 'address-search-input';
        
        // Match original styling as closely as possible
        const style = document.createElement('style');
        style.innerHTML = `
            gmp-place-autocomplete {
                width: 100%;
                color-scheme: light;
                --gmp-color-background: #f9fafb;
                --gmp-color-surface: #ffffff;
                --gmp-color-on-background: #111827;
                --gmp-color-on-surface: #111827;
                --gmp-color-primary: #E50000;
                --gmp-radius-container: 0.75rem;
                --gmp-border-color: #e5e7eb;
                box-shadow: none;
            }
            gmp-place-autocomplete::part(container) {
                border: 1px solid #e5e7eb;
                border-radius: 0.75rem;
                padding-left: 2rem;
            }
            gmp-place-autocomplete::part(input) {
                font-size: 0.875rem;
            }
        `;
        document.head.appendChild(style);

        input.parentNode.replaceChild(autocomplete, input);

        autocomplete.addEventListener('gmp-placeselect', async (event) => {
            console.log("1. gmp-placeselect disparado", event);
            const place = event.place;
            if (!place) {
                console.error("2. Error: event.place es undefined o nulo");
                return;
            }
            
            try {
                console.log("3. Intentando fetchFields para el lugar:", place.id);
                await place.fetchFields({ fields: ['location', 'formattedAddress'] });
                console.log("4. fetchFields exitoso. Datos:", place);
                
                if (place.location) {
                    const lat = place.location.lat();
                    const lng = place.location.lng();
                    const address = place.formattedAddress || autocomplete.value || autocomplete.inputValue || 'Dirección seleccionada';

                    document.getElementById('form_latitude').value = lat;
                    document.getElementById('form_longitude').value = lng;
                    document.getElementById('modal-address-preview').innerText = address;

                    if (map && marker) {
                        const pos = { lat: lat, lng: lng };
                        map.setCenter(pos);
                        map.setZoom(16);
                        marker.position = pos;
                    }
                    return;
                } else {
                    console.error("5. fetchFields funcionó pero place.location está vacío.");
                }
            } catch (error) {
                console.error("X. Error crítico en fetchFields (Probablemente falte habilitar 'Places API (New)' en Google Cloud):", error);
            }
            
            // Fallback using Geocoder if fetchFields is restricted or fails
            if (geocoder) {
                const request = place.id ? { placeId: place.id } : { address: (autocomplete.value || autocomplete.inputValue) + ', Perú' };
                console.log("6. Ejecutando Geocoder de respaldo con request:", request);
                
                geocoder.geocode(request, (results, status) => {
                    console.log("7. Respuesta de Geocoder de respaldo:", status, results);
                    
                    if (status === 'OK' && results[0]) {
                        const lat = results[0].geometry.location.lat();
                        const lng = results[0].geometry.location.lng();
                        const address = results[0].formatted_address;

                        document.getElementById('form_latitude').value = lat;
                        document.getElementById('form_longitude').value = lng;
                        document.getElementById('modal-address-preview').innerText = address;

                        if (map && marker) {
                            const pos = { lat: lat, lng: lng };
                            map.setCenter(pos);
                            map.setZoom(16);
                            marker.position = pos;
                        }
                    } else {
                        console.error("8. Geocoder falló. Status:", status);
                        alert("No pudimos obtener la ubicación exacta. Por favor, intenta de nuevo.");
                    }
                });
            } else {
                console.error("9. Geocoder de respaldo falló porque geocoder no está inicializado.");
            }
        });

        geocoder = new google.maps.Geocoder();
        console.log("0. Google Maps inicializado correctamente.");
    }

    // Initialize Map explicitly inside modal when opened
    async function initMapOnce() {
        if (!map && typeof google !== 'undefined') {
            const chiclayo = { lat: -6.7719, lng: -79.8441 };
            
            const { Map } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
            
            map = new Map(document.getElementById('map'), {
                center: chiclayo,
                zoom: 15,
                disableDefaultUI: false,
                mapId: "DEMO_MAP_ID"
            });

            marker = new AdvancedMarkerElement({
                position: chiclayo,
                map: map,
                gmpDraggable: true
            });

            marker.addListener('dragend', () => {
                const pos = marker.position;
                const lat = pos.lat;
                const lng = pos.lng;
                document.getElementById('form_latitude').value = lat;
                document.getElementById('form_longitude').value = lng;
                document.getElementById('modal-address-preview').innerText = "Lat: " + lat.toFixed(4) + ", Lng: " + lng.toFixed(4);
                
                reverseGeocode(lat, lng);
            });
        }
    }

    // Reverse geocoding using Google Geocoder
    function reverseGeocode(lat, lng) {
        if (!geocoder) return;
        geocoder.geocode({ location: { lat: parseFloat(lat), lng: parseFloat(lng) } }, (results, status) => {
            if (status === 'OK' && results[0]) {
                let addr = results[0].formatted_address;
                addr = addr.replace(', Peru', '').replace(', Perú', '');
                const input = document.getElementById('address-search-input');
                if(input) {
                    if('inputValue' in input) input.inputValue = addr;
                    else input.value = addr;
                }
            }
        });
    }

    // Geocode manual address search
    function geocodeAddress(address) {
        if (!geocoder) {
            console.error("geocodeAddress manual falló porque geocoder es nulo.");
            return;
        }
        
        const confirmBtn = document.querySelector('#modal-location button[onclick="saveLocationModal()"]');
        const originalText = confirmBtn.innerText;
        confirmBtn.disabled = true;
        confirmBtn.innerText = "Buscando dirección...";

        console.log("A. Buscando dirección manual con Geocoder:", address + ', Chiclayo, Perú');
        geocoder.geocode({ address: address + ', Chiclayo, Perú' }, (results, status) => {
            console.log("B. Respuesta manual de Geocoder:", status, results);
            
            confirmBtn.disabled = false;
            confirmBtn.innerText = originalText;
            
            try {
                if (status === 'OK' && results[0]) {
                    const lat = typeof results[0].geometry.location.lat === 'function' ? results[0].geometry.location.lat() : results[0].geometry.location.lat;
                    const lng = typeof results[0].geometry.location.lng === 'function' ? results[0].geometry.location.lng() : results[0].geometry.location.lng;
                    
                    document.getElementById('form_latitude').value = lat;
                    document.getElementById('form_longitude').value = lng;
                    document.getElementById('modal-address-preview').innerText = results[0].formatted_address;
                    
                    if (map && marker) {
                        const pos = { lat: lat, lng: lng };
                        map.setCenter(pos);
                        map.setZoom(16);
                        if (typeof marker.setPosition === 'function') marker.setPosition(pos);
                        else marker.position = pos;
                    }
                    
                    console.log("C. Todo parseado bien. Ejecutando saveLocationModal con lat/lng:", lat, lng);
                    // Llama de nuevo a saveLocationModal, ahora que lat y lng existen.
                    saveLocationModal();
                } else {
                    console.error("C. Geocoder manual falló. Status:", status);
                    alert("No pudimos encontrar la dirección exacta. Por favor, selecciona una opción de la lista o mueve el marcador en el mapa.");
                }
            } catch (error) {
                console.error("D. Error crítico procesando la respuesta del Geocoder:", error);
                alert("Ocurrió un error al procesar tu dirección.");
            }
        });
    }

    // Geolocation
    function detectMyLocation() {
        if (navigator.geolocation) {
            const btn = document.querySelector('#modal-delivery-content button[onclick="detectMyLocation()"]');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<span style="font-size:0.75rem; color:var(--primary);">Buscando...</span>';
            
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('form_latitude').value = lat;
                document.getElementById('form_longitude').value = lng;
                document.getElementById('modal-address-preview').innerText = "Lat: " + lat.toFixed(4) + ", Lng: " + lng.toFixed(4);

                const pos = { lat: lat, lng: lng };
                if (map && marker) {
                    map.setCenter(pos);
                    map.setZoom(16);
                    marker.position = pos;
                }

                reverseGeocode(lat, lng);
                btn.innerHTML = originalHTML;
            }, function(error) {
                alert("No se pudo obtener la ubicación: " + error.message);
                btn.innerHTML = originalHTML;
            });
        } else {
            alert("La geolocalización no está soportada por este navegador.");
        }
    }

    // Fetch delivery pricing from Gourmetica backend (which asks Nakama Delivery API)
    function fetchDeliveryPrice(lat, lng, callback) {
        const confirmBtn = document.querySelector('#modal-location button[onclick="saveLocationModal()"]');
        const originalText = confirmBtn.innerText;
        confirmBtn.disabled = true;
        confirmBtn.innerText = "Calculando costo de envío...";

        const hqId = document.getElementById('form_pickup_hq').value;

        fetch(`/checkout/calculate-delivery?latitude=${lat}&longitude=${lng}&headquarter_id=${hqId}`)
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.message || 'Error al calcular precio'); });
                }
                return res.json();
            })
            .then(data => {
                console.log("F. Respuesta de fetchDeliveryPrice parseada:", data);
                if (data.success) {
                    if (data.fuera_de_chiclayo) {
                        console.warn("G. El backend reporta que la ubicación está fuera de chiclayo (o fuera de la cobertura).");
                        selectedDeliveryPrice = 0;
                        closeModal();
                        setTimeout(() => openModal('modal-no-coverage'), 300);
                        callback(false);
                    } else {
                        console.log("G. Cobertura aceptada. Precio:", data.price);
                        selectedDeliveryPrice = parseFloat(data.price);
                        callback(true);
                    }
                } else {
                    console.error("H. El backend devolvió success: false. Mensaje:", data.message);
                    closeModal();
                    setTimeout(() => openModal('modal-no-coverage'), 300);
                    callback(false);
                }
            })
            .catch(err => {
                closeModal();
                setTimeout(() => openModal('modal-no-coverage'), 300);
                callback(false);
            })
            .finally(() => {
                confirmBtn.disabled = false;
                confirmBtn.innerText = originalText;
            });
    }

    // Modal Handling
    const overlay = document.getElementById('modal-overlay');
    
    function openModal(modalId) {
        currentModal = document.getElementById(modalId);
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        
        document.querySelectorAll('.modal-box').forEach(m => m.classList.add('hidden'));
        currentModal.classList.remove('hidden');
        currentModal.classList.add('modal-enter');
        
        requestAnimationFrame(() => {
            currentModal.classList.add('modal-enter-active');
        });
 
        if(modalId === 'modal-location' && selectedShippingType === 'delivery') {
            initMapOnce();
        }
    }
 
    function closeModal() {
        if(!currentModal) return;
        currentModal.classList.remove('modal-enter-active');
        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
            currentModal.classList.add('hidden');
            currentModal = null;
        }, 200);
    }
 
    // Location Modal Logic
    function setShippingType(type) {
        selectedShippingType = type;
        const btnPickup = document.getElementById('tab-pickup');
        const btnDelivery = document.getElementById('tab-delivery');
        
        if (type === 'pickup') {
            btnPickup.className = "flex-1 py-2.5 text-center rounded-full font-bold border-2 border-black bg-black text-white text-sm shadow-sm transition-all";
            btnDelivery.className = "flex-1 py-2.5 text-center rounded-full font-bold border-2 border-black bg-white text-black text-sm shadow-sm transition-all";
            document.getElementById('modal-pickup-content').classList.remove('hidden');
            document.getElementById('modal-delivery-content').classList.add('hidden');
        } else {
            btnDelivery.className = "flex-1 py-2.5 text-center rounded-full font-bold border-2 border-black bg-black text-white text-sm shadow-sm transition-all";
            btnPickup.className = "flex-1 py-2.5 text-center rounded-full font-bold border-2 border-black bg-white text-black text-sm shadow-sm transition-all";
            document.getElementById('modal-pickup-content').classList.add('hidden');
            document.getElementById('modal-delivery-content').classList.remove('hidden');
            initMapOnce();
        }
    }
 
    function saveLocationModal() {
        document.getElementById('form_shipping_type').value = selectedShippingType;
        if(selectedShippingType === 'pickup') {
            const el = document.querySelector('input[name="modal_pickup_hq"]:checked');
            if(el) {
                document.getElementById('form_pickup_hq').value = el.value;
                document.getElementById('main-preview-shipping-title').innerText = `Recojo en Tienda: ${el.getAttribute('data-name')}`;
                document.getElementById('main-preview-shipping-desc').innerText = el.getAttribute('data-address');
            }
            selectedDeliveryPrice = 0;
            updatePrices();
            closeModal();
        } else {
            const lat = document.getElementById('form_latitude').value;
            const lng = document.getElementById('form_longitude').value;
            if(!lat || !lng) {
                const addrElement = document.getElementById('address-search-input');
                const addrText = (addrElement && addrElement.value) ? addrElement.value : ((addrElement && addrElement.inputValue) ? addrElement.inputValue : '');
                
                if (addrText.trim().length > 0) {
                    geocodeAddress(addrText);
                } else {
                    alert("Por favor, selecciona una ubicación en el mapa.");
                }
                return;
            }

            const addrElement = document.getElementById('address-search-input');
            const addr = (addrElement && addrElement.value) ? addrElement.value : ((addrElement && addrElement.inputValue) ? addrElement.inputValue : 'Dirección seleccionada en mapa');
            
            fetchDeliveryPrice(lat, lng, function(success) {
                if (success) {
                    document.getElementById('form_delivery_address').value = addr;
                    document.getElementById('main-preview-shipping-title').innerText = `Delivery`;
                    document.getElementById('main-preview-shipping-desc').innerText = addr + ` (Envío: S/ ${selectedDeliveryPrice.toFixed(2)})`;
                    updatePrices();
                    closeModal();
                }
            });
        }
    }

    // Phone Modal Logic
    function savePhoneModal() {
        const val = document.getElementById('modal-input-phone').value;
        if(val) {
            document.getElementById('form_phone').value = val;
            document.getElementById('main-preview-phone').innerText = val;
            document.getElementById('btn-phone').innerText = "Cambiar";
        }
        closeModal();
    }

    // Date Modal Logic
    function saveDateModal() {
        const date = document.getElementById('modal-input-date').value;
        const time = document.getElementById('modal-input-time').value;
        document.getElementById('form_date').value = date;
        document.getElementById('form_time').value = time;
        document.getElementById('main-preview-date').innerText = `${date} - ${time}`;
        closeModal();
    }

    // Billing Modal Logic
    function toggleInvoiceModalFields(type) {
        document.getElementById('modal-fields-boleta').style.display = type === 'boleta' ? 'block' : 'none';
        document.getElementById('modal-fields-factura').style.display = type === 'factura' ? 'block' : 'none';
    }

    function saveBillingModal() {
        const type = document.getElementById('modal-invoice-type').value;
        document.getElementById('form_invoice_type').value = type;
        if(type === 'boleta') {
            const doc = document.getElementById('modal-boleta-doc').value;
            document.getElementById('main-preview-billing').innerText = `Boleta - Doc: ${doc || 'Pendiente'}`;
        } else {
            const ruc = document.getElementById('modal-factura-ruc').value;
            document.getElementById('main-preview-billing').innerText = `Factura - RUC: ${ruc || 'Pendiente'}`;
        }
        closeModal();
    }

    function updatePrices() {
        const finalTotal = cartTotal + selectedDeliveryPrice;
        document.getElementById('summary-delivery').innerText = selectedDeliveryPrice === 0 ? 'S/ 0.00' : `S/ ${selectedDeliveryPrice.toFixed(2)}`;
        document.getElementById('summary-total').innerText = `S/ ${finalTotal.toFixed(2)}`;
        document.getElementById('btn-total').innerText = `S/ ${finalTotal.toFixed(2)}`;
    }

    // Extra switches
    document.getElementById('main-toggle-torta').addEventListener('change', function() {
        document.getElementById('main-dedicatoria-torta-box').style.display = this.checked ? 'block' : 'none';
    });

    document.getElementById('main-toggle-tarjeta').addEventListener('change', function() {
        document.getElementById('text-tarjeta').innerText = this.checked ? "El pedido se entregará con la tarjeta de dedicatoria." : "No se incluirá tarjeta física.";
    });

    document.getElementById('main-toggle-gift').addEventListener('change', function() {
        document.getElementById('text-gift').innerText = this.checked ? "El pedido se entregará sin boleta / factura (formato regalo)." : "El pedido se entregará con boleta / factura normal.";
    });

    // Culqi logic
    Culqi.publicKey = '{{ $culqiPublicKey ?? "pk_test_sample" }}';
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        if (method === 'culqi' && !document.getElementById('culqi_token').value) {
            e.preventDefault();
            Culqi.settings({
                title: 'Gourmetica',
                currency: 'PEN',
                description: 'Pedido',
                amount: Math.round((cartTotal + selectedDeliveryPrice) * 100)
            });
            Culqi.open();
        }
    });

    function culqi() {
        if (Culqi.token) {
            document.getElementById('culqi_token').value = Culqi.token.id;
            document.getElementById('checkout-form').submit();
        } else {
            alert(Culqi.error.user_message);
        }
    }

    // No Coverage Modal Actions
    function switchToPickup() {
        closeModal();
        setShippingType('pickup');
        saveLocationModal();
    }

    function tryDifferentAddress() {
        closeModal();
        setTimeout(() => {
            openModal('modal-location');
        }, 300);
    }
</script>
@endpush
