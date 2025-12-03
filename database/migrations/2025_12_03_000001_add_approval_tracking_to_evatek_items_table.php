<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('evatek_items', function (Blueprint $table) {
            // Add columns if they don't exist
            if (!Schema::hasColumn('evatek_items', 'approved_at')) {
                $table->dateTime('approved_at')->nullable()->after('current_date');
            }
            if (!Schema::hasColumn('evatek_items', 'not_approved_at')) {
                $table->dateTime('not_approved_at')->nullable()->after('approved_at');
            }
        });

        Schema::table('evatek_revisions', function (Blueprint $table) {
            // Add notes column if it doesn't exist
            if (!Schema::hasColumn('evatek_revisions', 'notes')) {
                $table->longText('notes')->nullable()->after('alasan_reject');
            }
        });
    }

    public function down(): void
    {
        Schema::table('evatek_items', function (Blueprint $table) {
            $table->dropColumn(['approved_at', 'not_approved_at']);
        });

        Schema::table('evatek_revisions', function (Blueprint $table) {
            if (Schema::hasColumn('evatek_revisions', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
