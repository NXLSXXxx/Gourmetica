<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gourmetica | Fine Pastries & Artisanal Bakery')</title>
    <meta name="description" content="Experience the finest artisanal pastries and gourmet breads at Gourmetica. Crafted with passion, delivered with elegance.">
    
    <!-- SEO & Icons -->
    <link rel="icon" type="image/png" href="/favicon.png">
    
    <!-- Styles & Tailwind CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        @theme {
            --color-brand-primary: #E9A171;
            --color-brand-secondary: #3D2B1F;
            --color-brand-bg: #FFFBF7;
            --color-brand-dark: #2D1B14;
        }
    </style>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    
    @stack('styles')
</head>
<body class="antialiased" x-data="{ mobileMenuOpen: false }">
    @if(!request()->routeIs('login') && !request()->routeIs('register'))
    <!-- Header Maria Almenara Style -->
    <header class="fixed top-0 left-0 right-0 z-[60] bg-white">
        <!-- Top Tier -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <!-- Burger + Logo -->
            <div class="flex items-center gap-4">
                <button type="button" @click="mobileMenuOpen = true" class="lg:hidden text-brand-secondary cursor-pointer focus:outline-none">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
                </button>
                <a href="/" class="flex-shrink-0">
                    <span class="text-2xl font-serif font-bold tracking-tighter text-brand-secondary">GOURMETICA</span>
                </a>
            </div>

            <!-- Location Pill (Fully Functional) -->
            <div class="hidden xl:flex items-center">
                <button onclick="openSelectHeadquarterModal()" class="pill-location shadow-sm border border-gray-100 hover:bg-gray-50 transition-colors flex items-center cursor-pointer text-left focus:outline-none">
                    <svg class="w-4 h-4 mr-2 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>
                        @if($selected_headquarter)
                            ¡Pedido para! <b class="text-brand-secondary">{{ (stripos($selected_headquarter->name, 'sede') === 0) ? $selected_headquarter->name : 'Sede ' . $selected_headquarter->name }}</b>
                        @else
                            ¡Comienza tu pedido! <b>Elige tu dirección</b>
                        @endif
                    </span>
                </button>
            </div>

            <!-- Search Pill (Fully Functional) -->
            <form action="{{ route('shop.index') }}" method="GET" class="hidden md:flex flex-1 max-w-xs">
                <div class="pill-search w-full">
                    <svg class="w-4 h-4 text-gray-400 cursor-pointer hover:text-brand-secondary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" onclick="this.closest('form').submit()"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="¿Qué buscas?" class="bg-transparent border-none focus:ring-0 text-xs ml-2 w-full outline-none">
                </div>
            </form>

            <!-- Actions -->
            <div class="flex items-center space-x-4">
                <!-- WhatsApp -->
                <a href="https://wa.me/{{ $contact_whatsapp }}" target="_blank" class="hidden sm:flex items-center text-[10px] font-bold text-brand-secondary">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" class="w-6 h-6 mr-1" alt="WhatsApp">
                    <div class="flex flex-col">
                        <span>Escríbenos</span>
                        <span class="text-brand-primary">{{ $contact_whatsapp }}</span>
                    </div>
                </a>

                <!-- User -->
                @auth
                <a href="{{ route('profile.index') }}" class="flex items-center gap-2">
                    <img src="https://static.thenounproject.com/png/363640-200.png" class="w-8 h-8 opacity-80" alt="User">
                    <div class="hidden lg:flex flex-col text-[10px] font-bold text-brand-secondary leading-tight">
                        <span>Bienvenid@</span>
                        <span class="text-brand-primary">{{ explode(' ', auth()->user()->name)[0] }} ▼</span>
                    </div>
                </a>
                @else
                <a href="/login" class="flex items-center gap-2">
                    <img src="https://static.thenounproject.com/png/363640-200.png" class="w-8 h-8 opacity-80" alt="User">
                    <div class="hidden lg:flex flex-col text-[10px] font-bold text-brand-secondary leading-tight">
                        <span>Bienvenid@</span>
                        <span class="text-brand-primary">Iniciar sesión ▼</span>
                    </div>
                </a>
                @endauth

                <!-- Cart Pill -->
                <a href="{{ route('shop.cart') }}" class="cart-pill shadow-lg hover:scale-105 transition-transform" id="cart-toggle">
                    <svg class="w-6 h-6 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span>{{ sprintf('%02d', array_sum(array_column(session('cart', []), 'quantity'))) }}</span>
                </a>
            </div>
        </div>

        <!-- Bottom Tier (Sub-Nav) -->
        <div class="sub-nav hidden lg:block relative z-[60]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <ul class="flex items-center justify-between text-[11px] font-bold text-brand-secondary py-3 tracking-widest">
                    <li class="group relative">
                        <a href="/shop" class="flex items-center hover:text-brand-primary">
                            NUESTRA CARTA 
                            <svg class="w-3 h-3 ml-1 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </a>
                        
                        <!-- Mega Menu Container -->
                        <div class="mega-menu">
                            <div class="max-w-7xl mx-auto flex h-[450px]">
                                <!-- Left: Categories -->
                                <div class="w-1/4 border-r border-gray-100 overflow-y-auto no-scrollbar py-6 bg-gray-50/50">
                                    <div class="px-6 mb-4 text-[9px] uppercase tracking-[0.2em] text-gray-400 font-extrabold">CATEGORÍAS</div>
                                    @foreach($categories as $cat)
                                    <div class="mega-menu-item text-xs font-bold tracking-wider" 
                                         onmouseover="fetchProducts('{{ $cat->slug }}', this)">
                                        {{ $cat->name }}
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Right: Products Grid -->
                                <div class="w-3/4 p-10 overflow-y-auto no-scrollbar relative">
                                    <div id="mega-products-grid" class="grid grid-cols-3 gap-8 h-full">
                                        <div class="col-span-3 flex flex-col items-center justify-center h-full text-brand-secondary/40 min-h-[300px]">
                                            <div class="relative mb-6">
                                                <div class="absolute inset-0 bg-brand-primary/10 rounded-full animate-ping"></div>
                                                <div class="relative bg-brand-bg rounded-full p-5 border border-brand-primary/20 shadow-sm">
                                                    <svg class="w-10 h-10 text-brand-primary opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h14a2 2 0 012 2v6z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 7V3m-4 4V3m8 4V3"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 11h10v4H7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <h4 class="text-lg font-serif font-black tracking-widest uppercase text-brand-secondary mb-2">Descubre nuestra carta</h4>
                                            <p class="text-[11px] font-bold tracking-widest text-brand-secondary/50 text-center uppercase">Selecciona una categoría a la izquierda para ver<br>nuestras delicias recién horneadas.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><a href="/about" class="hover:text-brand-primary">NOSOTROS</a></li>
                    <li><a href="/catering" class="flex items-center hover:text-brand-primary text-brand-secondary px-4 py-1 bg-white rounded-full shadow-sm border border-gray-100">CATERING <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></a></li>
                    <li><a href="/locations" class="flex items-center hover:text-brand-primary">NUESTRAS CASAS <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></a></li>
                    <li><a href="/contact" class="hover:text-brand-primary">CONTACTO</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Mobile Menu Drawer (Alpine.js) -->
    <div x-show="mobileMenuOpen" 
         class="fixed inset-0 z-[100] lg:hidden" 
         role="dialog" 
         aria-modal="true"
         style="display: none;">
        <!-- Backdrop overlay -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs" 
             @click="mobileMenuOpen = false"></div>

        <!-- Drawer Content -->
        <div x-show="mobileMenuOpen"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 w-full max-w-xs bg-brand-bg shadow-2xl flex flex-col p-6 z-10 border-r border-brand-secondary/10">
            
            <!-- Drawer Header -->
            <div class="flex items-center justify-between mb-8 pb-4 border-b border-brand-secondary/10">
                <span class="text-xl font-serif font-black tracking-widest text-brand-secondary">GOURMETICA</span>
                <button @click="mobileMenuOpen = false" class="text-brand-secondary p-1 hover:scale-110 transition-transform cursor-pointer focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Mobile Location selector (fully working) -->
            <div class="mb-6">
                <button onclick="openSelectHeadquarterModal(); mobileMenuOpen = false;" class="w-full pill-location shadow-sm border border-gray-100 hover:bg-gray-50 transition-colors flex items-center justify-center cursor-pointer py-3 text-xs">
                    <svg class="w-4 h-4 mr-2 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="truncate">
                        @if($selected_headquarter)
                            ¡Pedido para Sede! <b class="text-brand-primary font-black">{{ $selected_headquarter->name }}</b>
                        @else
                            ¡Comienza! <b>Elige tu sede</b>
                        @endif
                    </span>
                </button>
            </div>

            <!-- Mobile Search -->
            <form action="{{ route('shop.index') }}" method="GET" class="mb-8">
                <div class="pill-search py-2.5 px-4 w-full flex items-center bg-white border border-gray-100 rounded-full shadow-xs">
                    <svg class="w-4 h-4 text-gray-400 cursor-pointer hover:text-brand-secondary transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" onclick="this.closest('form').submit()"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="¿Qué buscas en la carta?" class="bg-transparent border-none focus:ring-0 text-xs ml-2 w-full outline-none">
                </div>
            </form>

            <!-- Navigation Links -->
            <nav class="flex flex-col space-y-4 text-sm font-bold tracking-widest text-brand-secondary">
                <a href="/shop" class="flex items-center justify-between py-2 border-b border-brand-secondary/5 hover:text-brand-primary">
                    <span>NUESTRA CARTA</span>
                    <svg class="w-4 h-4 text-brand-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <a href="/about" class="flex items-center justify-between py-2 border-b border-brand-secondary/5 hover:text-brand-primary">
                    <span>NOSOTROS</span>
                    <svg class="w-4 h-4 text-brand-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <a href="/catering" class="flex items-center justify-between py-2 border-b border-brand-secondary/5 hover:text-brand-primary">
                    <span>CATERING</span>
                    <svg class="w-4 h-4 text-brand-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <a href="/locations" class="flex items-center justify-between py-2 border-b border-brand-secondary/5 hover:text-brand-primary">
                    <span>NUESTRAS CASAS</span>
                    <svg class="w-4 h-4 text-brand-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                <a href="/contact" class="flex items-center justify-between py-2 border-b border-brand-secondary/5 hover:text-brand-primary">
                    <span>CONTACTO</span>
                    <svg class="w-4 h-4 text-brand-secondary/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </nav>

            <!-- Bottom info inside drawer -->
            <div class="mt-auto pt-6 border-t border-brand-secondary/10 text-center">
                <span class="text-[10px] text-gray-400 font-extrabold tracking-widest block mb-2 uppercase">ATENCIÓN AL CLIENTE</span>
                <span class="text-sm text-brand-primary font-black block">{{ $contact_whatsapp }}</span>
            </div>
        </div>
    </div>
    @endif

    <main class="{{ (request()->routeIs('login') || request()->routeIs('register')) ? '' : 'pt-32' }}">
        @yield('content')
    <!-- Premium Footer -->
    <footer class="{{ (request()->routeIs('login') || request()->routeIs('register')) ? 'bg-brand-secondary py-8' : 'bg-brand-secondary' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!request()->routeIs('login') && !request()->routeIs('register'))
            
            <!-- Footer Top: Brand + Nav Columns -->
            <div class="pt-16 pb-12 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-12 border-b border-white/10">
                
                <!-- Brand Column -->
                <div class="lg:col-span-2">
                    <a href="/" class="inline-block mb-6">
                        <span class="text-3xl font-serif font-black tracking-widest text-white">GOURMETICA</span>
                        <div class="h-0.5 w-12 bg-brand-primary mt-2"></div>
                    </a>
                    <p class="text-white/60 text-sm leading-relaxed max-w-xs font-medium">
                        Arte, pasión y sabor en cada creación. Llevamos la pastelería artesanal a un nivel de experiencia sublime, con ingredientes honestos y procesos que respetan la tradición.
                    </p>
                    <!-- Social Icons -->
                    <div class="flex items-center gap-3 mt-8">
                        <span class="text-[10px] uppercase tracking-widest text-white/40 font-bold">Síguenos</span>
                        <a href="#" aria-label="Instagram" class="w-9 h-9 bg-white/10 hover:bg-brand-primary rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" aria-label="Facebook" class="w-9 h-9 bg-white/10 hover:bg-brand-primary rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="#" aria-label="TikTok" class="w-9 h-9 bg-white/10 hover:bg-brand-primary rounded-full flex items-center justify-center transition-all duration-300 hover:scale-110">
                            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Explore Column -->
                <div>
                    <h4 class="text-white font-black uppercase tracking-widest text-xs mb-6 flex items-center gap-2">
                        <span class="w-4 h-0.5 bg-brand-primary inline-block"></span>
                        Explora
                    </h4>
                    <ul class="space-y-3">
                        <li><a href="/shop" class="text-white/60 hover:text-brand-primary transition-colors text-sm font-medium flex items-center gap-2 group"><span class="w-0 group-hover:w-2 h-px bg-brand-primary transition-all duration-300"></span>Nuestra Carta</a></li>
                        <li><a href="/about" class="text-white/60 hover:text-brand-primary transition-colors text-sm font-medium flex items-center gap-2 group"><span class="w-0 group-hover:w-2 h-px bg-brand-primary transition-all duration-300"></span>Nosotros</a></li>
                        <li><a href="/catering" class="text-white/60 hover:text-brand-primary transition-colors text-sm font-medium flex items-center gap-2 group"><span class="w-0 group-hover:w-2 h-px bg-brand-primary transition-all duration-300"></span>Catering</a></li>
                        <li><a href="/locations" class="text-white/60 hover:text-brand-primary transition-colors text-sm font-medium flex items-center gap-2 group"><span class="w-0 group-hover:w-2 h-px bg-brand-primary transition-all duration-300"></span>Nuestras Casas</a></li>
                    </ul>
                </div>

                <!-- Services Column -->
                <div>
                    <h4 class="text-white font-black uppercase tracking-widest text-xs mb-6 flex items-center gap-2">
                        <span class="w-4 h-0.5 bg-brand-primary inline-block"></span>
                        Servicios
                    </h4>
                    <ul class="space-y-3">
                        <li><a href="/catering" class="text-white/60 hover:text-brand-primary transition-colors text-sm font-medium flex items-center gap-2 group"><span class="w-0 group-hover:w-2 h-px bg-brand-primary transition-all duration-300"></span>Eventos Corporativos</a></li>
                        <li><a href="/catering" class="text-white/60 hover:text-brand-primary transition-colors text-sm font-medium flex items-center gap-2 group"><span class="w-0 group-hover:w-2 h-px bg-brand-primary transition-all duration-300"></span>Bodas & Celebraciones</a></li>
                        <li><a href="/catering" class="text-white/60 hover:text-brand-primary transition-colors text-sm font-medium flex items-center gap-2 group"><span class="w-0 group-hover:w-2 h-px bg-brand-primary transition-all duration-300"></span>Pedidos Personalizados</a></li>
                        <li><a href="/shop" class="text-white/60 hover:text-brand-primary transition-colors text-sm font-medium flex items-center gap-2 group"><span class="w-0 group-hover:w-2 h-px bg-brand-primary transition-all duration-300"></span>Delivery</a></li>
                    </ul>
                </div>

                <!-- Contact Column -->
                <div>
                    <h4 class="text-white font-black uppercase tracking-widest text-xs mb-6 flex items-center gap-2">
                        <span class="w-4 h-0.5 bg-brand-primary inline-block"></span>
                        Contacto
                    </h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-brand-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold">WhatsApp</p>
                                <a href="https://wa.me/{{ $contact_whatsapp }}" class="text-white/70 hover:text-brand-primary transition-colors text-sm font-medium">{{ $contact_whatsapp }}</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold">Correo</p>
                                <a href="mailto:{{ $contact_email }}" class="text-white/70 hover:text-brand-primary transition-colors text-sm font-medium">{{ $contact_email }}</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-white/10 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-white/40 uppercase tracking-widest font-bold">Sedes</p>
                                <a href="/locations" class="text-white/70 hover:text-brand-primary transition-colors text-sm font-medium">Ver nuestras casas</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Footer Bottom Bar -->
            <div class="py-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-white/30 text-xs font-medium">&copy; {{ date('Y') }} Gourmetica. Todos los derechos reservados.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="text-white/30 hover:text-white/60 text-xs transition-colors">Política de Privacidad</a>
                    <a href="#" class="text-white/30 hover:text-white/60 text-xs transition-colors">Términos y Condiciones</a>
                    <a href="#" class="text-white/30 hover:text-white/60 text-xs transition-colors">Libro de Reclamaciones</a>
                </div>
            </div>
            @else
            <div class="py-8 text-center">
                <p class="text-white/50 text-sm">&copy; {{ date('Y') }} Gourmetica. Todos los derechos reservados.</p>
            </div>
            @endif
        </div>
    </footer>

    <!-- Modal para seleccionar Sede -->
    <div id="selectHeadquarterModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-xs" onclick="closeSelectHeadquarterModal()"></div>
        
        <!-- Modal Content -->
        <div class="bg-white rounded-3xl shadow-2xl relative z-10 w-full max-w-md mx-4 overflow-hidden border border-gray-100">
            <!-- Header -->
            <header class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-base font-serif font-bold text-brand-primary flex items-center">
                    <svg class="w-5 h-5 mr-2 text-brand-secondary animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                    Elige la sede para tu pedido
                </h3>
                <button onclick="closeSelectHeadquarterModal()" class="text-gray-400 hover:text-brand-secondary transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </header>
            
            <!-- Body -->
            <div class="p-6 space-y-4 max-h-[50vh] overflow-y-auto">
                <p class="text-xs text-gray-500 mb-2 leading-relaxed">
                    Selecciona la tienda de Gourmetica más cercana para garantizar la disponibilidad inmediata de nuestros pasteles finos y panes artesanales recién horneados.
                </p>
                <div class="grid grid-cols-1 gap-3">
                    @foreach($global_headquarters as $hq)
                    <button onclick="setHeadquarter({{ $hq->id }})" class="w-full text-left p-4 border rounded-2xl transition-all flex items-center justify-between hover:bg-gray-50 {{ session('selected_headquarter_id') == $hq->id ? 'border-brand-secondary bg-brand-secondary/5 font-semibold' : 'border-gray-100' }}">
                        <div class="pr-4">
                            <h4 class="text-sm font-bold text-brand-primary">{{ $hq->name }}</h4>
                            <p class="text-[11px] text-gray-400 mt-0.5 flex items-center">
                                {{ $hq->address }}
                            </p>
                        </div>
                        @if(session('selected_headquarter_id') == $hq->id)
                            <span class="bg-brand-secondary text-brand-dark px-2.5 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-wider flex-shrink-0">Activa</span>
                        @else
                            <span class="border border-gray-300 text-gray-400 hover:border-brand-secondary hover:text-brand-secondary px-2.5 py-0.5 rounded-full text-[8px] font-bold uppercase tracking-wider transition-colors flex-shrink-0">Elegir</span>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        function openSelectHeadquarterModal() {
            document.getElementById('selectHeadquarterModal').classList.remove('hidden');
        }
        function closeSelectHeadquarterModal() {
            document.getElementById('selectHeadquarterModal').classList.add('hidden');
        }
        function setHeadquarter(id) {
            fetch('{{ route("select-headquarter") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ headquarter_id: id })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => console.error(err));
        }
    </script>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
