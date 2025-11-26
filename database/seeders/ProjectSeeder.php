<?php

namespace Database\Seeders;

use App\Models\Contract;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\PaymentSchedule;
use App\Models\InspectionReport;
use Carbon\Carbon;
use App\Models\Procurement;   // <-- WAJIB


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
                'is_importer' => false,
            ],
            [
                'id_vendor' => 2,
                'name_vendor' => 'PT Pindad',
                'address' => 'Jl. Gatot Subroto, Bandung',
                'phone_number' => '022-87654321',
                'email' => 'procurement@pindad.com',
                'legal_status' => 'verified',
                'is_importer' => false,
            ],
            [
                'id_vendor' => 3,
                'name_vendor' => 'PT Dirgantara Indonesia',
                'address' => 'Jl. Pajajaran No. 154, Bandung',
                'phone_number' => '022-98765432',
                'email' => 'sales@indonesian-aerospace.com',
                'legal_status' => 'pending',
                'is_importer' => true,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(
                ['id_vendor' => $vendor['id_vendor']], // key untuk check
                $vendor // data untuk update/create
            );
        }

        /**
         * ============================
         * PROJECT 1 - Completed
         * ============================
         */
        $project1 = Project::updateOrCreate(
            ['project_code' => 'W000301'], // key untuk check
            [
                'project_name' => 'Pengadaan Material Kapal Fregat',
                'description' => 'Pengadaan material utama untuk kapal fregat kelas sigma',
                'owner_division_id' => 2,
                'priority' => 'tinggi',
                'start_date' => Carbon::now()->subDays(90),
                'end_date' => Carbon::now()->addDays(30),
                'status_project' => 'completed',
            ]
        );

        // Ambil department yang ada untuk memastikan foreign key valid
        $departments = \App\Models\Department::all();
        $dept1 = $departments->first();
        $dept2 = $departments->skip(1)->first() ?? $dept1;
        $dept3 = $departments->skip(2)->first() ?? $dept1;
        $dept4 = $departments->skip(3)->first() ?? $dept1;

        // Ambil kode project
$projectCode = $project1->project_code; // contoh: "PRC-2025"

// Seeder untuk procurement 1
$procurement1 = \App\Models\Procurement::create([
    'project_id' => $project1->project_id,
    'code_procurement' => $projectCode . '-01',
    'name_procurement' => 'Pengadaan Material Baja Berkualitas Tinggi',
    'description' => 'Pengadaan material baja untuk proyek 1',
    'department_procurement' => $dept1->department_id,
    'priority' => 'tinggi',
    'start_date' => Carbon::now()->subDays(100),
    'end_date' => Carbon::now()->addDays(30),
    'status_procurement' => 'in_progress',
]);

// Seeder untuk procurement 2
$procurement2 = \App\Models\Procurement::create([
    'project_id' => $project1->project_id,
    'code_procurement' => $projectCode . '-02',
    'name_procurement' => 'Pengadaan Komponen Elektronik',
    'description' => 'Pengadaan komponen elektronik untuk sistem kontrol',
    'department_procurement' => $dept2->department_id,
    'priority' => 'sedang',
    'start_date' => Carbon::now()->subDays(60),
    'end_date' => Carbon::now()->addDays(15),
    'status_procurement' => 'submitted',
]);

// Seeder untuk procurement 3
$procurement3 = \App\Models\Procurement::create([
    'project_id' => $project1->project_id,
    'code_procurement' => $projectCode . '-03',
    'name_procurement' => 'Jasa Cutting dan Fabrication',
    'description' => 'Jasa potong dan fabrikasi material logam',
    'department_procurement' => $dept3->department_id,
    'priority' => 'tinggi',
    'start_date' => Carbon::now()->subDays(40),
    'end_date' => Carbon::now()->addDays(10),
    'status_procurement' => 'approved',
]);

