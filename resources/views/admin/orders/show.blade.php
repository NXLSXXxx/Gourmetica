@extends('layouts.intranet')

@section('title', 'Detalles del Pedido #' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . ' | Gourmetica Intranet')

@section('content')
<div class="max-w-5xl mx-auto">
    <header class="flex flex-col md:flex-row md:justify-between md:items-center mb-12 gap-4">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="text-slate-400 hover:text-white transition-colors flex items-center mb-4 text-sm font-semibold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver a Pedidos
            </a>
            <h1 class="text-3xl font-serif font-bold text-white tracking-tight flex items-center">
                Pedido #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                <span class="ml-4 text-xs px-3 py-1.5 rounded-full font-sans font-bold tracking-wide uppercase 
                    @if($order->status === 'delivered') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                    @elseif($order->status === 'cancelled') bg-red-500/10 text-red-400 border border-red-500/20
                    @elseif($order->status === 'pending') bg-amber-500/10 text-amber-400 border border-amber-500/20
                    @else bg-blue-500/10 text-blue-400 border border-blue-500/20 @endif">
                    @if($order->status === 'pending') Pendiente
                    @elseif($order->status === 'preparing') Preparando
                    @elseif($order->status === 'shipped') Enviado
                    @elseif($order->status === 'delivered') Entregado
                    @else Cancelado @endif
                </span>
            </h1>
            <p class="text-slate-400 mt-2">Registrado el {{ $order->created_at->format('d/m/Y H:i A') }}</p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <a href="{{ route('admin.orders.ticket', ['id' => $order->id, 'type' => 'boleta']) }}" target="_blank" class="px-5 py-3 rounded-xl bg-brand-secondary text-brand-dark font-bold text-sm flex items-center hover:scale-105 transition-transform shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                IMPRIMIR BOLETA
            </a>
            <a href="{{ route('admin.orders.comanda', $order->id) }}" target="_blank" class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm flex items-center hover:scale-105 transition-transform shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                IMPRIMIR COMANDA
            </a>
        </div>
    </header>

    @if(session('success'))
        <div class="mb-8 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Order Stats and Controls (left 2 cols on big screen) -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- Products Card -->
            <div class="bg-brand-primary border border-slate-700 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 bg-slate-800/40 border-b border-slate-700">
                    <h2 class="text-lg font-serif font-bold text-white">Detalle de Productos</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-800/20 text-slate-400 text-xs uppercase tracking-wider">
                                <th class="px-6 py-4">Producto</th>
                                <th class="px-6 py-4 text-center">Cant.</th>
                                <th class="px-6 py-4 text-right">Unitario</th>
                                <th class="px-6 py-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700 text-slate-300">
                            @foreach($order->items as $item)
                            <tr>
                                <td class="px-6 py-5">
                                    <span class="font-medium text-white block">{{ $item->product->name }}</span>
                                    @if($item->options)
                                        <div class="mt-1.5 space-y-0.5 text-xs text-slate-500 font-mono">
                                            @foreach($item->options as $key => $val)
                                                <span>• {{ strtoupper($key) }}: {{ strtoupper($val) }}</span><br>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center font-bold text-white">{{ $item->quantity }}</td>
                                <td class="px-6 py-5 text-right">S/ {{ number_format($item->price, 2) }}</td>
                                <td class="px-6 py-5 text-right font-bold text-white">S/ {{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="bg-slate-800/40 px-6 py-5 border-t border-slate-700 flex justify-between items-center">
                    <span class="text-sm font-semibold text-slate-400 uppercase tracking-wider">Monto Total</span>
                    <span class="text-2xl font-bold text-white">S/ {{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <!-- Notes or Info -->
            @if($order->notes)
            <div class="bg-brand-primary border border-slate-700 p-6 rounded-2xl shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-3">Notas Especiales del Cliente</h3>
                <p class="text-slate-200 leading-relaxed italic bg-slate-800/45 p-4 rounded-xl border border-slate-700">
                    "{{ $order->notes }}"
                </p>
            </div>
            @endif

            <!-- Comanda & Boleta/Factura Gourmetica Official Templates Card -->
            <div class="bg-brand-primary border border-slate-700 rounded-2xl shadow-xl overflow-hidden" x-data="{ docTab: 'comanda' }">
                <div class="px-6 py-5 bg-slate-800/40 border-b border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-serif font-bold text-white flex items-center">
                            <span class="text-brand-secondary mr-2">📋</span>
                            Formatos Comanda y Boleta / Factura
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Plantillas oficiales para producción en cocina, despacho y envío directo por WhatsApp</p>
                    </div>
                    <div class="flex items-center gap-1.5 bg-slate-900/60 p-1.5 rounded-xl border border-slate-700">
                        <button type="button" @click="docTab = 'comanda'" :class="docTab === 'comanda' ? 'bg-brand-secondary text-brand-dark font-bold' : 'text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-lg text-xs transition-all">
                            📋 Comanda
                        </button>
                        <button type="button" @click="docTab = 'boleta'" :class="docTab === 'boleta' ? 'bg-brand-secondary text-brand-dark font-bold' : 'text-slate-400 hover:text-white'" class="px-3 py-1.5 rounded-lg text-xs transition-all">
                            🧾 Boleta / Factura
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Tab 1: Comanda -->
                    <div x-show="docTab === 'comanda'" class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center">
                                <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                                Formato Comanda (Cocina & Despacho)
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="copyDocumentText('comanda-text', 'comanda-btn-text')" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-colors flex items-center shadow">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                    <span id="comanda-btn-text">Copiar Comanda</span>
                                </button>
                                <a href="https://wa.me/?text={{ urlencode($order->getFormattedComandaText()) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#25D366] hover:bg-[#128C7E] text-white text-xs font-bold transition-colors flex items-center shadow">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    Enviar a WhatsApp
                                </a>
                                <a href="{{ route('admin.orders.comanda', $order->id) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-bold transition-colors flex items-center">
                                    🖨️ Ticket Térmico
                                </a>
                            </div>
                        </div>
                        <pre id="comanda-text" class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-4 text-xs font-mono text-slate-200 whitespace-pre-wrap leading-relaxed select-all">{{ $order->getFormattedComandaText() }}</pre>
                    </div>

                    <!-- Tab 2: Boleta / Factura -->
                    <div x-show="docTab === 'boleta'" class="space-y-4" style="display: none;">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center">
                                <span class="w-2 h-2 rounded-full bg-amber-500 mr-2"></span>
                                Formato Boleta o Factura (Con Logo Gourmetica)
                            </span>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="copyDocumentText('boleta-text', 'boleta-btn-text')" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold transition-colors flex items-center shadow">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                                    <span id="boleta-btn-text">Copiar Boleta</span>
                                </button>
                                @php
                                    $targetWa = preg_replace('/[^0-9]/', '', $order->phone ?: ($order->user->phone ?? ''));
                                    $waUrl = $targetWa ? "https://wa.me/{$targetWa}?text=" . urlencode($order->getFormattedInvoiceText()) : "https://wa.me/?text=" . urlencode($order->getFormattedInvoiceText());
                                @endphp
                                <a href="{{ $waUrl }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-[#25D366] hover:bg-[#128C7E] text-white text-xs font-bold transition-colors flex items-center shadow">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                    Enviar al Cliente
                                </a>
                                <a href="{{ route('admin.orders.ticket', ['id' => $order->id, 'type' => 'boleta']) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-amber-500/20 text-amber-300 hover:bg-amber-500 hover:text-black border border-amber-500/30 text-xs font-bold transition-colors flex items-center">
                                    🧾 Ticket Boleta
                                </a>
                            </div>
                        </div>
                        <pre id="boleta-text" class="bg-slate-900/90 border border-slate-700/80 rounded-xl p-4 text-xs font-mono text-slate-200 whitespace-pre-wrap leading-relaxed select-all">{{ $order->getFormattedInvoiceText() }}</pre>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar Information Details (right 1 col) -->
        <div class="space-y-8">
            
            <!-- Update Status Card -->
            <div class="bg-brand-primary border border-slate-700 p-6 rounded-2xl shadow-xl">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">Actualizar Estado</h3>
                <form action="{{ route('admin.orders.update_status', $order->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    
                    <select name="status" class="w-full bg-slate-800 border border-slate-600 rounded-lg px-4 py-3 text-white focus:border-brand-primary outline-none transition-all cursor-pointer">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="preparing" {{ $order->status == 'preparing' ? 'selected' : '' }}>Preparando</option>
                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Enviado</option>
                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Entregado</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>

                    <button type="submit" class="w-full py-3 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-600 text-white font-semibold text-xs uppercase tracking-wider transition-colors">
                        Guardar Estado
                    </button>
                </form>
            </div>

            <!-- Customer Info -->
            <div class="bg-brand-primary border border-slate-700 p-6 rounded-2xl shadow-xl space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Detalles del Cliente</h3>
                
                <div class="border-t border-slate-700/60 pt-4 space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 block">Nombre</span>
                        <span class="text-white font-semibold">{{ $order->customer_name ?: ($order->user->name ?? 'Cliente Anónimo') }}</span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Correo electrónico</span>
                        <span class="text-slate-300 font-mono text-xs">{{ $order->email ?: ($order->user->email ?? 'Sin correo') }}</span>
                    </div>
                    @if($order->phone)
                    <div>
                        <span class="text-xs text-slate-500 block">Teléfono de contacto</span>
                        <span class="text-slate-300 font-mono text-xs">{{ $order->phone }}</span>
                    </div>
                    @endif
                    @if($order->invoice_type)
                    <div class="mt-4 pt-3 border-t border-slate-700/60">
                        <span class="text-xs text-slate-500 block">Comprobante Solicitado</span>
                        <span class="text-white font-semibold uppercase">{{ $order->invoice_type == 'ninguno' ? 'Sin comprobante (Ticket)' : $order->invoice_type }}</span>
                        @if($order->invoice_document)
                        <span class="text-slate-400 text-xs ml-2">Doc: {{ $order->invoice_document }}</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Info -->
            <div class="bg-brand-primary border border-slate-700 p-6 rounded-2xl shadow-xl space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Detalles de Entrega</h3>
                
                <div class="border-t border-slate-700/60 pt-4 space-y-3 text-sm">
                    <div>
                        <span class="text-xs text-slate-500 block">Sede Preparadora</span>
                        <span class="text-white font-semibold flex items-center">
                            <span class="w-2 h-2 rounded-full bg-brand-secondary mr-2"></span>
                            {{ $order->headquarter->name }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-slate-500 block">Dirección de Envío</span>
                        <span class="text-slate-300">{{ $order->address }}</span>
                    </div>
                    @if($order->payment_method)
                    <div>
                        <span class="text-xs text-slate-500 block">Método de Pago</span>
                        <span class="text-slate-300 font-semibold uppercase tracking-wide">{{ $order->payment_method }}</span>
                    </div>
                    @endif
                    
                    @if($order->delivery_date || $order->delivery_time)
                    <div class="mt-4 pt-3 border-t border-slate-700/60 bg-amber-500/5 p-3 rounded-xl border border-amber-500/10">
                        <span class="text-xs text-amber-500/80 font-bold uppercase tracking-wider block mb-1">Horario Programado</span>
                        <span class="text-amber-400 font-bold text-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') : 'Hoy' }} - {{ $order->delivery_time ?: 'Lo antes posible' }}
                        </span>
                    </div>
                    @endif
                    
                    @if($order->is_gift || $order->has_card || $order->has_dedication)
                    <div class="mt-4 pt-3 border-t border-slate-700/60 space-y-2">
                        <span class="text-xs text-pink-400 font-bold uppercase tracking-wider block mb-1 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path></svg>
                            Extras & Regalo
                        </span>
                        @if($order->is_gift)
                            <span class="px-2 py-1 bg-pink-500/20 text-pink-300 text-xs rounded border border-pink-500/30 inline-block mr-1">🎁 Es Regalo (No enviar boleta)</span>
                        @endif
                        @if($order->has_card)
                            <span class="px-2 py-1 bg-purple-500/20 text-purple-300 text-xs rounded border border-purple-500/30 inline-block mr-1">💌 Incluir Tarjeta Física</span>
                        @endif
                        @if($order->has_dedication)
                            <span class="px-2 py-1 bg-blue-500/20 text-blue-300 text-xs rounded border border-blue-500/30 inline-block">✍️ Dedicatoria en Torta</span>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyDocumentText(elemId, btnTextId) {
        const text = document.getElementById(elemId).innerText;
        navigator.clipboard.writeText(text).then(function() {
            const btnText = document.getElementById(btnTextId);
            if (btnText) {
                const originalText = btnText.innerText;
                btnText.innerText = '¡Copiado! ✓';
                setTimeout(() => {
                    btnText.innerText = originalText;
                }, 2500);
            }
        }).catch(function(err) {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand("copy");
            document.body.removeChild(textArea);
            alert("¡Texto copiado al portapapeles!");
        });
    }
</script>
@endsection
