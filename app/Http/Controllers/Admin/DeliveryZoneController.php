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
        \Illuminate\Support\Facades\Gate::authorize('manage-delivery-zones');

        $zones = DeliveryZone::with('headquarter')->orderBy('headquarter_id')->orderBy('name')->get();
        $headquarters = Headquarter::where('is_active', true)->get();

        return view('admin.delivery_zones.index', compact('zones', 'headquarters'));
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-delivery-zones');

        $validated = $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'coordinates' => 'nullable|string'
        ]);

        $validated['is_active'] = $request->has('is_active');

        DeliveryZone::create($validated);

        return redirect()->route('admin.delivery_zones.index')->with('success', 'Zona de delivery agregada exitosamente.');
    }

    public function update(Request $request, DeliveryZone $deliveryZone)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-delivery-zones');

        $validated = $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'coordinates' => 'nullable|string'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $deliveryZone->update($validated);

        return redirect()->route('admin.delivery_zones.index')->with('success', 'Zona de delivery actualizada exitosamente.');
    }

    public function destroy(DeliveryZone $deliveryZone)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-delivery-zones');

        $deliveryZone->delete();

        return redirect()->route('admin.delivery_zones.index')->with('success', 'Zona de delivery eliminada exitosamente.');
    }
}
