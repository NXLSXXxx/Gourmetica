@extends('layouts.intranet')

@section('title', 'Productos | Gourmetica Intranet')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Catálogo de Productos</h1>
        <p class="text-slate-400 mt-2">Gestiona tus productos, precios y stock por sede.</p>
    </div>
    <a href="{{ route('admin.products.create') }}" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform">
        + NUEVO PRODUCTO
    </a>
</header>

        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4">Producto</th>
                            <th class="px-6 py-4">Categoría</th>
                            <th class="px-6 py-4">Precio Base</th>
                            <th class="px-6 py-4">Sedes / Stock</th>
                            <th class="px-6 py-4">Estado</th>
                            <th class="px-6 py-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($products as $product)
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
                            <td class="px-6 py-4 text-white font-bold">S/ {{ number_format($product->base_price, 2) }}</td>
                            <td class="px-6 py-4">
                                <div class="flex -space-x-2">
                                    @foreach($product->headquarters as $hq)
                                    <div class="w-8 h-8 rounded-full bg-brand-primary flex items-center justify-center border-2 border-[#1E293B] text-[10px] font-bold text-white group relative" title="{{ $hq->name }}: {{ $hq->pivot->stock }} en stock">
                                        {{ substr($hq->name, 0, 1) }}
                                        <span class="absolute -top-8 left-1/2 -translate-x-1/2 bg-black text-white px-2 py-1 rounded text-[10px] whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                            {{ $hq->name }}: {{ $hq->pivot->stock }}
                                        </span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
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
