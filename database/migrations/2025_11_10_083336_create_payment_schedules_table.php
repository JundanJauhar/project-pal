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
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id('payment_schedule_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->enum('payment_type', ['dp', 'termin', 'final', 'lc', 'tt', 'sekbun'])->default('dp');
            $table->decimal('amount', 19, 2);
            $table->decimal('percentage', 5, 2)->nullable(); // Persentase dari total kontrak
            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->enum('status', ['pending', 'verified_accounting', 'verified_treasury', 'paid', 'rejected'])->default('pending');
            $table->unsignedBigInteger('verified_by_accounting')->nullable();
            $table->unsignedBigInteger('verified_by_treasury')->nullable();
            $table->timestamp('verified_at_accounting')->nullable();
            $table->timestamp('verified_at_treasury')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable(); // Dokumen pembayaran
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('contract_id')->references('contract_id')->on('contracts')->onDelete('set null');
            $table->foreign('verified_by_accounting')->references('id')->on('users')->onDelete('set null');
            $table->foreign('verified_by_treasury')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
