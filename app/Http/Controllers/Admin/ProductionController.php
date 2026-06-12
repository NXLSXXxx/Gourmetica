<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Production;
use App\Models\Product;
use App\Models\Headquarter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function index()
    {
        $user = auth('admin')->user();
        $query = Production::with(['user', 'headquarter', 'product'])->latest();

        if ($user->isSedeAdmin() || $user->isCajero()) {
            $query->where('headquarter_id', $user->headquarter_id);
        }

        $productions = $query->paginate(15);
        return view('admin.productions.index', compact('productions'));
    }

    public function create()
    {
        $user = auth('admin')->user();
        $hqQuery = Headquarter::where('is_active', true);

        if ($user->isSedeAdmin() || $user->isCajero()) {
            $hqQuery->where('id', $user->headquarter_id);
        }

        $headquarters = $hqQuery->get();

        // Only show products that have a recipe configured
        $products = Product::has('recipes')->where('is_active', true)->get();

        return view('admin.productions.create', compact('headquarters', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'headquarter_id' => 'required|exists:headquarters,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth('admin')->user();
        if (($user->isSedeAdmin() || $user->isCajero()) && $user->headquarter_id !== (int)$request->headquarter_id) {
            abort(403, 'No tienes permiso para registrar una producción en esta sede.');
        }

        $product = Product::with('recipes.supply')->findOrFail($request->product_id);
        $quantity = (int)$request->quantity;

        // Verify if product has recipe
        if ($product->recipes->isEmpty()) {
            return back()->with('error', 'El producto seleccionado no tiene una receta configurada.');
        }

        // 1. Verify enough stock of all ingredients
        $insufficientSupplies = [];

        foreach ($product->recipes as $recipe) {
            $supply = $recipe->supply;
            $needed = $recipe->quantity * $quantity;

            // Find current stock in headquarter_supply
            $stockRecord = DB::table('headquarter_supply')
                ->where('headquarter_id', $request->headquarter_id)
                ->where('supply_id', $supply->id)
                ->first();

            $available = $stockRecord ? (float)$stockRecord->stock : 0.0;

            if ($available < $needed) {
                $insufficientSupplies[] = [
                    'name' => $supply->name,
                    'unit' => $supply->unit,
                    'needed' => $needed,
                    'available' => $available,
                ];
            }
        }

        if (!empty($insufficientSupplies)) {
            $errMsg = 'No hay suficiente stock de insumos para esta producción:';
            foreach ($insufficientSupplies as $ins) {
                $errMsg .= sprintf(
                    '<br>- <b>%s</b>: Se requieren %.2f %s, pero solo hay %.2f %s disponibles.',
                    $ins['name'],
                    $ins['needed'],
                    $ins['unit'],
                    $ins['available'],
                    $ins['unit']
                );
            }
            return back()->with('error', $errMsg);
        }

        // 2. Perform transaction to update inventory and log production
        DB::transaction(function() use ($request, $product, $quantity, $user) {
            // A. Deduct ingredients
            foreach ($product->recipes as $recipe) {
                $supply = $recipe->supply;
                $needed = $recipe->quantity * $quantity;

                // Subtract from headquarter_supply
                DB::table('headquarter_supply')
                    ->where('headquarter_id', $request->headquarter_id)
                    ->where('supply_id', $supply->id)
                    ->decrement('stock', $needed);
            }

            // B. Increment product stock in headquarter_product
            $hqProduct = $product->headquarters()->where('headquarter_id', $request->headquarter_id)->first();
            if ($hqProduct) {
                $newProductStock = $hqProduct->pivot->stock + $quantity;
                $product->headquarters()->updateExistingPivot($request->headquarter_id, [
                    'stock' => $newProductStock,
                ]);
            } else {
                // If not associated yet, attach it
                $product->headquarters()->attach($request->headquarter_id, [
                    'stock' => $quantity,
                    'price' => $product->base_price,
                ]);
            }

            // C. Create production record log
            Production::create([
                'user_id' => $user->id,
                'headquarter_id' => $request->headquarter_id,
                'product_id' => $request->product_id,
                'quantity' => $quantity,
            ]);
        });

        return redirect()->route('admin.productions.index')->with('success', 'Producción registrada exitosamente. Se descontaron los insumos e incrementó el stock del producto.');
    }
}
