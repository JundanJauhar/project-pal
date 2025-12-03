<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evatek_items', function (Blueprint $table) {
            // Add unique constraint to prevent duplicates
            $table->unique(['item_id', 'procurement_id', 'vendor_id'], 'unique_item_procurement_vendor');
        });
    }

    public function down(): void
    {
        Schema::table('evatek_items', function (Blueprint $table) {
            $table->dropUnique('unique_item_procurement_vendor');
        });
    }
};
