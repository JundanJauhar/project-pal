<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\Contract;
use App\Models\PaymentSchedule;
use App\Models\InspectionReport;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * ============================
         * CREATE SAMPLE VENDORS
         * ============================
         */
        $vendors = [
            [
                'id_vendor' => 1,
                'name_vendor' => 'PT Krakatau Steel',
                'address' => 'Jl. Industri No. 1, Cilegon, Banten',
                'phone_number' => '021-12345678',
                'email' => 'sales@krakatausteel.com',
                'legal_status' => 'verified',
                'is_importir' => false,
            ],
            [
                'id_vendor' => 2,
                'name_vendor' => 'PT Pindad',
                'address' => 'Jl. Gatot Subroto, Bandung',
                'phone_number' => '022-87654321',
                'email' => 'procurement@pindad.com',
                'legal_status' => 'verified',
                'is_importir' => false,
            ],
            [
                'id_vendor' => 3,
                'name_vendor' => 'PT Dirgantara Indonesia',
                'address' => 'Jl. Pajajaran No. 154, Bandung',
                'phone_number' => '022-98765432',
                'email' => 'sales@indonesian-aerospace.com',
                'legal_status' => 'pending',
                'is_importir' => true,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }

        /**
         * ============================
         * PROJECT 1 - Completed
         * ============================
         */
        $project1 = Project::create([
            'project_code' => 'KCJ-202511-001',
            'project_name' => 'Pengadaan Material Kapal Fregat',
            'description' => 'Pengadaan material utama untuk kapal fregat kelas sigma',
            'owner_division_id' => 2,
            'priority' => 'tinggi',
            'start_date' => Carbon::now()->subDays(90),
            'end_date' => Carbon::now()->addDays(30),
            'status_project' => 'completed',
        ]);

        $procurement1 = \App\Models\Procurement::create([
    'code_procurement' => 'PRC-2025-001',
    'name_procurement' => 'Procurement Project 1',
    'description' => 'Pengadaan untuk proyek 1',
    'department_procurement' => 1,
    'priority' => 'tinggi',
    'start_date' => Carbon::now()->subDays(100),
    'end_date' => Carbon::now()->addDays(30),
    'status_procurement' => 'in_progress',
]);


        /**
         * REQUEST PROCUREMENT (sesuai schema baru)
         */
        $request1 = RequestProcurement::create([
            'procurement_id' => 1, // atau buat procurement jika tabelnya sudah ada
            'vendor_id' => 1,
            'request_name' => 'Material Baja Berkualitas Tinggi',
            'created_date' => Carbon::now()->subDays(90),
            'deadline_date' => Carbon::now()->addDays(30),
            'request_status' => 'completed',
            'department_id' => 1, // harus FK ke department
        ]);

        /**
         * ITEMS — HARUS MENGIKUTI request_procurement_id
         */
        Item::create([
            'request_procurement_id' => $request1->request_id,
            'item_name' => 'Baja High Grade',
            'item_description' => 'Material baja high grade untuk struktur kapal',
            'amount' => 100,
            'unit' => 'ton',
            'unit_price' => 120000000,
            'total_price' => 12000000000,
        ]);

        /**
         * CONTRACT FOR PROJECT 1
         */
        $contract1 = Contract::create([
            'project_id' => $project1->project_id,
            'vendor_id' => 1,
            'contract_number' => 'CTR/PAL/2025/001',
            'contract_value' => 14500000000,
            'start_date' => Carbon::now()->subDays(70),
            'end_date' => Carbon::now()->addDays(30),
            'status' => 'active',
            'created_by' => 2, // user_id
        ]);

        /**
         * PAYMENT SCHEDULES
         */
        PaymentSchedule::create([
            'project_id' => $project1->project_id,
            'contract_id' => $contract1->contract_id,
            'payment_type' => 'dp',
            'amount' => 4350000000,
            'percentage' => 30,
            'due_date' => Carbon::now()->subDays(65),
            'status' => 'paid',
            'verified_by_treasury' => 3,
            'verified_by_accounting' => 4,
            'payment_date' => Carbon::now()->subDays(65),
        ]);

        /**
         * INSPECTION REPORT
         */
        InspectionReport::create([
            'project_id' => $project1->project_id,
            'item_id' => 1,
            'inspection_date' => Carbon::now()->subDays(10),
            'inspector_id' => 5,
            'result' => 'passed',
            'notes' => 'Material sesuai spesifikasi teknis.',
        ]);

        /**
         * PROJECT 2–7 dibuat ulang versi pendek
         * tanpa HPS / Negotiation / Evatek
         * karena tabelnya BELUM ADA
         */

        Project::create([
            'project_code' => 'KCJ-202511-002',
            'project_name' => 'Pengadaan Sistem Radar Navigasi',
            'description' => 'Pengadaan radar navigasi untuk kapal perang',
            'owner_division_id' => 2,
            'priority' => 'tinggi',
            'start_date' => Carbon::now()->subDays(45),
            'end_date' => Carbon::now()->addDays(60),
            'status_project' => 'negosiasi_harga',
        ]);

        Project::create([
            'project_code' => 'KCJ-202511-003',
            'project_name' => 'Pengadaan Mesin Diesel Utama',
            'description' => 'Mesin diesel untuk kapal tanker',
            'owner_division_id' => 7,
            'priority' => 'sedang',
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->addDays(120),
            'status_project' => 'review_sc',
        ]);

        Project::create([
            'project_code' => 'KCJ-202511-004',
            'project_name' => 'Pengadaan Peralatan Keselamatan Kapal',
            'description' => 'Life jacket, fire extinguisher, dll.',
            'owner_division_id' => 1,
            'priority' => 'sedang',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(90),
            'status_project' => 'draft',
        ]);

        Project::create([
            'project_code' => 'KCJ-202511-005',
            'project_name' => 'Pengadaan Cat Anti Karat & Coating',
            'description' => 'Cat marine grade untuk kapal',
            'owner_division_id' => 2,
            'priority' => 'rendah',
            'start_date' => Carbon::now()->subDays(15),
            'end_date' => Carbon::now()->addDays(75),
            'status_project' => 'persetujuan_sekretaris',
        ]);

        Project::create([
            'project_code' => 'KCJ-202511-006',
            'project_name' => 'Pengadaan Sistem Komunikasi Satelit',
            'description' => 'Sistem satelit untuk kapal jelajah jauh',
            'owner_division_id' => 2,
            'priority' => 'tinggi',
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->addDays(90),
            'status_project' => 'pembuatan_hps',
        ]);

        Project::create([
            'project_code' => 'KCJ-202511-007',
            'project_name' => 'Pengadaan Generator Listrik',
            'description' => 'Generator cadangan 500 KVA',
            'owner_division_id' => 2,
            'priority' => 'sedang',
            'start_date' => Carbon::now()->subDays(25),
            'end_date' => Carbon::now()->addDays(80),
            'status_project' => 'pemilihan_vendor',
        ]);

        echo "✅ Seeder berhasil disesuaikan dengan schema terbaru.\n";
    }
}
