<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DivisionSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            VendorSeeder::class,
            ProcurementSeeder::class,
            ProjectSeeder::class,
            CheckpointSeeder::class,
            RequestProcurementSeeder::class,
            ItemSeeder::class,
            ProcurementProgressSeeder::class,
        ]);
    }
}
