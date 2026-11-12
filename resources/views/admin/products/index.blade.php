@extends('layouts.intranet')

@section('title', 'Productos | Gourmetica Intranet')

@section('content')
@php
    $selectedCategoryId = request('category_id', request('category'));
    $hasActiveFilters = request()->filled('search')
        || request()->filled('category_id')
        || request()->filled('category')
        || request()->filled('hq_id')
        || request()->filled('status')
        || request()->filled('stock_status')
        || request()->filled('min_price')
        || request()->filled('max_price')
        || request()->filled('only_available')
        || (request()->filled('sort') && request('sort') !== 'category');
@endphp

<header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Catálogo de Productos</h1>
        <p class="text-slate-400 mt-1 text-sm">Gestiona tus productos organizados por categorías, precios y disponibilidad por sede.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.create') }}" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform flex items-center gap-2 shadow-lg shadow-brand-secondary/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            NUEVO PRODUCTO
        </a>
    </div>
</header>

<!-- Navegación rápida por Categorías (Pills) -->
<div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-4">
    <a href="{{ route('admin.products.index', array_merge(request()->except(['category_id', 'category', 'page']))) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all border {{ !$selectedCategoryId ? 'bg-brand-secondary text-brand-dark border-brand-secondary shadow-md shadow-brand-secondary/20' : 'bg-[#1E293B] text-slate-300 border-slate-700 hover:border-slate-500 hover:text-white' }}">
        <span>Todas las Categorías</span>
        <span class="px-2 py-0.5 rounded-full text-[10px] {{ !$selectedCategoryId ? 'bg-brand-dark/20 text-brand-dark' : 'bg-slate-800 text-slate-400' }}">
            {{ $categories->sum('products_count') }}
        </span>
    </a>
    @foreach($categories as $category)
        @php
            $isCatActive = (string)$selectedCategoryId === (string)$category->id || (string)$selectedCategoryId === (string)$category->slug;
        @endphp
        <a href="{{ route('admin.products.index', array_merge(request()->except(['category', 'page']), ['category_id' => $category->id])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all border {{ $isCatActive ? 'bg-brand-secondary text-brand-dark border-brand-secondary shadow-md shadow-brand-secondary/20' : 'bg-[#1E293B] text-slate-300 border-slate-700 hover:border-slate-500 hover:text-white' }}">
            <span>{{ $category->name }}</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $isCatActive ? 'bg-brand-dark/20 text-brand-dark' : 'bg-slate-800 text-slate-400' }}">
                {{ $category->products_count }}
            </span>
        </a>
    @endforeach
</div>

