<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        $query = Purchase::with('headquarter')->latest();

        if ($user->isSedeAdmin() || $user->isCajero()) {
            $query->where('headquarter_id', $user->headquarter_id);
        }

        $purchases = $query->paginate(15);
        return view('admin.purchases.index', compact('purchases'));
    }

    public function create()
    {
        return view('admin.purchases.create');
    }

    public function show(Purchase $purchase)
    {
        $user = auth('admin')->user();
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== $purchase->headquarter_id) {
            abort(403, 'No tienes permiso para ver esta compra.');
        }

        return view('admin.purchases.show', compact('purchase'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'supplier_ruc' => 'nullable|string|max:11',
            'document_type' => 'required|string',
            'series' => 'nullable|string|max:4',
            'number' => 'nullable|string|max:8',
            'total' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
        ]);

        if (empty($validated['supplier_ruc'])) {
            $validated['supplier_ruc'] = '99999999999'; // Generic Peruvian RUC for unidentified/informal suppliers
        }
        if (empty($validated['series'])) {
            $validated['series'] = 'INT';
        }
        if (empty($validated['number'])) {
            $validated['number'] = substr(strval(time()), -8); // Unique 8-digit number based on timestamp
        }

        $validated['headquarter_id'] = auth('admin')->user()->headquarter_id ?? \App\Models\Headquarter::first()->id;

        Purchase::create($validated);

        return redirect()->route('admin.purchases.index')->with('success', 'Compra/Egreso registrado correctamente.');
    }
}
