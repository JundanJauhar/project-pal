<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the sessions.user_id foreign key (if present).
     *
     * Rationale: This project uses multi-guard auth (web + vendor). Database
     * sessions may store either users.user_id or vendors.id_vendor in the
     * sessions.user_id column, so a FK to users would break vendor sessions.
     */
    public function up(): void
    {
        if (!Schema::hasTable('sessions')) {
            return;
        }

        // Default Laravel FK name would be: sessions_user_id_foreign
        // Some environments may not have this FK; ignore if it doesn't exist.
        try {
            Schema::table('sessions', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (Throwable $e) {
            // no-op
        }
    }

    public function down(): void
    {
        // Intentionally left blank. Re-adding FK would reintroduce the bug.
    }
};
