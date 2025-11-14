<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkpoints', function (Blueprint $table) {
            $table->id('point_id');
            $table->string('point_name', 100)->nullable();
            $table->text('point_sequence')->nullable();
            $table->unsignedBigInteger('responsible_division')->nullable();
            $table->boolean('is_true')->default(false);
            $table->timestamps();

            $table->foreign('responsible_division')->references('department_id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkpoints');
    }
};
