<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_procurements', function (Blueprint $table) {
            $table->id('request_id');
            $table->unsignedBigInteger('procurement_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->string('request_name')->nullable();
            $table->date('created_date')->nullable();
            $table->date('deadline_date')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('request_status', ['pending', 'approved', 'rejected', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('procurement_id')->references('procurement_id')->on('procurements')->onDelete('cascade');
            $table->foreign('department_id')->references('department_id')->on('departments')->onDelete('set null');
            $table->foreign('vendor_id')->references('id_vendor')->on('vendors')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_procurements');
    }
};
