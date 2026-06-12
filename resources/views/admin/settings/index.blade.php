@extends('layouts.intranet')

@section('title', 'Configuración de Sistema | Gourmetica Intranet')

@section('content')
<div class="max-w-4xl mx-auto">
    <header class="mb-10">
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Configuración Global</h1>
        <p class="text-slate-400 mt-2">Gestión de credenciales SUNAT y datos de la empresa.</p>
    </header>

    @php
        $isIngeniero = auth('admin')->user()->isIngeniero();
    @endphp

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PATCH')
        
        <!-- Company Settings -->
        <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Datos de la Empresa
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Razón Social</label>
                    <input type="text" name="company_name" value="{{ $settings['company_name'] }}" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Dirección Fiscal</label>
                    <input type="text" name="company_address" value="{{ $settings['company_address'] }}" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>
            </div>
        </div>

        <!-- Contact Settings -->
        <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                Información de Contacto Pública (Web)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">WhatsApp de Atención</label>
                    <input type="text" name="contact_whatsapp" value="{{ $settings['contact_whatsapp'] }}" placeholder="950664655" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                    <p class="text-xs text-slate-500 mt-1">Número sin espacios ni códigos de país (ej: 950664655).</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Correo de Contacto</label>
                    <input type="email" name="contact_email" value="{{ $settings['contact_email'] }}" placeholder="hola@gourmetica.pe" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                    <p class="text-xs text-slate-500 mt-1">Dirección para correos generales y consultas pública.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Central Telefónica</label>
                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] }}" placeholder="(01) 234-5678" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                    <p class="text-xs text-slate-500 mt-1">Número formateado para mostrar en la web.</p>
                </div>
            </div>
        </div>

        @if($isIngeniero)
        <!-- SUNAT Settings -->
        <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl relative overflow-hidden">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Credenciales SUNAT (Facturación Electrónica)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">RUC Emisor</label>
                    <input type="text" name="sunat_ruc" value="{{ $settings['sunat_ruc'] }}" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white font-mono focus:border-brand-primary outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Usuario SOL</label>
                    <input type="text" name="sunat_user" value="{{ $settings['sunat_user'] }}" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Clave SOL</label>
                    <input type="password" name="sunat_pass" value="{{ $settings['sunat_pass'] }}" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>
                <div class="col-span-3">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Certificado Digital (.pem)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-700 border-dashed rounded-xl hover:border-brand-secondary transition-colors cursor-pointer relative">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-slate-400">
                                <span class="relative cursor-pointer bg-transparent rounded-md font-medium text-brand-secondary hover:text-brand-accent focus-within:outline-none">Subir archivo</span>
                                <p class="pl-1">o arrastrar y soltar</p>
                            </div>
                            <p class="text-xs text-slate-500">PEM hasta 2MB</p>
                        </div>
                        <input type="file" name="sunat_certificate" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                    </div>
                </div>
            </div>
        </div>

        <!-- Culqi Settings -->
        <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl relative overflow-hidden">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Pasarela de Pagos (Culqi)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Llave Pública (PK)</label>
                    <input type="text" name="culqi_public_key" value="{{ $settings['culqi_public_key'] }}" placeholder="pk_test_..." class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white font-mono focus:border-brand-primary outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Llave Privada (SK)</label>
                    <input type="password" name="culqi_private_key" value="{{ $settings['culqi_private_key'] }}" placeholder="sk_test_..." class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-500 italic">Puedes obtener estas llaves en el panel de Culqi (Sección Desarrollo > Llaves).</p>
        </div>

        <!-- Nakama Delivery Settings -->
        <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl relative overflow-hidden">
            <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Integración de Delivery (Nakama Delivery)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">URL Base de la API</label>
                    <input type="text" name="nakama_api_url" value="{{ $settings['nakama_api_url'] }}" placeholder="https://nakama.pe" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white font-mono focus:border-brand-primary outline-none transition-all">
                    <p class="text-xs text-slate-500 mt-1">Ej: https://nakama.pe (sin barra diagonal al final).</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">API Key de Integración</label>
                    <input type="password" name="nakama_api_key" value="{{ $settings['nakama_api_key'] }}" placeholder="nk_..." class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>
                <div class="col-span-2 flex items-center mt-2">
                    <input type="checkbox" id="nakama_enabled" name="nakama_enabled" value="1" {{ $settings['nakama_enabled'] == '1' ? 'checked' : '' }} class="w-4 h-4 text-brand-secondary bg-slate-800 border-slate-600 rounded focus:ring-brand-primary focus:ring-2">
                    <label for="nakama_enabled" class="ml-3 text-sm font-medium text-slate-300 cursor-pointer">Activar envío automático de pedidos a Nakama Delivery al confirmar</label>
                </div>
            </div>
            <p class="mt-4 text-xs text-slate-500 italic">Esta integración enviará automáticamente las órdenes de delivery express a la flota de Nakama cuando el estado cambie a "Preparando".</p>
        </div>
        @endif

        <div class="flex justify-end">
            <button type="submit" class="px-12 py-4 rounded-xl bg-brand-secondary text-brand-dark font-bold hover:scale-105 transition-transform shadow-lg">
                GUARDAR CAMBIOS
            </button>
        </div>
    </form>
</div>
@endsection
