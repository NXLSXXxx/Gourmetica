<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Product::with(['category', 'headquarters'])->where('is_active', true);

        if ($request->has('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->paginate(12);
        $categories = \App\Models\Category::all();

        return view('shop.index', compact('products', 'categories'));
    }

    public function show($slug)
    {
        $product = \App\Models\Product::with(['category', 'headquarters', 'options.values'])
            ->where('slug', $slug)
            ->firstOrFail();
            
        return view('shop.product', compact('product'));
    }

    public function checkout()
    {
        $headquarters = \App\Models\Headquarter::where('is_active', true)->get();
        $culqiPublicKey = \App\Models\Setting::get('culqi_public_key');
        $deliveryZones = \App\Models\DeliveryZone::where('is_active', true)->get();
        return view('shop.checkout', compact('headquarters', 'culqiPublicKey', 'deliveryZones'));
    }

    public function getByCategory(\App\Models\Category $category)
    {
        return response()->json($category->products()->where('is_active', true)->take(6)->get());
    }

    public function tracking(\App\Models\Order $order)
    {
        return view('shop.tracking', compact('order'));
    }
}
