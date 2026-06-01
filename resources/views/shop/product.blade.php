@extends('layouts.app')

@section('title', $product->name . ' | Gourmetica')

@section('content')
<div class="bg-brand-bg min-h-screen py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
            <!-- Product Image -->
            <div class="relative">
                <div class="aspect-square rounded-3xl overflow-hidden shadow-2xl border border-gray-100 bg-white group flex items-center justify-center">
                    @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    @else
                        <svg class="w-24 h-24 text-gray-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    @endif
                </div>
                <!-- Badges -->
                <div class="absolute top-6 left-6 flex flex-col gap-2">
                    <span class="px-4 py-1 bg-brand-secondary text-white text-[10px] font-bold tracking-widest rounded-full shadow-lg">
                        {{ strtoupper($product->category->name) }}
                    </span>
                </div>
            </div>

            <!-- Product Details -->
            <div class="flex flex-col justify-center">
                <h1 class="text-5xl font-serif font-bold text-brand-primary mb-4 leading-tight">{{ $product->name }}</h1>
                <div class="flex items-center mb-6">
                    <div class="flex text-yellow-400">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <span class="ml-3 text-gray-500 text-sm">(24 reseñas de clientes)</span>
                </div>

                <p class="text-gray-600 text-lg mb-8 leading-relaxed">
                    {{ $product->description }}
                </p>

                <div class="mb-10">
                    <span class="text-4xl font-serif font-bold text-brand-primary" id="display-price">
                        S/ {{ number_format($product->base_price, 2) }}
                    </span>
                    <span class="text-gray-400 text-sm ml-2" id="price-label">Precio</span>
                </div>

                <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="base_price" id="base_price" value="{{ $product->base_price }}">

                    @foreach($product->options as $option)
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">
                            Seleccionar {{ $option->name }}
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            @foreach($option->values as $value)
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="options[{{ $option->id }}]" value="{{ $value->id }}" 
                                       data-price="{{ $value->price_modifier }}"
                                       class="peer sr-only" 
                                       required
                                       {{ $loop->first ? 'checked' : '' }}
                                       onchange="updateTotalPrice()">
                                <div class="px-5 py-4 border-2 border-gray-100 rounded-2xl bg-white transition-all group-hover:border-brand-secondary/30 peer-checked:border-brand-secondary peer-checked:bg-brand-secondary/5">
                                    <div class="flex justify-between items-center w-full">
                                        <span class="text-sm font-bold text-brand-primary">{{ $value->value }}</span>
                                        <span class="text-xs font-bold text-brand-secondary">
                                            S/ {{ number_format($product->base_price + $value->price_modifier, 2) }}
                                        </span>
                                    </div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach

                    <div class="flex gap-4 mt-12">
                        <div class="flex items-center border-2 border-gray-100 rounded-2xl px-4 bg-white">
                            <button type="button" onclick="changeQty(-1)" class="p-2 text-gray-400 hover:text-brand-primary">-</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" class="w-12 text-center font-bold text-brand-primary bg-transparent border-none outline-none focus:ring-0">
                            <button type="button" onclick="changeQty(1)" class="p-2 text-gray-400 hover:text-brand-primary">+</button>
                        </div>
                        <button type="submit" class="flex-1 btn-premium py-5 text-lg shadow-xl shadow-brand-primary/10">
                            AÑADIR AL CARRITO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function changeQty(delta) {
        const input = document.getElementById('quantity');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        input.value = val;
        updateTotalPrice();
    }

    function updateTotalPrice() {
        const basePrice = parseFloat(document.getElementById('base_price').value);
        const qty = parseInt(document.getElementById('quantity').value);
        let additional = 0;
        let hasOptions = false;
        
        document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            additional += parseFloat(radio.dataset.price || 0);
            hasOptions = true;
        });

        const total = (basePrice + additional) * qty;
        document.getElementById('display-price').innerText = 'S/ ' + total.toFixed(2);
        
        const label = document.getElementById('price-label');
        if (label) {
            label.innerText = hasOptions ? 'Total' : 'Precio';
        }
    }

    // Initialize price
    document.addEventListener('DOMContentLoaded', updateTotalPrice);
</script>
@endpush
@endsection
