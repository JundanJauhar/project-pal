<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evatek_revisions', function (Blueprint $table) {
            $table->id('revision_id');
            $table->unsignedBigInteger('evatek_id');

            $table->string('revision_code'); // R0, R1, R2, dst
            $table->text('vendor_link')->nullable();
            $table->text('design_link')->nullable();

            $table->enum('status', ['pending','approve','revisi','reject'])
                  ->default('pending');

            $table->date('date')->nullable();

            $table->timestamps();

            $table->foreign('evatek_id')
                ->references('evatek_id')
                ->on('evatek_items')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evatek_revisions');
    }
};
