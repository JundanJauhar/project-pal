<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [ 'department_name' => 'General Procurement', 'description' => 'Department default untuk pengadaan' ],
            [ 'department_name' => 'Engineering', 'description' => 'Engineering Department' ],
            [ 'department_name' => 'Finance', 'description' => 'Finance Department' ],
            [ 'department_name' => 'Operations', 'description' => 'Operations Department' ],
        ];

        foreach ($departments as $d) {
            Department::create($d);
        }
    }
}
