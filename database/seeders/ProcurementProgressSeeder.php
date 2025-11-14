<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcurementProgressSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('procurement_progress')->insert([
            [
                'progress_id' => 1,
                'request_id' => 1,
                'checkpoint_id' => 1,
                'status' => 'completed',
                'start_date' => '2025-01-20',
                'end_date' => '2025-01-25',
                'note' => 'Vendor successfully selected',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'progress_id' => 2,
                'request_id' => 1,
                'checkpoint_id' => 2,
                'status' => 'in_progress',
                'start_date' => '2025-01-26',
                'end_date' => null,
                'note' => 'Price negotiation ongoing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'progress_id' => 3,
                'request_id' => 2,
                'checkpoint_id' => 1,
                'status' => 'pending',
                'start_date' => null,
                'end_date' => null,
                'note' => 'Waiting for vendor selection',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
