<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('divisions')->insert([
            [
                'division_id' => 1,
                'division_name' => 'Supply Chain Management',
                'description' => 'Divisi yang bertanggung jawab atas pengadaan dan manajemen vendor',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'division_id' => 2,
                'division_name' => 'Engineering',
                'description' => 'Divisi teknik dan engineering kapal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'division_id' => 3,
                'division_name' => 'Finance',
                'description' => 'Divisi keuangan dan akuntansi',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'division_id' => 4,
                'division_name' => 'Production',
                'description' => 'Divisi produksi kapal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
