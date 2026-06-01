<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gourmetica | Fine Pastries & Artisanal Bakery')</title>
    <meta name="description" content="Experience the finest artisanal pastries and gourmet breads at Gourmetica. Crafted with passion, delivered with elegance.">
    
    <!-- SEO & Icons -->
    <link rel="icon" type="image/png" href="/favicon.png">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

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
    <header class="fixed top-0 left-0 right-0 z-50 bg-white">
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
                <button class="cart-pill shadow-lg hover:scale-105 transition-transform" id="cart-toggle">
                    <svg class="w-6 h-6 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <span>00</span>
                </button>
            </div>
        </div>

        <!-- Bottom Tier (Sub-Nav) -->
        <div class="sub-nav hidden lg:block relative">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <ul class="flex items-center justify-between text-[11px] font-bold text-brand-secondary py-3 tracking-widest">
                    <li class="group">
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
                                <div class="w-3/4 p-10 overflow-y-auto no-scrollbar">
                                    <div id="mega-products-grid" class="grid grid-cols-3 gap-8">
                                        <!-- Products will be injected here via AJAX -->
                                        <div class="col-span-3 flex flex-col items-center justify-center h-64 text-gray-300">
                                            <svg class="w-12 h-12 mb-4 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                            <p class="text-sm font-medium tracking-widest uppercase">Selecciona una categoría</p>
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
    <footer class="bg-brand-primary text-brand-secondary {{ (request()->routeIs('login') || request()->routeIs('register')) ? 'py-8' : 'pt-16 pb-8' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if(!request()->routeIs('login') && !request()->routeIs('register'))
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div class="col-span-1 md:col-span-2">
                    <span class="text-3xl font-serif font-black tracking-widest text-brand-secondary">GOURMETICA</span>
                    <p class="mt-4 text-brand-secondary/90 font-medium max-w-sm">
                        Llevamos el arte de la pastelería fina a tu mesa. Ingredientes de primera, procesos artesanales y un toque de magia en cada bocado.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-black mb-4 font-serif text-brand-secondary uppercase tracking-wider">Explora</h3>
                    <ul class="space-y-2">
                        <li><a href="/shop" class="text-brand-secondary/80 hover:text-brand-secondary hover:underline transition-colors font-semibold">Nuestra Carta</a></li>
                        <li><a href="/locations" class="text-brand-secondary/80 hover:text-brand-secondary hover:underline transition-colors font-semibold">Tiendas</a></li>
                        <li><a href="/contact" class="text-brand-secondary/80 hover:text-brand-secondary hover:underline transition-colors font-semibold">Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-black mb-4 font-serif text-brand-secondary uppercase tracking-wider">Síguenos</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-brand-secondary/80 hover:text-brand-secondary hover:underline transition-colors font-semibold">Instagram</a>
                        <a href="#" class="text-brand-secondary/80 hover:text-brand-secondary hover:underline transition-colors font-semibold">Facebook</a>
                    </div>
                </div>
            </div>
            @endif

            <div class="{{ (request()->routeIs('login') || request()->routeIs('register')) ? '' : 'mt-16 pt-8 border-t border-brand-secondary/20' }} text-center text-sm text-brand-secondary/70 font-semibold">
                <p>&copy; {{ date('Y') }} Gourmetica. Todos los derechos reservados.</p>
            </div>
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
