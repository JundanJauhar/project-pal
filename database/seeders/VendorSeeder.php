<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('vendors')->insert([
            [
                'id_vendor' => 1,
                'name_vendor' => 'PT Krakatau Steel',
                'is_importer' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_vendor' => 2,
                'name_vendor' => 'PT Pindad',
                'is_importer' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_vendor' => 3,
                'name_vendor' => 'PT Sumber Jaya Mandiri',
                'is_importer' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id_vendor' => 4,
                'name_vendor' => 'PT United Tractors',
                'is_importer' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
