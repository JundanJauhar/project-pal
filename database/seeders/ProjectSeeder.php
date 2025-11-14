<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('projects')->insert([
            [
                'project_id' => 1,
                'project_name' => 'Kapal Perang Tipe A - KRI Surabaya',
                'project_code' => 'KCJ-2025001',
                'procurement_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'project_id' => 2,
                'project_name' => 'Kapal Selam Tipe B - KRI Jakarta',
                'project_code' => 'KCJ-2025002',
                'procurement_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
