<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('category_id');
            });

            // Inicializar el orden secuencial (1, 2, 3...) dentro de cada categoría
            $categories = DB::table('products')->select('category_id')->distinct()->pluck('category_id');
            foreach ($categories as $categoryId) {
                $products = DB::table('products')
                    ->where('category_id', $categoryId)
                    ->orderBy('id', 'asc')
                    ->pluck('id');

                $position = 1;
                foreach ($products as $productId) {
                    DB::table('products')
                        ->where('id', $productId)
                        ->update(['sort_order' => $position++]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'sort_order')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};

