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

            $table->string('revision_code');
            $table->text('vendor_link')->nullable();
            $table->text('design_link')->nullable();

            $table->enum('status', ['pending','approve','revisi','not approve'])
                  ->default('pending');

            // Timeline untuk setiap revision
            $table->date('date')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('not_approved_at')->nullable();

            // Hasil evaluasi
            $table->longText('hasil_evatek')->nullable();
            $table->longText('catatan_approval')->nullable();
            $table->longText('alasan_reject')->nullable();
            $table->longText('log')->nullable();

            $table->timestamps();

            $table->foreign('evatek_id')
                ->references('evatek_id')
                ->on('evatek_items')
                ->cascadeOnDelete();
                
            $table->index('evatek_id');
            $table->index('status');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evatek_revisions');
    }
};