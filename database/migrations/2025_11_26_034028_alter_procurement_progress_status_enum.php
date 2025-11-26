<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Mengubah enum 'status' di tabel procurement_progress
     * dari 4 nilai menjadi 3 nilai agar sesuai dengan procurement.status_procurement
     */
    public function up(): void
    {
        echo "🔄 Starting migration: Alter procurement_progress status enum\n";
        
        // Step 1: Convert existing data
        echo "📊 Converting existing data...\n";
        
        $updated = DB::table('procurement_progress')
            ->where('status', 'not_started')
            ->update(['status' => 'in_progress']);
        echo "   • Converted {$updated} records: 'not_started' → 'in_progress'\n";
        
        $updated = DB::table('procurement_progress')
            ->where('status', 'blocked')
            ->update(['status' => 'cancelled']);
        echo "   • Converted {$updated} records: 'blocked' → 'cancelled'\n";
        
        // Step 2: Alter enum column
        echo "🔧 Altering enum column...\n";
        
        DB::statement("
            ALTER TABLE procurement_progress 
            MODIFY COLUMN status ENUM('in_progress', 'completed', 'cancelled') 
            NOT NULL DEFAULT 'in_progress'
        ");
        
        echo "✅ Enum column altered successfully!\n";
        
        // Step 3: Show current distribution
        echo "\n📈 Current status distribution:\n";
        $stats = DB::table('procurement_progress')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
            
        foreach ($stats as $stat) {
            echo "   • {$stat->status}: {$stat->count} records\n";
        }
        
        echo "\n✨ Migration completed successfully!\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        echo "⏪ Reverting migration...\n";
        
        // Alter back to original enum
        DB::statement("
            ALTER TABLE procurement_progress 
            MODIFY COLUMN status ENUM('not_started', 'in_progress', 'completed', 'blocked') 
            NOT NULL DEFAULT 'not_started'
        ");
        
        // Convert cancelled back to blocked
        DB::table('procurement_progress')
            ->where('status', 'cancelled')
            ->update(['status' => 'blocked']);
        
        echo "⚠️  Rollback completed. Note: 'not_started' values cannot be fully restored.\n";
    }
};