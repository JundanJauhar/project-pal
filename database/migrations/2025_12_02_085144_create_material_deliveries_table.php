<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_deliveries', function (Blueprint $table) {
            $table->id('delivery_id');
            $table->unsignedBigInteger('procurement_id');
            
            $table->string('incoterms')->nullable();
            $table->date('etd')->nullable();
            $table->date('eta_sby_port')->nullable();
            $table->date('eta_pal')->nullable();
            $table->date('atd')->nullable();
            $table->date('ata_sby_port')->nullable();
            $table->longText('remark')->nullable();
            $table->timestamps();
            
            $table->foreign('procurement_id')->references('procurement_id')->on('procurement')->cascadeOnDelete();
            
            $table->index('procurement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_deliveries');
    }
};
?>