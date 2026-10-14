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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'delivery_date')) {
                $table->date('delivery_date')->nullable()->after('delivery_price');
            }
            if (!Schema::hasColumn('orders', 'delivery_time')) {
                $table->string('delivery_time')->nullable()->after('delivery_date');
            }
            if (!Schema::hasColumn('orders', 'phone')) {
                $table->string('phone')->nullable()->after('delivery_time');
            }
            if (!Schema::hasColumn('orders', 'email')) {
                $table->string('email')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('orders', 'invoice_type')) {
                $table->string('invoice_type')->nullable()->after('email');
            }
            if (!Schema::hasColumn('orders', 'invoice_document')) {
                $table->string('invoice_document')->nullable()->after('invoice_type');
            }
            if (!Schema::hasColumn('orders', 'is_gift')) {
                $table->boolean('is_gift')->default(false)->after('invoice_document');
            }
            if (!Schema::hasColumn('orders', 'has_card')) {
                $table->boolean('has_card')->default(false)->after('is_gift');
            }
            if (!Schema::hasColumn('orders', 'has_dedication')) {
                $table->boolean('has_dedication')->default(false)->after('has_card');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columnsToDrop = array_filter([
                'delivery_date', 'delivery_time', 'phone', 'email', 
                'invoice_type', 'invoice_document', 'is_gift', 
                'has_card', 'has_dedication'
            ], fn($col) => Schema::hasColumn('orders', $col));

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
