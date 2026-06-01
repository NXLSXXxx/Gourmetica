@extends('layouts.intranet')

@section('title', 'Nuevo Banner | Gourmetica Intranet')

@section('content')
<header class="mb-10">
    <a href="{{ route('admin.banners.index') }}" class="text-brand-secondary text-sm font-bold flex items-center mb-4 hover:underline">
        ← VOLVER AL LISTADO
    </a>
    <h1 class="text-3xl font-serif font-bold text-white tracking-tight">Nuevo Banner</h1>
</header>

<div class="max-w-4xl">
    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="bg-[#1E293B] rounded-2xl border border-slate-700 shadow-xl p-8">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="col-span-2">
                <label class="block text-slate-400 text-sm font-bold mb-2">Imagen del Banner (Recomendado: 1200x600px)</label>
                <input type="file" name="image" required class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none">
            </div>

            <div>
                <label class="block text-slate-400 text-sm font-bold mb-2">Título</label>
                <input type="text" name="title" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none" placeholder="Ej: ¡¡quien dijo yo!!">
            </div>

            <div>
                <label class="block text-slate-400 text-sm font-bold mb-2">Subtítulo</label>
                <input type="text" name="subtitle" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none" placeholder="Ej: 4 porciones de tus favoritas">
            </div>

            <div>
                <label class="block text-slate-400 text-sm font-bold mb-2">Texto del Botón</label>
                <input type="text" name="button_text" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none" placeholder="Ej: pide aquí">
            </div>

            <div>
                <label class="block text-slate-400 text-sm font-bold mb-2">URL del Botón</label>
                <input type="text" name="button_url" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none" placeholder="Ej: /shop">
            </div>

            <div>
                <label class="block text-slate-400 text-sm font-bold mb-2">Tipo de Banner</label>
                <select name="type" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none">
                    <option value="hero">Banner Principal (Hero - Parte superior)</option>
                    <option value="promo">Banner Promocional (Cuerpo de la página)</option>
                    <option value="service_catering">Imagen: Servicio Catering</option>
                    <option value="service_corporate">Imagen: Pedidos Corporativos</option>
                    <option value="section_locations">Imagen: Fondo Nuestras Casas</option>
                </select>
            </div>

            <div>
                <label class="block text-slate-400 text-sm font-bold mb-2">Orden de visualización</label>
                <input type="number" name="order_index" value="0" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-4 py-3 text-white focus:border-brand-secondary outline-none">
            </div>

            <div class="col-span-2 flex items-center gap-2 py-4">
                <input type="checkbox" name="is_active" value="1" checked id="is_active" class="w-5 h-5 rounded border-slate-600 text-brand-secondary focus:ring-brand-secondary bg-slate-800">
                <label for="is_active" class="text-white font-bold">Banner Activo</label>
            </div>
        </div>

        <div class="mt-8 pt-8 border-t border-slate-700 flex justify-end">
            <button type="submit" class="px-10 py-4 bg-brand-secondary text-brand-dark font-black rounded-xl hover:scale-105 transition-transform">
                GUARDAR BANNER
            </button>
        </div>
    </form>
</div>
@endsection
