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
            $table->unsignedBigInteger('permintaan_pengadaan_id');
            $table->unsignedBigInteger('titik_id');
            $table->enum('status_progress', ['not_started', 'in_progress', 'completed', 'blocked'])->default('not_started');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
