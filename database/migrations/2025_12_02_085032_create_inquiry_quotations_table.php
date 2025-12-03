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
        Schema::create('inquiry_quotations', function (Blueprint $table) {
            $table->id('inquiry_quotation_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('procurement_id')->nullable();
            
            $table->date('tanggal_inquiry');
            $table->date('tanggal_quotation')->nullable();
            $table->date('target_quotation')->nullable();
            $table->string('lead_time')->nullable();
            
            $table->decimal('nilai_harga', 15, 2)->nullable();
            $table->string('currency', 10)->default('IDR');
            $table->longText('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('vendor_id')->references('id_vendor')->on('vendors')->cascadeOnDelete();
            $table->foreign('procurement_id')->references('procurement_id')->on('procurement')->nullOnDelete();
            
            $table->index('vendor_id');
            $table->index('procurement_id');
            $table->index('tanggal_inquiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiry_quotations');
    }
};
