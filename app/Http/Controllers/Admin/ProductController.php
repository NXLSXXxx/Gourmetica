<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('admin')->user();
        $headquarters = \App\Models\Headquarter::where('is_active', true)->get();
        $categories = \App\Models\Category::withCount('products')->orderBy('name')->get();

        $selectedHqId = $request->input('hq_id');
        if (($user && ($user->isSedeAdmin() || $user->isCajero())) && !$selectedHqId) {
            $selectedHqId = $user->headquarter_id;
        }

        $query = \App\Models\Product::with(['category', 'headquarters']);

        // Filtro por categoría (acepta category_id o category por ID o slug)
        $categoryFilter = $request->input('category_id', $request->input('category'));
        if ($categoryFilter !== null && $categoryFilter !== '') {
            if (is_numeric($categoryFilter)) {
                $query->where('category_id', $categoryFilter);
            } else {
                $query->whereHas('category', function ($q) use ($categoryFilter) {
                    $q->where('slug', $categoryFilter);
                });
            }
        }

        // Búsqueda por texto
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por estado global (activo / inactivo)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filtro por rango de precio
        if ($request->filled('min_price') && is_numeric($request->min_price)) {
            $query->where('base_price', '>=', (float) $request->min_price);
        }
        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $query->where('base_price', '<=', (float) $request->max_price);
        }

        // Filtro por disponibilidad en sede
        if ($selectedHqId && $request->boolean('only_available')) {
            $query->whereHas('headquarters', function($q) use ($selectedHqId) {
                $q->where('headquarters.id', $selectedHqId)
                  ->where('headquarter_product.is_available', true);
            });
        }

        // Filtro por stock
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->whereHas('headquarters', function ($q) use ($selectedHqId) {
                    if ($selectedHqId) {
                        $q->where('headquarters.id', $selectedHqId);
                    }
                    $q->where('headquarter_product.stock', '>', 0);
                });
            } elseif ($request->stock_status === 'out_of_stock') {
                if ($selectedHqId) {
                    $query->whereDoesntHave('headquarters', function ($q) use ($selectedHqId) {
                        $q->where('headquarters.id', $selectedHqId)
                          ->where('headquarter_product.stock', '>', 0);
                    });
                } else {
                    $query->whereDoesntHave('headquarters', function ($q) {
                        $q->where('headquarter_product.stock', '>', 0);
                    });
                }
            }
        }

        // Ordenamiento (por defecto organizado por categoría y orden personalizado 1°, 2°, 3°...)
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

        $products = $query->paginate(50)->withQueryString();
        return view('admin.products.index', compact('products', 'headquarters', 'categories', 'selectedHqId'));
    }

    /**
     * Reubica un producto en una posición específica (1°, 2°, 3°...) dentro de su categoría
     * y renumera secuencialmente los demás productos de la categoría.
     */
    protected function placeProductAtPosition(\App\Models\Product $product, int $targetPosition): void
    {
        if (!\App\Models\Product::hasSortOrderColumn()) {
            return;
        }

        $siblings = \App\Models\Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->values();

        $clampedIndex = max(0, min($targetPosition - 1, $siblings->count()));
        $siblings->splice($clampedIndex, 0, [$product]);

        foreach ($siblings as $idx => $item) {
            $newOrder = $idx + 1;
            if ((int) $item->sort_order !== $newOrder) {
                \App\Models\Product::where('id', $item->id)->update(['sort_order' => $newOrder]);
                if ($item->id === $product->id) {
                    $product->sort_order = $newOrder;
                }
            }
        }
    }

    /**
     * Endpoint para ordenar la lista de postres/productos (1°, 2°, 3°...)
     * Soporta drag & drop (ordered_ids), cambio directo de posición (position) o botones subir/bajar (direction).
     */
    public function reorder(Request $request)
    {
        if (!\App\Models\Product::hasSortOrderColumn()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Columna sort_order no disponible.'], 400);
            }
            return back()->with('error', 'Ejecuta las migraciones pendientes para habilitar el ordenamiento.');
        }

        // Caso 1: Lista completa de IDs ordenados (Drag & Drop)
        if ($request->filled('ordered_ids') && is_array($request->ordered_ids)) {
            foreach (array_values($request->ordered_ids) as $index => $id) {
                \App\Models\Product::where('id', (int) $id)->update(['sort_order' => $index + 1]);
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Orden actualizado correctamente.']);
            }
            return back()->with('success', 'Orden de productos actualizado correctamente.');
        }

        // Caso 2: Mover un producto específico por posición numérica (1°, 2°, 3°...) o dirección (up/down)
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'position' => 'nullable|integer|min:1',
            'direction' => 'nullable|in:up,down',
        ]);

        $product = \App\Models\Product::findOrFail($request->product_id);

        // Asegurar que la categoría tenga orden secuencial base antes de mover
        $categoryProducts = \App\Models\Product::where('category_id', $product->category_id)
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->values();

        $currentIndex = $categoryProducts->search(fn($item) => $item->id === $product->id);
        $currentPosition = $currentIndex !== false ? $currentIndex + 1 : 1;

        if ($request->filled('position')) {
            $targetPosition = (int) $request->position;
        } elseif ($request->direction === 'up') {
            $targetPosition = max(1, $currentPosition - 1);
        } elseif ($request->direction === 'down') {
            $targetPosition = min($categoryProducts->count(), $currentPosition + 1);
        } else {
            $targetPosition = $currentPosition;
        }

        $this->placeProductAtPosition($product, $targetPosition);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Posición de {$product->name} actualizada a #{$product->sort_order}.",
            ]);
        }

        return back()->with('success', "Se ubicó \"{$product->name}\" en la posición #{$product->sort_order}.");
    }

    public function create()
    {
        $categories = \App\Models\Category::all();
        $headquarters = \App\Models\Headquarter::all();
        $supplies = \App\Models\Supply::all();
        return view('admin.products.create', compact('categories', 'headquarters', 'supplies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sort_order' => 'nullable|integer|min:1',
            'base_price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'headquarters' => 'nullable|array',
            'headquarters.*.is_available' => 'nullable',
            'headquarters.*.stock' => 'nullable|integer|min:0',
            'headquarters.*.price' => 'nullable|numeric|min:0',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string|max:255',
            'options.*.values' => 'required_with:options|array',
            'options.*.values.*.value' => 'required|string|max:255',
            'options.*.values.*.price' => 'required|numeric|min:0',
            'recipe' => 'nullable|array',
            'recipe.*.supply_id' => 'required_with:recipe|exists:supplies,id',
            'recipe.*.quantity' => 'required_with:recipe|numeric|min:0.0001',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = \App\Models\Product::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'base_price' => $validated['base_price'],
            'description' => $validated['description'] ?? null,
            'image' => $imagePath,
            'is_active' => true,
        ]);

        if (!empty($validated['sort_order'])) {
            $this->placeProductAtPosition($product, (int) $validated['sort_order']);
        }

        if ($request->has('headquarters') && is_array($request->headquarters)) {
            $syncData = [];
            foreach ($request->headquarters as $hqId => $data) {
                $isAvailable = isset($data['is_available']) && ($data['is_available'] == '1' || $data['is_available'] === 'on');
                $price = (isset($data['price']) && $data['price'] !== '') ? (float)$data['price'] : null;
                $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
                $syncData[$hqId] = [
                    'is_available' => $isAvailable,
                    'stock' => $stock,
                    'price' => $price,
                ];
            }
            $product->headquarters()->sync($syncData);
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
        
        if ($request->has('recipe') && is_array($request->recipe)) {
            foreach ($request->recipe as $item) {
                if (empty($item['supply_id']) || empty($item['quantity'])) continue;
                $product->recipes()->create([
                    'supply_id' => $item['supply_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Producto creado exitosamente.');
    }

    public function edit(\App\Models\Product $product)
    {
        $categories = \App\Models\Category::all();
        $headquarters = \App\Models\Headquarter::all();
        $supplies = \App\Models\Supply::all();
        $product->load(['headquarters', 'options.values', 'recipes.supply']);
        return view('admin.products.edit', compact('product', 'categories', 'headquarters', 'supplies'));
    }

    public function update(Request $request, \App\Models\Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'sort_order' => 'nullable|integer|min:1',
            'base_price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:10240',
            'is_active' => 'boolean',
            'headquarters' => 'nullable|array',
            'headquarters.*.is_available' => 'nullable',
            'headquarters.*.stock' => 'nullable|integer|min:0',
            'headquarters.*.price' => 'nullable|numeric|min:0',
            'options' => 'nullable|array',
            'options.*.name' => 'required_with:options|string|max:255',
            'options.*.values' => 'required_with:options|array',
            'options.*.values.*.value' => 'required|string|max:255',
            'options.*.values.*.price' => 'required|numeric|min:0',
            'recipe' => 'nullable|array',
            'recipe.*.supply_id' => 'required_with:recipe|exists:supplies,id',
            'recipe.*.quantity' => 'required_with:recipe|numeric|min:0.0001',
        ]);

        $oldImagePath = $product->image;
        $newImagePath = null;
        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')->store('products', 'public');

            if (!$newImagePath) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'No se pudo guardar la imagen. Verifica los permisos de almacenamiento e inténtalo nuevamente.']);
            }
        }

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'base_price' => $validated['base_price'],
            'description' => $validated['description'] ?? null,
            'image' => $newImagePath ?? $product->image,
            'is_active' => $request->has('is_active'),
        ]);

        if (!empty($validated['sort_order'])) {
            $this->placeProductAtPosition($product, (int) $validated['sort_order']);
        }

        if ($newImagePath && $oldImagePath) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImagePath);
        }

        if ($request->has('headquarters') && is_array($request->headquarters)) {
            $syncData = [];
            foreach ($request->headquarters as $hqId => $data) {
                $isAvailable = isset($data['is_available']) && ($data['is_available'] == '1' || $data['is_available'] === 'on');
                $price = (isset($data['price']) && $data['price'] !== '') ? (float)$data['price'] : null;
                $stock = isset($data['stock']) ? (int)$data['stock'] : 0;
                $syncData[$hqId] = [
                    'is_available' => $isAvailable,
                    'stock' => $stock,
                    'price' => $price,
                ];
            }
            $product->headquarters()->sync($syncData);
        }

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

        if ($request->has('recipe') && is_array($request->recipe)) {
            $product->recipes()->delete();
            foreach ($request->recipe as $item) {
                if (empty($item['supply_id']) || empty($item['quantity'])) continue;
                $product->recipes()->create([
                    'supply_id' => $item['supply_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        } else {
            $product->recipes()->delete();
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
