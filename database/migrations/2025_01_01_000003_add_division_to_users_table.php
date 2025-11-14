<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('division_id')->nullable()->after('password');
            $table->enum('role', ['admin', 'user', 'supply_chain', 'finance', 'manager'])->default('user')->after('division_id');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            $table->softDeletes();

            $table->foreign('division_id')->references('division_id')->on('divisions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropColumn(['division_id', 'role', 'status']);
            $table->dropSoftDeletes();
        });
    }
};
