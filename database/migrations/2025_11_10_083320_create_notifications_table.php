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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('notification_id');
            $table->unsignedBigInteger('user_id'); // Penerima notifikasi
            $table->unsignedBigInteger('sender_id')->nullable(); // Pengirim notifikasi
            $table->string('type'); // jenis notifikasi: approval, evatek, payment, etc
            $table->string('title');
            $table->text('message');
            $table->string('reference_type')->nullable(); // Model yang direferensikan
            $table->unsignedBigInteger('reference_id')->nullable(); // ID dari model yang direferensikan
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
