@extends('layouts.intranet')

@section('title', 'Compras | Gourmetica Intranet')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Registro de Compras</h1>
        <p class="text-slate-400 mt-2">Gestiona el reabastecimiento y egresos de tus sedes.</p>
    </div>
    <a href="{{ route('admin.purchases.create') }}" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform">
        + REGISTRAR COMPRA
    </a>
</header>

<div class="bg-brand-primary rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Fecha / Doc</th>
                    <th class="px-6 py-4">Proveedor</th>
                    <th class="px-6 py-4">Sede</th>
                    <th class="px-6 py-4 text-right">Total</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($purchases as $purchase)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <span class="text-white font-bold block">{{ $purchase->purchase_date }}</span>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $purchase->document_type }} {{ $purchase->series }}-{{ $purchase->number }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-slate-300 font-medium block">{{ $purchase->supplier_name }}</span>
                        <span class="text-[10px] text-slate-500 uppercase">RUC: {{ $purchase->supplier_ruc }}</span>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-sm">
                        {{ $purchase->headquarter->name }}
                    </td>
                    <td class="px-6 py-4 text-right text-red-400 font-bold font-serif">
                        S/ {{ number_format($purchase->total, 2) }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="text-slate-500 hover:text-white transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic">No hay compras registradas aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-slate-700">
        {{ $purchases->links() }}
    </div>
</div>
@endsection
