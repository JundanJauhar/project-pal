<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procurements', function (Blueprint $table) {
            $table->id('procurement_id');
            $table->string('code_procurement', 50);
            $table->string('name_procurement', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('department_procurement')->nullable();
            $table->string('priority', 20)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status_procurement', ['draft', 'pending', 'approved', 'rejected', 'in_progress', 'completed'])->default('draft');
            $table->timestamps();

            $table->foreign('department_procurement')->references('division_id')->on('divisions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procurements');
    }
};
