<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CateringController extends Controller
{
    public function index()
    {
        return view('shop.catering');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'event_date' => 'required|date|after:today',
            'event_type' => 'required|string|max:100',
            'guests' => 'required|integer|min:5',
            'message' => 'nullable|string',
        ]);

        \App\Models\CateringRequest::create($validated);

        return redirect()->route('shop.catering')->with('success', '¡Gracias por tu solicitud! Nos pondremos en contacto contigo pronto para cotizar tu evento.');
    }
}
