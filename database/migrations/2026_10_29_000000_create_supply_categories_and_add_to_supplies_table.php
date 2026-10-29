<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('supplies', function (Blueprint $table) {
            $table->foreignId('supply_category_id')->nullable()->after('name')->constrained('supply_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropForeign(['supply_category_id']);
            $table->dropColumn('supply_category_id');
        });

        Schema::dropIfExists('supply_categories');
    }
};
