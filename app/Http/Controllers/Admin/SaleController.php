<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Order;
use App\Services\SunatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SaleController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Sale::class);

        $user = auth('admin')->user();
        $query = Sale::with(['user', 'headquarter'])->latest();

        if ($user->isSedeAdmin() || $user->isCajero()) {
            $query->where('headquarter_id', $user->headquarter_id);
        }

        $sales = $query->paginate(15);

        // Fetch headquarters, customers, and active products for the direct sale form
        $hqQuery = \App\Models\Headquarter::where('is_active', true);
        if ($user->isSedeAdmin() || $user->isCajero()) {
            $hqQuery->where('id', $user->headquarter_id);
        }
        $headquarters = $hqQuery->get();

        $customers = \App\Models\User::whereHas('role', function($q) {
            $q->where('slug', 'cliente');
        })->get();

        $products = \App\Models\Product::with('headquarters')->where('is_active', true)->get();

        return view('admin.sales.index', compact('sales', 'headquarters', 'customers', 'products'));
    }

    public function storeDirect(Request $request)
    {
        $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'user_id' => 'required|exists:users,id',
            'document_type' => 'required|in:01,03', // 01: Factura, 03: Boleta
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:255',
            'invoice_document' => 'nullable|string|max:20',
        ]);

        Gate::authorize('create', [Sale::class, (int)$request->headquarter_id]);

        $user = auth('admin')->user();
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$request->headquarter_id) {
            abort(403, 'No tienes permiso para registrar una venta en esta sede.');
        }

        // Calculate direct total using sede-specific prices
        $total = 0;
        foreach ($request->products as $pInput) {
            $product = \App\Models\Product::findOrFail($pInput['id']);
            $productPrice = $product->getPriceForHeadquarter($request->headquarter_id);
            $total += $productPrice * (int)$pInput['quantity'];
        }

        // Generate series and correlative based on document type
        $documentType = $request->document_type;
        $series = $documentType === '01' ? 'F001' : 'B001';

        $lastSale = Sale::where('document_type', $documentType)
            ->where('series', $series)
            ->orderBy('correlative', 'desc')
            ->first();

        $correlative = $lastSale ? $lastSale->correlative + 1 : 1;

        // Generar pedido y venta vinculada en una sola transacción atómica
        $sale = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $total, $documentType, $series, $correlative) {
            $customerUser = \App\Models\User::find($request->user_id);
            $customerName = trim((string)$request->input('customer_name')) ?: ($customerUser->name ?? 'Cliente Directo');
            $invoiceDoc = trim((string)$request->input('invoice_document')) ?: null;

            $order = \App\Models\Order::create([
                'user_id' => $request->user_id,
                'customer_name' => $customerName,
                'headquarter_id' => $request->headquarter_id,
                'total' => $total,
                'status' => 'delivered',
                'address' => 'Venta Directa - Sede',
                'payment_method' => 'Efectivo',
                'payment_status' => 'paid',
                'invoice_type' => $documentType === '01' ? 'factura' : 'boleta',
                'invoice_document' => $invoiceDoc,
                'stock_decremented' => false,
            ]);

            foreach ($request->products as $pInput) {
                $product = \App\Models\Product::findOrFail($pInput['id']);
                $productPrice = $product->getPriceForHeadquarter($request->headquarter_id);
                $qty = (int)$pInput['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'price' => $productPrice,
                ]);
            }

            // Descontar inmediatamente el stock de producto terminado en la sede seleccionada
            $order->decrementProductStock();

            return Sale::create([
                'user_id' => $request->user_id,
                'headquarter_id' => $request->headquarter_id,
                'document_type' => $documentType,
                'series' => $series,
                'correlative' => $correlative,
                'total' => $total,
                'status' => 'completed',
                'sunat_status' => 'pending',
                'order_id' => $order->id,
            ]);
        });

        return back()->with('success', 'Venta física registrada exitosamente con detalle de productos.');
    }

    public function declareToSunat($id, SunatService $sunatService)
    {
        $sale = Sale::findOrFail($id);
        Gate::authorize('declare', $sale);

        if ($sale->sunat_status === 'ACEPTADO') {
            return back()->with('info', 'Esta venta ya fue declarada y aceptada.');
        }

        try {
            $result = $sunatService->generateInvoice($sale);

            if ($result->isSuccess()) {
                $sale->update([
                    'sunat_status' => 'ACEPTADO',
                    'sunat_response' => (method_exists($result, 'getCdrResponse') && $result->getCdrResponse()) ? $result->getCdrResponse()->getDescription() : 'Aceptado por SUNAT',
                ]);
                return back()->with('success', 'Venta declarada exitosamente ante SUNAT.');
            } else {
                $sale->update([
                    'sunat_status' => 'RECHAZADO',
                    'sunat_response' => $result->getError()->getMessage(),
                ]);
                return back()->with('error', 'Error SUNAT: ' . $result->getError()->getMessage());
            }
        } catch (\InvalidArgumentException $e) {
            $sale->update([
                'sunat_status' => 'PENDIENTE_CORRECCION',
                'sunat_response' => 'Rechazo de validación tributaria: ' . $e->getMessage(),
            ]);
            return back()->with('error', 'Validación tributaria rechazada: ' . $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'Error en el proceso: ' . $e->getMessage());
        }
    }

    public function liveOrders(Request $request)
    {
        Gate::authorize('operate-cashier');

        $user = auth('admin')->user();

        if ($user->isSedeAdmin() || $user->isCajero()) {
            if ($request->filled('hq_id') && (int)$request->input('hq_id') !== (int)$user->headquarter_id) {
                if ($request->ajax()) {
                    return response()->json(['message' => 'No tienes permiso para acceder a las operaciones de caja de otra sede.'], 403);
                }
                abort(403, 'No tienes permiso para acceder a las operaciones de caja de otra sede.');
            }
            $hqId = (int)$user->headquarter_id;
            $hqQuery = \App\Models\Headquarter::where('id', $hqId);
        } else {
            $hqQuery = \App\Models\Headquarter::where('is_active', true);
            $hqId = (int)($request->input('hq_id') ?: (\App\Models\Headquarter::where('is_active', true)->first()->id ?? 1));
        }

        $headquarters = $hqQuery->get();

        $products = \App\Models\Product::with(['category', 'headquarters', 'options.values'])
            ->where('is_active', true)
            ->whereHas('headquarters', function($q) use ($hqId) {
                $q->where('headquarters.id', $hqId)
                  ->where('headquarter_product.is_available', true);
            })
            ->get();

        $categories = \App\Models\Category::all();

        $customers = \App\Models\User::whereHas('role', function($q) {
            $q->where('slug', 'cliente');
        })->get();

        $defaultCustomer = $customers->first();
        
        if ($request->ajax()) {
            return response()->json($this->getLiveOrdersData($hqId));
        }
        
        $liveOrdersData = $this->getLiveOrdersData($hqId);

        return view('admin.sales.live_orders', compact('products', 'categories', 'headquarters', 'customers', 'defaultCustomer', 'liveOrdersData', 'hqId'));
    }

    public function liveOrdersStore(Request $request)
    {
        Gate::authorize('operate-cashier');

        // This is for quick purchases (compra rapida) directly at the headquarter
        $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'user_id' => 'required|exists:users,id',
            'document_type' => 'required_unless:order_type,whatsapp|in:01,03',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.options' => 'nullable|array',
            'order_type' => 'nullable|in:salon,whatsapp',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'address' => 'nullable|string',
            'delivery_price' => 'nullable|numeric|min:0',
        ]);

        $user = auth('admin')->user();
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$request->headquarter_id) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para registrar una venta en esta sede.'], 403);
        }

        $orderType = $request->order_type ?? 'salon';
        $userId = $request->user_id;

        // Si es WhatsApp y hay datos de cliente, buscar o crear
        $orderCustomerName = null;
        if ($orderType === 'whatsapp' && ($request->customer_name || $request->customer_phone)) {
            $orderCustomerName = $request->customer_name;
            $phone = $request->customer_phone ?: 'wa_' . time() . rand(10, 99);
            $customer = \App\Models\User::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $request->customer_name ?: 'Cliente WhatsApp',
                    'email' => 'wa_' . time() . '@gourmetica.local',
                    'password' => bcrypt(\Illuminate\Support\Str::random(10)),
                    'role_id' => \App\Models\Role::where('slug', 'cliente')->first()->id ?? null
                ]
            );

            $userId = $customer->id;
        }

        // Recalcular precios de ítems y total estrictamente en el servidor
        $verifiedItems = [];
        $calculatedTotal = 0;
        $hqId = (int)$request->headquarter_id;

        foreach ($request->items as $rawItem) {
            $product = \App\Models\Product::findOrFail($rawItem['product_id']);
            $unitPrice = (float)$product->getPriceForHeadquarter($hqId);

            if (!empty($rawItem['options']) && is_array($rawItem['options'])) {
                $optValues = \App\Models\ProductOptionValue::whereIn('id', array_values($rawItem['options']))->get();
                $unitPrice += (float)$optValues->sum('price_modifier');
            }

            $qty = max(1, (int)$rawItem['quantity']);
            $calculatedTotal += ($unitPrice * $qty);

            $verifiedItems[] = [
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $unitPrice,
                'options' => $rawItem['options'] ?? null,
            ];
        }

        $deliveryPrice = $orderType === 'whatsapp' ? max(0, (float)($request->delivery_price ?? 0)) : 0;
        $calculatedTotal += $deliveryPrice;

        // Ejecutar creación del pedido, items, descuento de stock y venta en una sola transacción atómica
        $sale = null;
        $order = \Illuminate\Support\Facades\DB::transaction(function () use (
            $userId, $orderCustomerName, $request, $calculatedTotal, $orderType, $deliveryPrice, $verifiedItems, &$sale
        ) {
            $order = \App\Models\Order::create([
                'user_id' => $userId,
                'customer_name' => $orderCustomerName,
                'headquarter_id' => $request->headquarter_id,
                'total' => $calculatedTotal,
                'status' => $orderType === 'whatsapp' ? 'pending' : 'delivered',
                'address' => $orderType === 'whatsapp' ? ($request->address ?: 'Recojo') : 'Compra Rápida - Sede',
                'payment_method' => 'Efectivo',
                'payment_status' => $orderType === 'whatsapp' ? 'pending' : 'paid',
                'delivery_price' => $deliveryPrice,
            ]);

            foreach ($verifiedItems as $vItem) {
                $order->items()->create($vItem);
            }

            // Descontar inventario de forma segura
            if ($orderType === 'salon') {
                $order->decrementProductStock();

                $documentType = $request->document_type ?: '03';
                $series = $documentType === '01' ? 'F001' : 'B001';

                // Bloqueo de fila para evitar duplicados de correlativo en cajas concurrentes
                $lastSale = Sale::where('document_type', $documentType)
                    ->where('series', $series)
                    ->orderBy('correlative', 'desc')
                    ->lockForUpdate()
                    ->first();

                $correlative = $lastSale ? $lastSale->correlative + 1 : 500;

                $sale = Sale::create([
                    'user_id' => $userId,
                    'headquarter_id' => $request->headquarter_id,
                    'document_type' => $documentType,
                    'series' => $series,
                    'correlative' => $correlative,
                    'total' => $calculatedTotal,
                    'status' => 'completed',
                    'sunat_status' => 'pending',
                    'order_id' => $order->id,
                    'table_number' => null,
                ]);
            }

            return $order;
        });

        // Auto declare to SUNAT via SOAP
        $sunatService = new SunatService();
        try {
            $result = $sunatService->generateInvoice($sale);
            if ($result->isSuccess()) {
                $sale->update([
                    'sunat_status' => 'ACEPTADO',
                    'sunat_response' => (method_exists($result, 'getCdrResponse') && $result->getCdrResponse()) ? $result->getCdrResponse()->getDescription() : 'Aceptado por SUNAT',
                ]);
            } else {
                $sale->update([
                    'sunat_status' => 'RECHAZADO',
                    'sunat_response' => $result->getError()->getMessage(),
                ]);
            }
        } catch (\InvalidArgumentException $e) {
            $sale->update([
                'sunat_status' => 'PENDIENTE_CORRECCION',
                'sunat_response' => 'Rechazo de validación tributaria: ' . $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $sale->update([
                'sunat_status' => 'PENDIENTE_OSE',
                'sunat_response' => 'Error de conexión sandbox OSE: ' . $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Compra rápida registrada exitosamente.',
            'sale_id' => $sale->id,
            'ticket_url' => route('admin.orders.ticket', ['id' => $order->id, 'type' => 'boleta']),
            'comanda_url' => route('admin.orders.comanda', $order->id),
            'live_orders' => $this->getLiveOrdersData($request->headquarter_id),
        ]);
    }

    public function liveOrdersUpdateStatus(Request $request)
    {
        Gate::authorize('operate-cashier');

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:pending,preparing,shipped,delivered,cancelled',
        ]);

        $order = Order::findOrFail($request->order_id);
        Gate::authorize('update', $order);

        $isDelivery = $order->delivery_zone_id || $order->delivery_price > 0;
        // Removido temporalmente el bloqueo estricto para permitir Forzar Entrega Manual en caso de fallos de Webhook locales.

        $updateData = ['status' => $request->status];
        if ($request->status === 'delivered') {
            $updateData['payment_status'] = 'paid';
        }
        
        $order->update($updateData);

        if (in_array($request->status, ['preparing', 'shipped', 'delivered'])) {
            $order->decrementProductStock();
        }

        // Nakama Delivery Integration triggers
        if ($request->status === 'preparing' && ($order->delivery_zone_id || $order->delivery_price > 0) && empty($order->nakama_id)) {
            \App\Services\NakamaService::crearPedido($order);
        } elseif ($request->status === 'shipped' && !empty($order->nakama_id)) {
            \App\Services\NakamaService::marcarPreparado($order);
        }

        if ($request->status === 'delivered') {
            // Avoid creating a sale twice if already created (e.g. from quick purchase)
            $existingSale = Sale::where('order_id', $order->id)->first();
            if (!$existingSale) {
                Sale::createFromOrder($order);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado.',
            'live_orders' => $this->getLiveOrdersData($order->headquarter_id)
        ]);
    }

    public function liveOrdersCancel(Request $request)
    {
        Gate::authorize('operate-cashier');

        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        Gate::authorize('cancel', $order);

        $hqId = $order->headquarter_id;
        
        $order->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado.',
            'live_orders' => $this->getLiveOrdersData($hqId)
        ]);
    }

    private function getLiveOrdersData($hqId)
    {
        $orders = \App\Models\Order::with(['user', 'items.product'])
            ->where('headquarter_id', $hqId)
            ->whereIn('status', ['pending', 'preparing', 'shipped', 'delivered'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'pending' => [],
            'preparing' => [],
            'shipped' => [],
            'delivered' => [],
        ];

        foreach ($orders as $order) {

            $orderData = [
                'order_id' => $order->id,
                'customer_name' => $order->customer_name ?: ($order->user->name ?? 'Cliente Anónimo'),
                'total' => (float)$order->total,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'address' => $order->address,
                'is_delivery' => $order->delivery_zone_id || $order->delivery_price > 0,
                'created_at' => $order->created_at->format('H:i'),
                'time_ago' => $order->created_at->diffForHumans(),
                'phone' => $order->phone ?: ($order->user->phone ?? ''),
                'phone_digits' => preg_replace('/[^0-9]/', '', $order->phone ?: ($order->user->phone ?? '')),
                'ticket_url' => route('admin.orders.ticket', ['id' => $order->id, 'type' => 'boleta']),
                'comanda_url' => route('admin.orders.comanda', $order->id),
                'comanda_text' => $order->getFormattedComandaText(),
                'invoice_text' => $order->getFormattedInvoiceText(),
                'items' => $order->items->map(function($item) {
                    return [
                        'product_name' => $item->product->name ?? 'Producto',
                        'quantity' => (int)$item->quantity,
                        'options' => $item->options ?? null
                    ];
                })->toArray()
            ];

            $data[$order->status][] = $orderData;
        }

        return $data;
    }
}
