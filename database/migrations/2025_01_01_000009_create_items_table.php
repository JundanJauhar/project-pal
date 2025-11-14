<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id('item_id');
            $table->unsignedBigInteger('request_procurement_id');
            $table->string('item_name', 100)->nullable();
            $table->text('item_description')->nullable();
            $table->integer('amount')->nullable();
            $table->string('unit', 20)->nullable();
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('total_price')->default(0);
            $table->timestamps();

            $table->foreign('request_procurement_id')->references('request_id')->on('request_procurements')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
