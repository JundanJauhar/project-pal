<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');

            // ID user yang melakukan aksi
            $table->unsignedBigInteger('actor_user_id')->nullable();

            // Modul yang terpengaruh, contoh: users, procurements, projects
            $table->string('module', 120);

            // Aksi yang dilakukan: create, update, delete, approve, reject, dsb
            $table->string('action', 120);

            // ID dari objek yang dipengaruhi
            $table->unsignedBigInteger('target_id')->nullable();

            // Detail tambahan sebagai JSON
            $table->json('details')->nullable();

            // Timestamp log
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('actor_user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