<!-- Barra de Filtros Completa -->
<div class="bg-[#1E293B] p-4 rounded-2xl border border-slate-700 shadow-xl mb-6">
    <form action="{{ route('admin.products.index') }}" method="GET" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Búsqueda -->
            <div class="sm:col-span-2 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar producto por nombre o descripción..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 pl-10 text-sm text-white focus:border-brand-secondary outline-none">
                <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <!-- Filtro por Categoría -->
            <div>
                <select name="category_id" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-brand-secondary outline-none cursor-pointer">
                    <option value="">📂 Todas las categorías ({{ $categories->count() }})</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ ((string)$selectedCategoryId === (string)$category->id || (string)$selectedCategoryId === (string)$category->slug) ? 'selected' : '' }}>
                            {{ $category->name }} ({{ $category->products_count }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Sede -->
            <div>
                <select name="hq_id" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-brand-secondary outline-none cursor-pointer">
                    <option value="">📍 Todas las sedes</option>
                    @foreach($headquarters as $hq)
                        <option value="{{ $hq->id }}" {{ $selectedHqId == $hq->id ? 'selected' : '' }}>
                            📍 {{ $hq->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Estado Global -->
            <div>
                <select name="status" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-brand-secondary outline-none cursor-pointer">
                    <option value="">⚡ Estado: Todos</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>✅ Solo Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>⛔ Solo Inactivos</option>
                </select>
            </div>
        </div>

        <!-- Segunda fila de filtros: Stock, Rango de Precio, Orden y Acciones -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-700/60">
            <div class="flex flex-wrap items-center gap-3">
                <!-- Filtro de Stock -->
                <select name="stock_status" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-brand-secondary outline-none cursor-pointer">
                    <option value="">📦 Stock: Todos</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>Con stock disponible</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Sin stock (Agotados)</option>
                </select>

                <!-- Ordenamiento -->
                <select name="sort" onchange="this.form.submit()" class="bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-brand-secondary outline-none cursor-pointer">
                    <option value="category" {{ request('sort', 'category') === 'category' ? 'selected' : '' }}>🗂️ Agrupar por Categoría</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>🔤 Nombre: A - Z</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>🔤 Nombre: Z - A</option>
                    <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>💰 Precio: Menor a Mayor</option>
                    <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>💰 Precio: Mayor a Menor</option>
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>🕒 Más recientes</option>
                </select>

                <!-- Rango de Precio -->
                <div class="flex items-center gap-1.5 bg-slate-900 border border-slate-700 rounded-xl px-3 py-1.5">
                    <span class="text-[11px] text-slate-400 font-semibold">Precio S/:</span>
                    <input type="number" step="0.50" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="Mín" class="w-16 bg-transparent text-xs text-white focus:outline-none text-center">
                    <span class="text-slate-600">-</span>
                    <input type="number" step="0.50" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="Máx" class="w-16 bg-transparent text-xs text-white focus:outline-none text-center">
                </div>

                @if($selectedHqId)
                <label class="flex items-center gap-2 text-xs text-slate-300 cursor-pointer select-none bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2">
                    <input type="checkbox" name="only_available" value="1" {{ request('only_available') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-slate-700 text-brand-secondary focus:ring-0">
                    <span>Solo disponibles en sede</span>
                </label>
                @endif
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <button type="submit" class="px-5 py-2 bg-brand-secondary text-brand-dark hover:opacity-90 rounded-xl text-xs font-bold transition-all cursor-pointer">
                    Aplicar Filtros
                </button>

                @if($hasActiveFilters)
                <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-xl text-xs font-semibold transition-colors">
                    Limpiar
                </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Tabla de Productos Organizados por Categoría -->
<div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Producto</th>
                    <th class="px-6 py-4">Categoría</th>
                    <th class="px-6 py-4">Precio Base</th>
                    @if($selectedHqId)
                    <th class="px-6 py-4">Precio Sede</th>
                    <th class="px-6 py-4">Stock Sede</th>
                    <th class="px-6 py-4">En Sede</th>
                    @else
                    <th class="px-6 py-4">Sedes / Stock</th>
                    @endif
                    <th class="px-6 py-4">Estado Global</th>
                    <th class="px-6 py-4">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @php
                    $groupedProducts = $products->getCollection()->groupBy(fn($item) => $item->category ? $item->category->name : 'Sin Categoría');
                    $colSpan = $selectedHqId ? 8 : 6;
                @endphp

                @forelse($groupedProducts as $categoryName => $categoryProducts)
                    <!-- Encabezado de Grupo por Categoría -->
                    <tr class="bg-slate-900/90 border-y border-slate-700">
                        <td colspan="{{ $colSpan }}" class="px-6 py-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-brand-secondary"></span>
                                    <span class="font-serif font-bold text-brand-secondary text-sm tracking-wide uppercase">{{ $categoryName }}</span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                        {{ $categoryProducts->count() }} {{ $categoryProducts->count() === 1 ? 'producto' : 'productos' }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    @foreach($categoryProducts as $product)
                    @php
                        $hqPivot = $selectedHqId ? $product->headquarters->firstWhere('id', $selectedHqId)?->pivot : null;
                        $sedePrice = $product->getPriceForHeadquarter($selectedHqId);
                        $hasCustomPrice = $hqPivot && $hqPivot->price !== null && (float)$hqPivot->price > 0;
                        $isAvailInSede = $hqPivot ? (bool)($hqPivot->is_available ?? true) : false;
                    @endphp
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-lg bg-slate-700 overflow-hidden mr-4 flex-shrink-0">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-500">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <span class="font-medium text-white block">{{ $product->name }}</span>
                                    @if($product->description)
                                        <span class="text-xs text-slate-400 line-clamp-1 max-w-xs">{{ $product->description }}</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.products.index', array_merge(request()->except('page'), ['category_id' => $product->category_id])) }}"
                               class="px-3 py-1 rounded-full bg-slate-700 hover:bg-brand-secondary hover:text-brand-dark transition-colors text-xs text-slate-300 font-medium inline-block">
                                {{ $product->category->name ?? 'Sin categoría' }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-slate-300 font-bold">S/ {{ number_format($product->base_price, 2) }}</td>
                        
                        @if($selectedHqId)
                        <td class="px-6 py-4">
                            <span class="font-bold {{ $hasCustomPrice ? 'text-amber-400' : 'text-white' }}">
                                S/ {{ number_format($sedePrice, 2) }}
                            </span>
                            @if($hasCustomPrice)
                                <span class="block text-[10px] text-amber-400 font-semibold uppercase">Precio Sede</span>
                            @else
                                <span class="block text-[10px] text-slate-500 font-normal">Base</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-white">{{ $hqPivot ? $hqPivot->stock : 0 }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($isAvailInSede)
                                <span class="text-xs bg-emerald-500/20 text-emerald-400 font-bold px-2.5 py-1 rounded-full">Disponible</span>
                            @else
                                <span class="text-xs bg-red-500/20 text-red-400 font-bold px-2.5 py-1 rounded-full">No vendido</span>
                            @endif
                        </td>
                        @else
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($product->headquarters as $hq)
                                @php
                                    $hqIsAvail = (bool)($hq->pivot->is_available ?? true);
                                @endphp
                                <div class="px-2 py-1 rounded-lg {{ $hqIsAvail ? 'bg-slate-800 border border-slate-700 text-slate-200' : 'bg-slate-900 border border-slate-800 text-slate-500 opacity-60' }} text-[11px] font-medium flex items-center gap-1.5" title="{{ $hq->name }}: {{ $hq->pivot->stock }} en stock · Precio: S/ {{ number_format($hq->pivot->price ?? $product->base_price, 2) }}">
                                    <span class="w-2 h-2 rounded-full {{ $hqIsAvail ? 'bg-emerald-500' : 'bg-slate-600' }}"></span>
                                    <span>{{ $hq->name }}:</span>
                                    <span class="font-bold text-amber-400">S/ {{ number_format($hq->pivot->price ?? $product->base_price, 2) }}</span>
                                    <span class="text-slate-400">({{ $hq->pivot->stock }})</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                        @endif
                        <td class="px-6 py-4">
                            @if($product->is_active)
                            <span class="text-emerald-500 flex items-center text-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span> Activo
                            </span>
                            @else
                            <span class="text-slate-500 flex items-center text-sm">
                                <span class="w-2 h-2 rounded-full bg-slate-500 mr-2"></span> Inactivo
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-3">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-slate-400 hover:text-white transition-colors" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" data-confirm="¿Estás seguro de eliminar este producto?">
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
                    @endforeach
                @empty
                <tr>
                    <td colspan="{{ $colSpan }}" class="px-6 py-12 text-center text-slate-400">
                        <p class="mb-2">No se encontraron productos con los filtros seleccionados.</p>
                        @if($hasActiveFilters)
                            <a href="{{ route('admin.products.index') }}" class="text-xs text-brand-secondary hover:underline font-semibold">Limpiar todos los filtros</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">
        {{ $products->links() }}
    </div>
</div>
@endsection
