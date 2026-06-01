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
        $items = \App\Models\OrderItem::all();
        foreach ($items as $item) {
            // If it's already a string, it means it was double encoded
            if (is_string($item->options)) {
                $decoded = json_decode($item->options, true);
                if (is_array($decoded)) {
                    $item->options = $decoded;
                    $item->save();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
