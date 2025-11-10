<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            ['nama_divisi' => 'User Division', 'deskripsi' => 'User / Department yang mengajukan procurement'],
            ['nama_divisi' => 'Supply Chain', 'deskripsi' => 'Supply Chain Management Division'],
            ['nama_divisi' => 'Treasury', 'deskripsi' => 'Treasury Division - Pembayaran dan Keuangan'],
            ['nama_divisi' => 'Accounting', 'deskripsi' => 'Accounting Division - Verifikasi Dokumen'],
            ['nama_divisi' => 'Quality Assurance', 'deskripsi' => 'Quality Assurance - Inspeksi dan NCR'],
            ['nama_divisi' => 'Sekretaris Direksi', 'deskripsi' => 'Sekretaris Direksi - Approval Kontrak'],
            ['nama_divisi' => 'Desain', 'deskripsi' => 'Desain Division - HPS dan Evatek'],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
