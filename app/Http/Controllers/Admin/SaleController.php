<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\SunatService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index()
    {
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

        $products = \App\Models\Product::where('is_active', true)->get();

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
        ]);

        $user = auth('admin')->user();
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$request->headquarter_id) {
            abort(403, 'No tienes permiso para registrar una venta en esta sede.');
        }

        // Calculate direct total
        $total = 0;
        foreach ($request->products as $pInput) {
            $product = \App\Models\Product::findOrFail($pInput['id']);
            $total += $product->base_price * (int)$pInput['quantity'];
        }

        // Generate series and correlative based on document type
        $documentType = $request->document_type;
        $series = $documentType === '01' ? 'F001' : 'B001';

        $lastSale = Sale::where('document_type', $documentType)
            ->where('series', $series)
            ->orderBy('correlative', 'desc')
            ->first();

        $correlative = $lastSale ? $lastSale->correlative + 1 : 1;

        // Create Sale
        Sale::create([
            'user_id' => $request->user_id,
            'headquarter_id' => $request->headquarter_id,
            'document_type' => $documentType,
            'series' => $series,
            'correlative' => $correlative,
            'total' => $total,
            'status' => 'completed',
            'sunat_status' => 'pending',
        ]);

        return back()->with('success', 'Venta física registrada exitosamente.');
    }

    public function declareToSunat($id, SunatService $sunatService)
    {
        $sale = Sale::findOrFail($id);

        if ($sale->sunat_status === 'ACEPTADO') {
            return back()->with('info', 'Esta venta ya fue declarada y aceptada.');
        }

        try {
            // Simulation mode for now
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
        } catch (\Exception $e) {
            return back()->with('error', 'Error en el proceso: ' . $e->getMessage());
        }
    }

    public function pos()
    {
        $user = auth('admin')->user();
        
        // Scope active products by headquarter stock if needed, or simply load active products
        $products = \App\Models\Product::with(['category', 'headquarters', 'options.values'])
            ->where('is_active', true)
            ->get();

        $categories = \App\Models\Category::all();

        $hqQuery = \App\Models\Headquarter::where('is_active', true);
        if ($user->isSedeAdmin() || $user->isCajero()) {
            $hqQuery->where('id', $user->headquarter_id);
        }
        $headquarters = $hqQuery->get();

        $customers = \App\Models\User::whereHas('role', function($q) {
            $q->where('slug', 'cliente');
        })->get();

        // Find default customer
        $defaultCustomer = $customers->first();

        // Fetch pending orders (precuentas) for active tables in this headquarter
        $hqId = ($user->isSedeAdmin() || $user->isCajero()) ? $user->headquarter_id : $headquarters->first()->id;
        
        $pendingOrders = \App\Models\Order::with(['user', 'items.product'])
            ->where('headquarter_id', $hqId)
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->where('address', 'like', 'Mesa%')
            ->get()
            ->mapWithKeys(function($order) {
                return [$order->address => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'customer' => $order->user,
                    'total' => (float)$order->total,
                    'items' => $order->items->map(function($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product' => $item->product,
                            'quantity' => (int)$item->quantity,
                            'price' => (float)$item->price,
                            'options' => $item->options ?? null
                        ];
                    })
                ]];
            });

        return view('admin.sales.pos', compact('products', 'categories', 'headquarters', 'customers', 'defaultCustomer', 'pendingOrders'));
    }

    public function posStore(Request $request)
    {
        $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'user_id' => 'required|exists:users,id',
            'document_type' => 'required|in:01,03',
            'table_number' => 'required|string',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.options' => 'nullable|array',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        $user = auth('admin')->user();
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$request->headquarter_id) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para registrar una venta en esta sede.'], 403);
        }

        // If order_id is provided, retrieve and update it, otherwise create a new one
        if ($request->filled('order_id')) {
            $order = \App\Models\Order::findOrFail($request->order_id);
            $order->update([
                'user_id' => $request->user_id,
                'total' => $request->total,
                'status' => 'delivered',
                'payment_status' => 'paid',
                'payment_method' => 'Efectivo',
            ]);
            // Delete old items to overwrite
            $order->items()->delete();
        } else {
            // Create Order
            $order = \App\Models\Order::create([
                'user_id' => $request->user_id,
                'headquarter_id' => $request->headquarter_id,
                'total' => $request->total,
                'status' => 'delivered',
                'address' => $request->table_number,
                'payment_method' => 'Efectivo',
                'payment_status' => 'paid',
            ]);
        }

        // Save order items and update stock
        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'options' => $item['options'] ?? null,
            ]);

            // Decrement stock
            $product = \App\Models\Product::find($item['product_id']);
            if ($product) {
                $hqRelation = $product->headquarters()->where('headquarter_id', $request->headquarter_id)->first();
                if ($hqRelation) {
                    $currentStock = $hqRelation->pivot->stock;
                    $newStock = max(0, $currentStock - intval($item['quantity']));
                    $product->headquarters()->updateExistingPivot($request->headquarter_id, ['stock' => $newStock]);
                }
            }
        }

        // Create Sale
        $documentType = $request->document_type;
        $series = $documentType === '01' ? 'F001' : 'B001';

        $lastSale = Sale::where('document_type', $documentType)
            ->where('series', $series)
            ->orderBy('correlative', 'desc')
            ->first();

        $correlative = $lastSale ? $lastSale->correlative + 1 : 500;

        $sale = Sale::create([
            'user_id' => $request->user_id,
            'headquarter_id' => $request->headquarter_id,
            'document_type' => $documentType,
            'series' => $series,
            'correlative' => $correlative,
            'total' => $request->total,
            'status' => 'completed',
            'sunat_status' => 'pending',
            'order_id' => $order->id,
            'table_number' => $request->table_number,
        ]);

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
        } catch (\Exception $e) {
            $sale->update([
                'sunat_status' => 'PENDIENTE_OSE',
                'sunat_response' => 'Error de conexión sandbox OSE: ' . $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Venta física registrada y declarada exitosamente.',
            'sale_id' => $sale->id,
            'ticket_url' => route('admin.orders.ticket', $order->id),
            'sunat_status' => $sale->sunat_status,
            'sunat_response' => $sale->sunat_response,
            'pending_orders' => $this->getPendingOrdersMap($request->headquarter_id),
        ]);
    }

    public function posPreOrder(Request $request)
    {
        $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'user_id' => 'required|exists:users,id',
            'table_number' => 'required|string',
            'total' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.options' => 'nullable|array',
        ]);

        $user = auth('admin')->user();
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$request->headquarter_id) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para gestionar esta sede.'], 403);
        }

        // Look for an existing pending order for this table
        $order = \App\Models\Order::where('headquarter_id', $request->headquarter_id)
            ->where('address', $request->table_number)
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->first();

        if ($order) {
            // Update existing order
            $order->update([
                'user_id' => $request->user_id,
                'total' => $request->total,
            ]);
            // Delete old items
            $order->items()->delete();
        } else {
            // Create new pending order
            $order = \App\Models\Order::create([
                'user_id' => $request->user_id,
                'headquarter_id' => $request->headquarter_id,
                'total' => $request->total,
                'status' => 'pending',
                'address' => $request->table_number,
                'payment_method' => null,
                'payment_status' => 'pending',
            ]);
        }

        // Save new items
        foreach ($request->items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'options' => $item['options'] ?? null,
            ]);
        }

        // Return updated pending orders map
        $pendingOrders = $this->getPendingOrdersMap($request->headquarter_id);

        return response()->json([
            'success' => true,
            'message' => 'Precuenta guardada con éxito.',
            'pending_orders' => $pendingOrders
        ]);
    }

    public function posCancelOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = \App\Models\Order::findOrFail($request->order_id);
        $user = auth('admin')->user();
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== $order->headquarter_id) {
            return response()->json(['success' => false, 'message' => 'No tienes permiso para gestionar esta sede.'], 403);
        }

        $hqId = $order->headquarter_id;

        // Delete items and the order
        $order->items()->delete();
        $order->delete();

        $pendingOrders = $this->getPendingOrdersMap($hqId);

        return response()->json([
            'success' => true,
            'message' => 'Mesa liberada y precuenta cancelada.',
            'pending_orders' => $pendingOrders
        ]);
    }

    private function getPendingOrdersMap($hqId)
    {
        return \App\Models\Order::with(['user', 'items.product'])
            ->where('headquarter_id', $hqId)
            ->where('status', 'pending')
            ->where('payment_status', 'pending')
            ->where('address', 'like', 'Mesa%')
            ->get()
            ->mapWithKeys(function($order) {
                return [$order->address => [
                    'order_id' => $order->id,
                    'user_id' => $order->user_id,
                    'customer' => $order->user,
                    'total' => (float)$order->total,
                    'items' => $order->items->map(function($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product' => $item->product,
                            'quantity' => (int)$item->quantity,
                            'price' => (float)$item->price,
                            'options' => $item->options ?? null
                        ];
                    })
                ]];
            });
    }
}
