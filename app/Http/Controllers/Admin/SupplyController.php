<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supply;
use App\Models\Headquarter;
use Illuminate\Http\Request;

class SupplyController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero() && !$user->isSedeAdmin()) {
            abort(403, 'No tienes permiso para gestionar insumos.');
        }

        $supplies = Supply::latest()->get();
        return view('admin.supplies.index', compact('supplies'));
    }

    public function create()
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero()) {
            abort(403, 'No tienes permiso para crear insumos.');
        }

        return view('admin.supplies.create');
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero()) {
            abort(403, 'No tienes permiso para crear insumos.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|in:g,ml,und',
        ]);

        Supply::create($validated);

        return redirect()->route('admin.supplies.index')->with('success', 'Insumo creado correctamente.');
    }

    public function edit(Supply $supply)
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero()) {
            abort(403, 'No tienes permiso para editar insumos.');
        }

        return view('admin.supplies.edit', compact('supply'));
    }

    public function update(Request $request, Supply $supply)
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero()) {
            abort(403, 'No tienes permiso para editar insumos.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|in:g,ml,und',
        ]);

        $supply->update($validated);

        return redirect()->route('admin.supplies.index')->with('success', 'Insumo actualizado correctamente.');
    }

    public function destroy(Supply $supply)
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero()) {
            abort(403, 'No tienes permiso para eliminar insumos.');
        }

        // Check if supply is being used in any recipe
        if ($supply->recipes()->count() > 0) {
            return back()->with('error', 'No se puede eliminar el insumo porque forma parte de la receta de algún producto.');
        }

        $supply->delete();

        return redirect()->route('admin.supplies.index')->with('success', 'Insumo eliminado correctamente.');
    }

    // View to check/manage stock of a supply in each headquarter
    public function stock(Supply $supply)
    {
        $user = auth('admin')->user();
        $hqQuery = Headquarter::where('is_active', true);
        
        if ($user->isSedeAdmin() || $user->isCajero()) {
            $hqQuery->where('id', $user->headquarter_id);
        }

        $headquarters = $hqQuery->get();

        // Load stocks pivot values
        $stocks = [];
        foreach ($headquarters as $hq) {
            $pivot = $supply->headquarters()->where('headquarter_id', $hq->id)->first();
            $stocks[$hq->id] = $pivot ? $pivot->pivot->stock : 0;
        }

        return view('admin.supplies.stock', compact('supply', 'headquarters', 'stocks'));
    }

    // Save adjusted supply stocks per headquarter
    public function updateStock(Request $request, Supply $supply)
    {
        $request->validate([
            'headquarters' => 'required|array',
            'headquarters.*.stock' => 'required|numeric|min:0',
        ]);

        $user = auth('admin')->user();

        foreach ($request->headquarters as $hqId => $data) {
            if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$hqId) {
                continue; // Prevent editing stock of other headquarters
            }

            // Sync headquarter_supply
            $pivot = $supply->headquarters()->where('headquarter_id', $hqId)->first();
            if ($pivot) {
                $supply->headquarters()->updateExistingPivot($hqId, ['stock' => $data['stock']]);
            } else {
                $supply->headquarters()->attach($hqId, ['stock' => $data['stock']]);
            }
        }

        return redirect()->route('admin.supplies.index')->with('success', 'Stock de insumo actualizado correctamente.');
    }
}
