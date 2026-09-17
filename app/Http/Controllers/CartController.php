<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return view('shop.cart', compact('cart', 'total'));
    }

    public function add(Request $request)
    {
        $selectedHqId = session('selected_headquarter_id');
        if (!$selectedHqId) {
            $defaultHq = \App\Models\Headquarter::where('is_active', true)->first();
            if ($defaultHq) {
                $selectedHqId = $defaultHq->id;
                session()->put('selected_headquarter_id', $selectedHqId);
            }
        }

        $product = \App\Models\Product::findOrFail($request->product_id);

        // Verify product is available in this sede
        if ($selectedHqId && !$product->isAvailableInHeadquarter($selectedHqId)) {
            return redirect()->back()->with('error', 'Este producto no está disponible en la sede seleccionada.');
        }

        $options = $request->input('options', []); // [option_id => value_id]
        
        $optionValues = \App\Models\ProductOptionValue::whereIn('id', array_values($options))->get();
        $additionalPrice = $optionValues->sum('price_modifier');
        
        // Get price for the selected headquarter
        $sedePrice = $product->getPriceForHeadquarter($selectedHqId);
        $price = $sedePrice + $additionalPrice;
        
        $cart = session()->get('cart', []);
        
        // Unique key based on product and options
        $cartKey = $product->id . '_' . md5(json_encode($options));
        
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += (int)$request->quantity;
        } else {
            $cart[$cartKey] = [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'price' => $price,
                'headquarter_id' => $selectedHqId,
                'quantity' => (int)$request->quantity,
                'options' => $optionValues->pluck('value')->toArray(),
                'option_ids' => $options,
            ];
        }
        
        session()->put('cart', $cart);
        
        return redirect()->route('shop.cart')->with('success', 'Producto añadido al carrito');
    }

    public function remove($key)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }
        return redirect()->back()->with('success', 'Producto eliminado');
    }
}
