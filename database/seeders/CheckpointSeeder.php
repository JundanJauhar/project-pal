<?php

namespace Database\Seeders;

use App\Models\Checkpoint;
use Illuminate\Database\Seeder;

class CheckpointSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $checkpoints = [
            ['point_name' => 'Permintaan Pengadaan', 'point_sequence' => 1, 'responsible_division' => 2, 'is_final' => false],
            ['point_name' => 'Inquiry & Quotation', 'point_sequence' => 2, 'responsible_division' => 2, 'is_final' => false],
            ['point_name' => 'Evatek', 'point_sequence' => 3, 'responsible_division' => 2, 'is_final' => false],
            ['point_name' => 'Negotiation', 'point_sequence' => 4, 'responsible_division' => 2, 'is_final' => false],
            ['point_name' => 'Usulan Pengadaan / OC', 'point_sequence' => 5, 'responsible_division' => 2, 'is_final' => false],
            ['point_name' => 'Pengesahan Kontrak', 'point_sequence' => 6, 'responsible_division' => 2, 'is_final' => false],
            ['point_name' => 'Pembayaran DP', 'point_sequence' => 7, 'responsible_division' => 2, 'is_final' => false],
            ['point_name' => 'Pengiriman Material', 'point_sequence' => 8, 'responsible_division' => 2, 'is_final' => false],
            ['point_name' => 'Kedatangan Material', 'point_sequence' => 9, 'responsible_division' => 5, 'is_final' => false],
            ['point_name' => 'Inventory', 'point_sequence' => 10, 'responsible_division' => 3, 'is_final' => true],
        ];

        foreach ($checkpoints as $checkpoint) {
            Checkpoint::create($checkpoint);
        }
    }
}
