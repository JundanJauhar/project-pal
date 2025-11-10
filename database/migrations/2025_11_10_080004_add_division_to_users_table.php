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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('division_id')->nullable()->after('id');
            $table->enum('roles', ['user', 'supply_chain', 'treasury', 'accounting', 'qa', 'sekretaris_direksi', 'desain'])->default('user')->after('password');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('roles');

            $table->foreign('division_id')->references('divisi_id')->on('divisions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn(['division_id', 'roles', 'status']);
        });
    }
};
