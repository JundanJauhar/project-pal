<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengadaan_ocs', function (Blueprint $table) {
            $table->id('pengadaan_oc_id');

            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('vendor_id')->nullable();

            $table->string('currency', 10)->default('IDR');
            $table->decimal('nilai', 15, 2)->nullable();

            $table->date('tgl_kadep_to_kadiv')->nullable();
            $table->date('tgl_kadiv_to_cto')->nullable();
            $table->date('tgl_cto_to_ceo')->nullable();
            $table->date('tgl_acc')->nullable();

            $table->longText('remarks')->nullable();

            $table->timestamps();

            $table->foreign('procurement_id')
                ->references('procurement_id')->on('procurement')
                ->cascadeOnDelete();

            $table->foreign('vendor_id')
                ->references('id_vendor')->on('vendors')
                ->nullOnDelete();

            $table->index('procurement_id');
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengadaan_ocs');
    }
};
