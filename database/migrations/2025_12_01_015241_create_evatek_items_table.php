<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evatek_items', function (Blueprint $table) {
            $table->id('evatek_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('project_id');

            // Current status summary (always refers to last revision)
            $table->string('current_revision')->default('R0');
            $table->string('current_status')->default('On Progress'); // Revision Needed / Completed / On Progress
            $table->date('current_date')->nullable();

            // Log activity
            $table->longText('log')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('item_id')->references('item_id')->on('items')->cascadeOnDelete();
            $table->foreign('project_id')->references('project_id')->on('projects')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evatek_items');
    }
};
