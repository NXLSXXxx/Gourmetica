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

        if ($request->filled('category')) {
            $catParam = $request->category;
            $query->whereHas('category', function($q) use ($catParam) {
                if (is_numeric($catParam)) {
                    $q->where('id', $catParam);
                } else {
                    $q->where('slug', $catParam);
                }
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $query->where('base_price', '>=', (float) $request->min_price);
        }

        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $query->where('base_price', '<=', (float) $request->max_price);
        }

        if ($request->boolean('in_stock') && $selectedHqId) {
            $query->whereHas('headquarters', function($q) use ($selectedHqId) {
                $q->where('headquarters.id', $selectedHqId)
                  ->where('headquarter_product.stock', '>', 0);
            });
        }

        $sort = $request->input('sort', 'category');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('base_price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'latest':
                $query->latest();
                break;
            case 'category':
            default:
                $query->orderBy('category_id', 'asc');
                if (\App\Models\Product::hasSortOrderColumn()) {
                    $query->orderBy('sort_order', 'asc');
                }
                $query->orderBy('name', 'asc');
                break;
        }

        $products = $query->paginate(24)->withQueryString();
        $categories = \App\Models\Category::withCount(['products' => function ($q) use ($selectedHqId) {
            $q->where('is_active', true);
            if ($selectedHqId) {
                $q->availableInHeadquarter($selectedHqId);
            }
        }])->get();

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

    public function getByCategory($slug)
    {
        $category = \App\Models\Category::where('slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$category) {
            return response()->json([]);
        }

        $selectedHqId = session('selected_headquarter_id');
        $query = $category->products()->where('is_active', true);
        if ($selectedHqId) {
            $query->availableInHeadquarter($selectedHqId);
        }
        if (\App\Models\Product::hasSortOrderColumn()) {
            $query->orderBy('sort_order', 'asc');
        }
        $query->orderBy('name', 'asc');
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
