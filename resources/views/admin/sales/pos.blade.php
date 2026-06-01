@extends('layouts.intranet')

@section('title', 'Punto de Venta (POS) | Gourmetica Intranet')

@section('content')
<div class="h-[calc(100vh-100px)] flex flex-col md:flex-row gap-6 -mt-4 text-white overflow-hidden" id="pos-root" x-data="posApp()">
    
    <!-- Left Section: Products & Filters & Tables Map -->
    <div class="flex-1 flex flex-col bg-[#0F172A] rounded-2xl border border-slate-800 overflow-hidden p-6 space-y-4">
        
        <!-- POS Header & Search & Headquarter Selector -->
        <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
            <div>
                <h1 class="text-2xl font-serif font-bold text-brand-secondary flex items-center">
                    <span class="w-2.5 h-6 rounded bg-brand-secondary mr-2"></span>
                    Terminal Punto de Venta (POS)
                </h1>
                <p class="text-xs text-slate-400">Atención rápida, gestión de mesas y facturación directa en tienda.</p>
            </div>
            
            <div class="flex flex-1 max-w-md items-center gap-3">
                <!-- Sede selection (Read-only if Sede Admin or Cashier, otherwise selector) -->
                @if(auth('admin')->user()->isSedeAdmin() || auth('admin')->user()->isCajero())
                    <input type="hidden" id="pos-headquarter-id" value="{{ auth('admin')->user()->headquarter_id }}">
                    <div class="bg-slate-800/80 px-4 py-2.5 rounded-xl border border-slate-700 text-xs font-bold text-slate-300">
                        Sede: {{ auth('admin')->user()->headquarter->name }}
                    </div>
                @else
                    <select id="pos-headquarter-id" @change="changeHq()" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-2 text-xs font-bold text-slate-200 outline-none focus:border-brand-primary">
                        @foreach($headquarters as $hq)
                            <option value="{{ $hq->id }}">{{ $hq->name }}</option>
                        @endforeach
                    </select>
                @endif

                <!-- Search Input -->
                <div class="relative flex-1">
                    <input type="text" x-model="searchQuery" placeholder="Buscar producto (ej: Cheesecake)..." class="w-full bg-slate-800 border border-slate-700 rounded-xl pl-10 pr-4 py-2.5 text-xs text-slate-100 placeholder-slate-500 outline-none focus:border-brand-primary transition-all">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Restaurant Tables Map / Plano de Mesas -->
        <div class="bg-slate-900/60 border border-slate-800/80 rounded-2xl p-4 space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-xs uppercase font-extrabold text-brand-secondary tracking-wider flex items-center">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 animate-ping"></span>
                    Estado de Mesas en Salón
                </span>
                <span class="text-[10px] text-slate-400">Haz clic en una mesa ocupada (roja) para cargar y cobrar su precuenta.</span>
            </div>
            <div class="grid grid-cols-5 sm:grid-cols-10 gap-2">
                <!-- Takeaway Option -->
                <button 
                    @click="tableNumber = 'Llevar'; activeOrderId = null; clearCart();" 
                    :class="tableNumber === 'Llevar' ? 'bg-brand-secondary border-brand-secondary text-brand-dark' : 'bg-slate-800/60 border-slate-700 text-slate-400 hover:text-white'"
                    class="p-2.5 rounded-xl border text-center transition-all flex flex-col justify-center items-center text-xs font-bold"
                >
                    🛍️ Llevar
                </button>
                <!-- Physical Tables 1-10 -->
                <template x-for="n in 10" :key="n">
                    <button 
                        @click="handleTableClick('Mesa ' + n)" 
                        :class="getTableClass('Mesa ' + n)"
                        class="p-2.5 rounded-xl border text-center transition-all flex flex-col justify-center items-center relative overflow-hidden group min-h-[44px]"
                    >
                        <span class="text-xs font-black block" x-text="'Mesa ' + n"></span>
                        <span class="text-[9px] font-bold opacity-80 block mt-0.5" x-text="getTableStatusLabel('Mesa ' + n)"></span>
                        <!-- Cancel Precuenta Button if Occupied -->
                        <span 
                            x-show="isTableOccupied('Mesa ' + n)" 
                            @click.stop="cancelTablePreorder('Mesa ' + n)"
                            class="absolute top-0.5 right-1 w-3.5 h-3.5 text-[8px] text-red-400 hover:text-white font-extrabold cursor-pointer z-10 flex items-center justify-center bg-slate-900/80 rounded-full hover:bg-red-500"
                            title="Liberar Mesa sin Pagar"
                        >✕</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Category Horizontal Slider -->
        <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar border-b border-slate-800/80">
            <button @click="selectedCategory = 'all'" :class="selectedCategory === 'all' ? 'bg-brand-secondary text-brand-dark font-extrabold' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-800 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
                Todos
            </button>
            @foreach($categories as $category)
                <button @click="selectedCategory = '{{ $category->id }}'" :class="selectedCategory === '{{ $category->id }}' ? 'bg-brand-secondary text-brand-dark font-extrabold' : 'bg-slate-800/60 text-slate-400 hover:bg-slate-800 hover:text-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all whitespace-nowrap">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        <!-- Products Grid -->
        <div class="flex-1 overflow-y-auto no-scrollbar pr-2">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="product in filteredProducts()" :key="product.id">
                    <div @click="handleProductClick(product)" class="bg-[#1E293B] rounded-2xl border border-slate-800 hover:border-brand-secondary/40 transition-all cursor-pointer overflow-hidden group flex flex-col justify-between shadow-lg">
                        <div>
                            <!-- Product Image or Placeholder -->
                            <div class="h-32 bg-slate-800 relative overflow-hidden flex items-center justify-center">
                                <template x-if="product.image">
                                    <img :src="'/storage/' + product.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </template>
                                <template x-if="!product.image">
                                    <svg class="w-10 h-10 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </template>
                                <!-- Stock Badge -->
                                <span class="absolute top-2 right-2 px-2 py-0.5 text-[9px] font-extrabold rounded-md bg-slate-900/90 text-brand-secondary border border-brand-secondary/20" x-text="getStockLabel(product)"></span>
                            </div>
                            <!-- Product Details -->
                            <div class="p-4 space-y-1">
                                <p class="text-[10px] text-brand-secondary font-bold uppercase tracking-wider" x-text="product.category.name"></p>
                                <h4 class="font-bold text-white text-xs group-hover:text-brand-secondary transition-colors line-clamp-1" x-text="product.name"></h4>
                                <p class="text-slate-400 text-[10px] line-clamp-2" x-text="product.description || 'Sin descripción'"></p>
                            </div>
                        </div>
                        <div class="p-4 pt-0 flex justify-between items-center mt-2">
                            <span class="font-serif font-bold text-brand-secondary text-sm" x-text="'S/ ' + parseFloat(product.base_price).toFixed(2)"></span>
                            <span class="w-8 h-8 rounded-lg bg-slate-800 group-hover:bg-brand-secondary group-hover:text-brand-dark text-brand-secondary flex items-center justify-center transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </span>
                        </div>
                    </div>
                </template>
            </div>
            
            <!-- Empty Search State -->
            <div x-show="filteredProducts().length === 0" class="text-center py-20 text-slate-500">
                <svg class="w-12 h-12 mx-auto mb-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-sm font-bold">No se encontraron productos coincidentes.</p>
                <p class="text-xs text-slate-600 mt-1">Prueba a buscar con otros términos.</p>
            </div>
        </div>
    </div>

    <!-- Right Section: Cart & Transaction Details -->
    <div class="w-full md:w-96 flex flex-col bg-[#1E293B] rounded-2xl border border-slate-800 overflow-hidden shadow-2xl">
        
        <!-- Active Cart Header with Info -->
        <div class="p-6 border-b border-slate-800 bg-slate-800/40 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-serif font-bold text-white text-lg flex items-center">
                    <svg class="w-5 h-5 text-brand-secondary mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    Detalle de Orden
                </h3>
                <button @click="clearCart()" class="text-[10px] text-red-400 hover:text-red-300 font-bold uppercase tracking-wider transition-all">Limpiar todo</button>
            </div>

            <!-- Table & Service Type selection -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1.5 font-mono">Ubicación Actual</label>
                    <div class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-extrabold" x-text="tableNumber === 'Llevar' ? '🛍️ Llevar' : '🪑 ' + tableNumber"></div>
                </div>

                <div>
                    <label class="block text-[10px] uppercase font-bold text-slate-400 mb-1.5">Comprobante</label>
                    <select x-model="documentType" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white outline-none focus:border-brand-primary">
                        <option value="03">Boleta de Venta</option>
                        <option value="01">Factura Electrónica</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Selected Items list -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6 space-y-4">
            <template x-for="(item, index) in cart" :key="index">
                <div class="flex gap-4 p-3 bg-slate-900/40 rounded-xl border border-slate-800/80 relative group">
                    <!-- Remove Button -->
                    <button @click="removeFromCart(index)" class="absolute top-2 right-2 text-slate-500 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                    
                    <!-- Item quantity counter -->
                    <div class="flex flex-col justify-between items-center bg-slate-900 rounded-lg p-1.5 h-full">
                        <button @click="increaseQty(index)" class="text-brand-secondary hover:text-white transition-colors text-xs font-bold">+</button>
                        <span class="text-xs font-bold text-white px-2" x-text="item.quantity"></span>
                        <button @click="decreaseQty(index)" class="text-brand-secondary hover:text-white transition-colors text-xs font-bold">-</button>
                    </div>

                    <!-- Item info -->
                    <div class="flex-1 space-y-1">
                        <h5 class="font-bold text-xs text-white line-clamp-1 pr-6" x-text="item.product.name"></h5>
                        <!-- Display options if selected -->
                        <template x-if="item.selectedOptions && Object.keys(item.selectedOptions).length > 0">
                            <div class="flex flex-wrap gap-1">
                                <template x-for="(val, grp) in item.selectedOptions" :key="grp">
                                    <span class="px-1.5 py-0.5 rounded text-[8px] bg-slate-800 text-slate-400 font-medium" x-text="grp + ': ' + val.value"></span>
                                </template>
                            </div>
                        </template>
                        <p class="text-brand-secondary text-xs font-bold font-serif" x-text="'S/ ' + (item.price * item.quantity).toFixed(2)"></p>
                    </div>
                </div>
            </template>
            
            <!-- Empty Cart State -->
            <div x-show="cart.length === 0" class="text-center py-24 text-slate-600 space-y-3">
                <svg class="w-12 h-12 mx-auto text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="text-xs font-bold text-slate-500">Agrega productos o selecciona una mesa ocupada.</p>
            </div>
        </div>

        <!-- Checkout, Totals & Payment action -->
        <div class="p-6 border-t border-slate-800 bg-slate-800/40 space-y-4">
            
            <!-- Client Identification Form -->
            <div class="space-y-3 p-4 bg-slate-900/60 rounded-xl border border-slate-800">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] uppercase font-bold text-slate-400" x-text="documentType === '01' ? 'Datos de Factura (RUC)' : 'Datos de Boleta (DNI)'"></span>
                    <button @click="showClientModal = true" class="text-[9px] text-brand-secondary font-bold hover:underline">Seleccionar Cliente</button>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <p class="text-xs font-bold text-white line-clamp-1" x-text="selectedCustomer ? selectedCustomer.name : 'Cliente Genérico'"></p>
                        <p class="text-[10px] text-slate-500" x-text="selectedCustomer ? selectedCustomer.email : 'cliente@gourmetica.com'"></p>
                    </div>
                </div>
            </div>

            <!-- Price Breakdown -->
            <div class="space-y-2 text-xs">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal</span>
                    <span x-text="'S/ ' + getSubtotal().toFixed(2)"></span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>IGV (18%)</span>
                    <span x-text="'S/ ' + getIgv().toFixed(2)"></span>
                </div>
                <div class="border-t border-slate-800 my-2"></div>
                <div class="flex justify-between items-center">
                    <span class="font-bold text-white">TOTAL</span>
                    <span class="text-2xl font-serif font-bold text-brand-secondary" x-text="'S/ ' + getTotal().toFixed(2)"></span>
                </div>
            </div>

            <!-- Action Buttons (Guardar Precuenta & Cobrar) -->
            <div class="grid grid-cols-2 gap-3">
                <button 
                    @click="savePreOrder()" 
                    :disabled="cart.length === 0 || tableNumber === 'Llevar' || processing"
                    class="py-3.5 bg-slate-800 border border-slate-700 hover:bg-slate-700 text-slate-300 font-extrabold text-[11px] rounded-xl hover:scale-[1.02] transition-all shadow-md flex items-center justify-center gap-1.5 disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    PRECUENTA
                </button>

                <button 
                    @click="processSale()" 
                    :disabled="cart.length === 0 || processing"
                    class="py-3.5 bg-brand-secondary text-brand-dark font-extrabold text-[11px] rounded-xl hover:scale-[1.02] transition-transform shadow-lg shadow-brand-secondary/15 flex items-center justify-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <template x-if="processing">
                        <span class="w-3.5 h-3.5 border-2 border-brand-dark border-t-transparent rounded-full animate-spin"></span>
                    </template>
                    <template x-if="!processing">
                        <span x-text="activeOrderId ? '💳 COBRAR MESA' : '💳 COBRAR'"></span>
                    </template>
                </button>
            </div>
        </div>
    </div>

    <!-- Dynamic Options Selection Overlay Modal -->
    <div x-show="showOptionsModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="w-full max-w-md bg-[#1E293B] rounded-3xl border border-slate-700 shadow-2xl p-6 space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="font-serif font-bold text-lg text-white" x-text="modalProduct?.name"></h3>
                    <p class="text-xs text-slate-400">Personaliza las opciones del producto</p>
                </div>
                <button @click="showOptionsModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="space-y-4 max-h-[300px] overflow-y-auto no-scrollbar">
                <template x-for="option in modalProduct?.options" :key="option.id">
                    <div class="space-y-2">
                        <label class="block text-xs uppercase font-extrabold text-slate-400 tracking-wider" x-text="option.name"></label>
                        <div class="grid grid-cols-2 gap-2">
                            <template x-for="val in option.values" :key="val.id">
                                <button @click="selectModalOption(option.name, val)" :class="isModalOptionSelected(option.name, val) ? 'bg-brand-secondary border-brand-secondary text-brand-dark font-bold' : 'bg-slate-900 border-slate-700 text-slate-300 hover:bg-slate-800'" class="p-3 border rounded-xl text-left text-xs transition-all flex justify-between items-center">
                                    <span x-text="val.value"></span>
                                    <span class="text-[10px]" x-text="parseFloat(val.price_modifier) >= 0 ? '+' + parseFloat(val.price_modifier).toFixed(2) : parseFloat(val.price_modifier).toFixed(2)"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            <div class="border-t border-slate-700 my-4"></div>

            <div class="flex justify-between items-center">
                <div>
                    <p class="text-[10px] text-slate-500 uppercase font-bold">Precio Calculado</p>
                    <p class="text-xl font-serif font-bold text-brand-secondary" x-text="'S/ ' + getModalProductPrice().toFixed(2)"></p>
                </div>
                <button @click="addModalProductToCart()" class="px-8 py-3.5 bg-brand-secondary text-brand-dark font-extrabold rounded-xl text-xs hover:scale-105 transition-transform">
                    AGREGAR A ORDEN
                </button>
            </div>
        </div>
    </div>

    <!-- Client Selection List Modal -->
    <div x-show="showClientModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" style="display: none;">
        <div class="w-full max-w-md bg-[#1E293B] rounded-3xl border border-slate-700 shadow-2xl p-6 space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="font-serif font-bold text-lg text-white">Seleccionar Cliente</h3>
                <button @click="showClientModal = false" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Client Search input -->
            <input type="text" x-model="clientSearchQuery" placeholder="Buscar cliente por nombre o RUC/DNI..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 outline-none focus:border-brand-primary">

            <!-- Clients List scrollable -->
            <div class="space-y-2 max-h-[250px] overflow-y-auto no-scrollbar pr-1">
                <div @click="selectCustomer(null)" :class="selectedCustomer === null ? 'bg-brand-secondary border-brand-secondary text-brand-dark' : 'bg-slate-900/50 border-slate-800 text-slate-300 hover:bg-slate-800'" class="p-3 border rounded-xl cursor-pointer text-xs transition-colors flex justify-between items-center">
                    <div>
                        <p class="font-bold">Cliente Genérico</p>
                        <p class="text-[10px] opacity-60">Consumidor Final (DNI: 00000000)</p>
                    </div>
                    <span x-show="selectedCustomer === null" class="w-2 h-2 rounded-full bg-brand-dark"></span>
                </div>
                
                <template x-for="cust in filteredCustomers()" :key="cust.id">
                    <div @click="selectCustomer(cust)" :class="selectedCustomer?.id === cust.id ? 'bg-brand-secondary border-brand-secondary text-brand-dark' : 'bg-slate-900/50 border-slate-800 text-slate-300 hover:bg-slate-800'" class="p-3 border rounded-xl cursor-pointer text-xs transition-colors flex justify-between items-center">
                        <div>
                            <p class="font-bold" x-text="cust.name"></p>
                            <p class="text-[10px] opacity-60" x-text="cust.email"></p>
                        </div>
                        <span x-show="selectedCustomer?.id === cust.id" class="w-2 h-2 rounded-full bg-brand-dark"></span>
                    </div>
                </template>
            </div>
            
            <button @click="showClientModal = false" class="w-full py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold rounded-xl text-xs transition-colors">
                Cerrar
            </button>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function posApp() {
        return {
            products: @json($products),
            categories: @json($categories),
            customers: @json($customers),
            defaultCustomer: @json($defaultCustomer),
            pendingOrders: @json($pendingOrders),
            
            searchQuery: '',
            clientSearchQuery: '',
            selectedCategory: 'all',
            tableNumber: 'Llevar',
            documentType: '03', // Boleta (03) default
            
            cart: [],
            selectedCustomer: null,
            activeOrderId: null,
            processing: false,
            
            // Modal states
            showOptionsModal: false,
            modalProduct: null,
            modalSelectedOptions: {},
            
            showClientModal: false,
            
            init() {
                // Initialize with generic customer
                this.selectedCustomer = null;
                this.activeOrderId = null;
            },
            
            filteredProducts() {
                let list = this.products;
                
                // Filter by category
                if (this.selectedCategory !== 'all') {
                    list = list.filter(p => p.category_id == this.selectedCategory);
                }
                
                // Filter by search query
                if (this.searchQuery.trim() !== '') {
                    const q = this.searchQuery.toLowerCase();
                    list = list.filter(p => p.name.toLowerCase().includes(q) || (p.description && p.description.toLowerCase().includes(q)));
                }
                
                return list;
            },
            
            filteredCustomers() {
                if (this.clientSearchQuery.trim() === '') return this.customers;
                const q = this.clientSearchQuery.toLowerCase();
                return this.customers.filter(c => c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q));
            },
            
            getStockLabel(product) {
                const hqId = document.getElementById('pos-headquarter-id').value;
                const hq = product.headquarters.find(h => h.id == hqId);
                return hq ? `${hq.pivot.stock} und` : '0 und';
            },
            
            isTableOccupied(tableName) {
                return !!this.pendingOrders[tableName];
            },
            
            getTableStatusLabel(tableName) {
                return this.isTableOccupied(tableName) ? 'Ocupado' : 'Libre';
            },
            
            getTableClass(tableName) {
                const isSelected = this.tableNumber === tableName;
                const isOccupied = this.isTableOccupied(tableName);
                
                if (isSelected) {
                    return 'bg-brand-secondary border-brand-secondary text-brand-dark scale-[1.03] shadow-lg font-black';
                }
                if (isOccupied) {
                    return 'bg-red-500/10 border-red-500/40 text-red-400 hover:bg-red-500/20';
                }
                return 'bg-slate-800/60 border-slate-700 text-slate-400 hover:border-slate-500 hover:text-white';
            },
            
            handleTableClick(tableName) {
                this.tableNumber = tableName;
                
                if (this.isTableOccupied(tableName)) {
                    const order = this.pendingOrders[tableName];
                    this.activeOrderId = order.order_id;
                    this.selectedCustomer = order.customer;
                    
                    // Load items to cart
                    this.cart = order.items.map(item => {
                        return {
                            product: item.product,
                            price: parseFloat(item.price),
                            quantity: parseInt(item.quantity),
                            selectedOptions: item.options || {}
                        };
                    });
                    
                    Swal.fire({
                        title: 'Mesa Ocupada',
                        text: `Precuenta de la ${tableName} cargada. Puedes agregar más productos o proceder a cobrar.`,
                        icon: 'info',
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 2000,
                        background: '#1E293B',
                        color: '#F8FAFC'
                    });
                } else {
                    // Clicked an empty table
                    this.cart = [];
                    this.activeOrderId = null;
                    this.selectedCustomer = null;
                    
                    Swal.fire({
                        title: 'Mesa Libre',
                        text: `${tableName} seleccionada. Registra una nueva precuenta.`,
                        icon: 'success',
                        toast: true,
                        position: 'bottom-end',
                        showConfirmButton: false,
                        timer: 1500,
                        background: '#1E293B',
                        color: '#F8FAFC'
                    });
                }
            },
            
            async savePreOrder() {
                if (this.cart.length === 0 || this.tableNumber === 'Llevar') return;
                
                this.processing = true;
                const hqId = document.getElementById('pos-headquarter-id').value;
                const customerId = this.selectedCustomer ? this.selectedCustomer.id : this.defaultCustomer.id;
                
                const itemsRequest = this.cart.map(item => {
                    return {
                        product_id: item.product.id,
                        quantity: item.quantity,
                        price: item.price,
                        options: item.selectedOptions
                    };
                });
                
                try {
                    const response = await fetch('{{ route("admin.pos.pre_order") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            headquarter_id: hqId,
                            user_id: customerId,
                            table_number: this.tableNumber,
                            total: this.getTotal(),
                            items: itemsRequest
                        })
                    });
                    
                    const data = await response.json();
                    this.processing = false;
                    
                    if (data.success) {
                        this.pendingOrders = data.pending_orders;
                        Swal.fire({
                            title: 'Mesa Ocupada',
                            text: `La precuenta de la ${this.tableNumber} ha sido guardada.`,
                            icon: 'success',
                            confirmButtonColor: '#E2B182',
                            background: '#1E293B',
                            color: '#F8FAFC'
                        });
                        this.clearCart();
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'No se pudo guardar la precuenta.',
                            icon: 'error',
                            confirmButtonColor: '#E2B182',
                            background: '#1E293B',
                            color: '#F8FAFC'
                        });
                    }
                } catch (error) {
                    this.processing = false;
                    Swal.fire({
                        title: 'Error de Red',
                        text: 'No se pudo guardar la precuenta.',
                        icon: 'error',
                        confirmButtonColor: '#E2B182',
                        background: '#1E293B',
                        color: '#F8FAFC'
                    });
                }
            },
            
            async cancelTablePreorder(tableName) {
                const order = this.pendingOrders[tableName];
                if (!order) return;
                
                const result = await Swal.fire({
                    title: '¿Liberar Mesa?',
                    text: `¿Estás seguro de liberar la ${tableName} y borrar su precuenta?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#EF4444',
                    cancelButtonColor: '#475569',
                    confirmButtonText: 'Sí, liberar',
                    cancelButtonText: 'Cancelar',
                    background: '#1E293B',
                    color: '#F8FAFC'
                });
                
                if (result.isConfirmed) {
                    try {
                        const response = await fetch('{{ route("admin.pos.cancel_order") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                order_id: order.order_id
                            })
                        });
                        
                        const data = await response.json();
                        if (data.success) {
                            this.pendingOrders = data.pending_orders;
                            if (this.tableNumber === tableName) {
                                this.clearCart();
                                this.activeOrderId = null;
                            }
                            Swal.fire({
                                title: 'Mesa Liberada',
                                text: `La ${tableName} ahora está disponible.`,
                                icon: 'success',
                                confirmButtonColor: '#E2B182',
                                background: '#1E293B',
                                color: '#F8FAFC'
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo liberar la mesa.',
                            icon: 'error',
                            confirmButtonColor: '#E2B182',
                            background: '#1E293B',
                            color: '#F8FAFC'
                        });
                    }
                }
            },
            
            handleProductClick(product) {
                if (product.options && product.options.length > 0) {
                    this.modalProduct = product;
                    this.modalSelectedOptions = {};
                    // Preselect first value of each option group
                    product.options.forEach(opt => {
                        if (opt.values && opt.values.length > 0) {
                            this.modalSelectedOptions[opt.name] = opt.values[0];
                        }
                    });
                    this.showOptionsModal = true;
                } else {
                    this.addToCart(product, product.base_price, {});
                }
            },
            
            selectModalOption(groupName, valueObj) {
                this.modalSelectedOptions[groupName] = valueObj;
            },
            
            isModalOptionSelected(groupName, valueObj) {
                return this.modalSelectedOptions[groupName]?.id === valueObj.id;
            },
            
            getModalProductPrice() {
                if (!this.modalProduct) return 0;
                let price = parseFloat(this.modalProduct.base_price);
                Object.values(this.modalSelectedOptions).forEach(val => {
                    price += parseFloat(val.price_modifier);
                });
                return price;
            },
            
            addModalProductToCart() {
                const finalPrice = this.getModalProductPrice();
                this.addToCart(this.modalProduct, finalPrice, JSON.parse(JSON.stringify(this.modalSelectedOptions)));
                this.showOptionsModal = false;
            },
            
            addToCart(product, price, selectedOptions) {
                // Find if exactly identical item (same product and same options selection) is already in cart
                const existingIndex = this.cart.findIndex(item => {
                    if (item.product.id !== product.id) return false;
                    return JSON.stringify(item.selectedOptions) === JSON.stringify(selectedOptions);
                });
                
                if (existingIndex !== -1) {
                    this.cart[existingIndex].quantity++;
                } else {
                    this.cart.push({
                        product: product,
                        price: parseFloat(price),
                        quantity: 1,
                        selectedOptions: selectedOptions
                    });
                }
                
                // Alert Toast
                Swal.fire({
                    title: 'Agregado',
                    text: `${product.name} agregado a la orden`,
                    icon: 'success',
                    toast: true,
                    position: 'bottom-end',
                    showConfirmButton: false,
                    timer: 1500,
                    background: '#1E293B',
                    color: '#F8FAFC'
                });
            },
            
            removeFromCart(index) {
                this.cart.splice(index, 1);
            },
            
            increaseQty(index) {
                this.cart[index].quantity++;
            },
            
            decreaseQty(index) {
                if (this.cart[index].quantity > 1) {
                    this.cart[index].quantity--;
                } else {
                    this.removeFromCart(index);
                }
            },
            
            clearCart() {
                this.cart = [];
                this.activeOrderId = null;
                this.selectedCustomer = null;
            },
            
            selectCustomer(cust) {
                this.selectedCustomer = cust;
                this.showClientModal = false;
            },
            
            getSubtotal() {
                return this.getTotal() / 1.18;
            },
            
            getIgv() {
                return this.getTotal() - this.getSubtotal();
            },
            
            getTotal() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            },
            
            async processSale() {
                if (this.cart.length === 0) return;
                
                this.processing = true;
                const hqId = document.getElementById('pos-headquarter-id').value;
                const customerId = this.selectedCustomer ? this.selectedCustomer.id : this.defaultCustomer.id;
                
                // Formulate items request array
                const itemsRequest = this.cart.map(item => {
                    return {
                        product_id: item.product.id,
                        quantity: item.quantity,
                        price: item.price,
                        options: Object.entries(item.selectedOptions).map(([grp, val]) => {
                            return {
                                group: grp,
                                value: val.value,
                                price: parseFloat(item.product.base_price) + parseFloat(val.price_modifier)
                            };
                        })
                    };
                });
                
                try {
                    const response = await fetch('{{ route("admin.pos.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            headquarter_id: hqId,
                            user_id: customerId,
                            document_type: this.documentType,
                            table_number: this.tableNumber,
                            total: this.getTotal(),
                            items: itemsRequest,
                            order_id: this.activeOrderId
                        })
                    });
                    
                    const data = await response.json();
                    this.processing = false;
                    
                    if (data.success) {
                        this.pendingOrders = data.pending_orders;
                        Swal.fire({
                            title: '¡Venta Realizada!',
                            html: `
                                <div class="text-left space-y-3">
                                    <p class="text-sm">La orden de la <strong>${this.tableNumber}</strong> ha sido facturada exitosamente.</p>
                                    <div class="p-3 bg-slate-900/60 border border-slate-700/60 rounded-xl">
                                        <p class="text-xs"><strong>SUNAT Estado:</strong> <span class="px-2 py-0.5 rounded text-[10px] font-bold ${data.sunat_status === 'ACEPTADO' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'}">${data.sunat_status}</span></p>
                                        <p class="text-[10px] text-slate-400 mt-2"><strong>SUNAT Respuesta:</strong> ${data.sunat_response || 'Pendiente'}</p>
                                    </div>
                                </div>
                            `,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#E2B182',
                            cancelButtonColor: '#475569',
                            confirmButtonText: '🖨️ Imprimir Ticket',
                            cancelButtonText: 'Nueva Orden',
                            background: '#1E293B',
                            color: '#F8FAFC'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(data.ticket_url, '_blank');
                            }
                            this.clearCart();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: data.message || 'No se pudo procesar la venta.',
                            icon: 'error',
                            confirmButtonColor: '#E2B182',
                            background: '#1E293B',
                            color: '#F8FAFC'
                        });
                    }
                } catch (error) {
                    this.processing = false;
                    Swal.fire({
                        title: 'Error de Red',
                        text: 'Hubo un error de conexión con el servidor. Inténtalo de nuevo.',
                        icon: 'error',
                        confirmButtonColor: '#E2B182',
                        background: '#1E293B',
                        color: '#F8FAFC'
                    });
                }
            },

            changeHq() {
                // If headquarters changes, reload page or reset state
                const hqId = document.getElementById('pos-headquarter-id').value;
                window.location.reload();
            }
        };
    }
</script>

<style>
    /* Premium no-scrollbar utility */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
@endsection
