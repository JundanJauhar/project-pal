<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id('project_id');
            $table->string('project_name', 100);
            $table->string('project_code', 100)->nullable();
            $table->unsignedBigInteger('procurement_id')->nullable();
            $table->timestamps();

            $table->foreign('procurement_id')->references('procurement_id')->on('procurements')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
