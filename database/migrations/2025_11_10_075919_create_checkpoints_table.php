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
        Schema::create('checkpoints', function (Blueprint $table) {
            $table->id('point_id');
            $table->string('point_name');
            $table->integer('point_sequence');
            $table->unsignedBigInteger('responsible_division')->nullable();
            $table->boolean('is_final')->default(false);
            $table->timestamps();

            $table->foreign('responsible_division')->references('divisi_id')->on('divisions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkpoints');
    }
};
