<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestProcurementSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('request_procurements')->insert([
            [
                'request_id' => 1,
                'procurement_id' => 1,
                'item_id' => 1,
                'vendor_id' => 1,
                'request_name' => 'Request Steel Plates',
                'created_date' => '2025-01-20',
                'deadline_date' => '2025-03-30',
                'department_id' => 1,
                'request_status' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'request_id' => 2,
                'procurement_id' => 1,
                'item_id' => 2,
                'vendor_id' => 2,
                'request_name' => 'Request Engine Components',
                'created_date' => '2025-01-25',
                'deadline_date' => '2025-04-15',
                'department_id' => 1,
                'request_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
