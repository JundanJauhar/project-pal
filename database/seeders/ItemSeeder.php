<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('items')->insert([
            [
                'item_id' => 1,
                'request_procurement_id' => 1,
                'item_name' => 'Steel Plate Grade A',
                'item_description' => 'High quality steel plates for ship hull construction',
                'amount' => 500,
                'unit' => 'ton',
                'unit_price' => 15000000,
                'total_price' => 7500000000,
                'created_at' => now(),
            ],
            [
                'item_id' => 2,
                'request_procurement_id' => 1,
                'item_name' => 'Welding Materials',
                'item_description' => 'Professional welding rods and consumables',
                'amount' => 1000,
                'unit' => 'kg',
                'unit_price' => 250000,
                'total_price' => 250000000,
                'created_at' => now(),
            ],
            [
                'item_id' => 3,
                'request_procurement_id' => 2,
                'item_name' => 'Engine Parts - Turbine',
                'item_description' => 'Main engine turbine components',
                'amount' => 10,
                'unit' => 'unit',
                'unit_price' => 500000000,
                'total_price' => 5000000000,
                'created_at' => now(),
            ],
        ]);
    }
}
