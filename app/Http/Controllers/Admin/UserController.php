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
                $user = auth('admin')->user();
                if (!$user || (!$user->isAdmin() && !$user->isIngeniero())) {
                    abort(403, 'No tienes permisos para acceder a la gestión de administradores.');
                }
                return $next($request);
            }),
        ];
    }

    /**
     * Display a listing of administrative users.
     */
    public function index()
    {
        $currentUser = auth('admin')->user();
        
        $users = User::whereHas('role', function ($query) use ($currentUser) {
            $allowedSlugs = ['admin_general', 'admin_sede', 'cajero'];
            if ($currentUser->isIngeniero()) {
                $allowedSlugs[] = 'ingeniero';
            }
            $query->whereIn('slug', $allowedSlugs);
        })->with(['role', 'headquarter'])->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new administrative user.
     */
    public function create()
    {
        $currentUser = auth('admin')->user();
        $allowedSlugs = ['admin_general', 'admin_sede', 'cajero'];
        if ($currentUser->isIngeniero()) {
            $allowedSlugs[] = 'ingeniero';
        }

        $roles = Role::whereIn('slug', $allowedSlugs)->get();
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

        // Security block: Ensure a non-engineer cannot assign the engineer role
        $selectedRole = Role::findOrFail($request->role_id);
        if ($selectedRole->slug === 'ingeniero' && !auth('admin')->user()->isIngeniero()) {
            abort(403, 'No tienes permisos para asignar el rol de Ingeniero de Sistemas.');
        }

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
        $user = User::findOrFail($id);
        
        // Ensure we are editing an administrative user
        if ($user->role->slug === 'cliente') {
            abort(403, 'No puedes editar un cliente regular desde este panel.');
        }

        $currentUser = auth('admin')->user();

        // Security block: Prevent non-engineers from accessing an engineer user
        if ($user->isIngeniero() && !$currentUser->isIngeniero()) {
            abort(403, 'No tienes permisos para ver o editar a un Ingeniero de Sistemas.');
        }

        $allowedSlugs = ['admin_general', 'admin_sede', 'cajero'];
        if ($currentUser->isIngeniero()) {
            $allowedSlugs[] = 'ingeniero';
        }

        $roles = Role::whereIn('slug', $allowedSlugs)->get();
        $headquarters = Headquarter::all();

        return view('admin.users.edit', compact('user', 'roles', 'headquarters'));
    }

    /**
     * Update the specified administrative user in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        if ($user->role->slug === 'cliente') {
            abort(403);
        }

        $currentUser = auth('admin')->user();

        // Security block: Prevent non-engineers from updating an engineer user
        if ($user->isIngeniero() && !$currentUser->isIngeniero()) {
            abort(403, 'No tienes permisos para actualizar a un Ingeniero de Sistemas.');
        }

        // Security block: Ensure a non-engineer cannot change role to engineer
        $selectedRole = Role::findOrFail($request->role_id);
        if ($selectedRole->slug === 'ingeniero' && !$currentUser->isIngeniero()) {
            abort(403, 'No tienes permisos para asignar el rol de Ingeniero de Sistemas.');
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
        $user = User::findOrFail($id);

        if ($user->role->slug === 'cliente') {
            abort(403);
        }

        // Security block: Prevent non-engineers from deleting an engineer user
        if ($user->isIngeniero() && !auth('admin')->user()->isIngeniero()) {
            abort(403, 'No tienes permisos para eliminar a un Ingeniero de Sistemas.');
        }

        // Self-deletion check safeguards
        if ($id == auth('admin')->id()) {
            return redirect()->route('admin.users.index')->with('error', 'No puedes eliminar tu propio usuario de la sesión activa.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Usuario administrativo eliminado con éxito.');
    }
}
