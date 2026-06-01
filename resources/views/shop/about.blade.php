@extends('layouts.app')

@section('title', 'Nuestra Historia | Gourmetica')

@section('content')
<!-- Hero Story -->
<section class="relative py-32 bg-brand-primary text-white overflow-hidden">
    <div class="absolute inset-0 opacity-10 bg-brand-secondary"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="text-brand-secondary font-bold tracking-widest uppercase mb-4 block">Desde el Corazón</span>
        <h1 class="text-5xl md:text-7xl font-serif font-bold mb-8">Nuestra Historia</h1>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto leading-relaxed">
            Gourmetica nació de un sueño: elevar la pastelería tradicional peruana a un nivel de arte, fusionando ingredientes locales con técnicas europeas de vanguardia.
        </p>
    </div>
</section>

<!-- Values Section -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-24 items-center">
            <div>
                <h2 class="text-4xl font-serif font-bold text-brand-primary mb-8">El Compromiso con la Calidad</h2>
                <div class="space-y-8">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-brand-primary/10 text-brand-primary font-bold">1</div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-brand-primary">Ingredientes Honestos</h3>
                            <p class="mt-2 text-gray-500">Seleccionamos personalmente cada fruto, grano de cacao y harina de pequeños productores locales que comparten nuestra visión de respeto por la tierra.</p>
                        </div>
                    </div>
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-12 w-12 rounded-full bg-brand-primary/10 text-brand-primary font-bold">2</div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-brand-primary">Procesos Lentos</h3>
                            <p class="mt-2 text-gray-500">En Gourmetica no hay atajos. Respetamos los tiempos de fermentación natural y los procesos de horneado tradicionales para garantizar texturas y sabores únicos.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="relative">
                <div class="aspect-[4/3] bg-brand-primary/5 rounded-3xl shadow-2xl relative z-10 flex items-center justify-center">
                    <svg class="w-20 h-20 text-brand-primary/10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="absolute -bottom-10 -right-10 w-full h-full border-4 border-brand-secondary rounded-3xl -z-10"></div>
            </div>
        </div>
    </div>
</section>

<!-- Philosophy Section -->
<section class="py-24 bg-brand-bg relative overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-4xl font-serif font-bold text-brand-primary mb-12">Nuestra Filosofía</h2>
        <p class="text-2xl font-serif italic text-gray-600 leading-relaxed">
            "No solo hacemos postres; creamos momentos de felicidad pura. Creemos que la pastelería saludable es posible sin sacrificar el placer absoluto del sabor."
        </p>
        <div class="mt-12 h-px w-24 bg-brand-secondary mx-auto"></div>
        <p class="mt-8 font-bold text-brand-primary uppercase tracking-widest text-sm">El Equipo Gourmetica</p>
    </div>
</section>
@endsection
