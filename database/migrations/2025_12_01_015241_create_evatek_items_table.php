<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
    Schema::create('evatek_items', function (Blueprint $table) {
        $table->id('id_evatek_item');

        $table->unsignedBigInteger('item_id');

        // FIX: harus string karena vendor.id_vendor adalah string
        $table->string('vendor_id', 20);

        $table->enum('status', ['pending', 'approved', 'not_approved'])->default('pending');
        $table->text('evaluation_note')->nullable();
        $table->timestamps();

        $table->foreign('item_id')->references('item_id')->on('items')->onDelete('cascade');
        $table->foreign('vendor_id')->references('id_vendor')->on('vendors')->onDelete('cascade');
    });

    }

    public function down()
    {
        Schema::dropIfExists('evatek_items');   
    }

};
