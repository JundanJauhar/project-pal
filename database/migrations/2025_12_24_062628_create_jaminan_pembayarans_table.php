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
        Schema::create('jaminan_pembayarans', function (Blueprint $table) {
            $table->id('jaminan_pembayaran_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('procurement_id')->nullable();

            // Jenis Jaminan
            $table->enum('jenis_jaminan', [
                'advance_payment_guarantee',
                'performance_bond',
                'warranty_bond'
            ]);

            // Timeline Jaminan
            $table->date('target_terbit')->nullable();
            $table->date('realisasi_terbit')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('link')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('vendor_id')
                ->references('id_vendor')
                ->on('vendors')
                ->cascadeOnDelete();

            $table->foreign('procurement_id')
                ->references('procurement_id')
                ->on('procurement')
                ->cascadeOnDelete();

            // Indexes
            $table->index('vendor_id');
            $table->index('procurement_id');
            $table->index('target_terbit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jaminan_pembayarans');
    }
};
