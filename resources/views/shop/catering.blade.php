@extends('layouts.app')

@section('title', 'Catering y Eventos | Gourmetica')

@section('content')
@php
    $heroBanner = \App\Models\Banner::where('type', 'catering_hero')->where('is_active', true)->first();
    $heroImage = $heroBanner ? asset('storage/' . $heroBanner->image_path) : asset('images/cakes.png');
@endphp
<!-- Hero Section with background image -->
<section class="banner-hero relative min-h-[70vh] flex items-center justify-center overflow-hidden"
         style="background-image: url('{{ $heroImage }}'); background-size: cover; background-position: center;">
    <!-- Layered overlays -->
    <div class="absolute inset-0 bg-gradient-to-br from-brand-secondary/96 via-brand-secondary/85 to-brand-primary/60"></div>
    
    <!-- Decorative blobs -->
    <div class="absolute top-20 right-10 w-72 h-72 bg-brand-primary/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
    
    <!-- Pulsing ring -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] rounded-full border border-white/5 animate-ping pointer-events-none" style="animation-duration: 4s;"></div>
    
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-5 py-2 mb-8">
            <span class="w-2 h-2 bg-brand-primary rounded-full animate-pulse"></span>
            <span class="text-white/80 text-xs font-bold uppercase tracking-widest">Eventos Inolvidables</span>
        </div>
        
        <h1 class="text-6xl md:text-8xl font-serif font-black text-white mb-6 leading-none tracking-tight">
            Servicio de<br>
            <span class="text-brand-primary italic">Catering</span>
        </h1>
        <p class="text-xl text-white/70 max-w-3xl mx-auto leading-relaxed font-medium">
            Llevamos la excelencia de Gourmetica a tus celebraciones. Diseñamos mesas de postres exclusivas y menús personalizados para bodas, eventos corporativos y ocasiones especiales.
        </p>

        <!-- Quick stats -->
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12 mt-12">
            <div class="text-center">
                <span class="block text-3xl font-black text-brand-primary">+200</span>
                <span class="text-white/40 text-[10px] uppercase tracking-widest font-bold">Eventos realizados</span>
            </div>
            <div class="w-px h-10 bg-white/20 hidden md:block"></div>
            <div class="text-center">
                <span class="block text-3xl font-black text-brand-primary">100%</span>
                <span class="text-white/40 text-[10px] uppercase tracking-widest font-bold">Personalizado</span>
            </div>
            <div class="w-px h-10 bg-white/20 hidden md:block"></div>
            <div class="text-center">
                <span class="block text-3xl font-black text-brand-primary">5★</span>
                <span class="text-white/40 text-[10px] uppercase tracking-widest font-bold">Calificación</span>
            </div>
        </div>
    </div>
</section>


<!-- Form Section -->
<section class="py-24 bg-brand-bg relative">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="bg-white rounded-[3rem] shadow-2xl p-10 md:p-16 border border-gray-100 -mt-32">
            
            @if(session('success'))
            <div class="mb-10 p-6 bg-emerald-50 text-emerald-600 rounded-2xl border border-emerald-100 text-center">
                <svg class="w-12 h-12 mx-auto mb-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-xl font-bold mb-2">¡Solicitud Enviada!</h3>
                <p>{{ session('success') }}</p>
            </div>
            @endif

            <div class="text-center mb-12">
                <h2 class="text-3xl font-serif font-bold text-brand-primary mb-4">Cotiza tu Evento</h2>
                <p class="text-gray-500">Completa el formulario y uno de nuestros asesores se comunicará contigo para diseñar la experiencia perfecta.</p>
            </div>

            <form action="{{ route('shop.catering.store') }}" method="POST" class="space-y-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-2">Nombre Completo *</label>
                        <input type="text" name="name" required value="{{ old('name') }}" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-secondary focus:bg-white outline-none transition-all">
                        @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-2">Correo Electrónico *</label>
                        <input type="email" name="email" required value="{{ old('email') }}" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-secondary focus:bg-white outline-none transition-all">
                        @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-2">Teléfono *</label>
                        <input type="tel" name="phone" required value="{{ old('phone') }}" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-secondary focus:bg-white outline-none transition-all">
                        @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Event Date -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-2">Fecha del Evento *</label>
                        <input type="date" name="event_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required value="{{ old('event_date') }}" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-secondary focus:bg-white outline-none transition-all">
                        @error('event_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Event Type -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-2">Tipo de Evento *</label>
                        <select name="event_type" required class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-secondary focus:bg-white outline-none transition-all">
                            <option value="">Selecciona una opción</option>
                            <option value="Boda" {{ old('event_type') == 'Boda' ? 'selected' : '' }}>Boda</option>
                            <option value="Corporativo" {{ old('event_type') == 'Corporativo' ? 'selected' : '' }}>Corporativo / Empresa</option>
                            <option value="Cumpleaños" {{ old('event_type') == 'Cumpleaños' ? 'selected' : '' }}>Cumpleaños</option>
                            <option value="Bautizo/Comunión" {{ old('event_type') == 'Bautizo/Comunión' ? 'selected' : '' }}>Bautizo / Comunión</option>
                            <option value="Otro" {{ old('event_type') == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('event_type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Guests -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-2">Nº Invitados Aprox. *</label>
                        <input type="number" name="guests" min="5" required value="{{ old('guests') }}" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-secondary focus:bg-white outline-none transition-all" placeholder="Ej: 50">
                        @error('guests') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Message -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 uppercase tracking-widest mb-2">Detalles Adicionales</label>
                        <textarea name="message" rows="4" class="w-full bg-gray-50 border-2 border-gray-100 rounded-xl px-4 py-3 text-brand-primary focus:border-brand-secondary focus:bg-white outline-none transition-all" placeholder="Cuéntanos un poco más sobre lo que imaginas para tu evento...">{{ old('message') }}</textarea>
                        @error('message') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="text-center pt-8 border-t border-gray-100">
                    <button type="submit" class="btn-premium px-12 py-5 text-lg shadow-xl shadow-brand-primary/10 w-full md:w-auto">
                        SOLICITAR COTIZACIÓN
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
