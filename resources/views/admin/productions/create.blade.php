@extends('layouts.intranet')

@section('title', 'Registrar Preparación | Gourmetica Intranet')

@section('content')
<div class="max-w-2xl mx-auto">
    <header class="mb-10">
        <a href="{{ route('admin.productions.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al historial
        </a>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Registrar Preparación</h1>
        <p class="text-slate-400 mt-2">Registra las unidades preparadas de un producto. El sistema descontará automáticamente los insumos e incrementará el stock del producto.</p>
    </header>

    @if(session('error'))
    <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 text-sm">
        {!! session('error') !!}
    </div>
    @endif

    <div class="bg-[#1E293B] p-8 rounded-2xl border border-slate-700 shadow-xl" x-data="productionForm()">
        <form action="{{ route('admin.productions.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="space-y-6">
                <!-- Sede / Headquarter -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Sede / Almacén</label>
                    <select name="headquarter_id" required class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all cursor-pointer">
                        @foreach($headquarters as $hq)
                        <option value="{{ $hq->id }}">{{ $hq->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Product -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Producto Preparado</label>
                    <select name="product_id" required @change="updateProduct($el.value)" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all cursor-pointer">
                        <option value="">Selecciona un producto...</option>
                        @foreach($products as $prod)
                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Cantidad Producida (unidades)</label>
                    <input type="number" name="quantity" required min="1" value="1" @input="updateQty($el.value)" class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none transition-all placeholder-slate-500" placeholder="Ej. 10">
                </div>

                <!-- Dynamic Recipe Calculation (Premium Widget) -->
                <div x-show="selectedProduct !== null" class="bg-slate-900/50 p-6 rounded-xl border border-slate-700/50 space-y-4" style="display: none;">
                    <h3 class="text-sm font-bold text-slate-300 uppercase tracking-wider flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        Insumos a Descontar (Cálculo Estimado)
                    </h3>
                    <div class="divide-y divide-slate-800">
                        <template x-for="item in recipe">
                            <div class="py-2.5 flex justify-between items-center text-sm">
                                <span class="text-slate-400" x-text="item.supply_name"></span>
                                <span class="font-mono font-bold text-brand-secondary">
                                    <span x-text="(item.quantity * qty).toFixed(2)"></span> <span x-text="item.unit"></span>
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.productions.index') }}" class="px-6 py-3 text-slate-400 hover:text-white transition-colors text-sm font-bold">CANCELAR</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
                    CONFIRMAR PREPARACIÓN
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const productsRecipes = {
        @foreach($products as $prod)
        "{{ $prod->id }}": [
            @foreach($prod->recipes as $rec)
            {
                supply_id: "{{ $rec->supply_id }}",
                supply_name: "{{ $rec->supply->name }}",
                unit: "{{ $rec->supply->unit }}",
                quantity: parseFloat("{{ $rec->quantity }}")
            },
            @endforeach
        ],
        @endforeach
    };

    function productionForm() {
        return {
            selectedProduct: null,
            recipe: [],
            qty: 1,

            updateProduct(val) {
                if (val && productsRecipes[val]) {
                    this.selectedProduct = val;
                    this.recipe = productsRecipes[val];
                } else {
                    this.selectedProduct = null;
                    this.recipe = [];
                }
            },

            updateQty(val) {
                const parsed = parseInt(val);
                this.qty = isNaN(parsed) || parsed < 1 ? 1 : parsed;
            }
        }
    }
</script>
@endsection
