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
        Schema::create('contract_reviews', function (Blueprint $table) {
            $table->id('contract_review_id');
            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('project_id');
            $table->date('start_date');
            $table->string('current_revision', 10)->default('R0');
            $table->date('date_sent_to_vendor')->nullable();
            $table->date('date_vendor_feedback')->nullable();
            $table->text('remarks')->nullable();
            $table->text('log')->nullable(); // Activity log
            $table->enum('result', ['approve', 'not_approve', 'revisi', 'pending'])->default('pending'); // Final result
            $table->enum('status', ['on_progress', 'waiting_feedback', 'completed', 'cancelled'])->default('on_progress');
            $table->timestamps();

            // Foreign keys
            $table->foreign('procurement_id')->references('procurement_id')->on('procurement')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id_vendor')->on('vendors')->onDelete('cascade');
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_reviews');
    }
};
