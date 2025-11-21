<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [ 'department_name' => 'Departemen Hull Construction', 'description' => 'Department Lumbung' ],
            [ 'department_name' => 'Departemen Outfitting', 'description' => 'Department Outfit' ],
            [ 'department_name' => 'Departemen Machinery', 'description' => 'Department Mesin' ],
            [ 'department_name' => 'Departemen Electrical', 'description' => 'Department Listrik' ],
            [ 'department_name' => 'Departemen Quality Control', 'description' => 'Department Kualitas' ],
            [ 'department_name' => 'Departemen Engine', 'description' => 'Department Mesin-mesin' ],
        ];

        foreach ($departments as $d) {
            Department::create($d);
        }
    }
}
