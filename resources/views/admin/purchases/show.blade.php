@extends('layouts.intranet')

@section('title', 'Detalle de Compra | Gourmetica Intranet')

@section('content')
<div class="max-w-4xl mx-auto">
    <header class="flex justify-between items-center mb-10">
        <div>
            <a href="{{ route('admin.purchases.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al listado
            </a>
            <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Detalle de Compra</h1>
            <p class="text-slate-500 mt-1">Registrado el {{ $purchase->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="px-4 py-2 rounded-lg bg-slate-800 text-slate-400 text-xs font-mono border border-slate-700">
            ID: #{{ str_pad($purchase->id, 6, '0', STR_PAD_LEFT) }}
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Supplier Info -->
        <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-6 border-b border-slate-700 pb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Datos del Proveedor
            </h2>
            <div class="space-y-4">
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Razón Social</p>
                    <p class="text-white font-medium text-lg">{{ $purchase->supplier_name }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">RUC</p>
                    <p class="text-slate-300 font-mono">{{ $purchase->supplier_ruc }}</p>
                </div>
            </div>
        </div>

        <!-- Document Info -->
        <div class="bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
            <h2 class="text-lg font-bold text-white mb-6 border-b border-slate-700 pb-4 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Comprobante
            </h2>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Tipo</p>
                        <p class="text-white">{{ $purchase->document_type }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Fecha Emisión</p>
                        <p class="text-white">{{ $purchase->purchase_date }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold mb-1">Serie y Número</p>
                    <p class="text-white font-mono text-xl">{{ $purchase->series }}-{{ $purchase->number }}</p>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div class="md:col-span-2 bg-[#1E293B] p-8 rounded-2xl border-2 border-brand-secondary/20 shadow-2xl relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h3 class="text-slate-400 text-sm font-medium mb-1">Importe Total de la Operación</h3>
                    <p class="text-brand-secondary text-5xl font-serif font-bold">S/ {{ number_format($purchase->total, 2) }}</p>
                </div>
                <div class="mt-6 md:mt-0 text-center md:text-right">
                    <p class="text-slate-500 text-xs uppercase font-bold">Sede de Adquisición</p>
                    <p class="text-white text-lg font-semibold">{{ $purchase->headquarter->name }}</p>
                </div>
            </div>
            <!-- Background Decoration -->
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-brand-secondary/5 rounded-full blur-3xl"></div>
        </div>
    </div>
</div>
@endsection
