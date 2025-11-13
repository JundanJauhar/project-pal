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
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id('payment_schedule_id');

            // Foreign keys
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();

            // Data pembayaran
            $table->string('payment_type')->nullable(); // contoh: DP, Termin 1, Final Payment
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('status')->default('pending'); // contoh: pending, paid, verified

            // Verifikasi dan catatan
            $table->unsignedBigInteger('verified_by_accounting')->nullable();
            $table->unsignedBigInteger('verified_by_treasury')->nullable();
            $table->timestamp('verified_at_accounting')->nullable();
            $table->timestamp('verified_at_treasury')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();

            $table->timestamps();

            // 🔗 Relasi ke tabel lain (gunakan nama kolom yang sesuai di tabel terkait)
            $table->foreign('project_id')
                ->references('project_id')
                ->on('projects')
                ->onDelete('cascade');

            $table->foreign('contract_id')
                ->references('contract_id')
                ->on('contracts')
                ->onDelete('cascade');

            $table->foreign('verified_by_accounting')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('verified_by_treasury')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    /**
     * Hapus tabel.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
