@extends('layouts.intranet')

@section('title', 'Pedidos en Vivo | Gourmetica Intranet')
@section('container_class', 'w-full max-w-full h-full')
@section('main_padding', 'p-0')

@section('content')
<div class="flex h-[calc(100vh-4rem)] lg:h-[calc(100vh-0rem)] w-full m-0 relative overflow-hidden">
    <!-- Main Kanban Board -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-900 overflow-hidden">
        <header class="px-6 py-4 bg-slate-800 border-b border-slate-700 flex justify-between items-center z-10 shrink-0">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                    Pedidos en Vivo
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-primary opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-primary"></span>
                    </span>
                </h1>
                <p class="text-slate-400 text-sm mt-1">Sede activa: <span class="text-white font-medium">{{ $headquarters->where('id', $hqId)->first()->name ?? 'N/A' }}</span></p>
            </div>
            <div class="flex gap-3">
                <form action="{{ route('admin.live_orders.index') }}" method="GET" class="flex gap-2 items-center" id="hqForm">
                    <select name="hq_id" class="bg-slate-700 border border-slate-600 rounded-lg px-3 py-2 text-sm text-white focus:ring-2 focus:ring-brand-primary outline-none" onchange="document.getElementById('hqForm').submit()">
                        @foreach($headquarters as $hq)
                            <option value="{{ $hq->id }}" {{ $hqId == $hq->id ? 'selected' : '' }}>{{ $hq->name }}</option>
                        @endforeach
                    </select>
                </form>
                <button onclick="toggleQuickSale()" class="bg-brand-primary hover:bg-brand-primary/90 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors shadow-lg shadow-brand-primary/20 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Compra Rápida
                </button>
            </div>
        </header>

        <!-- Kanban Columns -->
        <div class="flex-1 overflow-x-auto overflow-y-hidden p-6 flex gap-6" id="kanbanBoard">
            <!-- Loading State -->
            <div class="w-full flex justify-center items-center h-64 text-slate-500">Cargando pedidos...</div>
        </div>
    </div>

    <!-- Quick Sale Sidebar -->
    <div id="quickSaleSidebar" class="absolute top-0 right-0 h-full z-50 w-full sm:w-96 bg-slate-800 shadow-2xl border-l border-slate-700 flex flex-col transform transition-transform duration-300 translate-x-full">
        <div class="px-6 py-4 border-b border-slate-700 flex justify-between items-center shrink-0">
            <h2 class="font-bold text-white text-lg">Nueva Compra Rápida</h2>
            <button onclick="toggleQuickSale()" class="text-slate-400 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        
        <div class="p-4 border-b border-slate-700">
            <input type="text" id="productSearch" placeholder="Buscar producto..." class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-primary outline-none">
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3" id="productList">
            @foreach($products as $product)
            <div class="product-item bg-slate-900/50 p-3 rounded-xl border border-slate-700 flex justify-between items-center gap-3 cursor-pointer hover:border-brand-primary/50 transition-colors" data-name="{{ htmlspecialchars(strtolower($product->name)) }}" onclick="addToCart({{ $product->id }}, {{ json_encode($product->name) }}, {{ $product->base_price }})">
                <div>
                    <h4 class="font-medium text-white text-sm">{{ $product->name }}</h4>
                    <span class="text-white font-bold text-sm">S/ {{ number_format($product->base_price, 2) }}</span>
                </div>
                <button class="bg-slate-800 hover:bg-slate-700 text-white rounded p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
            </div>
            @endforeach
        </div>

        <div class="bg-slate-900 border-t border-slate-700 flex flex-col shrink-0 max-h-[65%]">
            <div class="p-4 overflow-y-auto flex-1 no-scrollbar flex flex-col">
                <h3 class="text-white font-medium mb-3 shrink-0">Orden Actual</h3>
                <div class="shrink-0 space-y-2 mb-4 min-h-[50px]" id="cartItems">
                    <!-- Cart items here -->
                </div>
                
                <div class="shrink-0 space-y-4 mt-auto">
                    <div class="flex justify-between items-center text-lg">
                        <span class="text-slate-400">Total</span>
                        <span class="font-bold text-white" id="cartTotal">S/ 0.00</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="order_type" value="salon" class="peer sr-only" checked onchange="toggleOrderType()">
                            <div class="text-center px-3 py-2 rounded border border-slate-600 text-slate-400 peer-checked:bg-brand-primary/20 peer-checked:border-brand-primary peer-checked:text-brand-primary text-sm transition-colors">Salón (Rápida)</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="order_type" value="whatsapp" class="peer sr-only" onchange="toggleOrderType()">
                            <div class="text-center px-3 py-2 rounded border border-slate-600 text-slate-400 peer-checked:bg-brand-primary/20 peer-checked:border-brand-primary peer-checked:text-brand-primary text-sm transition-colors">WhatsApp</div>
                        </label>
                    </div>

                    <div id="whatsappFields" class="hidden space-y-3">
                        <input type="text" id="waName" placeholder="Nombre Cliente" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-primary outline-none">
                        <input type="text" id="waPhone" placeholder="Teléfono" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-primary outline-none">
                        
                        <div class="relative">
                            <input type="text" id="waAddress" placeholder="Dirección de Envío" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white focus:border-brand-primary outline-none">
                            <span id="waAddressStatus" class="absolute right-3 top-2 text-xs text-brand-primary hidden">Calculando...</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-sm text-slate-400 w-1/2">Costo de Envío:</span>
                            <input type="number" step="0.10" id="waDeliveryPrice" placeholder="0.00" class="w-1/2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white font-bold focus:outline-none" readonly>
                        </div>
                    </div>

                    <div id="documentFields" class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="document_type" value="03" class="peer sr-only" checked>
                            <div class="text-center px-3 py-2 rounded border border-slate-600 text-slate-400 peer-checked:bg-brand-primary/20 peer-checked:border-brand-primary peer-checked:text-brand-primary text-sm transition-colors">Boleta</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="document_type" value="01" class="peer sr-only">
                            <div class="text-center px-3 py-2 rounded border border-slate-600 text-slate-400 peer-checked:bg-brand-primary/20 peer-checked:border-brand-primary peer-checked:text-brand-primary text-sm transition-colors">Factura</div>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="p-4 border-t border-slate-800 shrink-0">
                <button onclick="processQuickSale()" id="btnCobrar" class="w-full bg-brand-primary hover:bg-brand-primary/90 text-white rounded-lg py-3 font-bold transition-colors disabled:opacity-50">
                    Cobrar y Entregar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Status (hidden by default) -->
