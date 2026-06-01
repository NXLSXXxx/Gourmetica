@extends('layouts.intranet')

@section('title', 'Registrar Compra | Gourmetica Intranet')

@section('content')
<div class="max-w-4xl mx-auto">
    <header class="mb-10">
        <a href="{{ route('admin.purchases.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-2 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al listado
        </a>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Registrar Nueva Compra</h1>
        <p class="text-slate-400 mt-1">Ingresa los datos del comprobante del proveedor.</p>
    </header>

    <form action="{{ route('admin.purchases.store') }}" method="POST" class="space-y-8">
        @csrf
        
        <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Nombre del Proveedor / Razón Social</label>
                    <input type="text" name="supplier_name" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">RUC del Proveedor</label>
                    <input type="text" name="supplier_ruc" maxlength="11" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Fecha de Compra</label>
                    <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                </div>

                <div class="border-t border-slate-700 col-span-2 my-4"></div>

                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-2">Tipo de Comprobante</label>
                    <select name="document_type" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                        <option value="FACTURA">Factura</option>
                        <option value="BOLETA">Boleta</option>
                        <option value="RECIBO">Recibo</option>
                        <option value="EGRESO_INTERNO">Recibo Interno / Egreso sin Comprobante</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Serie</label>
                        <input type="text" name="series" placeholder="F001" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Número</label>
                        <input type="text" name="number" placeholder="00000123" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-400 mb-2">Total de la Compra (S/)</label>
                    <input type="number" step="0.01" name="total" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white text-2xl font-bold focus:border-brand-primary outline-none transition-all text-brand-secondary">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-12 py-4 rounded-xl bg-brand-secondary text-brand-dark font-bold hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
                REGISTRAR COMPRA
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const docTypeSelect = document.querySelector('select[name="document_type"]');
        const rucInput = document.querySelector('input[name="supplier_ruc"]');
        const seriesInput = document.querySelector('input[name="series"]');
        const numberInput = document.querySelector('input[name="number"]');
        
        const rucLabel = rucInput.previousElementSibling;
        const seriesLabel = seriesInput.previousElementSibling;
        const numberLabel = numberInput.previousElementSibling;

        function updateFields() {
            if (docTypeSelect.value === 'EGRESO_INTERNO') {
                rucInput.removeAttribute('required');
                seriesInput.removeAttribute('removeAttribute');
                numberInput.removeAttribute('removeAttribute');
                
                rucLabel.innerHTML = 'RUC del Proveedor <span class="text-xs text-slate-500 font-normal">(Opcional para Egreso Interno)</span>';
                seriesLabel.innerHTML = 'Serie <span class="text-xs text-slate-500 font-normal">(Opcional)</span>';
                numberLabel.innerHTML = 'Número <span class="text-xs text-slate-500 font-normal">(Opcional)</span>';
                
                rucInput.placeholder = 'Ej: 99999999999 (RUC genérico)';
                seriesInput.placeholder = 'INT';
                numberInput.placeholder = 'Automático';
            } else {
                rucInput.setAttribute('required', 'required');
                seriesInput.setAttribute('required', 'required');
                numberInput.setAttribute('required', 'required');
                
                rucLabel.innerHTML = 'RUC del Proveedor';
                seriesLabel.innerHTML = 'Serie';
                numberLabel.innerHTML = 'Número';
                
                rucInput.placeholder = '';
                seriesInput.placeholder = 'F001';
                numberInput.placeholder = '00000123';
            }
        }

        docTypeSelect.addEventListener('change', updateFields);
        updateFields(); // Trigger initially
    });
</script>
@endsection
