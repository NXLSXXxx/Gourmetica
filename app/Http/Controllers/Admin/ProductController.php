<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = \App\Models\Product::with('category')->paginate(15);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = \App\Models\Category::all();
        $headquarters = \App\Models\Headquarter::all();
        return view('admin.products.create', compact('categories', 'headquarters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'headquarters' => 'required|array',
            'headquarters.*.stock' => 'required|integer|min:0',
            'headquarters.*.price' => 'nullable|numeric|min:0',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string|max:255',
            'options.*.values' => 'required_with:options|array',
            'options.*.values.*.value' => 'required|string|max:255',
            'options.*.values.*.price' => 'required|numeric|min:0',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = \App\Models\Product::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'base_price' => $validated['base_price'],
            'description' => $validated['description'],
            'image' => $imagePath,
            'is_active' => true,
        ]);

        foreach ($validated['headquarters'] as $hqId => $data) {
            $product->headquarters()->attach($hqId, [
                'stock' => $data['stock'],
                'price' => $data['price'] ?? $validated['base_price'],
            ]);
        }

        if ($request->has('options') && is_array($request->options)) {
            foreach ($request->options as $optData) {
                if (empty($optData['name'])) continue;
                
                $option = $product->options()->create([
                    'name' => $optData['name']
                ]);
                
                if (isset($optData['values']) && is_array($optData['values'])) {
                    foreach ($optData['values'] as $valData) {
                        if (empty($valData['value'])) continue;
                        
                        $priceFinal = floatval($valData['price']);
                        $modifier = $priceFinal - floatval($validated['base_price']);
                        
                        $option->values()->create([
                            'value' => $valData['value'],
                            'price_modifier' => $modifier
                        ]);
                    }
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(\App\Models\Product $product)
    {
        $categories = \App\Models\Category::all();
        $headquarters = \App\Models\Headquarter::all();
        $product->load(['headquarters', 'options.values']);
        return view('admin.products.edit', compact('product', 'categories', 'headquarters'));
    }

    public function update(Request $request, \App\Models\Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
            'headquarters' => 'required|array',
            'headquarters.*.stock' => 'required|integer|min:0',
            'headquarters.*.price' => 'nullable|numeric|min:0',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string|max:255',
            'options.*.values' => 'required_with:options|array',
            'options.*.values.*.value' => 'required|string|max:255',
            'options.*.values.*.price' => 'required|numeric|min:0',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'base_price' => $validated['base_price'],
            'description' => $validated['description'],
            'image' => $validated['image'] ?? $product->image,
            'is_active' => $request->has('is_active'),
        ]);

        $syncData = [];
        foreach ($validated['headquarters'] as $hqId => $data) {
            $syncData[$hqId] = [
                'stock' => $data['stock'],
                'price' => $data['price'] ?? $validated['base_price'],
            ];
        }
        $product->headquarters()->sync($syncData);

        if ($request->has('options') && is_array($request->options)) {
            $product->options()->delete();
            foreach ($request->options as $optData) {
                if (empty($optData['name'])) continue;
                
                $option = $product->options()->create([
                    'name' => $optData['name']
                ]);
                
                if (isset($optData['values']) && is_array($optData['values'])) {
                    foreach ($optData['values'] as $valData) {
                        if (empty($valData['value'])) continue;
                        
                        $priceFinal = floatval($valData['price']);
                        $modifier = $priceFinal - floatval($validated['base_price']);
                        
                        $option->values()->create([
                            'value' => $valData['value'],
                            'price_modifier' => $modifier
                        ]);
                    }
                }
            }
        } else {
            $product->options()->delete();
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(\App\Models\Product $product)
    {
        if ($product->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->image);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Producto eliminado exitosamente.');
    }
}
