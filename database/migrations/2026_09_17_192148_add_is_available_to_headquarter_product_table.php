<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('headquarter_product', function (Blueprint $table) {
            if (!Schema::hasColumn('headquarter_product', 'is_available')) {
                $table->boolean('is_available')->default(true)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('headquarter_product', function (Blueprint $table) {
            if (Schema::hasColumn('headquarter_product', 'is_available')) {
                $table->dropColumn('is_available');
            }
        });
    }
};
