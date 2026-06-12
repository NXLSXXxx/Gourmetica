@extends('layouts.intranet')

@section('title', 'Ajuste de Stock | Gourmetica Intranet')

@section('content')
<div class="max-w-2xl mx-auto">
    <header class="mb-10">
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Stock por Sede</h1>
        <p class="text-slate-400 mt-2">Ajuste de inventario para el insumo: <b class="text-brand-secondary">{{ $supply->name }}</b> ({{ $supply->unit }})</p>
    </header>

    <div class="bg-[#1E293B] p-8 rounded-2xl border border-slate-700 shadow-xl">
        <form action="{{ route('admin.supplies.update_stock', $supply->id) }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="space-y-4 divide-y divide-slate-700">
                @foreach($headquarters as $hq)
                <div class="pt-4 first:pt-0 flex items-center justify-between gap-4">
                    <div>
                        <h4 class="text-base font-bold text-white">{{ $hq->name }}</h4>
                        <p class="text-xs text-slate-400 mt-1">{{ $hq->address }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <input type="number" step="0.0001" name="headquarters[{{ $hq->id }}][stock]" value="{{ $stocks[$hq->id] }}" min="0" class="w-36 bg-slate-900 border border-slate-700 rounded-xl px-4 py-2 text-white outline-none focus:border-brand-secondary text-right font-mono font-bold" required>
                        <span class="text-sm text-slate-400 font-mono font-bold w-12">{{ $supply->unit }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pt-6 border-t border-slate-700 flex items-center justify-end space-x-4">
                <a href="{{ route('admin.supplies.index') }}" class="px-6 py-3 text-slate-400 hover:text-white transition-colors text-sm font-bold">CANCELAR</a>
                <button type="submit" class="px-8 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform shadow-lg shadow-brand-secondary/20">
                    GUARDAR STOCK
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
