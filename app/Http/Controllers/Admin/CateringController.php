<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CateringController extends Controller
{
    public function index()
    {
        $requests = \App\Models\CateringRequest::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.catering.index', compact('requests'));
    }

    public function show(\App\Models\CateringRequest $cateringRequest)
    {
        return view('admin.catering.show', compact('cateringRequest'));
    }

    public function updateStatus(Request $request, \App\Models\CateringRequest $cateringRequest)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,contacted,completed'
        ]);

        $cateringRequest->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Estado de la solicitud actualizado.');
    }
}
