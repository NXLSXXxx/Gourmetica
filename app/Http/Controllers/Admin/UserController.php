<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Headquarter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{
    /**
     * Security Guard: Only Admin General and Ingeniero can manage administrative users.
     */
    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                \Illuminate\Support\Facades\Gate::authorize('manage-users');
                return $next($request);
            }),
        ];
    }

    /**
     * Display a listing of administrative users.
     */
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');

        $users = User::whereHas('role', function ($query) {
            $query->whereIn('slug', ['admin_general', 'admin_sede', 'cajero', 'ingeniero']);
        })->with(['role', 'headquarter'])->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new administrative user.
     */
    public function create()
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');

        $roles = Role::whereIn('slug', ['admin_general', 'admin_sede', 'cajero', 'ingeniero'])->get();
        $headquarters = Headquarter::all();

        return view('admin.users.create', compact('roles', 'headquarters'));
    }

    /**
     * Store a newly created administrative user in storage.
     */
    public function store(Request $request)
    {
        $sedeAdminRole = Role::where('slug', 'admin_sede')->first();
        $cajeroRole = Role::where('slug', 'cajero')->first();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ];

        // Headquarter is strictly required if the role is Admin de Sede or Cajero
        $isStoreRole = ($sedeAdminRole && $request->role_id == $sedeAdminRole->id) || ($cajeroRole && $request->role_id == $cajeroRole->id);
        if ($isStoreRole) {
            $rules['headquarter_id'] = 'required|exists:headquarters,id';
        } else {
            $rules['headquarter_id'] = 'nullable|exists:headquarters,id';
        }

        $validated = $request->validate($rules, [
            'headquarter_id.required' => 'La sede es obligatoria para el rol seleccionado (Administrador de Sede o Cajero).',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'email.unique' => 'El correo electrónico ya se encuentra registrado.',
        ]);

        // If not a store-level role, nullify headquarter_id
        if (!$isStoreRole) {
            $validated['headquarter_id'] = null;
        }

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Usuario administrativo creado con éxito.');
    }

    /**
     * Show the form for editing the specified administrative user.
     */
    public function edit($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');

        $user = User::findOrFail($id);
        
        // Ensure we are editing an administrative user
        if ($user->role->slug === 'cliente') {
            abort(403, 'No puedes editar un cliente regular desde este panel.');
        }

        $roles = Role::whereIn('slug', ['admin_general', 'admin_sede', 'cajero', 'ingeniero'])->get();
        $headquarters = Headquarter::all();

        return view('admin.users.edit', compact('user', 'roles', 'headquarters'));
    }

    /**
     * Update the specified administrative user in storage.
     */
    public function update(Request $request, $id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');

        $user = User::findOrFail($id);
        
        if ($user->role->slug === 'cliente') {
            abort(403);
        }

        $sedeAdminRole = Role::where('slug', 'admin_sede')->first();
        $cajeroRole = Role::where('slug', 'cajero')->first();
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'role_id' => 'required|exists:roles,id',
        ];

        $isStoreRole = ($sedeAdminRole && $request->role_id == $sedeAdminRole->id) || ($cajeroRole && $request->role_id == $cajeroRole->id);
        if ($isStoreRole) {
            $rules['headquarter_id'] = 'required|exists:headquarters,id';
        } else {
            $rules['headquarter_id'] = 'nullable|exists:headquarters,id';
        }

        $validated = $request->validate($rules, [
            'headquarter_id.required' => 'La sede es obligatoria para el rol seleccionado (Administrador de Sede o Cajero).',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'email.unique' => 'El correo electrónico ya se encuentra registrado.',
        ]);

        if (!$isStoreRole) {
            $validated['headquarter_id'] = null;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'Usuario administrativo actualizado con éxito.');
    }

    /**
     * Remove the specified administrative user from storage.
     */
    public function destroy($id)
    {
        \Illuminate\Support\Facades\Gate::authorize('manage-users');

        $user = User::findOrFail($id);

        if ($user->role->slug === 'cliente') {
            abort(403);
        }

        // Self-deletion check safeguards
        if ($id == auth('admin')->id()) {
            return redirect()->route('admin.users.index')->with('error', 'No puedes eliminar tu propio usuario de la sesión activa.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario administrativo eliminado con éxito.');
    }
}
