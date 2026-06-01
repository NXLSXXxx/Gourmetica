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
        $product = \App\Models\Product::findOrFail($request->product_id);
        $options = $request->input('options', []); // [option_id => value_id]
        
        $optionValues = \App\Models\ProductOptionValue::whereIn('id', array_values($options))->get();
        $additionalPrice = $optionValues->sum('price_modifier');
        
        $price = $product->base_price + $additionalPrice;
        
        $cart = session()->get('cart', []);
        
        // Unique key based on product and options
        $cartKey = $product->id . '_' . md5(json_encode($options));
        
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $request->quantity;
        } else {
            $cart[$cartKey] = [
                'id' => $product->id,
                'name' => $product->name,
                'image' => $product->image,
                'price' => $price,
                'quantity' => $request->quantity,
                'options' => $optionValues->pluck('value')->toArray(),
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
