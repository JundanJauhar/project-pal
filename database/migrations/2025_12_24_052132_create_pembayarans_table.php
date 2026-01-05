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
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id(); // primary key default

            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('procurement_id');

            $table->string('payment_type', 20); // SKBDN | L/C | TT
            $table->decimal('percentage', 5, 2); // persen pembayaran
            $table->decimal('payment_value', 15, 2); // nilai hasil kalkulasi
            $table->string('currency', 10)->default('IDR');

            $table->string('no_memo')->nullable();
            $table->string('link')->nullable();

            $table->date('target_date')->nullable();
            $table->date('realization_date')->nullable();

            $table->timestamps();

            // ================= FOREIGN KEYS =================
            $table->foreign('vendor_id')
                ->references('id_vendor')
                ->on('vendors')
                ->cascadeOnDelete();

            $table->foreign('procurement_id')
                ->references('procurement_id')
                ->on('procurement')
                ->cascadeOnDelete();

            // ================= INDEX =================
            $table->index('vendor_id');
            $table->index('procurement_id');
            $table->index('payment_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
