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
        // Pastikan audit_logs sudah ada
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop FK existing untuk ubah kolom
            $table->dropForeign(['actor_user_id']);

            // Set kolom menjadi nullable
            $table->unsignedBigInteger('actor_user_id')->nullable()->change();

            // Re-add FK sesuai struktur users project_pal
            $table->foreign('actor_user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop FK
            $table->dropForeign(['actor_user_id']);

            // Ubah kembali menjadi NOT NULL
            $table->unsignedBigInteger('actor_user_id')->nullable(false)->change();

            // Tambahkan FK default (user_id masih dipakai)
            $table->foreign('actor_user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
