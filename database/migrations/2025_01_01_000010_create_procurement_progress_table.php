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
            $table->unsignedBigInteger('request_id')->nullable();
            $table->unsignedBigInteger('checkpoint_id')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delayed'])->default('pending');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('request_id')->references('request_id')->on('request_procurements')->onDelete('cascade');
            $table->foreign('checkpoint_id')->references('point_id')->on('checkpoints')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurement_progress');
    }
};
