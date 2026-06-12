@extends('layouts.intranet')

@section('title', 'Historial de Producción | Gourmetica Intranet')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Historial de Producción / Cocina</h1>
        <p class="text-slate-400 mt-2">Bitácora de preparaciones de postres y panes artesanales por sede.</p>
    </div>
    <a href="{{ route('admin.productions.create') }}" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform">
        + REGISTRAR PREPARACIÓN
    </a>
</header>

<div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Fecha y Hora</th>
                    <th class="px-6 py-4">Sede</th>
                    <th class="px-6 py-4">Producto Preparado</th>
                    <th class="px-6 py-4 text-center">Cantidad</th>
                    <th class="px-6 py-4">Registrado Por</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($productions as $prod)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 font-mono text-xs text-slate-300">
                        {{ $prod->created_at->format('d/m/Y H:i A') }}
                    </td>
                    <td class="px-6 py-4 font-medium text-white">{{ $prod->headquarter->name }}</td>
                    <td class="px-6 py-4 text-brand-secondary font-bold">{{ $prod->product->name }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 font-bold text-xs">
                            + {{ $prod->quantity }} uds.
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-sm">
                        {{ $prod->user->name }} ({{ $prod->user->role->name }})
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-500">No hay registros de producción registrados aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $productions->links() }}
</div>
@endsection
