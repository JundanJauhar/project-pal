<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id('id_vendor');

            $table->string('vendor_code', 10)->index();

            $table->string('name_vendor', 100);

            $table->enum('specialization', [
                'jasa',
                'material_lokal',
                'material_impor'
            ])->default('jasa');

            $table->text('address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('email', 100)->nullable();

            $table->string('user_vendor', 100)->unique()->nullable();
            $table->string('password')->nullable();

            $table->boolean('is_importer')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};