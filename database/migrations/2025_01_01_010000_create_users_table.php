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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');

            $table->string('name', 150);
            $table->string('email', 100)->unique();
            $table->string('password', 255);

            $table->unsignedBigInteger('division_id')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('active');

            /**
             * Remember token (Laravel default)
             */
            $table->rememberToken();

            /**
             * ✅ LAST LOGIN TRACKING
             */
            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('division_id')
                ->references('division_id')
                ->on('divisions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
