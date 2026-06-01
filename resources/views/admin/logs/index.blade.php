@extends('layouts.intranet')

@section('title', 'Logs del Sistema | Gourmetica')

@section('content')
<header class="mb-10">
    <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Logs del Sistema</h1>
    <p class="text-slate-400 mt-1">Últimos 100 eventos registrados en el servidor.</p>
</header>

<div class="bg-slate-900 rounded-2xl border border-slate-700 shadow-2xl overflow-hidden">
    <div class="p-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
        <div class="flex gap-2">
            <div class="w-3 h-3 rounded-full bg-red-500"></div>
            <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
            <div class="w-3 h-3 rounded-full bg-green-500"></div>
        </div>
        <span class="text-[10px] text-slate-500 font-mono">storage/logs/laravel.log</span>
    </div>
    <div class="p-6 font-mono text-xs overflow-y-auto max-h-[600px] space-y-2 bg-black/50">
        @forelse($logs as $log)
            <div class="p-2 rounded hover:bg-slate-800/50 transition-colors border-l-2 {{ str_contains($log, 'ERROR') ? 'border-red-500 text-red-400' : (str_contains($log, 'WARNING') ? 'border-yellow-500 text-yellow-400' : 'border-emerald-500 text-slate-300') }}">
                {{ $log }}
            </div>
        @empty
            <div class="text-slate-500 italic text-center py-10">No hay logs registrados actualmente.</div>
        @endforelse
    </div>
</div>

<div class="mt-6 flex justify-end">
    <form action="#" method="POST">
        @csrf
        <button type="submit" class="px-4 py-2 bg-red-500/10 text-red-500 border border-red-500/20 rounded-xl font-bold text-xs hover:bg-red-500 hover:text-white transition-all">
            LIMPIAR LOGS
        </button>
    </form>
</div>
@endsection
