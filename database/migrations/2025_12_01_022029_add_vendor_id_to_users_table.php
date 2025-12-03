<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {

        // Perbaiki tipe data → STRING (20)
        $table->unsignedBigInteger('vendor_id')
              ->nullable()
              ->after('division_id');

        // Foreign key harus sama tipe dan panjang
        $table->foreign('vendor_id')
              ->references('id_vendor')
              ->on('vendors')
              ->nullOnDelete();
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['vendor_id']);
        $table->dropColumn('vendor_id');
    });
}


};
