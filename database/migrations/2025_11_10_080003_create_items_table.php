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
        Schema::create('items', function (Blueprint $table) {
            $table->id('item_id');

            // FK ke request procurement
            $table->unsignedBigInteger('request_procurement_id');

            // Sesuai ERD
            $table->string('item_name', 200);
            $table->text('item_description')->nullable();

            // Kolom tambahan yang kamu ingin gunakan
            $table->text('specification')->nullable();

            $table->integer('amount')->nullable();
            $table->string('unit', 50)->nullable();

            // Standard Laravel timestamps
            $table->timestamps();

            // Foreign key
            $table->foreign('request_procurement_id')
                  ->references('request_id')
                  ->on('request_procurement')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
