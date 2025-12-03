<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Delete any orphan revisions with NULL evatek_id
        DB::table('evatek_revisions')
            ->whereNull('evatek_id')
            ->delete();

        // Make evatek_id NOT NULL to prevent future orphans
        Schema::table('evatek_revisions', function (Blueprint $table) {
            $table->unsignedBigInteger('evatek_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('evatek_revisions', function (Blueprint $table) {
            $table->unsignedBigInteger('evatek_id')->nullable()->change();
        });
    }
};
