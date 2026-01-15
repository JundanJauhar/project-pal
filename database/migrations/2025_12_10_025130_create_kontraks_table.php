<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kontraks', function (Blueprint $table) {
            $table->id('kontrak_id');

            $table->unsignedBigInteger('procurement_id');
            $table->string('no_po')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();

            $table->date('tgl_kontrak')->nullable();
            $table->string('maker')->nullable();

            $table->string('currency', 10)->default('IDR');
            $table->decimal('nilai', 15, 2)->nullable();

            $table->string('payment_term')->nullable();
            $table->string('incoterms')->nullable();
            $table->string('coo')->nullable();
            $table->string('warranty')->nullable();

            $table->longText('remarks')->nullable();

            $table->timestamps();

            $table->foreign('procurement_id')
                ->references('procurement_id')->on('procurement')
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('item_id')->on('items')
                ->nullOnDelete();

            $table->foreign('vendor_id')
                ->references('id_vendor')->on('vendors')
                ->nullOnDelete();

            $table->index('procurement_id');
            $table->index('item_id');
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kontraks');
    }
};
