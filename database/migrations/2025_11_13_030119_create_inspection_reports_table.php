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
            $table->string('result')->nullable(); // contoh: 'passed', 'failed', 'recheck'
            $table->text('findings')->nullable(); // temuan selama inspeksi
            $table->text('notes')->nullable(); // catatan tambahan
            $table->string('attachment_path')->nullable(); // lampiran file foto/dokumen
            $table->boolean('ncr_required')->default(false); // apakah perlu NCR (non-conformance report)

            $table->timestamps();

            // 🔗 Foreign keys — pastikan tabel terkait sudah memiliki kolom yang cocok
            $table->foreign('project_id')
                ->references('project_id')
                ->on('projects')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('item_id')
                ->on('items')
                ->onDelete('cascade');

            $table->foreign('inspector_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
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
