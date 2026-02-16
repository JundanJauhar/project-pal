<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EvatekSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Membuat data Evatek (with dummy data if needed)...');

        // Check if we need to create dummy data
        $procurementCount = DB::table('procurement')->count();
        $itemCount = DB::table('items')->count();
        $vendorCount = DB::table('vendors')->count();

        $this->command->info("📊 Existing data: Proc={$procurementCount}, Items={$itemCount}, Vendors={$vendorCount}");

        // Create dummy vendors if none exist
        if ($vendorCount === 0) {
            $this->command->info('Creating dummy vendors...');
            for ($i = 1; $i <= 10; $i++) {
                DB::table('vendors')->insert([
                    'vendor_name' => "Vendor Company " . $i,
                    'vendor_address' => "Jl. Industri No. {$i}, Surabaya",
                    'vendor_phone' => "031-" . rand(1000000, 9999999),
                    'vendor_email' => "vendor{$i}@example.com",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info('✅ Created 10 dummy vendors');
        }

        // Create dummy procurement if none exist
        if ($procurementCount === 0) {
            $this->command->info('Creating dummy procurement...');
            for ($i = 1; $i <= 5; $i++) {
                $procId = DB::table('procurement')->insertGetId([
                    'procurement_no' => 'PROC-2026-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'procurement_date' => Carbon::now()->subDays(rand(30, 90)),
                    'status' => ['pending', 'approved', 'in_progress'][array_rand(['pending', 'approved', 'in_progress'])],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Also create request_procurement entry
                DB::table('request_procurement')->insert([
                    'request_id' => $procId,
                    'request_date' => Carbon::now()->subDays(rand(30, 90)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info('✅ Created 5 dummy procurements');
        }

        // Create dummy items if none exist
        if ($itemCount === 0) {
            $this->command->info('Creating dummy items...');
            $procurements = DB::table('request_procurement')->pluck('request_id');

            $itemNames = [
                'Profil Baja IPN 400',
                'Plat Baja Marine Grade A131',
                'Steel Pipe Schedule 40',
                'Welding Rod E7018',
                'Marine Paint Epoxy',
                'Stainless Steel Sheet 304',
                'Anchor Chain Grade 2',
                'Hydraulic Cylinder',
                'Electric Motor 50HP',
                'Ball Valve DN100',
                'Rubber Fender Type D',
                'Wire Rope 24mm',
                'Gear Box Ratio 1:30',
                'Cooling Pump Centrifugal',
                'Navigation Light LED'
            ];

            foreach ($procurements as $procId) {
                $itemsPerProc = rand(3, 8);
                for ($j = 0; $j < $itemsPerProc; $j++) {
                    DB::table('items')->insert([
                        'request_procurement_id' => $procId,
                        'item_name' => $itemNames[array_rand($itemNames)],
                        'item_description' => 'Specification for marine industry standards',
                        'specification' => 'As per ASTM/JIS standards',
                        'amount' => rand(10, 500),
                        'unit' => ['pcs', 'kg', 'meter', 'set'][array_rand(['pcs', 'kg', 'meter', 'set'])],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $this->command->info('✅ Created dummy items');
        }

        // Now create Evatek data
        $items = DB::table('items')->get();
        $vendors = DB::table('vendors')->get();

        $this->command->info("🔨 Creating Evatek for {$items->count()} items...");

        $picOptions = ['EO', 'HC', 'MO', 'HO', 'SEWACO'];
        $statusOptions = ['approve', 'not_approve', 'on_progress'];

        $created = 0;
        $revisions = 0;

        foreach ($items as $item) {
            $procurement = DB::table('procurement')
                ->where('procurement_id', $item->request_procurement_id)
                ->first();

            if (!$procurement) continue;

            // Create evatek untuk 80% items
            if (rand(1, 100) > 80) continue;

            $vendor = $vendors->random();

            // Create 1-2 evatek per item
            $numberOfEvateks = rand(1, 2);

            for ($e = 0; $e < $numberOfEvateks; $e++) {
                $startDate = Carbon::now()->subDays(rand(30, 180));
                $targetDate = (clone $startDate)->addDays(rand(14, 60));
                $currentDate = (clone $startDate)->addDays(rand(0, 30));

                $status = $statusOptions[array_rand($statusOptions)];
                $revNum = rand(0, 5);
                $currentRevision = 'R' . $revNum;

                $evatekStatus = null;
                if ($status === 'on_progress') {
                    $rand = rand(1, 3);
                    $evatekStatus = $rand === 1 ? 'evatek-vendor' : ($rand === 2 ? 'evatek-desain' : null);
                }

                try {
                    $evatek = EvatekItem::create([
                        'item_id' => $item->item_id,
                        'procurement_id' => $procurement->procurement_id,
                        'vendor_id' => $vendor->id_vendor,
                        'pic_evatek' => $picOptions[array_rand($picOptions)],
                        'evatek_status' => $evatekStatus,
                        'start_date' => $startDate,
                        'target_date' => $targetDate,
                        'current_revision' => $currentRevision,
                        'status' => $status,
                        'current_date' => $currentDate,
                        'sc_design_link' => $this->randomLink(),
                        'log' => "Evatek {$currentRevision} - " . $this->getRandomNote(),
                    ]);

                    $created++;

                    // Create revisions
                    for ($i = 0; $i <= $revNum; $i++) {
                        $revCode = 'R' . $i;
                        $revDate = (clone $startDate)->addDays($i * 7);

                        $revStatus = ($i < $revNum)
                            ? (rand(0, 1) ? 'not_approve' : 'approve')
                            : $status;

                        $vendorLink = null;
                        $designLink = null;

                        if ($evatekStatus === null) {
                            $vendorLink = $this->randomLink();
                            $designLink = $this->randomLink();
                        } elseif ($evatekStatus === 'evatek-desain') {
                            $vendorLink = $this->randomLink();
                        }

                        EvatekRevision::create([
                            'evatek_id' => $evatek->evatek_id,
                            'revision_code' => $revCode,
                            'vendor_link' => $vendorLink,
                            'design_link' => $designLink,
                            'status' => $revStatus,
                            'date' => $revDate,
                            'approved_at' => $revStatus === 'approve' ? $revDate : null,
                            'not_approved_at' => $revStatus === 'not_approve' ? $revDate : null,
                            'log' => $this->getRevisionLog($revCode, $revStatus),
                        ]);

                        $revisions++;
                    }
                } catch (\Exception $e) {
                    $this->command->warn("⚠️  Skip: " . $e->getMessage());
                }
            }
        }

        $this->command->newLine();
        $this->command->info("✅ Berhasil membuat {$created} Evatek Items");
        $this->command->info("✅ Berhasil membuat {$revisions} Evatek Revisions");
        $this->command->info('🎉 Seeder selesai!');
    }

    private function randomLink(): string
    {
        $domains = [
            'drive.google.com/file/d/',
            'dropbox.com/s/',
            'onedrive.live.com/redir?resid=',
        ];
        return 'https://' . $domains[array_rand($domains)] . bin2hex(random_bytes(15));
    }

    private function getRandomNote(): string
    {
        $notes = [
            'Steel plate specification',
            'Material quality verification',
            'Dimensional accuracy',
            'Surface finish inspection',
            'Welding procedure',
            'Technical drawing',
            'Material certification',
            'Quality standards',
            'Manufacturing process',
            'Engineering design'
        ];
        return $notes[array_rand($notes)];
    }

    private function getRevisionLog(string $code, string $status): string
    {
        if ($status === 'approve') {
            $logs = [
                "{$code} - Approved by design team",
                "{$code} - Quality check passed",
                "{$code} - Technical review complete",
                "{$code} - Material specs verified",
                "{$code} - Design validated"
            ];
        } elseif ($status === 'not_approve') {
            $logs = [
                "{$code} - Revision needed: dimension mismatch",
                "{$code} - Not approved: material grade incorrect",
                "{$code} - Requires correction: welding specs",
                "{$code} - Design adjustment needed",
                "{$code} - Technical specs need revision"
            ];
        } else {
            $logs = [
                "{$code} - Under review",
                "{$code} - Technical evaluation in progress",
                "{$code} - Awaiting verification",
                "{$code} - Quality assessment ongoing",
                "{$code} - Engineering review"
            ];
        }
        return $logs[array_rand($logs)];
    }
}
