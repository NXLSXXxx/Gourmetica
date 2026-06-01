@extends('layouts.intranet')

@section('title', 'Editar Producto | Gourmetica Intranet')

@section('content')
<div class="min-h-screen bg-[#0F172A] text-white p-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-12">
            <a href="{{ route('admin.products.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver al listado
            </a>
            <h1 class="text-3xl font-serif font-bold text-brand-secondary">Editar Producto</h1>
            <p class="text-slate-400 mt-2">Actualiza los detalles de <strong>{{ $product->name }}</strong>.</p>
        </header>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Basic Info -->
            <div class="bg-[#1E293B] p-8 rounded-2xl border border-slate-700 shadow-xl">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold flex items-center">
                        <span class="w-8 h-8 rounded-full bg-brand-primary/20 text-brand-primary flex items-center justify-center mr-3 text-sm">1</span>
                        Información Básica
                    </h2>
                    <label class="flex items-center cursor-pointer">
                        <span class="mr-3 text-sm font-medium text-slate-400">Estado Activo</span>
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-primary"></div>
                        </div>
                    </label>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Nombre del Producto</label>
                        <input type="text" name="name" value="{{ $product->name }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary focus:ring-1 focus:ring-brand-primary outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Categoría</label>
                        <select name="category_id" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-2">Precio Base (S/)</label>
                        <input type="number" step="0.01" name="base_price" value="{{ $product->base_price }}" required class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Imagen Actual</label>
                        @if($product->image)
                        <div class="mb-4 relative w-32 h-32">
                            <img src="{{ asset('storage/' . $product->image) }}" class="w-full h-full object-cover rounded-xl border border-slate-600 shadow-lg">
                            <span class="absolute -top-2 -right-2 bg-brand-primary text-white text-[10px] font-bold px-2 py-1 rounded-full uppercase">Actual</span>
                        </div>
                        @endif
                        <label class="block text-sm font-medium text-slate-400 mb-2">Cambiar Imagen</label>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-600 border-dashed rounded-xl cursor-pointer bg-slate-800/50 hover:bg-slate-800 transition-all">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-6 h-6 mb-2 text-slate-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                    </svg>
                                    <p class="text-xs text-slate-400">Click para reemplazar imagen</p>
                                </div>
                                <input type="file" name="image" class="hidden" accept="image/*" />
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-slate-400 mb-2">Descripción</label>
                        <textarea name="description" rows="4" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all">{{ $product->description }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Multi-Sede Stock -->
            <div class="bg-[#1E293B] p-8 rounded-2xl border border-slate-700 shadow-xl">
                <h2 class="text-xl font-bold mb-6 flex items-center">
                    <span class="w-8 h-8 rounded-full bg-brand-accent/20 text-brand-accent flex items-center justify-center mr-3 text-sm">2</span>
                    Disponibilidad por Sede
                </h2>
                
                <div class="space-y-6">
                    @foreach($headquarters as $hq)
                    @php
                        $pivot = $product->headquarters->find($hq->id)->pivot ?? null;
                    @endphp
                    <div class="p-6 bg-slate-800/50 rounded-xl border border-slate-700 flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <h3 class="font-bold text-white">{{ $hq->name }}</h3>
                            <p class="text-xs text-slate-500">{{ $hq->address }}</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Stock</label>
                                <input type="number" name="headquarters[{{ $hq->id }}][stock]" value="{{ $pivot ? $pivot->stock : 0 }}" min="0" class="w-24 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase font-bold text-slate-500 mb-1">Precio Específico</label>
                                <input type="number" step="0.01" name="headquarters[{{ $hq->id }}][price]" value="{{ $pivot ? $pivot->price : '' }}" class="w-32 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white outline-none" placeholder="S/ 0.00">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Opciones y Adicionales (Toppings, Tamaños, etc.) -->
            <div class="bg-[#1E293B] p-8 rounded-2xl border border-slate-700 shadow-xl space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold flex items-center text-brand-primary">
                        <span class="w-8 h-8 rounded-full bg-brand-primary/20 text-brand-primary flex items-center justify-center mr-3 text-sm">3</span>
                        Opciones y Adicionales (Toppings, Tamaños, etc.)
                    </h2>
                </div>
                <p class="text-xs text-slate-400 -mt-2">Define los grupos de personalización para este producto (ej. "Tamaño", "Toppings"). Para simplificar la administración, ingresa el **Precio Final Absoluto** que pagará el cliente (ej. S/ 10.00 o S/ 45.00) y el sistema calculará automáticamente la diferencia con el precio base.</p>

                <div id="options-container" class="space-y-6">
                    @foreach($product->options as $optIndex => $option)
                    <div class="option-block p-6 bg-slate-800/30 border border-slate-700/80 rounded-2xl space-y-4 relative" data-index="{{ $optIndex }}">
                        <button type="button" onclick="removeOptionBlock(this)" class="absolute top-6 right-6 text-red-400 hover:text-red-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nombre del Grupo (ej: Tamaño, Toppings, Sabor)</label>
                            <input type="text" name="options[{{ $optIndex }}][name]" value="{{ $option->name }}" required class="w-full md:w-1/2 bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white outline-none focus:border-brand-primary">
                        </div>

                        <div class="values-container space-y-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Opciones / Adicionales Individuales</label>
                            @foreach($option->values as $valIndex => $value)
                            <div class="value-row flex flex-col md:flex-row items-center gap-4 bg-slate-900/50 p-4 rounded-xl border border-slate-700/50">
                                <div class="flex-1 w-full">
                                    <label class="block text-[10px] text-slate-500 uppercase mb-1">Nombre de Opción (ej: Porción, Entera, Fresas)</label>
                                    <input type="text" name="options[{{ $optIndex }}][values][{{ $valIndex }}][value]" value="{{ $value->value }}" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white outline-none">
                                </div>
                                <div class="w-full md:w-48">
                                    <label class="block text-[10px] text-slate-500 uppercase mb-1">Precio Final Absoluto (S/)</label>
                                    <input type="number" step="0.01" name="options[{{ $optIndex }}][values][{{ $valIndex }}][price]" value="{{ number_format($product->base_price + $value->price_modifier, 2, '.', '') }}" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white outline-none">
                                </div>
                                <button type="button" onclick="removeValueRow(this)" class="mt-5 text-slate-400 hover:text-red-400 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            @endforeach
                        </div>

                        <button type="button" onclick="addValueRow({{ $optIndex }}, this)" class="text-xs text-brand-primary hover:underline font-bold flex items-center mt-2">
                            + Agregar opción / adicional individual
                        </button>
                    </div>
                    @endforeach
                </div>

                <button type="button" onclick="addOptionBlock()" class="mt-4 px-6 py-3 rounded-xl bg-slate-800 border border-slate-700 text-slate-300 font-bold text-xs hover:bg-slate-700 hover:text-white transition-colors">
                    + AGREGAR GRUPO DE OPCIONES
                </button>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="btn-premium px-12 py-4 text-lg">
                    ACTUALIZAR PRODUCTO
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let optionIndex = {{ $product->options->count() }};

    function addOptionBlock() {
        const container = document.getElementById('options-container');
        const html = `
            <div class="option-block p-6 bg-slate-800/30 border border-slate-700/80 rounded-2xl space-y-4 relative mb-6" data-index="${optionIndex}">
                <button type="button" onclick="removeOptionBlock(this)" class="absolute top-6 right-6 text-red-400 hover:text-red-500 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nombre del Grupo (ej: Tamaño, Toppings, Sabor)</label>
                    <input type="text" name="options[${optionIndex}][name]" required class="w-full md:w-1/2 bg-slate-900 border border-slate-700 rounded-lg px-4 py-2.5 text-white outline-none focus:border-brand-primary" placeholder="ej: Toppings">
                </div>

                <div class="values-container space-y-3">
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Opciones / Adicionales Individuales</label>
                    <div class="value-row flex flex-col md:flex-row items-center gap-4 bg-slate-900/50 p-4 rounded-xl border border-slate-700/50">
                        <div class="flex-1 w-full">
                            <label class="block text-[10px] text-slate-500 uppercase mb-1">Nombre de Opción (ej: Porción, Entera, Fresas)</label>
                            <input type="text" name="options[${optionIndex}][values][0][value]" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white outline-none" placeholder="ej: Fresas">
                        </div>
                        <div class="w-full md:w-48">
                            <label class="block text-[10px] text-slate-500 uppercase mb-1">Precio Final Absoluto (S/)</label>
                            <input type="number" step="0.01" name="options[${optionIndex}][values][0][price]" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white outline-none" placeholder="0.00">
                        </div>
                        <button type="button" onclick="removeValueRow(this)" class="mt-5 text-slate-400 hover:text-red-400 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="button" onclick="addValueRow(${optionIndex}, this)" class="text-xs text-brand-primary hover:underline font-bold flex items-center mt-2">
                    + Agregar opción / adicional individual
                </button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        optionIndex++;
    }

    function addValueRow(optIndex, buttonEl) {
        const block = buttonEl.closest('.option-block');
        const valuesContainer = block.querySelector('.values-container');
        const valIndex = valuesContainer.querySelectorAll('.value-row').length;
        
        const html = `
            <div class="value-row flex flex-col md:flex-row items-center gap-4 bg-slate-900/50 p-4 rounded-xl border border-slate-700/50 mt-3" style="margin-top: 12px;">
                <div class="flex-1 w-full">
                    <label class="block text-[10px] text-slate-500 uppercase mb-1">Nombre de Opción (ej: Porción, Entera, Fresas)</label>
                    <input type="text" name="options[${optIndex}][values][${valIndex}][value]" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white outline-none">
                </div>
                <div class="w-full md:w-48">
                    <label class="block text-[10px] text-slate-500 uppercase mb-1">Precio Final Absoluto (S/)</label>
                    <input type="number" step="0.01" name="options[${optIndex}][values][${valIndex}][price]" required class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-white outline-none">
                </div>
                <button type="button" onclick="removeValueRow(this)" class="mt-5 text-slate-400 hover:text-red-400 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;
        valuesContainer.insertAdjacentHTML('beforeend', html);
    }

    function removeOptionBlock(buttonEl) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas eliminar este grupo de opciones y todos sus adicionales?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#475569',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#1E293B',
            color: '#F8FAFC'
        }).then((result) => {
            if (result.isConfirmed) {
                buttonEl.closest('.option-block').remove();
            }
        });
    }

    function removeValueRow(buttonEl) {
        const row = buttonEl.closest('.value-row');
        const container = row.closest('.values-container');
        if (container.querySelectorAll('.value-row').length > 1) {
            row.remove();
        } else {
            Swal.fire({
                title: 'Atención',
                text: 'Debe haber al menos una opción o adicional individual en este grupo.',
                icon: 'info',
                confirmButtonColor: '#E2B182',
                background: '#1E293B',
                color: '#F8FAFC'
            });
        }
    }
</script>
@endsection
