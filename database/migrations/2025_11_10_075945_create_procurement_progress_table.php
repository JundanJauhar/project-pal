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
        Schema::create('procurement_progress', function (Blueprint $table) {
            $table->id('progress_id');

            // FK ke procurement (monitoring dilakukan di level procurement, bukan request)
            $table->unsignedBigInteger('procurement_id');

            // FK ke checkpoint
            $table->unsignedBigInteger('checkpoint_id');

            // FK ke users
            $table->unsignedBigInteger('user_id')->nullable();

            // Updated enum: 3 nilai sesuai dengan status_procurement
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])
                  ->default('in_progress');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('note')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('procurement_id')
                  ->references('procurement_id')
                  ->on('procurement')
                  ->cascadeOnDelete();

            $table->foreign('checkpoint_id')
                  ->references('point_id')
                  ->on('checkpoints')
                  ->cascadeOnDelete();

            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement_progress');
    }
};