<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supply;
use App\Models\SupplyCategory;
use App\Models\Headquarter;
use Illuminate\Http\Request;

class SupplyController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero() && !$user->isSedeAdmin()) {
            abort(403, 'No tienes permiso para gestionar insumos.');
        }

        $query = Supply::with(['category', 'headquarters'])->latest();

        if ($request->filled('category_id')) {
            $query->where('supply_category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $supplies = $query->paginate(20)->withQueryString();
        $categories = SupplyCategory::orderBy('name')->get();
        $headquarters = Headquarter::where('is_active', true)->get();

        return view('admin.supplies.index', compact('supplies', 'categories', 'headquarters'));
    }

    public function create()
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero()) {
            abort(403, 'No tienes permiso para crear insumos.');
        }

        $categories = SupplyCategory::orderBy('name')->get();
        $headquarters = Headquarter::where('is_active', true)->get();

        return view('admin.supplies.create', compact('categories', 'headquarters'));
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
            'supply_category_id' => 'nullable|exists:supply_categories,id',
            'new_category' => 'nullable|string|max:100',
            'headquarters' => 'nullable|array',
            'headquarters.*.stock' => 'nullable|numeric|min:0',
        ]);

        if (!empty($request->new_category)) {
            $newCat = SupplyCategory::firstOrCreate(
                ['name' => trim($request->new_category)]
            );
            $validated['supply_category_id'] = $newCat->id;
        }

        $supply = Supply::create([
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'supply_category_id' => $validated['supply_category_id'] ?? null,
        ]);

        // Guardar stock inicial por sede si fue ingresado
        if (!empty($request->headquarters) && is_array($request->headquarters)) {
            foreach ($request->headquarters as $hqId => $data) {
                $stock = isset($data['stock']) && $data['stock'] !== '' ? (float)$data['stock'] : 0.0;
                $supply->headquarters()->attach($hqId, ['stock' => $stock]);
            }
        }

        return redirect()->route('admin.supplies.index')->with('success', 'Insumo creado correctamente con su stock inicial asignado.');
    }

    public function edit(Supply $supply)
    {
        $user = auth('admin')->user();
        if (!$user->isAdmin() && !$user->isIngeniero()) {
            abort(403, 'No tienes permiso para editar insumos.');
        }

        $categories = SupplyCategory::orderBy('name')->get();
        $headquarters = Headquarter::where('is_active', true)->get();
        $stocks = [];
        foreach ($headquarters as $hq) {
            $pivot = $supply->headquarters()->where('headquarter_id', $hq->id)->first();
            $stocks[$hq->id] = $pivot ? (float)$pivot->pivot->stock : 0.0;
        }

        return view('admin.supplies.edit', compact('supply', 'categories', 'headquarters', 'stocks'));
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
            'supply_category_id' => 'nullable|exists:supply_categories,id',
            'new_category' => 'nullable|string|max:100',
            'headquarters' => 'nullable|array',
            'headquarters.*.stock' => 'nullable|numeric|min:0',
        ]);

        if (!empty($request->new_category)) {
            $newCat = SupplyCategory::firstOrCreate(
                ['name' => trim($request->new_category)]
            );
            $validated['supply_category_id'] = $newCat->id;
        }

        $supply->update([
            'name' => $validated['name'],
            'unit' => $validated['unit'],
            'supply_category_id' => $validated['supply_category_id'] ?? null,
        ]);

        if ($request->has('headquarters') && is_array($request->headquarters)) {
            foreach ($request->headquarters as $hqId => $data) {
                if (isset($data['stock']) && $data['stock'] !== '') {
                    $stock = (float)$data['stock'];
                    $pivot = $supply->headquarters()->where('headquarter_id', $hqId)->first();
                    if ($pivot) {
                        $supply->headquarters()->updateExistingPivot($hqId, ['stock' => $stock]);
                    } else {
                        $supply->headquarters()->attach($hqId, ['stock' => $stock]);
                    }
                }
            }
        }

        return redirect()->route('admin.supplies.index')->with('success', 'Insumo y stock actualizados correctamente.');
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
