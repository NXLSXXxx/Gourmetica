@extends('layouts.intranet')

@section('title', 'Backups del Sistema | Gourmetica')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Backups del Sistema</h1>
        <p class="text-slate-400 mt-1">Gestión de copias de seguridad de la base de datos.</p>
    </div>
    <form action="{{ route('admin.backups.create') }}" method="POST">
        @csrf
        <button type="submit" class="px-6 py-3 bg-brand-secondary text-brand-dark font-bold rounded-xl hover:bg-white transition-all shadow-lg shadow-brand-secondary/20 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            GENERAR NUEVA COPIA
        </button>
    </form>
</header>

<div class="bg-brand-primary rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <table class="w-full text-left text-sm">
        <thead>
            <tr class="bg-slate-800/50 text-slate-400 text-[10px] uppercase tracking-wider">
                <th class="px-6 py-4">Nombre del Archivo</th>
                <th class="px-6 py-4">Fecha de Creación</th>
                <th class="px-6 py-4">Tamaño</th>
                <th class="px-6 py-4 text-right">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-700">
            @forelse($backups as $backup)
            <tr class="hover:bg-slate-800/30 transition-colors">
                <td class="px-6 py-4 font-mono text-brand-secondary">{{ $backup['name'] }}</td>
                <td class="px-6 py-4 text-slate-300">{{ $backup['date'] }}</td>
                <td class="px-6 py-4 text-slate-400">{{ $backup['size'] }}</td>
                <td class="px-6 py-4 text-right space-x-3">
                    <button class="text-emerald-500 hover:text-emerald-400 font-bold text-xs">DESCARGAR</button>
                    <button class="text-red-500 hover:text-red-400 font-bold text-xs">ELIMINAR</button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-6 py-10 text-center text-slate-500 italic">No hay backups generados todavía.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-8 bg-blue-500/10 border border-blue-500/20 p-6 rounded-2xl">
    <div class="flex">
        <svg class="w-6 h-6 text-blue-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
            <h4 class="text-blue-500 font-bold mb-1">Nota de Seguridad</h4>
            <p class="text-sm text-slate-400">Los backups se almacenan localmente en el servidor. Se recomienda descargarlos periódicamente y almacenarlos en un lugar seguro fuera del servidor principal.</p>
        </div>
    </div>
</div>
@endsection
