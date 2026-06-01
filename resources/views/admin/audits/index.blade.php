@extends('layouts.intranet')

@section('title', 'Auditorías de Sistema | Gourmetica')

@section('content')
<header class="flex justify-between items-center mb-10">
    <div>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Auditorías de Sistema</h1>
        <p class="text-slate-400 mt-1">Registro de actividad y modificaciones de datos.</p>
    </div>
</header>

<div class="bg-brand-primary rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead>
                <tr class="bg-slate-800/50 text-slate-400 text-[10px] uppercase tracking-wider">
                    <th class="px-6 py-4">Fecha</th>
                    <th class="px-6 py-4">Usuario</th>
                    <th class="px-6 py-4">Acción</th>
                    <th class="px-6 py-4">Módulo</th>
                    <th class="px-6 py-4">Detalles</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700">
                @forelse($audits as $audit)
                <tr class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4 text-slate-300">{{ $audit->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-6 py-4 text-brand-secondary font-medium">
                        {{ $audit->user ? $audit->user->name : 'Sistema' }}
                        <div class="text-[10px] text-slate-500">{{ $audit->ip_address }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest
                            {{ $audit->event === 'created' ? 'bg-emerald-500/20 text-emerald-400' : 
                               ($audit->event === 'updated' ? 'bg-blue-500/20 text-blue-400' : 
                               ($audit->event === 'deleted' ? 'bg-red-500/20 text-red-400' : 'bg-slate-700 text-slate-300')) }}">
                            {{ $audit->event }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-slate-400 text-xs">
                        {{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}
                    </td>
                    <td class="px-6 py-4">
                        <button onclick="toggleDetails({{ $audit->id }})" class="text-brand-secondary hover:text-white transition-colors text-xs font-bold flex items-center">
                            VER CAMBIOS
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </td>
                </tr>
                <!-- Details Row (Hidden by default) -->
                <tr id="details-{{ $audit->id }}" class="hidden bg-slate-900/50">
                    <td colspan="5" class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($audit->old_values)
                            <div>
                                <h4 class="text-xs font-bold text-red-400 mb-2 uppercase tracking-widest">Valores Anteriores</h4>
                                <pre class="bg-black/50 p-4 rounded-xl text-xs font-mono text-slate-300 overflow-x-auto border border-red-500/20">@json($audit->old_values, JSON_PRETTY_PRINT)</pre>
                            </div>
                            @endif
                            @if($audit->new_values)
                            <div>
                                <h4 class="text-xs font-bold text-emerald-400 mb-2 uppercase tracking-widest">Nuevos Valores</h4>
                                <pre class="bg-black/50 p-4 rounded-xl text-xs font-mono text-slate-300 overflow-x-auto border border-emerald-500/20">@json($audit->new_values, JSON_PRETTY_PRINT)</pre>
                            </div>
                            @endif
                        </div>
                        <div class="mt-4 text-[10px] text-slate-500 font-mono">User Agent: {{ $audit->user_agent }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-500 italic">No hay registros de auditoría.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">
    {{ $audits->links() }}
</div>
@endsection

@section('scripts')
<script>
    function toggleDetails(id) {
        const row = document.getElementById('details-' + id);
        if (row.classList.contains('hidden')) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    }
</script>
@endsection
