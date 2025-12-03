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
        Schema::create('negotiations', function (Blueprint $table) {
            $table->id('negotiation_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('procurement_id')->nullable();
            
            $table->decimal('hps', 15, 2)->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('harga_final', 15, 2)->nullable();
            $table->string('currency', 10)->default('IDR');
            
            $table->date('tanggal_kirim_ke_vendor')->nullable();
            $table->date('tanggal_vendor_terima')->nullable();
            $table->string('lead_time')->nullable();
            $table->longText('note')->nullable();            
            $table->timestamps();
            
            $table->foreign('vendor_id')->references('id_vendor')->on('vendors')->cascadeOnDelete();
            $table->foreign('procurement_id')->references('procurement_id')->on('procurement')->nullOnDelete();

            $table->index('vendor_id');
            $table->index('procurement_id');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('negotiations');
    }
};
