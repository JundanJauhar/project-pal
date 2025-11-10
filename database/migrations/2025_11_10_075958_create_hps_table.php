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
        Schema::create('hps', function (Blueprint $table) {
            $table->id('hps_id');
            $table->unsignedBigInteger('project_id');
            $table->date('hps_date')->nullable();
            $table->decimal('total_amount', 19, 2)->nullable();
            $table->enum('status', ['draft', 'approved', 'rejected'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hps');
    }
};
