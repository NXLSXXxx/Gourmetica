<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Headquarter;
use Illuminate\Http\Request;

class HeadquarterController extends Controller
{
    public function index()
    {
        if (!auth('admin')->user()->isAdmin()) {
            abort(403, 'No tienes permiso para gestionar sedes.');
        }
        $headquarters = Headquarter::with('users')->get();
        return view('admin.headquarters.index', compact('headquarters'));
    }

    public function show(Headquarter $headquarters)
    {
        $user = auth('admin')->user();
        
        // Authorization: Admin General or assigned staff
        if (!$user->isAdmin() && $user->headquarter_id !== $headquarters->id) {
            abort(403, 'No tienes permiso para ver los detalles de esta sede.');
        }

        $headquarters->load(['users', 'products']);
        return view('admin.headquarters.show', ['headquarter' => $headquarters]);
    }

    public function create()
    {
        return view('admin.headquarters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        Headquarter::create($request->all());

        return redirect()->route('admin.headquarters.index')
            ->with('success', 'Sede creada correctamente.');
    }

    public function edit(Headquarter $headquarters)
    {
        return view('admin.headquarters.edit', ['headquarter' => $headquarters]);
    }

    public function update(Request $request, Headquarter $headquarters)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        $headquarters->update($request->all());

        return redirect()->route('admin.headquarters.index')
            ->with('success', 'Sede actualizada correctamente.');
    }

    public function destroy(Headquarter $headquarters)
    {
        if ($headquarters->users()->count() > 0) {
            return back()->with('error', 'No se puede eliminar la sede porque tiene usuarios asignados.');
        }

        $headquarters->delete();

        return redirect()->route('admin.headquarters.index')
            ->with('success', 'Sede eliminada correctamente.');
    }
}
