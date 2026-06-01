<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder al panel.');
        }

        $query = \App\Models\Headquarter::query();
        
        if ($user->isSedeAdmin()) {
            $query->where('id', $user->headquarter_id);
        }

        $headquarters = $query->get();
        $headquarterIds = $headquarters->pluck('id');

        $stats = [
            'total_sales' => \App\Models\Sale::whereIn('headquarter_id', $headquarterIds)->count(),
            'total_products' => \App\Models\Product::whereHas('headquarters', function($q) use ($headquarterIds) {
                $q->whereIn('headquarter_id', $headquarterIds);
            })->count(),
            'total_purchases' => \App\Models\Purchase::whereIn('headquarter_id', $headquarterIds)->count(),
            'headquarters_count' => $headquarters->count(),
            'revenue_by_sede' => \App\Models\Sale::whereIn('headquarter_id', $headquarterIds)
                ->selectRaw('headquarter_id, SUM(total) as total')
                ->groupBy('headquarter_id')
                ->with('headquarter:id,name')
                ->get(),
            'total_sales_amount' => \App\Models\Sale::whereIn('headquarter_id', $headquarterIds)->where('status', 'completed')->sum('total'),
            'total_purchases_amount' => \App\Models\Purchase::whereIn('headquarter_id', $headquarterIds)->sum('total'),
        ];

        return view('admin.dashboard', compact('stats', 'headquarters'));
    }
}
