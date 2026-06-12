<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        $query = \App\Models\Order::with(['user', 'headquarter'])->latest();

        if ($user->isSedeAdmin() || $user->isCajero()) {
            $query->where('headquarter_id', $user->headquarter_id);
        }

        $orders = $query->paginate(15);
        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $user = auth('admin')->user();
        $order = \App\Models\Order::with(['user', 'headquarter', 'items.product'])->findOrFail($id);

        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== $order->headquarter_id) {
            abort(403, 'No tienes permiso para ver este pedido.');
        }

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = \App\Models\Order::findOrFail($id);
        $user = auth('admin')->user();
        
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== $order->headquarter_id) {
            abort(403, 'No tienes permiso para modificar este pedido.');
        }
        
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
        $user = auth('admin')->user();
        $order = \App\Models\Order::with(['user', 'headquarter', 'items.product'])->findOrFail($id);

        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== $order->headquarter_id) {
            abort(403, 'No tienes permiso para imprimir este pedido.');
        }

        return view('admin.orders.ticket', compact('order'));
    }
}
