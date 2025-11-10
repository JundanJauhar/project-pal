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
            $table->string('code_project', 50);
            $table->string('name_project', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('owner_division_id');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status_project', ['planning', 'in_progress', 'completed', 'cancelled'])->default('planning');
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
