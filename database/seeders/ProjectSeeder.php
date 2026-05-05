<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        $projects = [
            [
                'project_code' => 'W000301',
                'project_name' => 'Pengadaan Material Kapal Fregat',
                'description' => 'Pengadaan material utama untuk kapal fregat kelas sigma',
            ],
            [
                'project_code' => 'W000302',
                'project_name' => 'Pengadaan Sistem Radar Navigasi',
                'description' => 'Pengadaan radar navigasi untuk kapal perang',
            ],
            [
                'project_code' => 'W000303',
                'project_name' => 'Pengadaan Mesin Diesel Utama',
                'description' => 'Mesin diesel untuk kapal tanker',
            ],
            [
                'project_code' => 'W000304',
                'project_name' => 'Pengadaan Peralatan Keselamatan Kapal',
                'description' => 'Life jacket, fire extinguisher, dll.',
            ],
            [
                'project_code' => 'W000305',
                'project_name' => 'Pengadaan Cat Anti Karat & Coating',
                'description' => 'Cat marine grade untuk kapal',
            ],
            [
                'project_code' => 'W000306',
                'project_name' => 'Pengadaan Sistem Komunikasi Satelit',
                'description' => 'Sistem satelit untuk kapal jelajah jauh',
            ],
            [
                'project_code' => 'W000307',
                'project_name' => 'Pengadaan Generator Listrik',
                'description' => 'Generator cadangan 500 KVA',
            ],
        ];

        foreach ($projects as $project) {
            if (DB::table('projects')->where('project_code', $project['project_code'])->exists()) {
                DB::table('projects')
                    ->where('project_code', $project['project_code'])
                    ->update([
                        'project_name' => $project['project_name'],
                        'description' => $project['description'],
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('projects')->insert([
                    'project_code' => $project['project_code'],
                    'project_name' => $project['project_name'],
                    'description' => $project['description'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $projectRows = DB::table('projects')
            ->whereIn('project_code', array_column($projects, 'project_code'))
            ->orderBy('project_code')
            ->get(['project_id', 'project_code', 'project_name']);

        if ($projectRows->isEmpty()) {
            $this->command->error('Proyek tidak ditemukan. Pastikan ProjectSeeder berhasil membuat semua proyek.');
            return;
        }

        $projectIds = $projectRows->pluck('project_id')->toArray();
        $projectNames = $projectRows->pluck('project_name', 'project_id')->toArray();

        $departmentIds = DB::table('departments')->pluck('department_id')->toArray();
        if (empty($departmentIds)) {
            $this->command->error('Department tidak ditemukan. Pastikan DepartmentSeeder sudah dijalankan.');
            return;
        }

        $userId = DB::table('users')->orderBy('user_id')->value('user_id') ?: 1;

        $vendorIds = DB::table('vendors')->pluck('id_vendor')->toArray();
        if (empty($vendorIds)) {
            $this->command->error('Vendor tidak ditemukan. Pastikan VendorSeeder sudah dijalankan sebelum ProjectSeeder.');
            return;
        }

        $checkpoints = DB::table('checkpoints')
            ->orderBy('point_sequence', 'asc')
            ->get();

        if ($checkpoints->isEmpty()) {
            $this->command->error('Checkpoint tidak ditemukan. Pastikan CheckpointSeeder sudah dijalankan.');
            return;
        }

        $priorities = ['Rendah', 'Sedang', 'Tinggi'];
        $paymentTypes = ['SKBDN', 'L/C', 'TT'];
        $picEvatekCodes = ['EO', 'HC', 'MO', 'HO', 'SEWACO'];
        $incoterms = ['FOB', 'CIF', 'DDP', 'EXW'];

        $procurementTemplates = [
            [
                'name' => 'Pengadaan Plat Baja Marine Grade',
                'description' => 'Plat baja marine grade untuk struktur rangka kapal dan rangkaian utama.',
                'biro' => 'Raw Material & Consumable',
                'items' => [
                    ['item_name' => 'Plat Baja Marine Grade A131', 'item_description' => 'Plat baja untuk konstruksi rangka kapal fregat.', 'specification' => '10 mm x 6 m x 2 m', 'unit' => 'ton'],
                    ['item_name' => 'Profil Baja IPN 400', 'item_description' => 'Profil baja untuk struktur utama.', 'specification' => 'IPN 400 galvanis, 12 m', 'unit' => 'batang'],
                ],
            ],
            [
                'name' => 'Pengadaan Beton Precast Kapal',
                'description' => 'Beton precast berkekuatan tinggi untuk struktur pelabuhan dan dermaga.',
                'biro' => 'Raw Material & Consumable',
                'items' => [
                    ['item_name' => 'Beton Precast 40 MPa', 'item_description' => 'Beton precast kualitas tinggi untuk dermaga.', 'specification' => '40 MPa, ukuran 2x2x6 m', 'unit' => 'm3'],
                    ['item_name' => 'Rangka Beton Pra-tekan', 'item_description' => 'Rangka beton untuk konstruksi lantai dan tiang.', 'specification' => 'Beton pra-tekan, 16 m', 'unit' => 'unit'],
                ],
            ],
            [
                'name' => 'Pengadaan Pipa HDPE dan Fitting',
                'description' => 'Pipa HDPE untuk sistem air bersih dan utilitas kapal.',
                'biro' => 'Raw Material & Consumable',
                'items' => [
                    ['item_name' => 'Pipa HDPE 200 mm', 'item_description' => 'Pipa HDPE untuk saluran air bersih.', 'specification' => '200 mm x 12 m', 'unit' => 'batang'],
                    ['item_name' => 'Fitting Pipa HDPE 90 derajat', 'item_description' => 'Fitting untuk sambungan pipa HDPE.', 'specification' => '90 derajat, tekan', 'unit' => 'buah'],
                ],
            ],
            [
                'name' => 'Pengadaan Radar Navigasi X-Band',
                'description' => 'Radar navigasi X-Band untuk kapal patroli dan pengamanan laut.',
                'biro' => 'Navigation Systems',
                'items' => [
                    ['item_name' => 'Radar X-Band 25 kW', 'item_description' => 'Radar navigasi untuk deteksi target di laut.', 'specification' => '25 kW, 12 nm', 'unit' => 'set'],
                    ['item_name' => 'Antena Radar X-Band', 'item_description' => 'Antena radar untuk sistem navigasi.', 'specification' => 'Diameter 1.2 m', 'unit' => 'buah'],
                ],
            ],
            [
                'name' => 'Pengadaan Sistem GPS Maritime',
                'description' => 'Sistem GPS khusus kelautan untuk navigasi kapal.',
                'biro' => 'Navigation Systems',
                'items' => [
                    ['item_name' => 'GPS Receiver Marine', 'item_description' => 'Receiver GPS untuk posisi dan navigasi kapal.', 'specification' => 'Dual-band, IP67', 'unit' => 'unit'],
                    ['item_name' => 'Antena GPS Marine', 'item_description' => 'Antena GPS tahan cuaca laut.', 'specification' => 'Magnet mount, 5 m kabel', 'unit' => 'buah'],
                ],
            ],
            [
                'name' => 'Pengadaan Sensor Sonar',
                'description' => 'Sensor sonar untuk pemetaan bawah laut dan deteksi objek.',
                'biro' => 'Navigation Systems',
                'items' => [
                    ['item_name' => 'Transducer Sonar', 'item_description' => 'Transducer untuk sistem sonar.', 'specification' => '200 kHz, 4 kW', 'unit' => 'set'],
                    ['item_name' => 'Kabel Sonar Tahan Laut', 'item_description' => 'Kabel data untuk sensor sonar.', 'specification' => '50 m, armoured', 'unit' => 'rol'],
                ],
            ],
            [
                'name' => 'Pengadaan Mesin Diesel Utama',
                'description' => 'Mesin diesel utama untuk kapal tanker dan kapal patroli.',
                'biro' => 'Mechanical & Sparepart',
                'items' => [
                    ['item_name' => 'Mesin Diesel 2.500 HP', 'item_description' => 'Mesin diesel utama untuk kapal tanker.', 'specification' => '2.500 HP, 1500 rpm', 'unit' => 'unit'],
                    ['item_name' => 'Pompa Bahan Bakar Diesel', 'item_description' => 'Pompa bahan bakar untuk mesin diesel.', 'specification' => 'High pressure, 20 bar', 'unit' => 'buah'],
                ],
            ],
            [
                'name' => 'Pengadaan Gearbox Helical',
                'description' => 'Gearbox helical untuk transmisi tenaga mesin kapal.',
                'biro' => 'Mechanical & Sparepart',
                'items' => [
                    ['item_name' => 'Gearbox Helical', 'item_description' => 'Gearbox untuk poros penggerak kapal.', 'specification' => 'Ratio 4.2:1', 'unit' => 'unit'],
                    ['item_name' => 'Bearing Ball Marine', 'item_description' => 'Bearing untuk gearbox dan poros.', 'specification' => '6206, stainless steel', 'unit' => 'set'],
                ],
            ],
            [
                'name' => 'Pengadaan Life Jacket Inflatable',
                'description' => 'Life jacket inflatable untuk keselamatan awak kapal.',
                'biro' => 'Safety Equipment',
                'items' => [
                    ['item_name' => 'Life Jacket Inflatable', 'item_description' => 'Life jacket otomatis untuk keselamatan laut.', 'specification' => 'ISO 12402, ukuran universal', 'unit' => 'buah'],
                    ['item_name' => 'Buoyancy Aid', 'item_description' => 'Pelampung tambahan untuk latihan evakuasi.', 'specification' => '25 N', 'unit' => 'buah'],
                ],
            ],
            [
                'name' => 'Pengadaan Fire Suppression System',
                'description' => 'Sistem pemadam kebakaran otomatis untuk ruang mesin.',
                'biro' => 'Safety Equipment',
                'items' => [
                    ['item_name' => 'Fire Extinguisher CO2', 'item_description' => 'Pemadam kebakaran CO2 untuk ruang mesin.', 'specification' => '2 kg', 'unit' => 'buah'],
                    ['item_name' => 'Fire Alarm Control Panel', 'item_description' => 'Panel kontrol sistem alarm kebakaran.', 'specification' => '4 zona', 'unit' => 'unit'],
                ],
            ],
            [
                'name' => 'Pengadaan Generator Diesel 500 KVA',
                'description' => 'Generator cadangan listrik untuk kapal dan fasilitas pendukung.',
                'biro' => 'Electrical',
                'items' => [
                    ['item_name' => 'Generator Diesel 500 KVA', 'item_description' => 'Generator listrik cadangan marine.', 'specification' => '500 KVA, 400 V', 'unit' => 'unit'],
                    ['item_name' => 'Panel Synchronizer', 'item_description' => 'Panel sinkronisasi untuk multiple generator.', 'specification' => '3 phase', 'unit' => 'unit'],
                ],
            ],
            [
                'name' => 'Pengadaan Panel Listrik Main Switchboard',
                'description' => 'Panel switchboard utama untuk distribusi listrik kapal.',
                'biro' => 'Electrical',
                'items' => [
                    ['item_name' => 'Main Switchboard', 'item_description' => 'Switchboard utama untuk sistem kelistrikan.', 'specification' => '800 A, 3 phase', 'unit' => 'unit'],
                    ['item_name' => 'MCC Panel', 'item_description' => 'Motor Control Center untuk pompa dan kapal.', 'specification' => '10 circuit', 'unit' => 'unit'],
                ],
            ],
            [
                'name' => 'Pengadaan Server Rack 42U',
                'description' => 'Server rack untuk pusat data kapal dan sistem kontrol.',
                'biro' => 'IT & Communication',
                'items' => [
                    ['item_name' => 'Server Rack 42U', 'item_description' => 'Rak server untuk peralatan IT.', 'specification' => '42U, kedalaman 1200 mm', 'unit' => 'unit'],
                    ['item_name' => 'Network Switch 48 Port', 'item_description' => 'Switch jaringan untuk distribusi data.', 'specification' => '48 port Gigabit', 'unit' => 'unit'],
                ],
            ],
            [
                'name' => 'Pengadaan Sistem Satelit Komunikasi',
                'description' => 'Sistem satelit untuk komunikasi jarak jauh kapal.',
                'biro' => 'IT & Communication',
                'items' => [
                    ['item_name' => 'Terminal Satelit VSAT', 'item_description' => 'Terminal untuk koneksi satelit.', 'specification' => 'Ku-band, IP67', 'unit' => 'set'],
                    ['item_name' => 'Antenna Satelit 1.2 m', 'item_description' => 'Antenna satelit untuk komunikasi laut.', 'specification' => '1.2 m, motorized', 'unit' => 'buah'],
                ],
            ],
        ];

        $statusPool = array_merge(
            array_fill(0, 55, 'in_progress'),
            array_fill(0, 30, 'completed'),
            array_fill(0, 15, 'cancelled')
        );

        $createdProcurements = 0;

        for ($i = 1; $i <= 100; $i++) {
            $projectIndex = ($i - 1) % count($projectIds);
            $projectId = $projectIds[$projectIndex];
            $projectName = $projectNames[$projectId];
            $projectCode = $projectRows[$projectIndex]->project_code;
            $code = $projectCode . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);

            if (DB::table('procurement')->where('code_procurement', $code)->exists()) {
                continue;
            }

            $template = $procurementTemplates[array_rand($procurementTemplates)];
            $taskNameSuffixes = ['untuk proyek', 'untuk kapal', 'dengan spesifikasi tinggi', 'berbasis marine', 'untuk sistem navigasi'];
            $nameSuffix = $taskNameSuffixes[array_rand($taskNameSuffixes)];
            $procurementName = $template['name'] . ' ' . $nameSuffix . ' ' . substr($projectName, 11);

            $status = $statusPool[array_rand($statusPool)];
            $checkpointCount = $checkpoints->count();

            $startDate = Carbon::now()->subDays(rand(10, 180));
            $endDate = $status === 'cancelled'
                ? (clone $startDate)->addDays(rand(7, 45))
                : (clone $startDate)->addDays(rand(25, 120));

            if ($status === 'completed') {
                $progressDepth = $checkpointCount;
            } elseif ($status === 'cancelled') {
                $progressDepth = rand(1, max(1, (int) floor($checkpointCount / 2)));
            } else {
                $progressDepth = rand(2, max(2, $checkpointCount - 1));
                if (rand(1, 5) == 1) {
                    $progressDepth = max(2, $checkpointCount - rand(1, 2));
                }
            }

            $itemTemplates = $template['items'];
            shuffle($itemTemplates);
            $itemsToCreate = array_slice($itemTemplates, 0, rand(1, min(3, count($itemTemplates))));
            $departmentId = $departmentIds[array_rand($departmentIds)];
            $selectedVendorId = $vendorIds[array_rand($vendorIds)];
            $inquiryValueBase = round(rand(120, 620) * 1000000, 2);

            $procurementId = DB::table('procurement')->insertGetId([
                'project_id' => $projectId,
                'code_procurement' => $code,
                'name_procurement' => $procurementName,
                'description' => $template['description'] . ' (' . $projectName . ')',
                'department_procurement' => $departmentId,
                'juru_beli' => 'Juru Beli ' . substr($template['name'], 12),
                'biro_pengadaan' => $template['biro'],
                'no_pr' => 'PR-' . $projectCode . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'priority' => $priorities[array_rand($priorities)],
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'use_evatek' => $progressDepth >= 3,
                'status_procurement' => $status,
                'procurement_link' => 'https://procurement.pal.example.com/' . $code,
                'notes' => 'Procurement domain ' . $template['name'] . ' seeded untuk analytics lintas proyek.',
                'created_at' => $now,
                'updated_at' => $now,
            ], 'procurement_id');

            $requestId = DB::table('request_procurement')->insertGetId([
                'procurement_id' => $procurementId,
                'project_id' => $projectId,
                'vendor_id' => $selectedVendorId,
                'request_name' => 'Request ' . $template['name'] . ' ' . $code,
                'created_date' => $startDate->format('Y-m-d'),
                'deadline_date' => $endDate->format('Y-m-d'),
                'request_status' => 'submitted',
                'department_id' => $departmentId,
                'created_at' => $now,
                'updated_at' => $now,
            ], 'request_id');

            $itemIds = [];
            $arrivalDateValue = $progressDepth >= 9
                ? (clone $startDate)->addDays(rand(45, 110))->format('Y-m-d')
                : null;

            foreach ($itemsToCreate as $itemTemplate) {
                $itemIds[] = DB::table('items')->insertGetId([
                    'request_procurement_id' => $requestId,
                    'item_name' => $itemTemplate['item_name'],
                    'item_description' => $itemTemplate['item_description'],
                    'specification' => $itemTemplate['specification'],
                    'amount' => rand(5, 120),
                    'unit' => $itemTemplate['unit'],
                    'arrival_date' => $arrivalDateValue,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'item_id');
            }

            if ($progressDepth >= 2) {
                $inquiryDate = (clone $startDate)->addDays(rand(2, 8));
                $quotationDate = (clone $inquiryDate)->addDays(rand(3, 9));
                $targetQuotation = (clone $inquiryDate)->addDays(14);

                DB::table('inquiry_quotations')->insert([
                    'vendor_id' => $selectedVendorId,
                    'procurement_id' => $procurementId,
                    'tanggal_inquiry' => $inquiryDate->format('Y-m-d'),
                    'tanggal_quotation' => $quotationDate->format('Y-m-d'),
                    'target_quotation' => $targetQuotation->format('Y-m-d'),
                    'lead_time' => rand(7, 24) . ' hari',
                    'nilai_harga' => $inquiryValueBase,
                    'currency' => 'IDR',
                    'link' => 'https://vendor.example.com/inquiry/' . $code,
                    'notes' => 'Inquiry quotation domain ' . $template['name'] . '.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'inquiry_quotation_id');
            }

            if ($progressDepth >= 3) {
                $evatekStart = (clone $startDate)->addDays(rand(8, 18));
                $evatekStatus = $progressDepth > 3 ? null : 'evatek-desain';
                $evatekState = $progressDepth > 3 ? 'approve' : ($status === 'cancelled' ? 'not_approve' : 'on_progress');

                DB::table('evatek_items')->insert([
                    'item_id' => $itemIds[0],
                    'procurement_id' => $procurementId,
                    'vendor_id' => $selectedVendorId,
                    'pic_evatek' => $picEvatekCodes[array_rand($picEvatekCodes)],
                    'evatek_status' => $evatekStatus,
                    'start_date' => $evatekStart->format('Y-m-d'),
                    'target_date' => $evatekStart->copy()->addDays(rand(7, 14))->format('Y-m-d'),
                    'current_revision' => 'R' . rand(1, 3),
                    'status' => $evatekState,
                    'current_date' => $evatekStart->copy()->addDays(rand(2, 8))->format('Y-m-d'),
                    'sc_design_link' => 'https://design.pal.example.com/evatek/' . $code,
                    'log' => 'Evatek seeded untuk item ' . $itemIds[0],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'evatek_id');
            }

            $negotiationFinalValue = round($inquiryValueBase * 0.94, 2);
            $negotiationDate = (clone $startDate)->addDays(rand(16, 34));

            if ($progressDepth >= 4) {
                DB::table('negotiations')->insert([
                    'procurement_id' => $procurementId,
                    'vendor_id' => $selectedVendorId,
                    'hps' => round($inquiryValueBase * 1.05, 2),
                    'currency_hps' => 'IDR',
                    'budget' => round($inquiryValueBase * 1.02, 2),
                    'currency_budget' => 'IDR',
                    'harga_final' => $negotiationFinalValue,
                    'currency_harga_final' => 'IDR',
                    'tanggal_kirim' => $negotiationDate->format('Y-m-d'),
                    'tanggal_terima' => $negotiationDate->copy()->addDays(rand(6, 14))->format('Y-m-d'),
                    'lead_time' => rand(10, 21) . ' hari',
                    'link' => 'https://vendor.example.com/negotiation/' . $code,
                    'notes' => 'Negosiasi untuk ' . $template['name'] . ' pada proyek ' . $projectName,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'negotiation_id');
            }

            $usulanDate = (clone $negotiationDate)->addDays(rand(2, 6));
            if ($progressDepth >= 5) {
                DB::table('usulan_pengadaan')->insert([
                    'procurement_id' => $procurementId,
                    'vendor_id' => $selectedVendorId,
                    'currency' => 'IDR',
                    'nilai' => $negotiationFinalValue,
                    'tgl_kadep_to_kadiv' => $usulanDate->format('Y-m-d'),
                    'tgl_kadiv_to_cto' => $progressDepth > 5 ? $usulanDate->copy()->addDays(rand(1, 3))->format('Y-m-d') : null,
                    'tgl_cto_to_ceo' => $progressDepth > 6 ? $usulanDate->copy()->addDays(rand(3, 5))->format('Y-m-d') : null,
                    'tgl_acc' => $progressDepth > 6 ? $usulanDate->copy()->addDays(rand(4, 7))->format('Y-m-d') : null,
                    'remarks' => 'Usulan pengadaan untuk analitik bottleneck per tahap.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'usulan_pengadaan_id');
            }

            $pengesahanDate = (clone $usulanDate)->addDays(rand(2, 5));
            if ($progressDepth >= 6) {
                DB::table('pengesahan_kontraks')->insert([
                    'procurement_id' => $procurementId,
                    'vendor_id' => $selectedVendorId,
                    'currency' => 'IDR',
                    'nilai' => $negotiationFinalValue,
                    'tgl_kadep_to_kadiv' => $pengesahanDate->format('Y-m-d'),
                    'tgl_kadiv_to_cto' => $progressDepth > 6 ? $pengesahanDate->copy()->addDays(rand(1, 4))->format('Y-m-d') : null,
                    'tgl_cto_to_ceo' => $progressDepth > 7 ? $pengesahanDate->copy()->addDays(rand(3, 6))->format('Y-m-d') : null,
                    'tgl_acc' => $progressDepth > 7 ? $pengesahanDate->copy()->addDays(rand(4, 8))->format('Y-m-d') : null,
                    'remarks' => 'Pengesahan kontrak diisi untuk analitik.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'pengesahan_id');
            }

            $contractDate = (clone $pengesahanDate)->addDays(rand(2, 6));
            if ($progressDepth >= 7) {
                DB::table('kontraks')->insert([
                    'procurement_id' => $procurementId,
                    'no_po' => 'PO-' . $code,
                    'item_id' => $itemIds[0],
                    'vendor_id' => $selectedVendorId,
                    'tgl_kontrak' => $contractDate->format('Y-m-d'),
                    'maker' => 'PT PAL Indonesia',
                    'currency' => 'IDR',
                    'nilai' => $negotiationFinalValue,
                    'payment_term' => '30/70',
                    'incoterms' => 'CIF',
                    'coo' => 'Indonesia',
                    'warranty' => '12 bulan',
                    'delivery_time' => rand(45, 90) . ' hari',
                    'link' => 'https://contracts.pal.example.com/' . $code,
                    'remarks' => 'Kontrak disiapkan sebagai bagian dari workflow analytics.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'kontrak_id');
            }

            if ($progressDepth >= 8) {
                $paymentType = $paymentTypes[array_rand($paymentTypes)];
                $paymentValue = round($negotiationFinalValue * rand(20, 40) / 100, 2);
                $paymentTarget = (clone $contractDate)->addDays(rand(7, 18));

                DB::table('pembayarans')->insert([
                    'vendor_id' => $selectedVendorId,
                    'procurement_id' => $procurementId,
                    'payment_type' => $paymentType,
                    'percentage' => rand(20, 40),
                    'payment_value' => $paymentValue,
                    'currency' => 'IDR',
                    'no_memo' => 'Memo DP ' . $code,
                    'link' => 'https://payments.pal.example.com/' . $code,
                    'lsd' => (clone $contractDate)->addDays(rand(25, 40))->format('Y-m-d'),
                    'evidence_link' => 'https://evidence.pal.example.com/' . $code,
                    'target_date' => $paymentTarget->format('Y-m-d'),
                    'realization_date' => $status === 'completed' && $progressDepth >= 9 ? $paymentTarget->format('Y-m-d') : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($progressDepth >= 8) {
                $etd = (clone $contractDate)->addDays(rand(8, 18));
                $etaSby = (clone $etd)->addDays(rand(16, 24));
                $etaPal = (clone $etaSby)->addDays(rand(1, 4));

                DB::table('material_deliveries')->insert([
                    'procurement_id' => $procurementId,
                    'incoterms' => $incoterms[array_rand($incoterms)],
                    'imo_number' => 'IMO' . rand(1000000, 9999999),
                    'container_number' => 'CONT' . rand(1000, 9999),
                    'etd' => $etd->format('Y-m-d'),
                    'eta_sby_port' => $etaSby->format('Y-m-d'),
                    'eta_pal' => $etaPal->format('Y-m-d'),
                    'atd' => (clone $etd)->addDays(rand(1, 3))->format('Y-m-d'),
                    'ata_sby_port' => (clone $etaSby)->addDays(rand(1, 3))->format('Y-m-d'),
                    'link' => 'https://shipping.pal.example.com/' . $code,
                    'remark' => 'Material delivery seeded untuk procurement ' . $code,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], 'delivery_id');
            }

            if ($progressDepth >= 9) {
                $arrivalDate = (clone $startDate)->addDays(rand(45, 110))->format('Y-m-d');

                DB::table('items')
                    ->whereIn('item_id', $itemIds)
                    ->update([
                        'arrival_date' => $arrivalDate,
                        'updated_at' => $now,
                    ]);
            }

            $currentDate = $startDate->copy();
            foreach ($checkpoints as $index => $checkpoint) {
                if ($index + 1 > $progressDepth) {
                    break;
                }

                $stepEndDate = $currentDate->copy()->addDays(rand(1, 12));
                $isLastStep = $index + 1 === $progressDepth;
                $stepStatus = $isLastStep
                    ? ($status === 'completed' ? 'completed' : ($status === 'cancelled' ? 'cancelled' : 'in_progress'))
                    : 'completed';

                DB::table('procurement_progress')->insert([
                    'procurement_id' => $procurementId,
                    'checkpoint_id' => $checkpoint->point_id,
                    'user_id' => $userId,
                    'status' => $stepStatus,
                    'start_date' => $currentDate->format('Y-m-d H:i:s'),
                    'end_date' => $isLastStep && $stepStatus !== 'completed' ? null : $stepEndDate->format('Y-m-d H:i:s'),
                    'note' => $checkpoint->point_name . ' di-seed untuk ' . $template['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $currentDate = $stepEndDate;
            }

            $createdProcurements++;
        }

        $this->command->info("ProjectSeeder selesai: {$createdProcurements} procurements lengkap berhasil dibuat.");
    }
}
