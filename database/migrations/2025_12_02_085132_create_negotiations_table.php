<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negotiations', function (Blueprint $table) {
            $table->id('negotiation_id');

            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('vendor_id')->nullable(); // vendor dipilih manual

            $table->decimal('hps', 15, 2)->nullable();
            $table->string('currency_hps', 10)->default('IDR');
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('currency_budget', 10)->default('IDR');
            $table->decimal('harga_final', 15, 2)->nullable();
            $table->string('currency_harga_final', 10)->default('IDR');

            $table->date('tanggal_kirim')->nullable();
            $table->date('tanggal_terima')->nullable();
            $table->longText('notes')->nullable();

            $table->timestamps();

            $table->foreign('procurement_id')
                ->references('procurement_id')->on('procurement')
                ->cascadeOnDelete();

            $table->foreign('vendor_id')
                ->references('id_vendor')->on('vendors')
                ->nullOnDelete();

            $table->index('procurement_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negotiations');
    }
};
