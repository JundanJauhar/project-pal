<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            ['division_name' => 'User Division', 'description' => 'User / Department yang mengajukan procurement'],
            ['division_name' => 'Supply Chain', 'description' => 'Supply Chain Management Division'],
            ['division_name' => 'Treasury', 'description' => 'Treasury Division - Pembayaran dan Keuangan'],
            ['division_name' => 'Accounting', 'description' => 'Accounting Division - Verifikasi Dokumen'],
            ['division_name' => 'Quality Assurance', 'description' => 'Quality Assurance - Inspeksi dan NCR'],
            ['division_name' => 'Sekretaris Direksi', 'description' => 'Sekretaris Direksi - Approval Kontrak'],
            ['division_name' => 'Desain', 'description' => 'Desain Division - HPS dan Evatek'],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
