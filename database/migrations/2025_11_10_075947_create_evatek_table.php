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
        Schema::create('evatek', function (Blueprint $table) {
            $table->id('evatek_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('evaluated_by');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('evaluation_result')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('update_at')->nullable();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('evaluated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evatek');
    }
};
