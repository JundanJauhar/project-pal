<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->bigIncrements('approval_id');

            // Modul yang di-approve, misalnya 'procurement'
            $table->string('module'); 
            $table->unsignedBigInteger('module_id'); // id procurement/project

            // si approver (sekdir)
            $table->unsignedBigInteger('approver_id');

            // approved / rejected / pending
            $table->enum('status', ['pending', 'approved', 'rejected'])
                ->default('pending');

            // link dokumen persetujuan
            $table->string('approval_document_link')->nullable();

            // catatan dari sekdir
            $table->text('approval_notes')->nullable();

            // kapan disetujui/ditolak
            $table->timestamp('approved_at')->nullable();

            // user yang menyetujui (sekdir)
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->timestamps();

            // Index biar cepat dicari
            $table->index(['module', 'module_id']);

            // Foreign keys
            $table->foreign('approver_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
