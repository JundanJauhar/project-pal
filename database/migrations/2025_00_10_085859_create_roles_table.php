<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id('role_id');

            $table->unsignedBigInteger('division_id');
            $table->string('role_code', 50);   // inquiry, negotiation, delivery, qa_inspector
            $table->string('role_name', 100);  // Inquiry & Quotation
            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('division_id')
                ->references('division_id')
                ->on('divisions')
                ->cascadeOnDelete();

            $table->unique(['division_id', 'role_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
