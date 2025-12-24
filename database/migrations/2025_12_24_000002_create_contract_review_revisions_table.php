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
        Schema::create('contract_review_revisions', function (Blueprint $table) {
            $table->id('contract_review_revision_id');
            $table->unsignedBigInteger('contract_review_id');
            $table->string('revision_code', 10); // R0, R1, R2, etc.
            $table->text('vendor_link')->nullable();
            $table->text('sc_link')->nullable();
            $table->date('date_sent_to_vendor')->nullable();
            $table->date('date_vendor_feedback')->nullable();
            $table->date('date_result')->nullable();
            $table->enum('result', ['approve', 'not_approve', 'revisi', 'pending'])->default('pending');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('contract_review_id')->references('contract_review_id')->on('contract_reviews')->onDelete('cascade');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_review_revisions');
    }
};
