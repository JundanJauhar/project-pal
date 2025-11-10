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
        Schema::create('inspection_reports', function (Blueprint $table) {
            $table->id('inspection_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->date('inspection_date');
            $table->unsignedBigInteger('inspector_id'); // QA yang melakukan inspeksi
            $table->enum('result', ['passed', 'failed', 'conditional'])->default('passed');
            $table->text('findings')->nullable(); // Temuan inspeksi
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable(); // Berita Acara
            $table->boolean('ncr_required')->default(false); // Apakah perlu NCR
            $table->timestamps();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('items')->onDelete('set null');
            $table->foreign('inspector_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_reports');
    }
};
