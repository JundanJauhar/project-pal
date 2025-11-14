<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CheckpointSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('checkpoints')->insert([
            [
                'point_id' => 1,
                'point_name' => 'Vendor Selection',
                'point_sequence' => 'Step 1: Select qualified vendors',
                'responsible_division' => 1,
                'is_true' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'point_id' => 2,
                'point_name' => 'Price Negotiation',
                'point_sequence' => 'Step 2: Negotiate pricing with selected vendors',
                'responsible_division' => 1,
                'is_true' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'point_id' => 3,
                'point_name' => 'Quality Inspection',
                'point_sequence' => 'Step 3: Conduct quality inspection',
                'responsible_division' => 2,
                'is_true' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'point_id' => 4,
                'point_name' => 'Delivery Confirmation',
                'point_sequence' => 'Step 4: Confirm delivery and acceptance',
                'responsible_division' => 3,
                'is_true' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
