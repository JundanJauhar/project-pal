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
        Schema::create('request_procurement', function (Blueprint $table) {
            $table->id('request_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('request_name', 200);
            $table->date('created_date');
            $table->date('deadline_date')->nullable();
            $table->enum('request_status', ['draft', 'submitted', 'approved', 'rejected', 'completed'])->default('draft');
            $table->unsignedBigInteger('applicant_department');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('project_id')->references('project_id')->on('projects')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id_vendor')->on('vendors')->onDelete('cascade');
            $table->foreign('applicant_department')->references('divisi_id')->on('divisions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_procurement');
    }
};
