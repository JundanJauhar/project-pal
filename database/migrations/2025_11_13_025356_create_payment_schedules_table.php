<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id('payment_schedule_id');

            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();

            $table->string('payment_type', 100)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);

            $table->date('due_date')->nullable();
            $table->date('payment_date')->nullable();

            $table->enum('status', ['pending', 'paid', 'verified'])->default('pending');

            $table->unsignedBigInteger('verified_by_accounting')->nullable();
            $table->unsignedBigInteger('verified_by_treasury')->nullable();
            $table->timestamp('verified_at_accounting')->nullable();
            $table->timestamp('verified_at_treasury')->nullable();

            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            // FK
            $table->foreign('project_id')->references('project_id')->on('projects')->cascadeOnDelete();
            $table->foreign('contract_id')->references('contract_id')->on('contracts')->cascadeOnDelete();
            $table->foreign('verified_by_accounting')->references('user_id')->on('users')->nullOnDelete();
            $table->foreign('verified_by_treasury')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};
