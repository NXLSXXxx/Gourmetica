<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Intranet | Gourmetica')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            primary: '#1E293B',
                            secondary: '#E2B182',
                            accent: '#334155',
                            dark: '#0F172A',
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar-link.active {
            background: rgba(226, 177, 130, 0.1);
            color: #E2B182;
            border-right: 4px solid #E2B182;
        }
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        /* Hide scrollbar for Chrome, Safari and Opera */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        /* Hide scrollbar for IE, Edge and Firefox */
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
    @yield('styles')
</head>
<body class="bg-brand-dark text-slate-200 font-sans antialiased">

    <div class="flex min-h-screen relative" x-data="{ open: false }">
        <!-- Mobile Header -->
        <header class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-brand-primary border-b border-slate-800 flex items-center justify-between px-6 z-[60]">
            <h1 class="text-xl font-serif font-bold text-brand-secondary">GOURMETICA</h1>
            <button @click="open = !open" class="text-slate-400 hover:text-white">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </header>

        <!-- Sidebar Backdrop -->
        <div x-show="open" @click="open = false" class="lg:hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[70]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <!-- Sidebar -->
        <aside :class="open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" class="w-64 bg-brand-primary border-r border-slate-800 flex flex-col fixed h-full z-[80] transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none">
            <div class="p-6">
                <h1 class="text-2xl font-serif font-bold text-brand-secondary tracking-tight">GOURMETICA</h1>
                <p class="text-[10px] text-slate-500 uppercase tracking-[0.2em] mt-1">Intranet Administrativa</p>
            </div>

            <nav class="flex-1 mt-6 overflow-y-auto no-scrollbar space-y-6">
                <!-- Principal / Dashboard -->
                @if(!auth('admin')->user()->isCajero())
                <div>
                    <a href="{{ route('intranet.dashboard') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('intranet.dashboard') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </a>
                </div>
                @endif

                <!-- Ventas y Clientes -->
                <div class="space-y-1">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider px-6 mb-2">Ventas y Clientes</p>
                    
                    <a href="{{ route('admin.live_orders.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.live_orders.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span class="flex-1">Pedidos en Vivo</span>
                        <span class="px-2 py-0.5 text-[9px] font-extrabold uppercase rounded-full bg-brand-secondary text-brand-dark animate-pulse">Caja</span>
                    </a>

                    @if(!auth('admin')->user()->isCajero())
                    <a href="{{ route('admin.orders.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Pedidos
                    </a>

                    <a href="{{ route('admin.catering.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.catering.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path></svg>
                        Catering
                    </a>
                    @endif

                    <a href="{{ route('admin.sales.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Historial de Ventas
                    </a>

                    @if(auth('admin')->user()->isAdmin())
                    <a href="{{ route('admin.clients') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.clients') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Clientes
                    </a>
                    @endif
                </div>

                <!-- Inventario y Cocina -->
                <div class="space-y-1">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider px-6 mb-2">Inventario y Cocina</p>

                    <a href="{{ route('admin.supplies.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.supplies.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        Insumos
                    </a>

                    <a href="{{ route('admin.productions.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.productions.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Producción / Cocina
                    </a>

                    <a href="{{ route('admin.purchases.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.purchases.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Compras
                    </a>

                    @if(!auth('admin')->user()->isCajero())
                    <a href="{{ route('admin.products.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Productos
                    </a>
                    @endif

                    @if(auth('admin')->user()->isAdmin() || auth('admin')->user()->isIngeniero())
                    <a href="{{ route('admin.categories.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                        Categorías
                    </a>
                    @endif
                </div>

                <!-- Logística y Catálogo -->
                <div class="space-y-1">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider px-6 mb-2">Logística y Catálogo</p>

                    @if(auth('admin')->user()->isAdmin())
                    <a href="{{ route('admin.headquarters.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.headquarters.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Sedes
                    </a>
                    @endif

                    <a href="{{ route('admin.delivery_zones.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.delivery_zones.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                        Zonas de Delivery
                    </a>

                    @if(!auth('admin')->user()->isCajero())
                    <a href="{{ route('admin.banners.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Banners
                    </a>
                    @endif
                </div>

                <!-- Configuración y Seguridad -->
                @if(auth('admin')->user()->isAdmin() || auth('admin')->user()->isIngeniero())
                <div class="space-y-1">
                    <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider px-6 mb-2">Configuración y Sistema</p>

                    <a href="{{ route('admin.users.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Administradores
                    </a>

                    <a href="{{ route('admin.settings.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Configuración
                    </a>

                    @if(auth('admin')->user()->isIngeniero())
                    <a href="{{ route('admin.logs.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Logs del Sistema
                    </a>

                    <a href="{{ route('admin.backups.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.backups.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Backups
                    </a>

                    <a href="{{ route('admin.audits.index') }}" class="sidebar-link flex items-center px-6 py-3 transition-all hover:bg-slate-800 {{ request()->routeIs('admin.audits.*') ? 'active' : '' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        Auditorías
                    </a>
                    @endif
                </div>
                @endif
            </nav>

            <div class="p-6 border-t border-slate-800">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 rounded-full bg-brand-secondary/20 flex items-center justify-center text-brand-secondary font-bold mr-3">
                        {{ substr(auth('admin')->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-sm font-semibold truncate">{{ auth('admin')->user()->name }}</p>
                        <p class="text-[10px] text-slate-500 uppercase">{{ auth('admin')->user()->role->name }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 rounded-lg bg-slate-800 hover:bg-red-500/10 hover:text-red-500 transition-all text-sm font-medium">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 lg:ml-64 min-h-screen bg-brand-dark @yield('main_padding', 'p-8') mt-16 lg:mt-0 flex flex-col">
            <div class="@yield('container_class', 'max-w-6xl mx-auto') flex-1 flex flex-col">
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 text-sm">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Intercept form submissions that have a data-confirm attribute
            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (form.hasAttribute('data-confirm')) {
                    e.preventDefault();
                    const message = form.getAttribute('data-confirm') || '¿Estás seguro de realizar esta acción?';
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#475569',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                        background: '#1E293B',
                        color: '#F8FAFC'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.removeAttribute('data-confirm');
                            form.submit();
                        }
                    });
                }
            });
        });
    </script>

    <!-- Alpine.js for the toggle -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @yield('scripts')
</body>
</html>
