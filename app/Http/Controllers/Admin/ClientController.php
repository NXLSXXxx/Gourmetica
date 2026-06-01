<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        if (!auth('admin')->user()->isAdmin()) {
            abort(403, 'No tienes permiso para ver el listado de clientes.');
        }
        $clients = \App\Models\User::clients()->paginate(15);
        return view('admin.clients.index', compact('clients'));
    }
}
