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
        Schema::create('procurement', function (Blueprint $table) {
            $table->id('procurement_id'); // Primary Key

            // FK ke project
            $table->unsignedBigInteger('project_id')->nullable();

            $table->string('code_procurement', 50)->unique();
            $table->string('name_procurement', 100);
            $table->text('description')->nullable();

            // FK ke department (karena procurement dibuat oleh department tertentu)
            $table->unsignedBigInteger('department_procurement')->nullable();

            // Prioritas pengadaan
            $table->enum('priority', ['rendah', 'sedang', 'tinggi'])->default('sedang');

            // Range tanggal procurement
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Status alur procurement
            $table->enum('status_procurement', [
                'draft',
                'submitted',
                'reviewed',
                'approved',
                'rejected',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('draft');

            $table->timestamps();

            // Foreign keys
            $table->foreign('project_id')
                ->references('project_id')
                ->on('projects')
                ->nullOnDelete();

            $table->foreign('department_procurement')
                ->references('department_id')
                ->on('departments')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procurement');
    }
};
