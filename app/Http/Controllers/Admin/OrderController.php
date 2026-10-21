<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Order::class);

        $user = auth('admin')->user();
        $query = Order::with(['user', 'headquarter'])->latest();

        if ($user->isSedeAdmin() || $user->isCajero()) {
            $query->where('headquarter_id', $user->headquarter_id);
        }

        $orders = $query->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with(['user', 'headquarter', 'items.product'])->findOrFail($id);
        Gate::authorize('view', $order);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        Gate::authorize('update', $order);
        
        $updateData = ['status' => $request->status];
        if ($request->status === 'delivered') {
            $updateData['payment_status'] = 'paid';
        }
        
        $order->update($updateData);

        if (in_array($request->status, ['preparing', 'shipped', 'delivered'])) {
            $order->decrementProductStock();
        }

        // Nakama Delivery Integration triggers
        if ($request->status === 'preparing' && $order->delivery_zone_id && empty($order->nakama_id)) {
            \App\Services\NakamaService::crearPedido($order);
        } elseif ($request->status === 'shipped' && !empty($order->nakama_id)) {
            \App\Services\NakamaService::marcarPreparado($order);
        }

        if ($request->status === 'delivered') {
            \App\Models\Sale::createFromOrder($order);
        }

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function printTicket($id)
    {
        $order = Order::with(['user', 'headquarter', 'items.product', 'sale'])->findOrFail($id);
        Gate::authorize('printTicket', $order);

        $type = request('type', 'boleta');
        return view('admin.orders.ticket', compact('order', 'type'));
    }

    public function printComanda($id)
    {
        $order = Order::with(['user', 'headquarter', 'items.product', 'sale'])->findOrFail($id);
        Gate::authorize('printTicket', $order);

        $type = 'comanda';
        return view('admin.orders.ticket', compact('order', 'type'));
    }
}
