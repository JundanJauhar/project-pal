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
use App\Models\Procurement;
use App\Models\ProcurementProgress;


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
        $projectCode = $project1->project_code; // contoh: "W000301"

        /**
         * ============================
         * PROCUREMENT DENGAN STATUS YANG BENAR
         * ============================
         * Status yang valid: 'in_progress', 'completed', 'cancelled'
         */

        // Seeder untuk procurement 1 - Completed (sudah selesai semua checkpoint)
        $procurement1 = \App\Models\Procurement::create([
            'project_id' => $project1->project_id,
            'code_procurement' => $projectCode . '-01',
            'name_procurement' => 'Pengadaan Material Baja Berkualitas Tinggi',
            'description' => 'Pengadaan material baja untuk proyek 1',
            'department_procurement' => $dept1->department_id,
            'priority' => 'tinggi',
            'start_date' => Carbon::now()->subDays(100),
            'end_date' => Carbon::now()->subDays(5),
            'status_procurement' => 'completed', // FIXED: completed karena sudah selesai
        ]);

        // Seeder untuk procurement 2 - In Progress (masih dalam proses)
        $procurement2 = \App\Models\Procurement::create([
            'project_id' => $project1->project_id,
            'code_procurement' => $projectCode . '-02',
            'name_procurement' => 'Pengadaan Komponen Elektronik',
            'description' => 'Pengadaan komponen elektronik untuk sistem kontrol',
            'department_procurement' => $dept2->department_id,
            'priority' => 'sedang',
            'start_date' => Carbon::now()->subDays(60),
            'end_date' => Carbon::now()->addDays(15),
            'status_procurement' => 'in_progress', // FIXED: in_progress karena masih berjalan
        ]);

        // Seeder untuk procurement 3 - In Progress
        $procurement3 = \App\Models\Procurement::create([
            'project_id' => $project1->project_id,
            'code_procurement' => $projectCode . '-03',
            'name_procurement' => 'Jasa Cutting dan Fabrication',
            'description' => 'Jasa potong dan fabrikasi material logam',
            'department_procurement' => $dept3->department_id,
            'priority' => 'tinggi',
            'start_date' => Carbon::now()->subDays(40),
            'end_date' => Carbon::now()->addDays(10),
            'status_procurement' => 'in_progress', // FIXED: in_progress
        ]);

        // Seeder untuk procurement 4 - In Progress (baru mulai)
        $procurement4 = \App\Models\Procurement::create([
            'project_id' => $project1->project_id,
            'code_procurement' => $projectCode . '-04',
            'name_procurement' => 'Permintaan Alat Pelindung Diri (APD)',
            'description' => 'Pengadaan APD untuk keselamatan kerja',
            'department_procurement' => $dept4->department_id,
            'priority' => 'rendah',
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->addDays(5),
            'status_procurement' => 'in_progress', // FIXED: in_progress
        ]);

        // Seeder untuk procurement 5 - berada di checkpoint ke-11
        $procurement5 = \App\Models\Procurement::create([
            'project_id' => $project1->project_id,
            'code_procurement' => $projectCode . '-05',
            'name_procurement' => 'Pengadaan Mesin Pompa Hidrolik',
            'description' => 'Pengadaan mesin pompa hidrolik untuk sistem kapal',
            'department_procurement' => $dept2->department_id,
            'priority' => 'sedang',
            'start_date' => Carbon::now()->subDays(70),
            'end_date' => Carbon::now()->addDays(20),
            'status_procurement' => 'in_progress',
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
                'project_id' => $project1->project_id,
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
                'project_id' => $project1->project_id,
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
                'project_id' => $project1->project_id,
                'vendor_id' => null,
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
                'project_id' => $project1->project_id,
                'vendor_id' => null,
                'created_date' => Carbon::now()->subDays(20),
                'deadline_date' => Carbon::now()->addDays(5),
                'request_status' => 'draft',
                'department_id' => 4,
            ]
        );

        $request5 = RequestProcurement::updateOrCreate(
    [
        'procurement_id' => $procurement5->procurement_id,
        'request_name' => 'Pengadaan Mesin Pompa Hidrolik'
    ],
    [
        'project_id' => $project1->project_id,
        'vendor_id' => 1,
        'created_date' => Carbon::now()->subDays(70),
        'deadline_date' => Carbon::now()->addDays(20),
        'request_status' => 'submitted',
        'department_id' => $dept2->department_id,
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
            ]
        );

        $item2 = Item::create([
            'request_procurement_id' => $request1->request_id,
            'item_name' => 'Plat Baja Marine Grade',
            'item_description' => 'Plat baja tahan korosi untuk lambung kapal',
            'amount' => 50,
            'unit' => 'ton',
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
            'status' => 'not_approved',
        ]);

        Item::create([
            'request_procurement_id' => $request3->request_id,
            'item_name' => 'Cutting Laser',
            'item_description' => 'Alat pemotongan material dengan laser precision',
            'amount' => 20,
            'unit' => 'pcs',
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
            'status' => 'not_approved',
        ]);

        Item::create([
            'request_procurement_id' => $request4->request_id,
            'item_name' => 'Safety Shoes',
            'item_description' => 'Sepatu safety boots',
            'amount' => 100,
            'unit' => 'pcs',
            'status' => 'not_approved',
        ]);

        Item::create([
            'request_procurement_id' => $request5->request_id,
            'item_name' => 'Unit Pompa Hidrolik 500bar',
            'item_description' => 'Pompa hidrolik tekanan tinggi untuk sistem kapal',
            'amount' => 3,
            'unit' => 'unit',
            'status' => 'approved',
            'approved_by' => 5,
            'approved_at' => Carbon::now()->subDays(65),
        ]);


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

        // Progress untuk procurement 1 - COMPLETED (semua checkpoint selesai)
        foreach ($checkpoints as $index => $checkpoint) {
            \App\Models\ProcurementProgress::create([
                'procurement_id' => $procurement1->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => ($index % 3 === 0) ? 2 : (($index % 3 === 1) ? 3 : 5),
                'status' => 'completed', // Semua selesai
                'start_date' => Carbon::now()->subDays(100 - ($index * 6)),
                'end_date' => Carbon::now()->subDays(98 - ($index * 6)),
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name . ' - Selesai',
            ]);
        }

        // Progress untuk procurement 2 - IN PROGRESS (sampai checkpoint ke-7)
        foreach ($checkpoints->take(7) as $index => $checkpoint) {
            \App\Models\ProcurementProgress::create([
                'procurement_id' => $procurement2->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => ($index % 2 === 0) ? 2 : 3,
                'status' => $index < 6 ? 'completed' : 'in_progress',
                'start_date' => Carbon::now()->subDays(60 - ($index * 8)),
                'end_date' => $index < 6 ? Carbon::now()->subDays(57 - ($index * 8)) : null,
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name . ' - ' . ($index < 6 ? 'Selesai' : 'Sedang Berjalan'),
            ]);
        }

        foreach ($checkpoints->take(3) as $index => $checkpoint) {

    // CP1 = completed
    // CP2 = completed
    // CP3 = in_progress
    $status = ($index < 2) ? 'completed' : 'in_progress';

    \App\Models\ProcurementProgress::create([
        'procurement_id' => $procurement3->procurement_id,
        'checkpoint_id'  => $checkpoint->point_id,   // gunakan point_id yang benar
        'user_id'        => ($index % 2 === 0) ? 3 : 7,
        'status'         => $status,
        'start_date'     => Carbon::now()->subDays(40 - ($index * 3)),
        'end_date'       => $status === 'completed'
                            ? Carbon::now()->subDays(39 - ($index * 3))
                            : null,
        'note'           => "Checkpoint " . ($index + 1) . ": {$checkpoint->point_name} - " .
                           ($status === 'completed' ? 'Selesai' : 'Sedang Berjalan'),
    ]);
}


        // Progress untuk procurement 4 - IN PROGRESS (baru checkpoint ke-2)
        foreach ($checkpoints->take(2) as $index => $checkpoint) {
            \App\Models\ProcurementProgress::create([
                'procurement_id' => $procurement4->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => 2,
                'status' => $index < 1 ? 'completed' : 'in_progress',
                'start_date' => Carbon::now()->subDays(20 - ($index * 5)),
                'end_date' => $index < 1 ? Carbon::now()->subDays(18 - ($index * 5)) : null,
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name . ' - ' . ($index < 1 ? 'Selesai' : 'Sedang Berjalan'),
            ]);
        }

        // Progress Procurement 5 - sampai checkpoint ke-11
        foreach ($checkpoints->take(11) as $index => $checkpoint) {
            \App\Models\ProcurementProgress::create([
                'procurement_id' => $procurement5->procurement_id,
                'checkpoint_id' => $checkpoint->point_id,
                'user_id' => ($index % 2 === 0) ? 2 : 3,
                'status' => $index < 10 ? 'completed' : 'in_progress',
                'start_date' => Carbon::now()->subDays(70 - ($index * 6)),
                'end_date' => $index < 10 ? Carbon::now()->subDays(67 - ($index * 6)) : null,
                'note' => 'Checkpoint ' . ($index + 1) . ': ' . $checkpoint->point_name .
                            ' - ' . ($index < 10 ? 'Selesai' : 'Sedang Berjalan'),
            ]);
        }


        /**
         * PROJECT 2–41 - Additional projects (40 projects with varied dates and times)
         */
        $projects = [
            ['W000302', 'Pengadaan Sistem Radar Navigasi', 'Pengadaan radar navigasi untuk kapal perang', 2, 'tinggi', 180, 8, 45, 'negosiasi_harga'],
            ['W000303', 'Pengadaan Mesin Diesel Utama', 'Mesin diesel untuk kapal tanker', 7, 'sedang', 165, 14, 90, 'review_sc'],
            ['W000304', 'Pengadaan Peralatan Keselamatan Kapal', 'Life jacket, fire extinguisher, dll.', 1, 'sedang', 145, 10, 110, 'draft'],
            ['W000305', 'Pengadaan Cat Anti Karat & Coating', 'Cat marine grade untuk kapal', 2, 'rendah', 128, 9, 70, 'persetujuan_sekretaris'],
            ['W000306', 'Pengadaan Sistem Komunikasi Satelit', 'Sistem satelit untuk kapal jelajah jauh', 2, 'tinggi', 157, 15, 65, 'pembuatan_hps'],
            ['W000307', 'Pengadaan Generator Listrik', 'Generator cadangan 500 KVA', 2, 'sedang', 98, 11, 100, 'pemilihan_vendor'],
            ['W000308', 'Pengadaan Propeller Shaft', 'Poros baling-baling untuk kapal kargo', 7, 'tinggi', 210, 7, 85, 'completed'],
            ['W000309', 'Pengadaan Sistem Pemadam Kebakaran', 'Fire suppression system untuk kapal penumpang', 1, 'tinggi', 193, 13, 95, 'negosiasi_harga'],
            ['W000310', 'Pengadaan Rudder System', 'Sistem kemudi kapal', 7, 'sedang', 175, 16, 120, 'review_sc'],
            ['W000311', 'Pengadaan Anchor & Chain', 'Jangkar dan rantai jangkar', 2, 'rendah', 142, 8, 88, 'draft'],
            ['W000312', 'Pengadaan Winch Hidrolik', 'Winch hidrolik untuk kapal nelayan', 4, 'sedang', 167, 12, 75, 'persetujuan_sekretaris'],
            ['W000313', 'Pengadaan Ballast Tank Equipment', 'Peralatan tangki balast', 4, 'tinggi', 188, 9, 102, 'pembuatan_hps'],
            ['W000314', 'Pengadaan Main Switchboard', 'Panel listrik utama kapal', 2, 'tinggi', 201, 14, 67, 'pemilihan_vendor'],
            ['W000315', 'Pengadaan Ventilation System', 'Sistem ventilasi kapal', 1, 'sedang', 135, 10, 93, 'completed'],
            ['W000316', 'Pengadaan Crane Deck', 'Derek dek untuk kapal kargo', 7, 'tinggi', 159, 11, 78, 'negosiasi_harga'],
            ['W000317', 'Pengadaan Fuel Oil Transfer Pump', 'Pompa transfer bahan bakar', 2, 'sedang', 172, 15, 115, 'review_sc'],
            ['W000318', 'Pengadaan Sea Water Cooling System', 'Sistem pendingin air laut', 2, 'tinggi', 154, 7, 84, 'draft'],
            ['W000319', 'Pengadaan Steering Gear', 'Mesin kemudi kapal', 7, 'tinggi', 196, 13, 98, 'persetujuan_sekretaris'],
            ['W000320', 'Pengadaan Bilge Pump', 'Pompa lambung kapal', 1, 'rendah', 118, 8, 72, 'pembuatan_hps'],
            ['W000321', 'Pengadaan Fresh Water Generator', 'Generator air tawar', 2, 'sedang', 183, 16, 105, 'pemilihan_vendor'],
            ['W000322', 'Pengadaan Air Compressor', 'Kompresor udara untuk kapal', 2, 'sedang', 149, 9, 81, 'completed'],
            ['W000323', 'Pengadaan Hydraulic System', 'Sistem hidrolik kapal', 7, 'tinggi', 207, 12, 96, 'negosiasi_harga'],
            ['W000324', 'Pengadaan Emergency Generator', 'Generator darurat 300 KVA', 2, 'tinggi', 191, 14, 88, 'review_sc'],
            ['W000325', 'Pengadaan Sewage Treatment Plant', 'Instalasi pengolahan limbah', 1, 'sedang', 163, 10, 109, 'draft'],
            ['W000326', 'Pengadaan Navigation Light', 'Lampu navigasi kapal', 2, 'rendah', 122, 11, 63, 'persetujuan_sekretaris'],
            ['W000327', 'Pengadaan Mooring Equipment', 'Peralatan tambat kapal', 7, 'sedang', 176, 15, 92, 'pembuatan_hps'],
            ['W000328', 'Pengadaan Lifeboat & Davit', 'Sekoci dan davit penyelamat', 1, 'tinggi', 198, 7, 77, 'pemilihan_vendor'],
            ['W000329', 'Pengadaan Radio Communication', 'Sistem komunikasi radio', 2, 'tinggi', 185, 13, 101, 'completed'],
            ['W000330', 'Pengadaan Gyro Compass', 'Kompas gyro untuk navigasi', 2, 'sedang', 152, 8, 86, 'negosiasi_harga'],
            ['W000331', 'Pengadaan Echo Sounder', 'Alat ukur kedalaman laut', 2, 'rendah', 131, 16, 69, 'review_sc'],
            ['W000332', 'Pengadaan AIS Transponder', 'Automatic Identification System', 2, 'sedang', 169, 9, 94, 'draft'],
            ['W000333', 'Pengadaan CCTV System', 'Sistem kamera pengawasan kapal', 1, 'rendah', 114, 12, 58, 'persetujuan_sekretaris'],
            ['W000334', 'Pengadaan Watertight Door', 'Pintu kedap air', 7, 'tinggi', 203, 14, 107, 'pembuatan_hps'],
            ['W000335', 'Pengadaan Deck Machinery', 'Mesin-mesin dek kapal', 7, 'tinggi', 194, 10, 83, 'pemilihan_vendor'],
            ['W000336', 'Pengadaan Cargo Hatch Cover', 'Penutup palka kargo', 7, 'sedang', 160, 11, 99, 'completed'],
            ['W000337', 'Pengadaan Insulation Material', 'Material insulasi kapal', 2, 'rendah', 138, 15, 71, 'negosiasi_harga'],
            ['W000338', 'Pengadaan Piping System', 'Sistem perpipaan kapal', 2, 'sedang', 179, 7, 112, 'review_sc'],
            ['W000339', 'Pengadaan Battery Bank', 'Bank baterai untuk sistem darurat', 2, 'tinggi', 187, 13, 80, 'draft'],
            ['W000340', 'Pengadaan Oil Water Separator', 'Separator minyak dan air', 1, 'sedang', 146, 8, 76, 'persetujuan_sekretaris'],
            ['W000341', 'Pengadaan Ship Furniture', 'Furnitur dan interior kapal', 1, 'rendah', 125, 16, 65, 'pembuatan_hps'],
        ];

        foreach ($projects as $index => $projectData) {
            // Randomize hour and minute for more variety
            $hour = ($index * 3 + 7) % 24; // Vary hours between 0-23
            $minute = ($index * 17) % 60;  // Vary minutes between 0-59
            
            Project::updateOrCreate(
                ['project_code' => $projectData[0]],
                [
                    'project_name' => $projectData[1],
                    'description' => $projectData[2],
                    'owner_division_id' => $projectData[3],
                    'priority' => $projectData[4],
                    'start_date' => Carbon::now()->subDays($projectData[5])->setTime($hour, $minute),
                    'end_date' => Carbon::now()->addDays($projectData[7])->setTime(($hour + 8) % 24, ($minute + 30) % 60),
                    'status_project' => $projectData[8],
                    'created_at' => Carbon::now()->subDays($projectData[5])->setTime($hour, $minute),
                    'updated_at' => Carbon::now()->subDays($projectData[6])->setTime(($hour + 2) % 24, ($minute + 15) % 60),
                ]
            );
        }

        echo "✅ Seeder berhasil dengan 40 project data dan status procurement yang benar: 'in_progress', 'completed', 'cancelled'.\n";
    }
}