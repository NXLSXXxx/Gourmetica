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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Customer
            $table->foreignId('headquarter_id')->constrained(); // Sede that made the sale
            $table->string('document_type'); // 01: Factura, 03: Boleta
            $table->string('series'); // e.g. F001
            $table->unsignedInteger('correlative');
            $table->decimal('total', 10, 2);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            
            // SUNAT fields
            $table->string('sunat_status')->default('pending'); // pending, accepted, rejected
            $table->text('sunat_response')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->string('cdr_path')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
