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
        Schema::create('vendors', function (Blueprint $table) {
            $table->string('id_vendor', 20)->primary(); // PERBAIKAN #10 - ubah ke string
            $table->string('name_vendor', 100);
            $table->text('address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('email', 100)->nullable();

            // Status legal vendor (tambahan)
            $table->enum('legal_status', ['pending', 'verified', 'rejected'])->default('pending');

            // Konsisten dengan ERD - PERBAIKAN #10
            $table->boolean('is_importer')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
