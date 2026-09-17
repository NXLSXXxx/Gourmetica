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
            $table->date('delivery_date')->nullable()->after('delivery_price');
            $table->string('delivery_time')->nullable()->after('delivery_date');
            $table->string('phone')->nullable()->after('delivery_time');
            $table->string('email')->nullable()->after('phone');
            $table->string('invoice_type')->nullable()->after('email');
            $table->string('invoice_document')->nullable()->after('invoice_type');
            $table->boolean('is_gift')->default(false)->after('invoice_document');
            $table->boolean('has_card')->default(false)->after('is_gift');
            $table->boolean('has_dedication')->default(false)->after('has_card');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_date', 'delivery_time', 'phone', 'email', 
                'invoice_type', 'invoice_document', 'is_gift', 
                'has_card', 'has_dedication'
            ]);
        });
    }
};
