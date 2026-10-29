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

        // Only show products that have a recipe configured with ingredients eager loaded
        $products = Product::with('recipes.supply')->has('recipes')->where('is_active', true)->get();

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

        $quantity = (int)$request->quantity;

        return DB::transaction(function() use ($request, $quantity, $user) {
            $product = Product::with('recipes.supply')->lockForUpdate()->findOrFail($request->product_id);

            // Verify if product has recipe
            if ($product->recipes->isEmpty()) {
                return back()->with('error', 'El producto seleccionado no tiene una receta configurada.');
            }

            // 1. Verify enough stock of all ingredients with row locking
            $insufficientSupplies = [];
            $suppliesToDeduct = [];

            foreach ($product->recipes as $recipe) {
                $supply = $recipe->supply;
                $needed = (float)($recipe->quantity * $quantity);

                // Find current stock in headquarter_supply with exclusive lock
                $stockRecord = DB::table('headquarter_supply')
                    ->where('headquarter_id', $request->headquarter_id)
                    ->where('supply_id', $supply->id)
                    ->lockForUpdate()
                    ->first();

                $available = $stockRecord ? (float)$stockRecord->stock : 0.0;

                if ($available < $needed) {
                    $insufficientSupplies[] = [
                        'name' => $supply->name,
                        'unit' => $supply->unit,
                        'needed' => $needed,
                        'available' => $available,
                    ];
                } else {
                    $suppliesToDeduct[] = [
                        'id' => $stockRecord->id,
                        'needed' => $needed,
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

            // 2. A. Deduct ingredients
            foreach ($suppliesToDeduct as $deduction) {
                DB::table('headquarter_supply')
                    ->where('id', $deduction['id'])
                    ->decrement('stock', $deduction['needed']);
            }

            // 2. B. Increment product stock in headquarter_product
            $hqProduct = DB::table('headquarter_product')
                ->where('headquarter_id', $request->headquarter_id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            if ($hqProduct) {
                DB::table('headquarter_product')
                    ->where('id', $hqProduct->id)
                    ->increment('stock', $quantity);
            } else {
                DB::table('headquarter_product')->insert([
                    'headquarter_id' => $request->headquarter_id,
                    'product_id' => $product->id,
                    'stock' => $quantity,
                    'price' => $product->base_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 2. C. Create production record log
            Production::create([
                'user_id' => $user->id,
                'headquarter_id' => $request->headquarter_id,
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            return redirect()->route('admin.productions.index')
                ->with('success', "Producción registrada exitosamente: Se incrementaron {$quantity} unidades de '{$product->name}' y se descontaron los insumos de cocina.");
        });
    }
}
