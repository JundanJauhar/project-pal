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
        Schema::table('material_deliveries', function (Blueprint $table) {
            $table->string('imo_number', 20)->nullable()->after('incoterms');
            $table->string('container_number', 50)->nullable()->after('imo_number');
            
            // Add index for better query performance
            $table->index('imo_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_deliveries', function (Blueprint $table) {
            $table->dropIndex(['imo_number']);
            $table->dropColumn(['imo_number', 'container_number']);
        });
    }
};
