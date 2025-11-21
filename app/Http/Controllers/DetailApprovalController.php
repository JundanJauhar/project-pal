<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Procurement;
use App\Models\Item;
use App\Models\InspectionReport;
use App\Models\Checkpoint;

class DetailApprovalController extends Controller
{
    public function show(Request $request, $procurement_id)
    {
        $procurement = Procurement::with([
            'requestProcurements.vendor',
            'requestProcurements.items' => function ($q) {
                $q->with('inspectionReports');
            }
        ])->findOrFail($procurement_id);

        // flatten items (through requestProcurements)
        $items = collect();
        foreach ($procurement->requestProcurements as $req) {
            foreach ($req->items as $it) {
                $it->vendor = $req->vendor ?? null;
                $items->push($it);
            }
        }

        // KPI counts for cards in inspections.blade.php (global)
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')->value('point_id') ?? 13;
        $totalProcurements = Procurement::count();
        $butuhInspeksiCount = Procurement::whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
            $q->where('checkpoint_id', $inspectionCheckpointId)
              ->whereIn('status', ['not_started', 'in_progress', 'blocked']);
        })->count();

        $lolosCount = InspectionReport::where('result', 'passed')->distinct('item_id')->count('item_id');
        $gagalCount = InspectionReport::where('result', 'failed')->distinct('item_id')->count('item_id');

        return view('qa.detail-approval', compact(
            'procurement',
            'items',
            'totalProcurements',
            'butuhInspeksiCount',
            'lolosCount',
            'gagalCount'
        ));
    }

    public function saveAll(Request $request, $procurement_id)
    {
        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,item_id',
            'items.*.result'  => 'required|string|in:passed,failed',
            'items.*.notes'   => 'nullable|string|max:2000',
        ]);

        $now = Carbon::now();
        $saved = [];
        $itemIds = [];

        foreach ($data['items'] as $it) {
            $item = Item::find($it['item_id']);
            if (!$item) continue;

            if ($it['result'] === 'failed' && empty(trim($it['notes'] ?? ''))) {
                return response()->json([
                    'success' => false,
                    'message' => "Keterangan wajib diisi untuk item id {$item->item_id} yang tidak lolos."
                ], 422);
            }

            $existing = InspectionReport::where('item_id', $item->item_id)->first();

            if ($existing) {
                $existing->update([
                    'result' => $it['result'],
                    'notes' => $it['notes'] ?? null,
                    'inspection_date' => $now->format('Y-m-d'),
                    'inspector_id' => Auth::id(),
                    'updated_at' => $now,
                ]);
                $saved[] = $existing;
            } else {
                $report = InspectionReport::create([
                    'project_id' => $item->requestProcurement?->project_id ?? null,
                    'item_id' => $item->item_id,
                    'inspector_id' => Auth::id(),
                    'inspection_date' => $now->format('Y-m-d'),
                    'result' => $it['result'],
                    'notes' => $it['notes'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $saved[] = $report;
            }

            $itemIds[] = $item->item_id;
        }

        // recompute per-procurement inspection status
        $totalItems = Item::whereHas('requestProcurement', function ($q) use ($procurement_id) {
            $q->where('procurement_id', $procurement_id);
        })->count();

        $inspectedItems = InspectionReport::whereIn('item_id', function ($q) use ($procurement_id) {
            $q->select('item_id')->from('items')
              ->whereIn('request_procurement_id', function ($qq) use ($procurement_id) {
                  $qq->select('request_id')->from('request_procurement')->where('procurement_id', $procurement_id);
              });
        })->distinct('item_id')->count('item_id');

        $all_inspected = ($totalItems > 0 && $inspectedItems >= $totalItems);

        // global counters (to update top-cards)
        $lolosCount = InspectionReport::where('result', 'passed')->distinct('item_id')->count('item_id');
        $gagalCount = InspectionReport::where('result', 'failed')->distinct('item_id')->count('item_id');

        // recompute sedang_proses and butuh
        // butuh = number of procurements that have 0 inspected items
        $allProcs = Procurement::with(['requestProcurements.items.inspectionReports'])->get();
        $butuh = 0;
        $sedang_proses = 0;
        foreach ($allProcs as $proc) {
            $items = $proc->requestProcurements->flatMap->items;
            $totalItemsProc = $items->count();
            if ($totalItemsProc === 0) { $butuh++; continue; }
            $latestResults = $items->map(function ($it) {
                $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
                return $latest?->result ?? null;
            });
            $inspectedCountProc = $latestResults->filter(fn($r) => !is_null($r))->count();
            if ($inspectedCountProc === 0) { $butuh++; continue; }
            if ($inspectedCountProc < $totalItemsProc) { $sedang_proses++; continue; }
            if ($latestResults->every(fn($r) => $r === 'passed')) { /* counted in lolosCount separately */ }
            elseif ($latestResults->every(fn($r) => $r === 'failed')) { /* counted in gagalCount */ }
            else { $sedang_proses++; }
        }

        // return JSON including top-cards numbers
        return response()->json([
            'success' => true,
            'message' => 'Semua hasil inspeksi berhasil disimpan.',
            'saved_count' => count($saved),
            'all_inspected' => $all_inspected,
            'inspected_items' => $inspectedItems,
            'total_items' => $totalItems,
            'lolos_count' => $lolosCount,
            'gagal_count' => $gagalCount,
            'butuh' => $butuh,
            'sedang_proses' => $sedang_proses,
        ]);
    }
}
