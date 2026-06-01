@extends('layouts.intranet')

@section('title', 'Banners | Gourmetica Intranet')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Gestión de Banners</h1>
        <p class="text-slate-400 mt-2">Controla las imágenes del Hero y banners promocionales de la web.</p>
    </div>
    <a href="{{ route('admin.banners.create') }}" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform">
        + NUEVO BANNER
    </a>
</header>

@php
    $types = [
        'hero' => ['title' => 'Banners Principales (Hero)', 'desc' => 'Se muestran en la parte superior del inicio.'],
        'promo' => ['title' => 'Banners Promocionales', 'desc' => 'Se muestran entre secciones en el inicio.'],
        'service_catering' => ['title' => 'Imagen: Servicio Catering', 'desc' => 'Fondo para la tarjeta de Catering en la página principal.'],
        'service_corporate' => ['title' => 'Imagen: Pedidos Corporativos', 'desc' => 'Fondo para la tarjeta de Pedidos Corporativos en la página principal.'],
        'section_locations' => ['title' => 'Imagen: Fondo Nuestras Casas', 'desc' => 'Fondo general de la sección de ubicación (sedes).'],
        'footer' => ['title' => 'Banners de Pie de Página', 'desc' => 'Se muestran antes del footer (obsoleto).'],
    ];
@endphp

@foreach($types as $typeKey => $typeInfo)
<div class="mb-12">
    <div class="mb-4">
        <h2 class="text-xl font-bold text-white">{{ $typeInfo['title'] }}</h2>
        <p class="text-slate-400 text-sm">{{ $typeInfo['desc'] }}</p>
    </div>
    
    <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4">Miniatura</th>
                        <th class="px-6 py-4">Título</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Orden</th>
                        <th class="px-6 py-4">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($banners->get($typeKey, []) as $banner)
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <img src="{{ asset('storage/' . $banner->image_path) }}" class="w-20 h-12 object-cover rounded-lg border border-slate-600" alt="Banner">
                        </td>
                        <td class="px-6 py-4 font-medium text-white">{{ $banner->title ?? 'Sin título' }}</td>
                        <td class="px-6 py-4">
                            @if($banner->is_active)
                            <span class="text-emerald-500 flex items-center text-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span> Activo
                            </span>
                            @else
                            <span class="text-slate-500 flex items-center text-sm">
                                <span class="w-2 h-2 rounded-full bg-slate-500 mr-2"></span> Inactivo
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-white">{{ $banner->order_index }}</td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-3">
                                <a href="{{ route('admin.banners.edit', $banner) }}" class="text-slate-400 hover:text-white transition-colors" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" data-confirm="¿Estás seguro de que deseas eliminar este banner?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors" title="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-slate-500 text-sm italic">No hay banners en esta sección.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach
@endsection
