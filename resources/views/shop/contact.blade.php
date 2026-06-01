@extends('layouts.app')

@section('title', 'Contacto | Gourmetica')

@section('content')

<!-- Hero Banner -->
<section class="banner-hero relative min-h-[55vh] flex items-center justify-center overflow-hidden"
         style="background-image: url('{{ asset('images/hero.png') }}'); background-size: cover; background-position: center top;">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-gradient-to-br from-brand-secondary/96 via-brand-secondary/85 to-brand-primary/60"></div>
    
    <!-- Decorative blobs -->
    <div class="absolute top-10 right-10 w-72 h-72 bg-brand-primary/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 left-10 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full px-5 py-2 mb-8">
            <span class="w-2 h-2 bg-brand-primary rounded-full animate-pulse"></span>
            <span class="text-white/80 text-xs font-bold uppercase tracking-widest">Estamos aquí para ti</span>
        </div>

        <h1 class="text-5xl md:text-7xl font-black italic uppercase tracking-tighter text-white mb-6 leading-none">
            ¿Cómo podemos<br>
            <span class="text-brand-primary">ayudarte?</span>
        </h1>
        <p class="text-xl text-white/60 max-w-xl mx-auto font-medium">
            Elige el canal que prefieras. Respondemos con rapidez y atención personalizada.
        </p>
    </div>
</section>

<!-- Contact Cards -->
<div class="bg-brand-bg py-24">
    <div class="max-w-5xl w-full mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- WhatsApp -->
            <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl text-center border border-gray-50 hover:border-brand-primary/30 hover:-translate-y-3 transition-all duration-300 hover:shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-green-50/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-green-50 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" class="w-10 h-10" alt="WhatsApp">
                    </div>
                    <div class="inline-flex items-center gap-1 bg-green-50 text-green-600 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full mb-4">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                        Respuesta inmediata
                    </div>
                    <h3 class="text-xl font-bold text-brand-secondary mb-2">WhatsApp</h3>
                    <p class="text-gray-400 text-sm mb-6 leading-relaxed">Para pedidos, consultas y atención express en tiempo real.</p>
                    <a href="https://wa.me/{{ $contact_whatsapp }}" 
                       class="inline-flex items-center gap-2 text-brand-primary font-black hover:text-brand-secondary transition-colors">
                        <span>{{ $contact_whatsapp }}</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Email -->
            <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl text-center border border-gray-50 hover:border-brand-primary/30 hover:-translate-y-3 transition-all duration-300 hover:shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-brand-primary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="inline-flex items-center gap-1 bg-brand-primary/10 text-brand-primary text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full mb-4">
                        Consultas & Empresas
                    </div>
                    <h3 class="text-xl font-bold text-brand-secondary mb-2">Correo Electrónico</h3>
                    <p class="text-gray-400 text-sm mb-6 leading-relaxed">Para propuestas corporativas, catering y consultas formales.</p>
                    <a href="mailto:{{ $contact_email }}" 
                       class="inline-flex items-center gap-2 text-brand-primary font-black hover:text-brand-secondary transition-colors break-all">
                        <span>{{ $contact_email }}</span>
                        <svg class="w-4 h-4 flex-shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Phone -->
            <div class="group bg-white p-8 rounded-[2.5rem] shadow-xl text-center border border-gray-50 hover:border-brand-primary/30 hover:-translate-y-3 transition-all duration-300 hover:shadow-2xl relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-secondary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-brand-secondary/10 rounded-3xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-10 h-10 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    </div>
                    <div class="inline-flex items-center gap-1 bg-brand-secondary/10 text-brand-secondary text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full mb-4">
                        Llamadas directas
                    </div>
                    <h3 class="text-xl font-bold text-brand-secondary mb-2">Central</h3>
                    <p class="text-gray-400 text-sm mb-6 leading-relaxed">Llámanos directamente para atención personalizada de lun. a dom.</p>
                    <a href="tel:{{ preg_replace('/[^0-9]/', '', $contact_phone) }}" 
                       class="inline-flex items-center gap-2 text-brand-primary font-black hover:text-brand-secondary transition-colors">
                        <span>{{ $contact_phone }}</span>
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
