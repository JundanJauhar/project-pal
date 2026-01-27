<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Procurement;
use App\Models\Item;
use App\Models\InspectionReport;
use App\Models\Checkpoint;
use App\Models\ProcurementProgress;
use App\Helpers\ActivityLogger;


class DetailApprovalController extends Controller
{
    /**
     * Tampilkan halaman detail inspeksi untuk satu pengadaan.
     * 
     * Endpoint ini untuk QA Inspector membaca detail procurement + items
     * yang akan diinspeksi di checkpoint "Kedatangan Material"
     */
    public function show(Request $request, $procurement_id)
    {
        // Ambil pengadaan + relasi yang dibutuhkan untuk halaman detail
        $procurement = Procurement::with([
            'project',
            'department',
            'requestProcurements.vendor',
            'requestProcurements.items.inspectionReports',
        ])->findOrFail($procurement_id);

        // Flatten semua item dari setiap request pengadaan
        $items = $procurement->requestProcurements
            ->flatMap(function ($req) {
                return $req->items->map(function ($item) use ($req) {
                    // sisipkan vendor ke masing-masing item untuk dipakai di view (kalau perlu)
                    $item->vendor = $req->vendor ?? null;
                    return $item;
                });
            });

        ActivityLogger::log(
            module: 'QA',
            action: 'view_procurement_inspection_detail',
            targetId: $procurement_id,
            details: [
                'user_id' => Auth::id(),
                'item_count' => $items->count()
            ]
        );

        return view('qa.detail-approval', [
            'procurement' => $procurement,
            'items'       => $items,
        ]);
    }

