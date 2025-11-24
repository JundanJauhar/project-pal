<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Procurement;
use App\Models\Item;
use App\Models\InspectionReport;

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
                // sisipkan vendor ke masing-masing item untuk dipakai di view (kalau perlu)
                return $req->items->map(function ($item) use ($req) {
                    $item->vendor = $req->vendor ?? null;
                    return $item;
                });
            });

        // Tidak perlu hitung kartu KPI di sini; kartu sudah dihandle di halaman Department/Inspeksi.
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

        $now   = Carbon::now();
        $saved = [];
        $itemIds = [];

        foreach ($data['items'] as $it) {
            $item = Item::find($it['item_id']);
            if (!$item) {
                continue;
            }

            // notes wajib untuk result failed
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

        // Hitung ulang info dasar untuk response (kalau mau dipakai di JS)
        $totalItems = Item::whereHas('requestProcurement', function ($q) use ($procurement_id) {
            $q->where('procurement_id', $procurement_id);
        })->count();

        $inspectedItems = InspectionReport::whereIn('item_id', function ($q) use ($procurement_id) {
            $q->select('item_id')->from('items')
                ->whereIn('request_procurement_id', function ($qq) use ($procurement_id) {
                    $qq->select('request_id')
                        ->from('request_procurement')
                        ->where('procurement_id', $procurement_id);
                });
        })->distinct('item_id')->count('item_id');

        $all_inspected = ($totalItems > 0 && $inspectedItems >= $totalItems);

        // global counters untuk kartu (berdasarkan jumlah item yang lolos/gagal)
        $lolosCount = InspectionReport::where('result', 'passed')
            ->distinct('item_id')->count('item_id');
        $gagalCount = InspectionReport::where('result', 'failed')
            ->distinct('item_id')->count('item_id');

        // hitung butuh & sedang_proses per pengadaan (opsional, jika masih dipakai di JS)
        $allProcs = Procurement::with(['requestProcurements.items.inspectionReports'])->get();
        $butuh = 0;
        $sedang_proses = 0;

        foreach ($allProcs as $proc) {
            $items = $proc->requestProcurements->flatMap->items;
            $totalItemsProc = $items->count();

            if ($totalItemsProc === 0) {
                $butuh++;
                continue;
            }

            $latestResults = $items->map(function ($it) {
                $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
                return $latest?->result ?? null;
            });

            $inspectedCountProc = $latestResults->filter(fn ($r) => !is_null($r))->count();

            if ($inspectedCountProc === 0) {
                $butuh++;
                continue;
            }

            if ($inspectedCountProc < $totalItemsProc) {
                $sedang_proses++;
                continue;
            }

            if ($latestResults->every(fn ($r) => $r === 'passed')) {
                // sudah dihitung di $lolosCount (per item)
            } elseif ($latestResults->every(fn ($r) => $r === 'failed')) {
                // sudah dihitung di $gagalCount
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
