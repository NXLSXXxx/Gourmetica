@extends('layouts.intranet')

@section('title', 'Nueva Categoría | Gourmetica Intranet')

@section('content')
<div class="max-w-2xl mx-auto">
    <header class="mb-12">
        <a href="{{ route('admin.categories.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-4 text-sm font-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Volver al listado
        </a>
        <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Nueva Categoría</h1>
        <p class="text-slate-400 mt-2">Crea una nueva categoría para organizar tus productos.</p>
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

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8 bg-brand-primary p-8 rounded-2xl border border-slate-700 shadow-xl">
        @csrf
        
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">Nombre de la Categoría</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all" placeholder="Ej: Tortas de Boda">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">URL Slug (Identificador)</label>
            <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all" placeholder="ej-tortas-boda">
            <p class="mt-2 text-xs text-slate-500">Se usará en la URL de la tienda (minúsculas, sin espacios).</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-400 mb-2">Imagen de Portada (Opcional)</label>
            <input type="file" name="image" id="image" accept="image/*" class="w-full text-slate-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-secondary/15 file:text-brand-secondary hover:file:bg-brand-secondary/25 cursor-pointer file:cursor-pointer">
            <p class="mt-2 text-xs text-slate-500">Formatos recomendados: JPG, PNG o WEBP. Máx: 1MB.</p>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-12 py-4 rounded-xl bg-brand-secondary text-brand-dark font-bold hover:scale-105 transition-transform shadow-lg">
                GUARDAR CATEGORÍA
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');

        nameInput.addEventListener('input', function() {
            const name = this.value;
            const slug = name.toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '') // remove accents
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '-');
            slugInput.value = slug;
        });
    });
</script>
@endsection
