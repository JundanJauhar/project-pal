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

class DetailApprovalController extends Controller
{
    /**
     * Tampilkan halaman detail inspeksi untuk satu pengadaan.
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

        return view('qa.detail-approval', [
            'procurement' => $procurement,
            'items'       => $items,
        ]);
    }

    /**
     * Simpan hasil inspeksi untuk beberapa item dalam satu pengadaan (AJAX).
     */
    public function saveAll(Request $request, $procurement_id)
    {
        $data = $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,item_id',
            'items.*.result'  => 'required|string|in:passed,failed',
            'items.*.notes'   => 'nullable|string|max:2000',
        ]);

        $now     = Carbon::now();
        $saved   = [];
        $itemIds = [];

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

            $existing = InspectionReport::where('item_id', $item->item_id)->first();

            if ($existing) {
                $existing->update([
                    'result'          => $it['result'],
                    'notes'           => $it['notes'] ?? null,
                    'inspection_date' => $now->format('Y-m-d'),
                    'inspector_id'    => Auth::id(),
                    'updated_at'      => $now,
                ]);
                $saved[] = $existing;
            } else {
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

        // ==== Hitung ulang informasi dasar untuk procurement ini ====
        $totalItems = Item::whereHas('requestProcurement', function ($q) use ($procurement_id) {
            $q->where('procurement_id', $procurement_id);
        })->count();

        $itemsWithReports = Item::whereHas('requestProcurement', function ($q) use ($procurement_id) {
                $q->where('procurement_id', $procurement_id);
            })
            ->with('inspectionReports')
            ->get();

        $latestResults = $itemsWithReports->map(function ($it) {
            $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
            return $latest?->result ?? null;
        });

        $inspectedItems = $latestResults->filter(fn ($r) => !is_null($r))->count();
        $all_inspected  = ($totalItems > 0 && $inspectedItems >= $totalItems);

        // ===== Klasifikasi status inspeksi untuk procurement ini =====
        //  - butuh  : belum ada yang diinspeksi
        //  - sedang : sebagian / hasil campuran
        //  - lolos  : semua item LOLOS
        //  - gagal  : semua item TIDAK LOLOS
        $statusProc = 'butuh';

        if ($totalItems === 0) {
            $statusProc = 'butuh';
        } elseif ($inspectedItems === 0) {
            $statusProc = 'butuh';
        } elseif ($inspectedItems < $totalItems) {
            $statusProc = 'sedang';
        } else {
            $allPassed = $latestResults->every(fn ($r) => $r === 'passed');
            $allFailed = $latestResults->every(fn ($r) => $r === 'failed');

            if ($allPassed) {
                $statusProc = 'lolos';
            } elseif ($allFailed) {
                $statusProc = 'gagal';
            } else {
                $statusProc = 'sedang';
            }
        }

        // ==== UPDATE PROCUREMENT_PROGRESS BERDASARKAN STATUS ====
        // Checkpoint:
        // 11 = Inspeksi Barang
        // 12 = Berita Acara / NCR
        // 13 = Verifikasi Dokumen
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')->value('point_id');
        $ncrCheckpointId        = Checkpoint::where('point_name', 'Berita Acara / NCR')->value('point_id');
        $verifDocCheckpointId   = Checkpoint::where('point_name', 'Verifikasi Dokumen')->value('point_id');

        // Helper untuk set / buat progress
        $setProgress = function (?int $checkpointId, string $status) use ($procurement_id, $now) {
            if (!$checkpointId) {
                return;
            }

            $progress = ProcurementProgress::firstOrCreate([
                'procurement_id' => $procurement_id,
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

        // Untuk meminimalkan "bingung" status, kita atur hanya 1 checkpoint
        // yang in_progress di antara 11–13 sesuai statusProc.
        if ($statusProc === 'lolos') {
            // 11 completed, 12 bukan in_progress, 13 in_progress
            if ($inspectionCheckpointId) {
                $setProgress($inspectionCheckpointId, 'completed');
            }
            if ($ncrCheckpointId) {
                // kalau sudah sempat in_progress, kembalikan ke not_started
                $ncr = ProcurementProgress::where('procurement_id', $procurement_id)
                    ->where('checkpoint_id', $ncrCheckpointId)
                    ->first();
                if ($ncr) {
                    $ncr->status = 'not_started';
                    $ncr->save();
                }
            }
            if ($verifDocCheckpointId) {
                $setProgress($verifDocCheckpointId, 'in_progress');
            }
        } elseif ($statusProc === 'gagal') {
            // 11 completed, 12 in_progress, 13 bukan in_progress
            if ($inspectionCheckpointId) {
                $setProgress($inspectionCheckpointId, 'completed');
            }
            if ($ncrCheckpointId) {
                $setProgress($ncrCheckpointId, 'in_progress');
            }
            if ($verifDocCheckpointId) {
                $verif = ProcurementProgress::where('procurement_id', $procurement_id)
                    ->where('checkpoint_id', $verifDocCheckpointId)
                    ->first();
                if ($verif && $verif->status === 'in_progress') {
                    $verif->status = 'not_started';
                    $verif->save();
                }
            }
        } elseif ($statusProc === 'sedang') {
            // Masih di Inspeksi Barang
            if ($inspectionCheckpointId) {
                $setProgress($inspectionCheckpointId, 'in_progress');
            }
            // 12 & 13 dibiarkan apa adanya (belum jalan)
        }
        // status "butuh" tidak di-handle di sini karena saveAll hanya dipanggil
        // ketika minimal 1 item sudah dipilih hasil inspeksinya.

        // ==== Hitung global counters untuk kartu (berdasarkan jumlah item) ====
        $lolosCount = InspectionReport::where('result', 'passed')
            ->distinct('item_id')->count('item_id');
        $gagalCount = InspectionReport::where('result', 'failed')
            ->distinct('item_id')->count('item_id');

        // hitung butuh & sedang_proses per pengadaan (opsional, untuk update kartu lama)
        $allProcs      = Procurement::with(['requestProcurements.items.inspectionReports'])->get();
        $butuh         = 0;
        $sedang_proses = 0;

        foreach ($allProcs as $proc) {
            $itemsProc       = $proc->requestProcurements->flatMap->items;
            $totalItemsProc  = $itemsProc->count();

            if ($totalItemsProc === 0) {
                $butuh++;
                continue;
            }

            $latestPerItem = $itemsProc->map(function ($it) {
                $latestReport = $it->inspectionReports->sortByDesc('inspection_date')->first();
                return $latestReport?->result ?? null;
            });

            $inspectedCountProc = $latestPerItem->filter(fn ($r) => !is_null($r))->count();

            if ($inspectedCountProc === 0) {
                $butuh++;
                continue;
            }

            if ($inspectedCountProc < $totalItemsProc) {
                $sedang_proses++;
                continue;
            }

            if ($latestPerItem->every(fn ($r) => $r === 'passed')) {
                // counted di $lolosCount (per item)
            } elseif ($latestPerItem->every(fn ($r) => $r === 'failed')) {
                // counted di $gagalCount
            } else {
                $sedang_proses++;
            }
        }

        return response()->json([
            'success'         => true,
            'message'         => 'Semua hasil inspeksi berhasil disimpan.',
            'saved_count'     => count($saved),
            'all_inspected'   => $all_inspected,
            'inspected_items' => $inspectedItems,
            'total_items'     => $totalItems,
            'lolos_count'     => $lolosCount,
            'gagal_count'     => $gagalCount,
            'butuh'           => $butuh,
            'sedang_proses'   => $sedang_proses,
        ]);
    }
}
