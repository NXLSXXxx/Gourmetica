<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index()
    {
        \Illuminate\Support\Facades\Gate::authorize('view-audits');

        $audits = \App\Models\Audit::with('user')->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.audits.index', compact('audits'));
    }
}