<div id="statusModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center hidden backdrop-blur-sm">
    <div class="bg-slate-800 rounded-xl border border-slate-700 shadow-2xl w-full max-w-sm p-6">
        <h3 class="text-xl font-bold text-white mb-4">Actualizar Estado</h3>
        <p class="text-slate-400 text-sm mb-6" id="modalOrderName"></p>
        
        <input type="hidden" id="modalOrderId">
        
        <div class="space-y-2" id="statusButtons">
            <!-- Buttons injected by JS -->
        </div>
        
        <button onclick="closeStatusModal()" class="mt-6 w-full py-2 text-slate-400 hover:text-white transition-colors">Cancelar</button>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXo-Lpvk7_PQ6KnL4XjxA5ux1pHsopYTk&libraries=places&callback=initGoogleMapLiveOrders&loading=async" async defer></script>
<script>
    const API_URL = '{{ route('admin.live_orders.index') }}';
    const UPDATE_URL = '{{ route('admin.live_orders.update_status') }}';
    const CANCEL_URL = '{{ route('admin.live_orders.cancel') }}';
    const STORE_URL = '{{ route('admin.live_orders.store') }}';
    const CALCULATE_DELIVERY_URL = '{{ route('checkout.calculate_delivery') }}';
    const CSRF_TOKEN = '{{ csrf_token() }}';
    const HQ_ID = '{{ $hqId }}';
    const DEFAULT_CUSTOMER_ID = '{{ $defaultCustomer->id ?? 1 }}';

    let cart = [];
    let autocomplete = null;

    function initGoogleMapLiveOrders() {
        const input = document.getElementById('waAddress');
        if (!input) return;

        autocomplete = new google.maps.places.Autocomplete(input, {
            componentRestrictions: { country: 'pe' }
        });

        autocomplete.addListener('place_changed', () => {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();
            
            calculateDeliveryPrice(lat, lng);
        });
    }

    function calculateDeliveryPrice(lat, lng) {
        const statusEl = document.getElementById('waAddressStatus');
        const priceEl = document.getElementById('waDeliveryPrice');
        
        statusEl.classList.remove('hidden');
        priceEl.value = '';

        fetch(`${CALCULATE_DELIVERY_URL}?latitude=${lat}&longitude=${lng}&headquarter_id=${HQ_ID}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && !data.fuera_de_chiclayo) {
                    priceEl.value = parseFloat(data.price).toFixed(2);
                } else {
                    alert('La dirección seleccionada está fuera de cobertura.');
                    priceEl.value = '0.00';
                }
            })
            .catch(err => {
                alert('Error al calcular el costo de envío.');
                priceEl.value = '0.00';
            })
            .finally(() => {
                statusEl.classList.add('hidden');
                renderCart(); // Update total just in case
            });
    }

    const COLUMNS = [
        { id: 'pending', title: 'Novedad', color: 'bg-blue-500' },
        { id: 'preparing', title: 'Preparando', color: 'bg-orange-500' },
        { id: 'shipped', title: 'Listo para entregar', color: 'bg-purple-500' },
        { id: 'delivered', title: 'Entregado', color: 'bg-emerald-500' }
    ];

    const NEXT_STATUS = {
        'pending': [{val: 'preparing', label: 'Empezar Preparación', class: 'bg-orange-500 hover:bg-orange-600'}],
        'preparing': [{val: 'shipped', label: 'Marcar como Listo', class: 'bg-purple-500 hover:bg-purple-600'}],
        'shipped': [{val: 'delivered', label: 'Entregar a Cliente', class: 'bg-emerald-500 hover:bg-emerald-600'}],
        'delivered': []
    };

    function fetchOrders() {
        fetch(`${API_URL}?hq_id=${HQ_ID}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => renderKanban(data))
        .catch(err => console.error(err));
    }

    function renderKanban(data) {
        const board = document.getElementById('kanbanBoard');
        board.innerHTML = '';

        COLUMNS.forEach(col => {
            const orders = data[col.id] || [];
            
            const colEl = document.createElement('div');
            colEl.className = 'flex-1 min-w-[300px] flex flex-col bg-slate-900/50 rounded-xl border border-slate-700/50';
            
            colEl.innerHTML = `
                <div class="p-4 border-b border-slate-700/50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full ${col.color}"></div>
                        <h2 class="font-bold text-slate-300">${col.title}</h2>
                    </div>
                    <span class="bg-slate-800 text-slate-400 text-xs px-2 py-1 rounded-full font-bold">${orders.length}</span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    ${orders.map(order => createOrderCard(order, col.id)).join('')}
                    ${orders.length === 0 ? `<div class="text-center text-slate-600 py-8 text-sm border-2 border-dashed border-slate-700 rounded-xl">No hay pedidos</div>` : ''}
                </div>
            `;
            board.appendChild(colEl);
        });
    }

    function createOrderCard(order, status) {
        let itemsHtml = order.items.map(i => `<div class="text-sm text-slate-400 flex justify-between"><span>${i.quantity}x ${i.product_name}</span></div>`).join('');
        
        return `
            <div class="bg-slate-800 border border-slate-700 rounded-xl p-4 shadow-lg cursor-pointer hover:border-slate-500 transition-colors" onclick="openStatusModal(${order.order_id}, '${order.customer_name}', '${status}', ${order.is_delivery})">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-xs font-bold text-slate-500">#${String(order.order_id).padStart(5, '0')}</span>
                        <h3 class="font-bold text-white leading-tight mt-1">${order.customer_name}</h3>
                    </div>
                    <span class="text-xs text-slate-500 bg-slate-900 px-2 py-1 rounded">${order.time_ago}</span>
                </div>
                <div class="space-y-1 mb-4">
                    ${itemsHtml}
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-slate-700">
                    <span class="text-xs ${order.payment_status === 'paid' ? 'text-emerald-400' : 'text-orange-400'}">${order.payment_status === 'paid' ? 'Pagado' : 'Por pagar'}</span>
                    <span class="font-bold text-brand-primary">S/ ${order.total.toFixed(2)}</span>
                </div>
            </div>
        `;
    }

    function openStatusModal(orderId, customerName, currentStatus, isDelivery = false) {
        document.getElementById('modalOrderId').value = orderId;
        document.getElementById('modalOrderName').innerHTML = `
            Pedido de ${customerName} 
            <a href="/shop/tracking/${orderId}" target="_blank" class="ml-2 text-brand-primary hover:underline text-xs bg-brand-primary/10 px-2 py-1 rounded-full"><i class="fas fa-external-link-alt mr-1"></i>Ver Seguimiento</a>
        `;
        
        const btnContainer = document.getElementById('statusButtons');
        btnContainer.innerHTML = '';

        const actions = NEXT_STATUS[currentStatus] || [];
        
        actions.forEach(action => {
            if (action.val === 'delivered' && isDelivery) {
                btnContainer.innerHTML += `<div class="text-center text-xs text-slate-400 py-2 border border-slate-700/50 rounded-lg mb-2">Este pedido será marcado automáticamente por Nakama Delivery.</div>`;
                btnContainer.innerHTML += `<button onclick="updateStatus('${action.val}')" class="w-full text-slate-400 font-bold py-2 rounded-lg border border-slate-600 hover:bg-slate-700 transition-colors text-sm">Forzar Entrega Manual</button>`;
                return;
            }
            btnContainer.innerHTML += `<button onclick="updateStatus('${action.val}')" class="w-full text-white font-bold py-3 rounded-lg ${action.class} transition-colors">${action.label}</button>`;
        });

        if(currentStatus !== 'delivered') {
            btnContainer.innerHTML += `<button onclick="updateStatus('cancelled')" class="w-full text-red-400 hover:bg-red-500/10 font-bold py-3 rounded-lg border border-red-500/20 transition-colors mt-2">Cancelar Pedido</button>`;
        }

        document.getElementById('statusModal').classList.remove('hidden');
    }

    function closeStatusModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }

    function updateStatus(newStatus) {
        const orderId = document.getElementById('modalOrderId').value;
        closeStatusModal();
        
        fetch(UPDATE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ order_id: orderId, status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                renderKanban(data.live_orders);
            } else {
                alert(data.message);
            }
        });
    }

    // Quick Sale Logic
    function toggleQuickSale() {
        const sidebar = document.getElementById('quickSaleSidebar');
        if(sidebar.style.display === 'none') {
            sidebar.style.display = 'flex';
            setTimeout(() => sidebar.classList.remove('translate-x-full'), 10);
        } else {
            sidebar.classList.add('translate-x-full');
            setTimeout(() => sidebar.style.display = 'none', 300);
        }
    }

    document.getElementById('productSearch').addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(el => {
            if(el.dataset.name.includes(term)) el.style.display = 'flex';
            else el.style.display = 'none';
        });
    });

    function addToCart(id, name, price) {
        const existing = cart.find(i => i.id === id);
        if(existing) {
            existing.qty++;
        } else {
            cart.push({id, name, price, qty: 1});
        }
        renderCart();
    }

    function updateQty(id, delta) {
        const item = cart.find(i => i.id === id);
        if(!item) return;
        item.qty += delta;
        if(item.qty <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cartItems');
        container.innerHTML = '';
        let total = 0;

        cart.forEach(item => {
            total += item.price * item.qty;
            container.innerHTML += `
                <div class="flex justify-between items-center bg-slate-800 p-2 rounded">
                    <div class="flex-1 min-w-0 pr-2">
                        <div class="text-white text-sm truncate">${item.name}</div>
                        <div class="text-white text-xs font-bold">S/ ${(item.price * item.qty).toFixed(2)}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="updateQty(${item.id}, -1)" class="w-6 h-6 rounded bg-slate-700 text-white flex justify-center items-center hover:bg-slate-600">-</button>
                        <span class="text-white text-sm w-4 text-center">${item.qty}</span>
                        <button onclick="updateQty(${item.id}, 1)" class="w-6 h-6 rounded bg-brand-primary text-white flex justify-center items-center hover:bg-brand-primary/80">+</button>
                    </div>
                </div>
            `;
        });

        const isWhatsapp = document.querySelector('input[name="order_type"]:checked').value === 'whatsapp';
        if (isWhatsapp) {
            const dPrice = parseFloat(document.getElementById('waDeliveryPrice').value) || 0;
            total += dPrice;
        }

        document.getElementById('cartTotal').innerText = `S/ ${total.toFixed(2)}`;
        document.getElementById('btnCobrar').disabled = cart.length === 0;
    }

    function toggleOrderType() {
        const isWhatsapp = document.querySelector('input[name="order_type"]:checked').value === 'whatsapp';
        document.getElementById('whatsappFields').classList.toggle('hidden', !isWhatsapp);
        document.getElementById('documentFields').classList.toggle('hidden', isWhatsapp);
        document.getElementById('btnCobrar').innerText = isWhatsapp ? 'Enviar a Cocina (Novedad)' : 'Cobrar y Entregar';
        renderCart(); // Update total to include/exclude delivery price
    }

    function processQuickSale() {
        if(cart.length === 0) return;
        
        const orderType = document.querySelector('input[name="order_type"]:checked').value;
        const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        let finalTotal = total;

        const payload = {
            headquarter_id: HQ_ID,
            user_id: DEFAULT_CUSTOMER_ID,
            order_type: orderType,
            items: cart.map(i => ({ product_id: i.id, quantity: i.qty, price: i.price }))
        };

        if (orderType === 'whatsapp') {
            const dPrice = parseFloat(document.getElementById('waDeliveryPrice').value) || 0;
            finalTotal += dPrice;
            payload.total = finalTotal;
            payload.delivery_price = dPrice;
            payload.customer_name = document.getElementById('waName').value;
            payload.customer_phone = document.getElementById('waPhone').value;
            payload.address = document.getElementById('waAddress').value;
        } else {
            payload.total = total;
            payload.document_type = document.querySelector('input[name="document_type"]:checked').value;
        }

        const btn = document.getElementById('btnCobrar');
        const originalText = btn.innerText;
        btn.innerText = 'Procesando...';
        btn.disabled = true;

        fetch(STORE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                cart = [];
                renderCart();
                renderKanban(data.live_orders);
                toggleQuickSale();
                if (orderType === 'whatsapp') {
                    document.getElementById('waName').value = '';
                    document.getElementById('waPhone').value = '';
                    document.getElementById('waAddress').value = '';
                    document.getElementById('waDeliveryPrice').value = '';
                }
                if(data.ticket_url && orderType !== 'whatsapp') {
                    window.open(data.ticket_url, '_blank');
                }
            } else {
                alert(data.message || 'Error al procesar la venta.');
            }
        })
        .finally(() => {
            btn.innerText = originalText;
            btn.disabled = cart.length === 0;
        });
    }

    // Polling every 15 seconds
    setInterval(fetchOrders, 15000);
    
    // Initial fetch
    fetchOrders();

</script>
@endsection
