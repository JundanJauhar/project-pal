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
        Schema::create('pengiriman_materials', function (Blueprint $table) {
            $table->id('pengiriman_id');
            $table->unsignedBigInteger('procurement_id')->nullable();
            $table->unsignedBigInteger('vendor_id');

            // Jadwal pengiriman
            $table->date('etd')->nullable(); // Estimated Time of Departure
            $table->date('atd')->nullable(); // Actual Time of Departure
            
            $table->date('eta_sby_port')->nullable(); // Estimated Time Arrival Surabaya Port
            $table->date('ata_sby_port')->nullable(); // Actual Time Arrival Surabaya Port
            
            $table->date('eta_pal')->nullable(); // Estimated Time Arrival PAL
            $table->date('ata_pal')->nullable(); // Actual Time Arrival PAL
            
            $table->date('received_at')->nullable(); // Tanggal material diterima di PAL            
            $table->longText('remark')->nullable();
            
            $table->timestamps();
            
            $table->foreign('procurement_id')->references('procurement_id')->on('procurement')->nullOnDelete();
            $table->foreign('vendor_id')->references('id_vendor')->on('vendors')->cascadeOnDelete();
            
            $table->index('vendor_id');
            $table->index('procurement_id');
            $table->index('etd');
            $table->index('received_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengiriman_materials');
    }
};
