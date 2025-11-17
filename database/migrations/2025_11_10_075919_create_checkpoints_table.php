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

            // FK ke divisions (PIC checkpoint)
            $table->unsignedBigInteger('responsible_division')->nullable();

            // field dari ERD
            $table->boolean('is_true')->default(false);

            // tambahan custom field (kalau ingin)
            $table->boolean('is_final')->default(false);

            $table->timestamps();

            // foreign key yang benar
            $table->foreign('responsible_division')
                  ->references('division_id')
                  ->on('divisions')
                  ->nullOnDelete();
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
