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
    /**
     * Show detail approval page for a procurement (cards per item).
     */
    public function show(Request $request, $procurement_id)
    {
        // load procurement + items + vendor + inspection reports
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
                // attach vendor & request for easier access in view
                $it->vendor = $req->vendor ?? null;
                $items->push($it);
            }
        }

        // KPI counts for cards in inspections.blade.php
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')->value('point_id') ?? 13;
        $totalProcurements = Procurement::count();
        $butuhInspeksiCount = Procurement::whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
            $q->where('checkpoint_id', $inspectionCheckpointId)
              ->whereIn('status', ['not_started', 'in_progress', 'blocked']);
        })->count();

        // global inspection counts (can be used to update top-cards)
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

    /**
     * Save results for ALL items for this procurement (AJAX).
     * Expects payload:
     *  - items: [ { item_id, result: 'passed'|'failed', notes: string|null }, ... ]
     */
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

            // notes required if failed
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

        return response()->json([
            'success' => true,
            'message' => 'Semua hasil inspeksi berhasil disimpan.',
            'saved_count' => count($saved),
            'all_inspected' => $all_inspected,
            'inspected_items' => $inspectedItems,
            'total_items' => $totalItems,
            'lolos_count' => $lolosCount,
            'gagal_count' => $gagalCount,
        ]);
    }
}
