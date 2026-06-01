@extends('layouts.app')

@section('title', 'Catering y Eventos | Gourmetica')

@section('content')
<!-- Hero Section -->
<section class="relative py-32 bg-brand-primary text-white overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-brand-secondary"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="text-brand-secondary font-bold tracking-widest uppercase mb-4 block">Eventos Inolvidables</span>
        <h1 class="text-5xl md:text-7xl font-serif font-bold mb-8">Servicio de Catering</h1>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
            Llevamos la excelencia de Gourmetica a tus celebraciones. Diseñamos mesas de postres exclusivas y menús personalizados para bodas, eventos corporativos y ocasiones especiales.
        </p>
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
