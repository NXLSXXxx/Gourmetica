@extends('layouts.intranet')

@section('title', 'Sedes | Gourmetica Intranet')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Gestión de Sedes</h1>
        <p class="text-slate-400 mt-2">Administra los locales físicos y el personal a cargo.</p>
    </div>
    <a href="{{ route('admin.headquarters.create') }}" class="px-6 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm hover:scale-105 transition-transform">
        + NUEVA SEDE
    </a>
</header>

<div class="bg-brand-primary rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                    <th class="px-6 py-4">Sede</th>
                    <th class="px-6 py-4">Ubicación / Contacto</th>
                    <th class="px-6 py-4">Personal Asignado</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($headquarters as $hq)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <span class="font-bold text-white block">{{ $hq->name }}</span>
                        <span class="text-xs text-slate-500">ID: {{ $hq->id }}</span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-300 text-sm">
                            <p>{{ $hq->address }}, {{ $hq->city }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $hq->phone }} | {{ $hq->email }}</p>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-2">
                            @forelse($hq->users as $staff)
                                <span class="px-2 py-1 rounded bg-slate-800 text-[10px] text-slate-300 border border-slate-700" title="{{ $staff->role->name }}">
                                    {{ $staff->name }}
                                </span>
                            @empty
                                <span class="text-xs text-slate-600 italic">Sin personal</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($hq->is_active)
                            <span class="px-2 py-1 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">ACTIVO</span>
                        @else
                            <span class="px-2 py-1 text-[10px] font-bold rounded bg-red-500/10 text-red-500 border border-red-500/20">INACTIVO</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex justify-end space-x-3">
                            <a href="{{ route('admin.headquarters.edit', $hq->id) }}" class="text-slate-400 hover:text-white transition-colors" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                            <form action="{{ route('admin.headquarters.destroy', $hq->id) }}" method="POST" data-confirm="¿Estás seguro de eliminar esta sede?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors">
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
                    <td colspan="5" class="px-6 py-10 text-center text-slate-500">No hay sedes registradas aún.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
