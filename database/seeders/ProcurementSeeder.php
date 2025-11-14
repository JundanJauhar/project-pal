<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('procurements')->insert([
            [
                'procurement_id' => 1,
                'code_procurement' => 'PROC-2025-001',
                'name_procurement' => 'Procurement Kapal Perang Tipe A',
                'description' => 'Pengadaan material untuk kapal perang tipe A',
                'department_procurement' => 1,
                'priority' => 'tinggi',
                'start_date' => '2025-01-15',
                'end_date' => '2025-06-30',
                'status_procurement' => 'in_progress',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'procurement_id' => 2,
                'code_procurement' => 'PROC-2025-002',
                'name_procurement' => 'Procurement Engine Parts',
                'description' => 'Pengadaan spare parts mesin kapal',
                'department_procurement' => 1,
                'priority' => 'sedang',
                'start_date' => '2025-02-01',
                'end_date' => '2025-04-30',
                'status_procurement' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
