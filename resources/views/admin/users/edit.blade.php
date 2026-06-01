@extends('layouts.intranet')

@section('title', 'Editar Administrador | Gourmetica Intranet')

@section('content')
<div class="max-w-2xl mx-auto">
    <header class="mb-12">
        <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-4 text-sm font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al listado
        </a>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Editar Administrador</h1>
        <p class="text-slate-400 mt-2">Modifica los datos de la cuenta o actualiza sus accesos.</p>
    </header>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-8 bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
        @csrf
        @method('PATCH')
        
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">Nombre Completo</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all" placeholder="Ej: Nelson Solis">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">Correo Electrónico</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all font-mono" placeholder="nelson@gourmetica.pe">
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">Nueva Contraseña (Opcional)</label>
            <input type="password" name="password" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all" placeholder="Dejar en blanco para mantener la actual">
            <p class="mt-2 text-xs text-slate-500">Mínimo 8 caracteres. Solo complétalo si deseas cambiar su clave de acceso.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-slate-400 mb-2">Rol / Permisos</label>
                <select name="role_id" id="role_id" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all cursor-pointer">
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" data-slug="{{ $role->slug }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div id="headquarter_container" class="hidden">
                <label class="block text-sm font-medium text-slate-400 mb-2">Sede Asignada</label>
                <select name="headquarter_id" id="headquarter_id" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all cursor-pointer">
                    <option value="" disabled>Selecciona la sede...</option>
                    @foreach($headquarters as $headquarter)
                        <option value="{{ $headquarter->id }}" {{ old('headquarter_id', $user->headquarter_id) == $headquarter->id ? 'selected' : '' }}>{{ $headquarter->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-12 py-4 rounded-xl bg-brand-secondary text-brand-dark font-bold hover:scale-105 transition-transform shadow-lg">
                GUARDAR CAMBIOS
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role_id');
        const headquarterContainer = document.getElementById('headquarter_container');
        const headquarterSelect = document.getElementById('headquarter_id');

        function checkRole() {
            const selectedOption = roleSelect.options[roleSelect.selectedIndex];
            if (!selectedOption) return;
            
            const slug = selectedOption.getAttribute('data-slug');
            
            if (slug === 'admin_sede' || slug === 'cajero') {
                headquarterContainer.classList.remove('hidden');
                headquarterSelect.setAttribute('required', 'required');
            } else {
                headquarterContainer.classList.add('hidden');
                headquarterSelect.removeAttribute('required');
                headquarterSelect.value = '';
            }
        }

        roleSelect.addEventListener('change', checkRole);
        
        // Run immediately to load initial state
        checkRole();
    });
</script>
@endsection
