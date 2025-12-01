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

            // user actor
            $table->unsignedBigInteger('actor_user_id')->nullable();
            
            // contoh: users, settings, admin_scopes
            $table->string('module', 120);

            // contoh: create, update, delete, force_logout, reset_password
            $table->string('action', 120);

            // ID target dalam module, contoh user_id, setting_id
            $table->unsignedBigInteger('target_id')->nullable();

            // JSON payload
            $table->json('details')->nullable();

            // waktu aktivitas
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
