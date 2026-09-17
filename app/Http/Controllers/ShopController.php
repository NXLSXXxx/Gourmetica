<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $selectedHqId = session('selected_headquarter_id');
        if (!$selectedHqId) {
            $defaultHq = \App\Models\Headquarter::where('is_active', true)->first();
            if ($defaultHq) {
                $selectedHqId = $defaultHq->id;
                session()->put('selected_headquarter_id', $selectedHqId);
            }
        }

        $query = \App\Models\Product::with(['category', 'headquarters'])->where('is_active', true);

        if ($selectedHqId) {
            $query->availableInHeadquarter($selectedHqId);
        }

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

        return view('shop.index', compact('products', 'categories', 'selectedHqId'));
    }

    public function show($slug)
    {
        $selectedHqId = session('selected_headquarter_id');
        if (!$selectedHqId) {
            $defaultHq = \App\Models\Headquarter::where('is_active', true)->first();
            if ($defaultHq) {
                $selectedHqId = $defaultHq->id;
                session()->put('selected_headquarter_id', $selectedHqId);
            }
        }

        $product = \App\Models\Product::with(['category', 'headquarters', 'options.values'])
            ->where('slug', $slug)
            ->firstOrFail();

        $sedePrice = $product->getPriceForHeadquarter($selectedHqId);
        $isAvailableInSede = $product->isAvailableInHeadquarter($selectedHqId);
            
        return view('shop.product', compact('product', 'sedePrice', 'isAvailableInSede', 'selectedHqId'));
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
        $selectedHqId = session('selected_headquarter_id');
        $query = $category->products()->where('is_active', true);
        if ($selectedHqId) {
            $query->availableInHeadquarter($selectedHqId);
        }
        $products = $query->take(6)->get()->map(function($product) use ($selectedHqId) {
            $product->price = $product->getPriceForHeadquarter($selectedHqId);
            return $product;
        });
        return response()->json($products);
    }

    public function tracking(\App\Models\Order $order)
    {
        return view('shop.tracking', compact('order'));
    }
}
