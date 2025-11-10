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
        Schema::create('ncr_reports', function (Blueprint $table) {
            $table->id('ncr_id');
            $table->string('ncr_number')->unique();
            $table->unsignedBigInteger('inspection_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->date('ncr_date');
            $table->text('nonconformance_description'); // Deskripsi ketidaksesuaian
            $table->enum('severity', ['minor', 'major', 'critical'])->default('minor');
            $table->text('root_cause')->nullable(); // Akar masalah
            $table->text('corrective_action')->nullable(); // Tindakan perbaikan
            $table->text('preventive_action')->nullable(); // Tindakan pencegahan
            $table->unsignedBigInteger('assigned_to')->nullable(); // Vendor/SC yang bertanggung jawab
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->enum('status', ['open', 'in_progress', 'resolved', 'verified', 'closed'])->default('open');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->foreign('inspection_id')->references('inspection_id')->on('inspection_reports')->onDelete('cascade');
            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('items')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ncr_reports');
    }
};
