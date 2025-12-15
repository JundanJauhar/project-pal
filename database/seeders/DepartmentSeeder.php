<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [ 'department_name' => 'Departemen Pengadaan Produksi', 'description' => 'Department Pengadaan Produksi' ],
            [ 'department_name' => 'Departemen Pengadaan Non Produksi & Investasi', 'description' => 'Department Pengadaan Non Produksi & Investasi' ],
            [ 'department_name' => 'Departemen Pengadaan Jasa', 'description' => 'Department Pengadaan Jasa' ],
        ];

        foreach ($departments as $d) {
            Department::create($d);
        }
    }
}
