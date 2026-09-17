<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->isAdmin() && !$user->isSedeAdmin())) {
            abort(403, 'No tienes permiso para ver el listado de clientes.');
        }

        $query = \App\Models\User::clients()->with('headquarter');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->latest()->paginate(15)->withQueryString();
        $totalClients = \App\Models\User::clients()->count();

        return view('admin.clients.index', compact('clients', 'totalClients'));
    }

    public function show($id)
    {
        $user = auth('admin')->user();
        if (!$user || (!$user->isAdmin() && !$user->isSedeAdmin())) {
            abort(403, 'No tienes permiso para ver el detalle del cliente.');
        }

        $client = \App\Models\User::clients()->with(['headquarter'])->findOrFail($id);

        $orders = \App\Models\Order::where('user_id', $client->id)
            ->with(['headquarter', 'items.product'])
            ->latest()
            ->paginate(10, ['*'], 'orders_page')
            ->withQueryString();

        $sales = \App\Models\Sale::where('user_id', $client->id)
            ->with(['headquarter'])
            ->latest()
            ->paginate(10, ['*'], 'sales_page')
            ->withQueryString();

        // Métricas de consumo y actividad
        $totalOrdersCount = \App\Models\Order::where('user_id', $client->id)->count();
        $totalSpentOrders = (float)\App\Models\Order::where('user_id', $client->id)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $totalSalesCount = \App\Models\Sale::where('user_id', $client->id)->count();
        $totalSpentSales = (float)\App\Models\Sale::where('user_id', $client->id)
            ->whereNotIn('status', ['anulado', 'cancelado'])
            ->sum('total');

        $totalSpent = $totalSpentOrders + $totalSpentSales;

        $favorites = $client->favorites()->take(8)->get();

        return view('admin.clients.show', compact(
            'client',
            'orders',
            'sales',
            'totalOrdersCount',
            'totalSalesCount',
            'totalSpent',
            'favorites'
        ));
    }
}
