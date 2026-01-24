<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evatek_items', function (Blueprint $table) {
            $table->id('evatek_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('vendor_id')->nullable();

            $table->enum('pic_evatek', ['EO', 'HC', 'MO', 'HO', 'SEWACO'])
                  ->nullable()
                  ->comment('EO=Engineering Officer, HC=Head of Construction, MO=Material Officer, HO=Head of Operations');

            $table->enum('evatek_status', ['evatek-vendor', 'evatek-desain'])
                  ->nullable()
                  ->default(null)
                  ->comment('evatek-vendor=Waiting for vendor link, evatek-desain=Waiting for design link, null=Complete or Final');

            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();

            $table->string('current_revision')->default('R0');
            $table->enum('status', ['approve','not_approve','on_progress'])
                  ->default('on_progress');
            $table->date('current_date')->nullable();

            $table->text('sc_design_link')->nullable()
                  ->comment('Link desain dari divisi Supply Chain/Desain');

            $table->longText('log')->nullable();

            $table->timestamps();

            $table->foreign('item_id')->references('item_id')->on('items')->cascadeOnDelete();
            $table->foreign('procurement_id')->references('procurement_id')->on('procurement')->cascadeOnDelete();
            $table->foreign('vendor_id')->references('id_vendor')->on('vendors')->nullOnDelete();

            $table->index('procurement_id');
            $table->index('vendor_id');
            $table->index('status');
            $table->index('pic_evatek');
            $table->index('evatek_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evatek_items');
    }
};