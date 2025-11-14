<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('departments')->insert([
            [
                'department_id' => 1,
                'department_name' => 'Procurement Department',
                'description' => 'Department handling all procurement processes',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => 2,
                'department_name' => 'Quality Control',
                'description' => 'Department ensuring quality standards',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'department_id' => 3,
                'department_name' => 'Logistics',
                'description' => 'Department managing logistics and delivery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
