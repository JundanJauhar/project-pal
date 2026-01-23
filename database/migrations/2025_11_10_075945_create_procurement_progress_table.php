<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurement_progress', function (Blueprint $table) {
            $table->id('progress_id');

            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('checkpoint_id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->enum('status', ['in_progress', 'completed', 'cancelled'])
                ->default('in_progress');

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->text('note')->nullable();

            $table->timestamps();

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

    public function down(): void
    {
        Schema::dropIfExists('procurement_progress');
    }
};
