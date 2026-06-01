@extends('layouts.app')

@section('title', 'Contacto | Gourmetica')

@section('content')
<div class="bg-brand-bg min-h-[60vh] flex items-center justify-center py-20">
    <div class="max-w-4xl w-full px-4">
        <div class="text-center mb-16">
            <h1 class="text-4xl md:text-6xl font-black italic uppercase tracking-tighter text-brand-secondary mb-4">¿Cómo podemos ayudarte?</h1>
            <p class="text-gray-500 text-lg">Estamos aquí para escucharte. Elige el canal que prefieras.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- WhatsApp -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-xl text-center border border-gray-50 hover:scale-105 transition-transform">
                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" class="w-8 h-8" alt="WhatsApp">
                </div>
                <h3 class="text-xl font-bold text-brand-secondary mb-2">WhatsApp</h3>
                <p class="text-gray-400 text-sm mb-6">Respuesta inmediata para tus pedidos.</p>
                <a href="https://wa.me/{{ $contact_whatsapp }}" class="text-brand-primary font-black">{{ $contact_whatsapp }}</a>
            </div>

            <!-- Email -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-xl text-center border border-gray-50 hover:scale-105 transition-transform">
                <div class="w-16 h-16 bg-brand-primary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-brand-secondary mb-2">Correo</h3>
                <p class="text-gray-400 text-sm mb-6">Para consultas generales o corporativas.</p>
                <a href="mailto:{{ $contact_email }}" class="text-brand-primary font-black">{{ $contact_email }}</a>
            </div>

            <!-- Phones -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-xl text-center border border-gray-50 hover:scale-105 transition-transform">
                <div class="w-16 h-16 bg-brand-secondary/10 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-brand-secondary mb-2">Central</h3>
                <p class="text-gray-400 text-sm mb-6">Llamadas directas a nuestra central.</p>
                <a href="tel:{{ preg_replace('/[^0-9]/', '', $contact_phone) }}" class="text-brand-primary font-black">{{ $contact_phone }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
