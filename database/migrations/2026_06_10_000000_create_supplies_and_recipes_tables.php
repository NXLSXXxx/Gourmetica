<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Supplies (Insumos) Table
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit', 20)->default('g'); // e.g., g, ml, und
            $table->timestamps();
        });

        // 2. Headquarter Supply Stock Pivot
        Schema::create('headquarter_supply', function (Blueprint $table) {
            $table->id();
            $table->foreignId('headquarter_id')->constrained()->onDelete('cascade');
            $table->foreignId('supply_id')->constrained()->onDelete('cascade');
            $table->decimal('stock', 12, 4)->default(0);
            $table->timestamps();
        });

        // 3. Recipes Table (Product Supplies / Formula)
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('supply_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 12, 4)->default(0); // quantity of supply required for 1 unit of product
            $table->timestamps();
        });

        // 4. Productions Table (Log)
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('headquarter_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity'); // quantity of products prepared
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productions');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('headquarter_supply');
        Schema::dropIfExists('supplies');
    }
};
