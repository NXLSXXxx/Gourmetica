@extends('layouts.intranet')

@section('title', 'Clientes | Gourmetica Intranet')

@section('content')
<header class="mb-10">
    <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Gestión de Clientes</h1>
    <p class="text-slate-400 mt-2">Visualiza y gestiona la base de datos de clientes registrados.</p>
</header>

        <div class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-800/50 text-slate-400 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4">Nombre</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Sede Preferida</th>
                            <th class="px-6 py-4">Fecha Registro</th>
                            <th class="px-6 py-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($clients as $client)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4 font-medium text-white">{{ $client->name }}</td>
                            <td class="px-6 py-4 text-slate-400 text-sm">{{ $client->email }}</td>
                            <td class="px-6 py-4 text-slate-400 text-sm">{{ $client->headquarter->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-slate-400 text-sm">{{ $client->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <a href="#" class="text-brand-secondary hover:underline text-sm font-medium">Ver Historial</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500">No hay clientes registrados aún.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-700">
                {{ $clients->links() }}
            </div>
        </div>
@endsection
