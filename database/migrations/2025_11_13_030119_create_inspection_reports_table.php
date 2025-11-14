<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('inspection_reports', function (Blueprint $table) {
            $table->id('inspection_id');

            // Relasi ke tabel lain
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('inspector_id')->nullable();

            // Data inspeksi
            $table->date('inspection_date')->nullable();

            $table->enum('result', ['passed', 'failed', 'recheck'])
                  ->nullable();

            $table->text('findings')->nullable(); 
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->boolean('ncr_required')->default(false);

            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')
                ->references('project_id')
                ->on('projects')
                ->cascadeOnDelete();

            $table->foreign('item_id')
                ->references('item_id')
                ->on('items')
                ->cascadeOnDelete();

            // FIX: gunakan user_id, bukan id
            $table->foreign('inspector_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Hapus tabel jika di-rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_reports');
    }
};
