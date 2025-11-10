<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Hps;
use App\Models\Vendor;
use App\Models\Contract;
use App\Models\Evatek;
use App\Models\Negotiation;
use App\Models\PaymentSchedule;
use App\Models\InspectionReport;
use App\Models\ProcurementProgress;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample vendors
        $vendors = [
            [
                'id_vendor' => 1,
                'name_vendor' => 'PT Krakatau Steel',
                'address' => 'Jl. Industri No. 1, Cilegon, Banten',
                'phone_number' => '021-12345678',
                'email' => 'sales@krakatausteel.com',
                'legal_status' => 'verified',
            ],
            [
                'id_vendor' => 2,
                'name_vendor' => 'PT Pindad',
                'address' => 'Jl. Gatot Subroto, Bandung',
                'phone_number' => '022-87654321',
                'email' => 'procurement@pindad.com',
                'legal_status' => 'verified',
            ],
            [
                'id_vendor' => 3,
                'name_vendor' => 'PT Dirgantara Indonesia',
                'address' => 'Jl. Pajajaran No. 154, Bandung',
                'phone_number' => '022-98765432',
                'email' => 'sales@indonesian-aerospace.com',
                'legal_status' => 'pending',
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::create($vendor);
        }

        // Project 1: Completed - Pengadaan Material Kapal Fregat
        $project1 = Project::create([
            'code_project' => 'KCJ-202511-001',
            'name_project' => 'Pengadaan Material Kapal Fregat',
            'description' => 'Pengadaan material utama untuk pembangunan kapal fregat kelas sigma',
            'owner_division_id' => 2, // Supply Chain
            'priority' => 'high',
            'start_date' => Carbon::now()->subDays(90),
            'end_date' => Carbon::now()->addDays(30),
            'status_project' => 'completed',
        ]);

        // Create Request Procurement for Project 1
        $request1 = RequestProcurement::create([
            'project_id' => $project1->project_id,
            'item_id' => 1,
            'vendor_id' => 1,
            'request_name' => 'Material Baja Berkualitas Tinggi',
            'created_date' => Carbon::now()->subDays(90),
            'deadline_date' => Carbon::now()->addDays(30),
            'request_status' => 'completed',
            'applicant_department' => 2,
        ]);

        // Create HPS for Project 1
        $hps1 = Hps::create([
            'project_id' => $project1->project_id,
            'hps_date' => Carbon::now()->subDays(85),
            'total_amount' => 15000000000, // 15 Miliar
            'status' => 'approved',
            'notes' => 'HPS telah disetujui oleh Sekretaris Direksi',
            'created_by' => 7, // Desain
        ]);

        // Create Contract for Project 1
        $contract1 = Contract::create([
            'project_id' => $project1->project_id,
            'vendor_id' => 1,
            'contract_number' => 'CTR/PAL/2025/001',
            'contract_value' => 14500000000, // Setelah negosiasi
            'start_date' => Carbon::now()->subDays(70),
            'end_date' => Carbon::now()->addDays(30),
            'status' => 'active',
            'created_by' => 2,
        ]);

        // Create Payment Schedule for Project 1
        PaymentSchedule::create([
            'project_id' => $project1->project_id,
            'contract_id' => $contract1->contract_id,
            'payment_type' => 'dp',
            'amount' => 4350000000, // 30% DP
            'percentage' => 30.00,
            'due_date' => Carbon::now()->subDays(65),
            'status' => 'paid',
            'verified_by_treasury' => 3,
            'verified_by_accounting' => 4,
            'payment_date' => Carbon::now()->subDays(65),
        ]);

        PaymentSchedule::create([
            'project_id' => $project1->project_id,
            'contract_id' => $contract1->contract_id,
            'payment_type' => 'termin',
            'amount' => 7250000000, // 50% Progress
            'percentage' => 50.00,
            'due_date' => Carbon::now()->subDays(30),
            'status' => 'paid',
            'verified_by_treasury' => 3,
            'verified_by_accounting' => 4,
            'payment_date' => Carbon::now()->subDays(30),
        ]);

        PaymentSchedule::create([
            'project_id' => $project1->project_id,
            'contract_id' => $contract1->contract_id,
            'payment_type' => 'final',
            'amount' => 2900000000, // 20% Final
            'percentage' => 20.00,
            'due_date' => Carbon::now()->addDays(5),
            'status' => 'pending',
        ]);

        // Create Inspection Report for Project 1
        InspectionReport::create([
            'project_id' => $project1->project_id,
            'item_id' => null,
            'inspection_date' => Carbon::now()->subDays(10),
            'inspector_id' => 5, // QA
            'result' => 'passed',
            'findings' => null,
            'notes' => 'Material sesuai spesifikasi teknis. Kualitas sangat baik.',
        ]);

        // Project 2: In Progress - Pengadaan Sistem Radar
        $project2 = Project::create([
            'code_project' => 'KCJ-202511-002',
            'name_project' => 'Pengadaan Sistem Radar Navigasi',
            'description' => 'Pengadaan dan instalasi sistem radar untuk kapal perang',
            'owner_division_id' => 2,
            'priority' => 'high',
            'start_date' => Carbon::now()->subDays(45),
            'end_date' => Carbon::now()->addDays(60),
            'status_project' => 'negosiasi_harga',
        ]);

        $hps2 = Hps::create([
            'project_id' => $project2->project_id,
            'hps_date' => Carbon::now()->subDays(40),
            'total_amount' => 25000000000, // 25 Miliar
            'status' => 'approved',
            'notes' => 'HPS untuk sistem radar navigasi',
            'created_by' => 7,
        ]);

        // Buat Request Procurement minimal untuk negosiasi Project 2
        $request2 = RequestProcurement::create([
            'project_id' => $project2->project_id,
            'item_id' => 1,
            'vendor_id' => 3,
            'request_name' => 'Sistem Radar Navigasi',
            'created_date' => Carbon::now()->subDays(42),
            'deadline_date' => Carbon::now()->addDays(60),
            'request_status' => 'submitted',
            'applicant_department' => 2,
        ]);

        Negotiation::create([
            'request_id' => $request2->request_id,
            'status' => 'in_progress',
            'notes' => 'Vendor menawarkan diskon 6% dari HPS',
        ]);

        // Project 3: Review Stage - Pengadaan Mesin Diesel
        $project3 = Project::create([
            'code_project' => 'KCJ-202511-003',
            'name_project' => 'Pengadaan Mesin Diesel Utama',
            'description' => 'Pengadaan mesin diesel 4 unit untuk kapal tanker',
            'owner_division_id' => 7, // Desain
            'priority' => 'medium',
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->addDays(120),
            'status_project' => 'review_sc',
        ]);

        RequestProcurement::create([
            'project_id' => $project3->project_id,
            'item_id' => 1,
            'vendor_id' => 2,
            'request_name' => 'Mesin Diesel MTU Series 4000',
            'created_date' => Carbon::now()->subDays(20),
            'deadline_date' => Carbon::now()->addDays(120),
            'request_status' => 'submitted',
            'applicant_department' => 7,
        ]);

        // Project 4: Draft - Pengadaan Peralatan Keselamatan
        $project4 = Project::create([
            'code_project' => 'KCJ-202511-004',
            'name_project' => 'Pengadaan Peralatan Keselamatan Kapal',
            'description' => 'Life jacket, life boat, fire extinguisher, dan peralatan keselamatan lainnya',
            'owner_division_id' => 1,
            'priority' => 'medium',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(90),
            'status_project' => 'draft',
        ]);

        // Project 5: Approval Stage - Pengadaan Cat & Coating
        $project5 = Project::create([
            'code_project' => 'KCJ-202511-005',
            'name_project' => 'Pengadaan Cat Anti Karat & Coating',
            'description' => 'Cat marine grade untuk proteksi kapal dari korosi air laut',
            'owner_division_id' => 2,
            'priority' => 'low',
            'start_date' => Carbon::now()->subDays(15),
            'end_date' => Carbon::now()->addDays(75),
            'status_project' => 'persetujuan_sekretaris',
        ]);

        // Project 6: HPS Creation - Pengadaan Sistem Komunikasi
        $project6 = Project::create([
            'code_project' => 'KCJ-202511-006',
            'name_project' => 'Pengadaan Sistem Komunikasi Satelit',
            'description' => 'Sistem komunikasi satelit untuk kapal jelajah jauh',
            'owner_division_id' => 2,
            'priority' => 'high',
            'start_date' => Carbon::now()->subDays(30),
            'end_date' => Carbon::now()->addDays(90),
            'status_project' => 'pembuatan_hps',
        ]);

        // Project 7: Vendor Selection - Pengadaan Generator
        $project7 = Project::create([
            'code_project' => 'KCJ-202511-007',
            'name_project' => 'Pengadaan Generator Listrik',
            'description' => 'Generator cadangan 500 KVA untuk galangan kapal',
            'owner_division_id' => 2,
            'priority' => 'medium',
            'start_date' => Carbon::now()->subDays(25),
            'end_date' => Carbon::now()->addDays(80),
            'status_project' => 'pemilihan_vendor',
        ]);

        $hps7 = Hps::create([
            'project_id' => $project7->project_id,
            'hps_date' => Carbon::now()->subDays(20),
            'total_amount' => 8000000000,
            'status' => 'approved',
            'notes' => 'HPS generator listrik backup',
            'created_by' => 7,
        ]);

        echo "✅ Created 7 sample projects with different stages\n";
        echo "✅ Created 3 vendors\n";
        echo "✅ Created contracts, HPS, payment schedules, and inspection reports\n";
        echo "\n";
        echo "📊 Project Status Distribution:\n";
        echo "   - Draft: 1 project\n";
        echo "   - Review SC: 1 project\n";
        echo "   - Approval: 1 project\n";
        echo "   - HPS Creation: 1 project\n";
        echo "   - Vendor Selection: 1 project\n";
        echo "   - Negotiation: 1 project\n";
        echo "   - Completed: 1 project\n";
    }
}