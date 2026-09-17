@extends('layouts.intranet')

@section('title', 'Productos | Gourmetica Intranet')

@section('content')
<header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Catálogo de Productos</h1>
        <p class="text-slate-400 mt-1">Gestiona tus productos, precios y disponibilidad diferenciada por sede.</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.products.create') }}" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            NUEVO PRODUCTO
        </a>
    </div>
</header>

<!-- Filters Bar -->
<div class="bg-[#1E293B] p-4 rounded-2xl border border-slate-700 shadow-xl mb-6">
    <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-col md:flex-row items-stretch md:items-center gap-4">
        <!-- Search -->
        <div class="flex-1 relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o descripción..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 pl-10 text-sm text-white focus:border-brand-primary outline-none">
            <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>

        <!-- Sede Filter -->
        <div class="w-full md:w-64">
            <select name="hq_id" onchange="this.form.submit()" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-brand-primary outline-none cursor-pointer">
                <option value="">-- Ver todas las sedes --</option>
                @foreach($headquarters as $hq)
                    <option value="{{ $hq->id }}" {{ $selectedHqId == $hq->id ? 'selected' : '' }}>
                        📍 {{ $hq->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if($selectedHqId)
        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer select-none">
            <input type="checkbox" name="only_available" value="1" {{ request('only_available') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-slate-700 text-brand-primary focus:ring-0">
            <span>Solo activos en sede</span>
        </label>
        @endif

        <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-sm font-semibold transition-colors">
            Filtrar
        </button>

        @if(request()->hasAny(['search', 'hq_id', 'only_available']))
        <a href="{{ route('admin.products.index') }}" class="px-4 py-2.5 text-slate-400 hover:text-white text-xs font-semibold text-center transition-colors">
            Limpiar
        </a>
        @endif
    </form>
</div>

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
                        @forelse($products as $product)
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
                                    <span class="font-medium text-white">{{ $product->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full bg-slate-700 text-xs text-slate-300">
                                    {{ $product->category->name }}
                                </span>
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
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">No hay productos registrados aún.</td>
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
