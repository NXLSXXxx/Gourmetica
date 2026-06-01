<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function toggle(Product $product)
    {
        $user = Auth::user();
        $user->favorites()->toggle($product->id);

        return back()->with('success', 'Lista de favoritos actualizada.');
    }
}
