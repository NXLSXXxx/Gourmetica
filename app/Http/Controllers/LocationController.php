<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = \App\Models\Headquarter::where('is_active', true)->get();
        return view('shop.locations', compact('locations'));
    }
}