// Seeder untuk procurement 4
$procurement4 = \App\Models\Procurement::create([
    'project_id' => $project1->project_id,
    'code_procurement' => $projectCode . '-04',
    'name_procurement' => 'Permintaan Alat Pelindung Diri (APD)',
    'description' => 'Pengadaan APD untuk keselamatan kerja',
    'department_procurement' => $dept4->department_id,
    'priority' => 'rendah',
    'start_date' => Carbon::now()->subDays(20),
    'end_date' => Carbon::now()->addDays(5),
    'status_procurement' => 'draft',
]);



        /**
         * REQUEST PROCUREMENT (sesuai schema baru)
         */
        $request1 = RequestProcurement::updateOrCreate(
            [
                'procurement_id' => $procurement1->procurement_id,
                'request_name' => 'Material Baja Berkualitas Tinggi'
            ], // key untuk check
            [
                'project_id' => $project1->project_id, // PERBAIKAN: tambahkan project_id
                'vendor_id' => 1,
                'created_date' => Carbon::now()->subDays(90),
                'deadline_date' => Carbon::now()->addDays(30),
                'request_status' => 'completed',
                'department_id' => 1,
            ]
        );

        $request2 = RequestProcurement::updateOrCreate(
            [
                'procurement_id' => $procurement2->procurement_id,
                'request_name' => 'Pengadaan Komponen Elektronik'
            ], // key untuk check
            [
                'project_id' => $project1->project_id, // PERBAIKAN: tambahkan project_id
                'vendor_id' => 2,
                'created_date' => Carbon::now()->subDays(60),
                'deadline_date' => Carbon::now()->addDays(15),
                'request_status' => 'submitted',
                'department_id' => 2,
            ]
        );

        $request3 = RequestProcurement::updateOrCreate(
            [
                'procurement_id' => $procurement3->procurement_id,
                'request_name' => 'Jasa Cutting dan Fabrication'
            ], // key untuk check
            [
                'project_id' => $project1->project_id, // PERBAIKAN: tambahkan project_id
                'vendor_id' => 3,
                'created_date' => Carbon::now()->subDays(40),
                'deadline_date' => Carbon::now()->addDays(10),
                'request_status' => 'approved',
                'department_id' => 3,
            ]
        );

        $request4 = RequestProcurement::updateOrCreate(
            [
                'procurement_id' => $procurement4->procurement_id,
                'request_name' => 'Permintaan Alat Pelindung Diri (APD)'
            ], // key untuk check
            [
                'project_id' => $project1->project_id, // PERBAIKAN: tambahkan project_id
                'vendor_id' => null,
                'created_date' => Carbon::now()->subDays(20),
                'deadline_date' => Carbon::now()->addDays(5),
                'request_status' => 'draft',
                'department_id' => 4,
            ]
        );


        /**
         * ITEMS — DENGAN STATUS APPROVED/NOT_APPROVED
         */
        Item::updateOrCreate(
            [
                'request_procurement_id' => $request1->request_id,
                'item_name' => 'Baja High Grade'
            ], // key untuk check
            [
                'item_description' => 'Material baja high grade untuk struktur kapal',
                'amount' => 100,
                'unit' => 'ton',
                'unit_price' => 120000000,
                'total_price' => 12000000000,
            ]
        );

        $item2 = Item::create([
            'request_procurement_id' => $request1->request_id,
            'item_name' => 'Plat Baja Marine Grade',
            'item_description' => 'Plat baja tahan korosi untuk lambung kapal',
            'amount' => 50,
            'unit' => 'ton',
            'unit_price' => 95000000,
            'total_price' => 4750000000,
            'status' => 'approved',
            'approved_by' => 2,
            'approved_at' => Carbon::now()->subDays(85),
        ]);

        Item::create([
            'request_procurement_id' => $request2->request_id,
            'item_name' => 'Panel Kontrol Navigasi',
            'item_description' => 'Panel kontrol elektronik untuk sistem navigasi',
            'amount' => 5,
            'unit' => 'unit',
            'unit_price' => 250000000,
            'total_price' => 1250000000,
            'status' => 'approved',
            'approved_by' => 3,
            'approved_at' => Carbon::now()->subDays(55),
        ]);

        Item::create([
            'request_procurement_id' => $request2->request_id,
            'item_name' => 'Sensor Radar',
            'item_description' => 'Sensor radar untuk deteksi objek',
            'amount' => 10,
            'unit' => 'unit',
            'unit_price' => 85000000,
            'total_price' => 850000000,
            'status' => 'not_approved',
        ]);

        Item::create([
            'request_procurement_id' => $request3->request_id,
            'item_name' => 'Jasa Cutting Laser',
            'item_description' => 'Jasa pemotongan material dengan laser precision',
            'amount' => 200,
            'unit' => 'jam',
            'unit_price' => 500000,
            'total_price' => 100000000,
            'status' => 'approved',
            'approved_by' => 5,
            'approved_at' => Carbon::now()->subDays(35),
        ]);

        Item::create([
            'request_procurement_id' => $request4->request_id,
            'item_name' => 'Helm Safety',
            'item_description' => 'Helm pengaman standar K3',
            'amount' => 100,
            'unit' => 'pcs',
            'unit_price' => 150000,
            'total_price' => 15000000,
            'status' => 'not_approved',
        ]);

        Item::create([
            'request_procurement_id' => $request4->request_id,
            'item_name' => 'Safety Shoes',
            'item_description' => 'Sepatu safety boots',
            'amount' => 100,
            'unit' => 'pcs',
            'unit_price' => 350000,
            'total_price' => 35000000,
            'status' => 'not_approved',
        ]);

        /**
         * CONTRACT FOR PROJECT 1
         */
        // $contract1 = Contract::updateOrCreate(
        //     ['contract_number' => 'CTR/PAL/2025/001'], // key untuk check
        //     [
        //         'project_id' => $project1->project_id,
        //         'vendor_id' => 1,
        //         'contract_value' => 14500000000,
        //         'start_date' => Carbon::now()->subDays(70),
        //         'end_date' => Carbon::now()->addDays(30),
        //         'status' => 'active',
        //         'created_by' => 2, // user_id
        //     ]
        // );

        /**
         * INSPECTION REPORT
         */
        InspectionReport::updateOrCreate(
            [
                'project_id' => $project1->project_id,
                'item_id' => 1,
                'inspection_date' => Carbon::now()->subDays(10)
            ], // key untuk check
            [
                'inspector_id' => 5,
                'result' => 'passed',
                'notes' => 'Material sesuai spesifikasi teknis.',
            ]
        );

        InspectionReport::create([
            'project_id' => $project1->project_id,
            'item_id' => $item2->item_id,
            'inspection_date' => Carbon::now()->subDays(10),
            'inspector_id' => 5,
            'result' => 'passed',
            'notes' => 'Plat baja dalam kondisi baik, tidak ada cacat.',
        ]);

        /**
         * PROCUREMENT PROGRESS - Monitoring step procurement
         */
        $checkpoints = \App\Models\Checkpoint::orderBy('point_sequence')->get();

        // Progress untuk procurement 1 - Sudah sampai checkpoint 13 (Inspeksi Barang)
        foreach ($checkpoints->take(5) as $index => $checkpoint) {
            \App\Models\ProcurementProgress::create([
                'procurement_id' => $procurement1->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => ($index % 3 === 0) ? 2 : (($index % 3 === 1) ? 3 : 5),
                'status' => $index < 4 ? 'completed' : 'in_progress',
                'start_date' => Carbon::now()->subDays(90 - ($index * 6)),
                'end_date' => $index < 4 ? Carbon::now()->subDays(88 - ($index * 6)) : null,
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name . ' - ' . ($index < 12 ? 'Selesai' : 'Sedang Berjalan'),
            ]);
        }

        // Progress untuk procurement 2 - Baru sampai checkpoint 7 (Pengesahan Kontrak)
        foreach ($checkpoints->take(5) as $index => $checkpoint) {
            \App\Models\ProcurementProgress::create([
                'procurement_id' => $procurement2->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => ($index % 2 === 0) ? 2 : 3,
                'status' => $index < 4 ? 'completed' : 'in_progress',
                'start_date' => Carbon::now()->subDays(60 - ($index * 8)),
                'end_date' => $index < 4 ? Carbon::now()->subDays(57 - ($index * 8)) : null,
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name . ' - ' . ($index < 6 ? 'Selesai' : 'Sedang Berjalan'),
            ]);
        }

        // Progress untuk procurement 3 - Baru sampai checkpoint 4 (Evatek)
        foreach ($checkpoints->take(5) as $index => $checkpoint) {
            \App\Models\ProcurementProgress::create([
                'procurement_id' => $procurement3->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => ($index % 2 === 0) ? 3 : 7,
                'status' => $index < 3 ? 'completed' : 'in_progress',
                'start_date' => Carbon::now()->subDays(40 - ($index * 9)),
                'end_date' => $index < 3 ? Carbon::now()->subDays(37 - ($index * 9)) : null,
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name . ' - ' . ($index < 3 ? 'Selesai' : 'Sedang Berjalan'),
            ]);
        }

        // Progress untuk procurement 4 - Baru sampai checkpoint 2 (Pengecekan)
        foreach ($checkpoints->take(5) as $index => $checkpoint) {
            \App\Models\ProcurementProgress::create([
                'procurement_id' => $procurement4->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => 2,
                'status' => $index < 4 ? 'completed' : 'in_progress',
                'start_date' => Carbon::now()->subDays(20 - ($index * 5)),
                'end_date' => $index < 4 ? Carbon::now()->subDays(18 - ($index * 5)) : null,
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name . ' - ' . ($index < 1 ? 'Selesai' : 'Sedang Berjalan'),
            ]);
        }

        /**
         * PROJECT 2–7 - Additional projects
         */

        Project::updateOrCreate(
            ['project_code' => 'W000302'], // key untuk check
            [
                'project_name' => 'Pengadaan Sistem Radar Navigasi',
                'description' => 'Pengadaan radar navigasi untuk kapal perang',
                'owner_division_id' => 2,
                'priority' => 'tinggi',
                'start_date' => Carbon::now()->subDays(45),
                'end_date' => Carbon::now()->addDays(60),
                'status_project' => 'negosiasi_harga',
            ]
        );

        Project::updateOrCreate(
            ['project_code' => 'W000303'], // key untuk check
            [
                'project_name' => 'Pengadaan Mesin Diesel Utama',
                'description' => 'Mesin diesel untuk kapal tanker',
                'owner_division_id' => 7,
                'priority' => 'sedang',
                'start_date' => Carbon::now()->subDays(20),
                'end_date' => Carbon::now()->addDays(120),
                'status_project' => 'review_sc',
            ]
        );

        Project::updateOrCreate(
            ['project_code' => 'W000304'], // key untuk check
            [
                'project_name' => 'Pengadaan Peralatan Keselamatan Kapal',
                'description' => 'Life jacket, fire extinguisher, dll.',
                'owner_division_id' => 1,
                'priority' => 'sedang',
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(90),
                'status_project' => 'draft',
            ]
        );

        Project::updateOrCreate(
            ['project_code' => 'W000305'], // key untuk check
            [
                'project_name' => 'Pengadaan Cat Anti Karat & Coating',
                'description' => 'Cat marine grade untuk kapal',
                'owner_division_id' => 2,
                'priority' => 'rendah',
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->addDays(75),
                'status_project' => 'persetujuan_sekretaris',
            ]
        );

        //   $project1 = Project::create([
        //     'project_code'       => 'W000A01',
        //     'project_name'       => 'Pengadaan Barang Tes 1',
        //     'description'        => 'Project untuk testing approval - 1',
        //     'owner_division_id'  => 1,
        //     'priority'           => 'sedang',
        //     'start_date'         => Carbon::now()->subDays(2),
        //     'end_date'           => Carbon::now()->addDays(20),
        //     'status_project'     => 'persetujuan_sekretaris',
        // ]);

        // Procurement::create([
        //     'project_id'            => $project1->project_id,
        //     'code_procurement'      => $project1->project_code . '-P1',
        //     'name_procurement'      => 'Procurement Tes Pertama',
        //     'description'           => 'Procurement 1 untuk testing approval',
        //     'department_procurement'=> 1,
        //     'priority'              => 'sedang',
        //     'start_date'            => Carbon::now(),
        //     'end_date'              => Carbon::now()->addDays(7),
        //     'status_procurement'    => 'submitted',
        // ]);

        // -------------------------
        // DATA 2
        // -------------------------
        // $project2 = Project::create([
        //     'project_code'       => 'W000A02',
        //     'project_name'       => 'Pengadaan Barang Tes 2',
        //     'description'        => 'Project untuk testing approval - 2',
        //     'owner_division_id'  => 1,
        //     'priority'           => 'tinggi',
        //     'start_date'         => Carbon::now()->subDays(1),
        //     'end_date'           => Carbon::now()->addDays(25),
        //     'status_project'     => 'persetujuan_sekretaris',
        // ]);

        // Procurement::create([
        //     'project_id'            => $project2->project_id,
        //     'code_procurement'      => $project2->project_code . '-P1',
        //     'name_procurement'      => 'Procurement Tes Kedua',
        //     'description'           => 'Procurement 2 untuk testing approval',
        //     'department_procurement'=> 1,
        //     'priority'              => 'tinggi',
        //     'start_date'            => Carbon::now(),
        //     'end_date'              => Carbon::now()->addDays(10),
        //     'status_procurement'    => 'submitted',
        // ]);


        Project::updateOrCreate(
            ['project_code' => 'W000306'], // key untuk check
            [
                'project_name' => 'Pengadaan Sistem Komunikasi Satelit',
                'description' => 'Sistem satelit untuk kapal jelajah jauh',
                'owner_division_id' => 2,
                'priority' => 'tinggi',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(90),
                'status_project' => 'pembuatan_hps',
            ]
        );

        Project::updateOrCreate(
            ['project_code' => 'W000307'], // key untuk check
            [
                'project_name' => 'Pengadaan Generator Listrik',
                'description' => 'Generator cadangan 500 KVA',
                'owner_division_id' => 2,
                'priority' => 'sedang',
                'start_date' => Carbon::now()->subDays(25),
                'end_date' => Carbon::now()->addDays(80),
                'status_project' => 'pemilihan_vendor',
            ]
        );

        echo "✅ Seeder berhasil (dengan PaymentSchedule & InspectionReport, tanpa Contract).\n";
    }
}
