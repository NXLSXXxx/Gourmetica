<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $orders = \App\Models\Order::with(['headquarter', 'items.product'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $favorites = $user->favorites()->with('category')->get();

        return view('profile.index', compact('user', 'orders', 'favorites'));
    }
}