    /**
     * Simpan hasil inspeksi untuk beberapa item dalam satu pengadaan (AJAX).
     * 
     * FLOW UTAMA:
     * 1. Validasi & simpan inspection reports untuk setiap item
     * 2. Hitung status procurement berdasarkan hasil inspeksi terbaru
     * 3. Jika LOLOS:
     *    - Set "Kedatangan Material" → completed
     *    - Set "Inventory" → completed
     *    - Update procurement.status_procurement = completed
     * 4. Jika GAGAL atau SEDANG:
     *    - Tetap di "Kedatangan Material" (in_progress)
     *    - procurement.status_procurement tetap != completed
     * 
     * CATATAN: Tidak ada checkpoint "Inspeksi Barang", "Berita Acara", "Verifikasi Dokumen"
     */
    public function saveAll(Request $request, $procurement_id)
    {
        $data = $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,item_id',
            'items.*.result'  => 'required|string|in:passed,failed',
            'items.*.notes'   => 'nullable|string|max:2000',
        ]);

        $now = Carbon::now();
        $saved = [];
        $itemIds = [];

        // ========== STEP 1: SIMPAN INSPECTION REPORTS ==========
        foreach ($data['items'] as $it) {
            $item = Item::find($it['item_id']);
            if (!$item) {
                continue;
            }

            // Notes wajib jika result = failed
            if ($it['result'] === 'failed' && empty(trim($it['notes'] ?? ''))) {
                return response()->json([
                    'success' => false,
                    'message' => "Keterangan wajib diisi untuk item id {$item->item_id} yang tidak lolos.",
                ], 422);
            }

            // Cek apakah sudah ada inspection report untuk item ini
            $existing = InspectionReport::where('item_id', $item->item_id)->first();

            if ($existing) {
                // Update existing report
                $existing->update([
                    'result'          => $it['result'],
                    'notes'           => $it['notes'] ?? null,
                    'inspection_date' => $now->format('Y-m-d'),
                    'inspector_id'    => Auth::id(),
                    'updated_at'      => $now,
                ]);
                $saved[] = $existing;
            } else {
                // Create new report
                $report = InspectionReport::create([
                    'project_id'      => $item->requestProcurement?->project_id ?? null,
                    'item_id'         => $item->item_id,
                    'inspector_id'    => Auth::id(),
                    'inspection_date' => $now->format('Y-m-d'),
                    'result'          => $it['result'],
                    'notes'           => $it['notes'] ?? null,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
                $saved[] = $report;
            }

            $itemIds[] = $item->item_id;
        }

        // ========== STEP 2: HITUNG STATUS PROCUREMENT ==========
        $totalItems = Item::whereHas('requestProcurement', function ($q) use ($procurement_id) {
            $q->where('procurement_id', $procurement_id);
        })->count();

        $itemsWithReports = Item::whereHas('requestProcurement', function ($q) use ($procurement_id) {
            $q->where('procurement_id', $procurement_id);
        })
            ->with('inspectionReports')
            ->get();

        // Ambil hasil inspeksi terbaru untuk setiap item
        $latestResults = $itemsWithReports->map(function ($it) {
            $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
            return $latest?->result ?? null;
        });

        $inspectedItems = $latestResults->filter(fn($r) => !is_null($r))->count();

        // Klasifikasi status procurement
        $procStatus = $this->classifyProcurementStatus($totalItems, $inspectedItems, $latestResults);

        // ========== STEP 3: UPDATE PROCUREMENT PROGRESS & STATUS ==========
        $this->updateProcurementProgress($procurement_id, $procStatus, $now);

        // ========== STEP 4: HITUNG GLOBAL STATS (untuk response) ==========
        $stats = $this->calculateGlobalStats();

        // ========== ACTIVITY LOGGING ==========
        ActivityLogger::log(
            module: 'QA',
            action: 'submit_inspection_results',
            targetId: $procurement_id,
            details: [
                'user_id'        => Auth::id(),
                'saved_items'    => $itemIds,
                'saved_count'    => count($saved),
                'procurement_status' => $procStatus,
                'all_inspected'  => $inspectedItems >= $totalItems,
            ]
        );

        return response()->json([
            'success'         => true,
            'message'         => 'Semua hasil inspeksi berhasil disimpan.',
            'saved_count'     => count($saved),
            'all_inspected'   => $inspectedItems >= $totalItems,
            'inspected_items' => $inspectedItems,
            'total_items'     => $totalItems,
            'procurement_status' => $procStatus,
            'stats'           => $stats,
        ]);
    }

    /**
     * Klasifikasi status procurement berdasarkan hasil inspeksi item
     * 
     * Hasil:
     *  - 'butuh'  : belum ada item yang diinspeksi
     *  - 'sedang' : sebagian sudah diinspeksi / hasil campuran
     *  - 'lolos'  : semua item LOLOS
     *  - 'gagal'  : semua item TIDAK LOLOS
     */
    private function classifyProcurementStatus(int $totalItems, int $inspectedItems, $latestResults): string
    {
        // Tidak ada item sama sekali atau belum ada yang diinspeksi
        if ($totalItems === 0 || $inspectedItems === 0) {
            return 'butuh';
        }

        // Sebagian sudah diinspeksi, sebagian belum
        if ($inspectedItems < $totalItems) {
            return 'sedang';
        }

        // Semua item sudah diinspeksi → cek komposisi hasilnya
        $allPassed = $latestResults->every(fn($r) => $r === 'passed');
        $allFailed = $latestResults->every(fn($r) => $r === 'failed');

        if ($allPassed) {
            return 'lolos';
        }

        if ($allFailed) {
            return 'gagal';
        }

        // Campuran passed/failed
        return 'sedang';
    }

    /**
     * Update ProcurementProgress sesuai dengan hasil inspeksi
     * 
     * LOGIC:
     * - Jika lolos: "Kedatangan Material" → completed, "Inventory" → completed
     * - Jika tidak lolos: "Kedatangan Material" → in_progress
     * 
     * UPDATE: procurement.status_procurement = completed hanya jika lolos
     */
    private function updateProcurementProgress(string $procurementId, string $procStatus, Carbon $now): void
    {
        // Get checkpoint IDs
        $kedatanganCheckpointId = Checkpoint::where('point_name', 'Kedatangan Material')->value('point_id');
        $inventoryCheckpointId = Checkpoint::where('point_name', 'Inventory')->value('point_id');

        // Helper untuk set progress
        $setProgress = function(?int $checkpointId, string $status) use ($procurementId, $now) {
            if (!$checkpointId) {
                return;
            }

            $progress = ProcurementProgress::firstOrCreate([
                'procurement_id' => $procurementId,
                'checkpoint_id'  => $checkpointId,
            ]);

            $progress->status = $status;

            if ($status === 'in_progress' && !$progress->start_date) {
                $progress->start_date = $now->toDateString();
            }

            if ($status === 'completed' && !$progress->end_date) {
                $progress->end_date = $now->toDateString();
            }

            $progress->save();
        };

        if ($procStatus === 'lolos') {
            // Semua lolos → langsung ke Inventory dan selesai
            $setProgress($kedatanganCheckpointId, 'completed');
            $setProgress($inventoryCheckpointId, 'completed');

            // Update procurement status
            $procurement = Procurement::findOrFail($procurementId);
            $procurement->status_procurement = 'completed';
            $procurement->save();
        } else {
            // Tidak lolos (gagal atau sedang) → tetap di Kedatangan Material
            $setProgress($kedatanganCheckpointId, 'in_progress');

            // Jangan set Inventory - biarkan not_started
            // Jangan ubah procurement status jika belum lolos
        }
    }

    /**
     * Hitung global statistics untuk semua procurement (untuk card update di dashboard)
     * 
     * Menghitung:
     * - Belum diinspeksi (butuh)
     * - Sedang diinspeksi
     * - Lolos
     * - Gagal
     */
    private function calculateGlobalStats(): array
    {
        $allProcs = Procurement::with(['requestProcurements.items.inspectionReports'])->get();

        $butuh = 0;
        $sedang = 0;
        $lolos = 0;
        $gagal = 0;

        foreach ($allProcs as $proc) {
            $items = $proc->requestProcurements->flatMap->items;
            $totalItems = $items->count();

            if ($totalItems === 0) {
                $butuh++;
                continue;
            }

            $latestResults = $items->map(function ($it) {
                $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
                return $latest?->result ?? null;
            });

            $inspectedCount = $latestResults->filter(fn($r) => !is_null($r))->count();

            if ($inspectedCount === 0) {
                $butuh++;
                continue;
            }

            if ($inspectedCount < $totalItems) {
                $sedang++;
                continue;
            }

            // Semua inspected
            $allPassed = $latestResults->every(fn($r) => $r === 'passed');
            $allFailed = $latestResults->every(fn($r) => $r === 'failed');

            if ($allPassed) {
                $lolos++;
            } elseif ($allFailed) {
                $gagal++;
            } else {
                $sedang++;
            }
        }

        return [
            'total' => $allProcs->count(),
            'butuh' => $butuh,
            'sedang' => $sedang,
            'lolos' => $lolos,
            'gagal' => $gagal,
        ];
    }
}