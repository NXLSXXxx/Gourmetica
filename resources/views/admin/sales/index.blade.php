@extends('layouts.intranet')

@section('title', 'Ventas y SUNAT | Gourmetica Intranet')

@section('content')
<header class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-10 gap-4">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Registro de Ventas</h1>
        <p class="text-slate-400 mt-2">Control de ingresos y declaración de comprobantes electrónicos.</p>
    </div>
    <div class="flex items-center space-x-4">
        <button onclick="openDirectSaleModal()" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform flex items-center shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            REGISTRAR VENTA FÍSICA
        </button>
        <div class="px-6 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 font-bold text-sm">
            MODO BETA SUNAT
        </div>
    </div>
</header>

<div class="bg-brand-primary rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">ID / Fecha</th>
                    <th class="px-6 py-4">Sede</th>
                    <th class="px-6 py-4">Serie-Correlativo</th>
                    <th class="px-6 py-4 text-right">Total</th>
                    <th class="px-6 py-4 text-center">Estado SUNAT</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($sales as $sale)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-white font-bold block">#{{ str_pad($sale->id, 6, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-[10px] text-slate-500">{{ $sale->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-300 text-sm">
                        {{ $sale->headquarter->name }}
                    </td>
                    <td class="px-6 py-4 text-slate-300 font-mono text-sm">
                        {{ $sale->series }}-{{ str_pad($sale->correlative, 8, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4 text-right text-brand-secondary font-bold font-serif">
                        S/ {{ number_format($sale->total, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        @if($sale->sunat_status === 'ACEPTADO')
                            <span class="px-2 py-1 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">ACEPTADO</span>
                        @elseif($sale->sunat_status === 'RECHAZADO')
                            <span class="px-2 py-1 text-[10px] font-bold rounded bg-red-500/10 text-red-500 border border-red-500/20" title="{{ $sale->sunat_response }}">RECHAZADO</span>
                        @else
                            <span class="px-2 py-1 text-[10px] font-bold rounded bg-slate-800 text-slate-400 border border-slate-700">PENDIENTE</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($sale->sunat_status !== 'ACEPTADO')
                        <form action="{{ route('admin.sales.declare', $sale->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs bg-brand-secondary text-brand-dark px-3 py-1.5 rounded-lg font-bold hover:scale-105 transition-transform">
                                DECLARAR
                            </button>
                        </form>
                        @else
                        <button class="text-slate-500 cursor-not-allowed text-xs font-bold" disabled>
                            COMPLETADO
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-slate-500 italic">No hay ventas registradas aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">
        {{ $sales->links() }}
    </div>
</div>

<!-- Modal de Registro de Venta Física -->
<div id="directSaleModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" onclick="closeDirectSaleModal()"></div>
    
    <!-- Modal Content -->
    <div class="bg-brand-primary border border-slate-700 w-full max-w-3xl rounded-2xl shadow-2xl relative z-10 overflow-hidden mx-4 max-h-[90vh] flex flex-col">
        <!-- Header -->
        <header class="px-6 py-5 bg-slate-800/40 border-b border-slate-700 flex justify-between items-center">
            <h2 class="text-xl font-serif font-bold text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Registrar Venta Física (Mostrador)
            </h2>
            <button onclick="closeDirectSaleModal()" class="text-slate-400 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </header>

        <!-- Body -->
        <form action="{{ route('admin.sales.store_direct') }}" method="POST" class="flex-1 overflow-y-auto p-6 space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Sede -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Sede Preparadora</label>
                    @if(auth('admin')->user()->isSedeAdmin())
                        <input type="text" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-slate-400 outline-none" value="{{ auth('admin')->user()->headquarter->name }}" readonly disabled>
                        <input type="hidden" name="headquarter_id" value="{{ auth('admin')->user()->headquarter_id }}">
                    @else
                        <select name="headquarter_id" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all cursor-pointer">
                            @foreach($headquarters as $hq)
                                <option value="{{ $hq->id }}">{{ $hq->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <!-- Cliente -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Cliente</label>
                    <select name="user_id" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all cursor-pointer">
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tipo Comprobante -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tipo de Comprobante</label>
                    <select name="document_type" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all cursor-pointer">
                        <option value="03">Boleta de Venta (B001)</option>
                        <option value="01">Factura de Venta (F001)</option>
                    </select>
                </div>
            </div>

            <!-- Products Table Widget -->
            <div class="border border-slate-700/60 rounded-xl overflow-hidden bg-slate-800/20">
                <div class="px-4 py-3 bg-slate-800/40 border-b border-slate-700/60 flex justify-between items-center">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Detalle de Productos</span>
                    <button type="button" onclick="addProductRow()" class="text-xs text-brand-secondary hover:text-white transition-colors font-bold flex items-center">
                        + AGREGAR PRODUCTO
                    </button>
                </div>
                
                <div class="p-4 space-y-4 max-h-[30vh] overflow-y-auto" id="productRowsContainer">
                    <!-- Product Rows will be dynamically loaded here by JS -->
                </div>
            </div>

            <!-- Footer / Totals and Submit -->
            <footer class="pt-4 border-t border-slate-700 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Venta Directa:</span>
                    <span class="text-2xl font-bold text-brand-secondary font-serif" id="modalGrandTotal">S/ 0.00</span>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeDirectSaleModal()" class="px-5 py-3 rounded-lg border border-slate-600 hover:bg-slate-800 text-slate-300 hover:text-white font-semibold text-sm transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" class="px-8 py-3 rounded-lg bg-brand-secondary text-brand-dark hover:scale-105 transition-transform font-bold text-sm shadow-md">
                        EMITIR COMPROBANTE
                    </button>
                </div>
            </footer>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let productIndex = 0;
    
    // Dump product details in JS object
    const productsList = [
        @foreach($products as $product)
        { id: {{ $product->id }}, name: "{{ $product->name }}", price: {{ $product->base_price }} },
        @endforeach
    ];

    function openDirectSaleModal() {
        document.getElementById('directSaleModal').classList.remove('hidden');
        document.getElementById('productRowsContainer').innerHTML = '';
        productIndex = 0;
        addProductRow();
    }

    function closeDirectSaleModal() {
        document.getElementById('directSaleModal').classList.add('hidden');
    }

    function addProductRow() {
        const container = document.getElementById('productRowsContainer');
        const rowId = `product-row-${productIndex}`;
        
        const rowHtml = `
            <div id="${rowId}" class="flex flex-col sm:flex-row items-center gap-4 bg-slate-900/50 border border-slate-800 p-3 rounded-xl relative">
                <div class="flex-1 w-full">
                    <label class="block text-[10px] text-slate-500 uppercase mb-1">Producto</label>
                    <select name="products[${productIndex}][id]" onchange="updateRowTotal(this, ${productIndex})" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm text-white focus:border-brand-primary outline-none transition-colors cursor-pointer select-product-input">
                        <option value="" disabled selected>Selecciona producto</option>
                        ${productsList.map(p => `<option value="${p.id}" data-price="${p.price}">${p.name} - S/ ${p.price.toFixed(2)}</option>`).join('')}
                    </select>
                </div>
                
                <div class="w-full sm:w-28">
                    <label class="block text-[10px] text-slate-500 uppercase mb-1">Cantidad</label>
                    <input type="number" name="products[${productIndex}][quantity]" value="1" min="1" oninput="updateRowTotal(this, ${productIndex})" class="w-full bg-slate-800 border border-slate-700 rounded px-3 py-2 text-sm text-white focus:border-brand-primary outline-none text-center input-quantity-field">
                </div>

                <div class="w-full sm:w-28 text-right sm:text-center">
                    <span class="block text-[10px] text-slate-500 uppercase mb-1">Subtotal</span>
                    <span class="text-sm font-bold text-white font-serif block mt-2" id="row-subtotal-${productIndex}">S/ 0.00</span>
                </div>

                <div class="absolute top-2 right-2 sm:relative sm:top-0 sm:right-0 pt-0 sm:pt-4">
                    <button type="button" onclick="removeProductRow('${rowId}')" class="text-slate-500 hover:text-red-500 transition-colors p-1.5" title="Eliminar fila">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', rowHtml);
        productIndex++;
        calculateGrandTotal();
    }

    function removeProductRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            calculateGrandTotal();
        }
    }

    function updateRowTotal(element, index) {
        const parentRow = document.getElementById(`product-row-${index}`);
        if (!parentRow) return;
        
        const select = parentRow.querySelector('.select-product-input');
        const qtyInput = parentRow.querySelector('.input-quantity-field');
        const subtotalText = document.getElementById(`row-subtotal-${index}`);
        
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption || select.value === '') return;
        
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const qty = parseInt(qtyInput.value) || 0;
        
        const subtotal = price * qty;
        subtotalText.innerText = `S/ ${subtotal.toFixed(2)}`;
        
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        const container = document.getElementById('productRowsContainer');
        const rows = container.children;
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const select = row.querySelector('.select-product-input');
            const qtyInput = row.querySelector('.input-quantity-field');
            
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && select.value !== '') {
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const qty = parseInt(qtyInput.value) || 0;
                grandTotal += price * qty;
            }
        }
        
        document.getElementById('modalGrandTotal').innerText = `S/ ${grandTotal.toFixed(2)}`;
    }
</script>
@endsection
