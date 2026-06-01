<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryZone;
use App\Models\Headquarter;

class DeliveryZoneController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();

        $query = DeliveryZone::with('headquarter');

        if ($user->isSedeAdmin() || $user->isCajero()) {
            $query->where('headquarter_id', $user->headquarter_id);
        }

        $zones = $query->orderBy('headquarter_id')->orderBy('name')->get();

        // Also fetch active headquarters for the dropdown when creating/editing
        $hqQuery = Headquarter::where('is_active', true);
        if ($user->isSedeAdmin() || $user->isCajero()) {
            $hqQuery->where('id', $user->headquarter_id);
        }
        $headquarters = $hqQuery->get();

        return view('admin.delivery_zones.index', compact('zones', 'headquarters'));
    }

    public function store(Request $request)
    {
        $user = auth('admin')->user();

        $validated = $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'coordinates' => 'nullable|string'
        ]);

        // Security check for store-level users
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$validated['headquarter_id']) {
            abort(403, 'No tienes permiso para gestionar zonas de otra sede.');
        }

        $validated['is_active'] = $request->has('is_active');

        DeliveryZone::create($validated);

        return redirect()->route('admin.delivery_zones.index')->with('success', 'Zona de delivery agregada exitosamente.');
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        $user = auth('admin')->user();

        // Security check for store-level users
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== $deliveryZone->headquarter_id) {
            abort(403, 'No tienes permiso para gestionar zonas de otra sede.');
        }

        $validated = $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'coordinates' => 'nullable|string'
        ]);

        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$validated['headquarter_id']) {
            abort(403, 'No puedes reasignar esta zona a otra sede.');
        }

        $validated['is_active'] = $request->has('is_active');

        $deliveryZone->update($validated);

        return redirect()->route('admin.delivery_zones.index')->with('success', 'Zona de delivery actualizada exitosamente.');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        $user = auth('admin')->user();

        // Security check for store-level users
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== $deliveryZone->headquarter_id) {
            abort(403, 'No tienes permiso para gestionar zonas de otra sede.');
        }

        $deliveryZone->delete();

        return redirect()->route('admin.delivery_zones.index')->with('success', 'Zona de delivery eliminada exitosamente.');
    }
}
