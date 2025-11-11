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
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->string('code_project', 50)->unique();
            $table->string('name_project', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_division_id');
            $table->enum('priority', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status_project', [
                'draft',
                'review_sc',
                'persetujuan_sekretaris',
                'persetujuan_direksi',
                'pembuatan_hps',
                'pemilihan_vendor',
                'pengecekan_legalitas',
                'negosiasi_harga',
                'pembuatan_kontrak',
                'pembukaan_lc_tt',
                'verifikasi_treasury',
                'verifikasi_accounting',
                'pemesanan',
                'pengiriman_material',
                'inspeksi_barang',
                'completed',
                'cancelled'
            ])->default('draft');
            $table->timestamps();

            $table->foreign('owner_division_id')->references('divisi_id')->on('divisions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};