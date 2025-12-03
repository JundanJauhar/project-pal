<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_procurement', function (Blueprint $table) {
            $table->id('request_id');

            // FK ke procurement
            $table->unsignedBigInteger('procurement_id')->nullable();

            $table->unsignedBigInteger('project_id')->nullable();

            $table->unsignedBigInteger('vendor_id')->nullable();

            $table->string('request_name', 200);
            $table->date('created_date');
            $table->date('deadline_date')->nullable();

            $table->enum('request_status', [
                'draft', 'submitted', 'approved', 'rejected', 'completed'
            ])->default('draft');

            // department
            $table->unsignedBigInteger('department_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('procurement_id')
                  ->references('procurement_id')
                  ->on('procurement')
                  ->nullOnDelete();

            $table->foreign('project_id')
                  ->references('project_id')
                  ->on('projects')
                  ->nullOnDelete();

            $table->foreign('vendor_id')
                  ->references('id_vendor')
                  ->on('vendors')
                  ->nullOnDelete();

            $table->foreign('department_id')
                  ->references('department_id')
                  ->on('departments')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_procurement');
    }
};
