<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// Models
use App\Models\Procurement;
use App\Models\Checkpoint;
use App\Models\InspectionReport;
use App\Models\Item;

class ListApprovalController extends Controller
{
    /**
     * Halaman List Approval — khusus pengadaan yang berada pada checkpoint "Inspeksi Barang"
     * dan belum completed.
     */
    public function index(Request $request)
    {
        // ID checkpoint Inspeksi Barang
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')
            ->value('point_id') ?? 13;

        // total checkpoint (untuk progress bar)
        $totalCheckpoints = Checkpoint::count() ?: 1;

        // Query procurement yang berada di checkpoint Inspeksi Barang dan statusnya belum completed
        $procurementsQuery = Procurement::with([
                'department',
                'procurementProgress' => function ($q) use ($inspectionCheckpointId) {
                    $q->where('checkpoint_id', $inspectionCheckpointId);
                },
                'procurementProgress.checkpoint',
                'requestProcurements.vendor',
                'items.requestProcurement.vendor',
                'items.inspectionReports',
            ])
            ->whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
                $q->where('checkpoint_id', $inspectionCheckpointId)
                  ->whereIn('status', ['not_started', 'in_progress', 'blocked']);
            })
            ->orderBy('created_at', 'desc');

        // Server-side search
        if ($q = $request->query('q')) {
            $procurementsQuery->where(function ($qq) use ($q) {
                $qq->where('code_procurement', 'like', "%{$q}%")
                   ->orWhere('name_procurement', 'like', "%{$q}%");
            });
        }

        // Pagination
        $procurements = $procurementsQuery->paginate(12)->withQueryString();

        return view('qa.list-approval', compact(
            'procurements',
            'totalCheckpoints',
            'inspectionCheckpointId'
        ));
    }


    /**
     * Save or update inspection result for ONE item (AJAX).
     *
     * Request:
     *  - item_id
     *  - procurement_id
     *  - result : "passed" | "failed"
     *  - notes (required if failed)
     */
    public function saveInspectionItem(Request $request)
    {
        $data = $request->validate([
            'item_id'        => 'required|integer|exists:items,item_id',
            'procurement_id' => 'nullable|integer',
            'project_id'     => 'nullable|integer',
            'result'         => 'required|string|in:passed,failed',
            'notes'          => 'nullable|string|max:2000',
        ]);

        // Wajib isi notes jika failed
        if ($data['result'] === 'failed' && empty(trim($data['notes'] ?? ''))) {
            return response()->json([
                'success' => false,
                'message' => 'Keterangan wajib diisi saat memilih "Tidak Lolos".'
            ], 422);
        }

        // Ambil item
        $item = Item::find($data['item_id']);

        // Tentukan project_id & procurement_id jika tidak dikirim
        $projectId = $data['project_id'] ?? ($item->requestProcurement?->project_id ?? null);
        $procurementId = $data['procurement_id'] ?? ($item->requestProcurement?->procurement_id ?? null);

        // Cek apakah sudah ada inspection report
        $existing = InspectionReport::where('item_id', $item->item_id)->first();

        $now = Carbon::now();

        if ($existing) {
            // UPDATE
            $existing->update([
                'result'          => $data['result'],
                'notes'           => $data['notes'] ?? null,
                'inspection_date' => $now->format('Y-m-d'),
                'inspector_id'    => Auth::id(),
                'updated_at'      => $now,
            ]);

            $report = $existing;
        } else {
            // CREATE
            $report = InspectionReport::create([
                'project_id'      => $projectId,
                'item_id'         => $item->item_id,
                'inspector_id'    => Auth::id(),
                'inspection_date' => $now->format('Y-m-d'),
                'result'          => $data['result'],
                'notes'           => $data['notes'] ?? null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }

        /**
         * CEK apakah semua item dalam procurement ini sudah di-inspeksi
         */
        $allInspected = false;

        if ($procurementId) {

            // Hitung total item pada procurement
            $totalItems = Item::whereHas('requestProcurement', function ($q) use ($procurementId) {
                $q->where('procurement_id', $procurementId);
            })->count();

            // Hitung berapa item yang punya inspection report
            $inspectedItems = InspectionReport::whereIn('item_id', function ($q) use ($procurementId) {
                $q->select('item_id')
                  ->from('items')
                  ->whereIn('request_procurement_id', function ($qq) use ($procurementId) {
                      $qq->select('request_id')
                         ->from('request_procurement')
                         ->where('procurement_id', $procurementId);
                  });
            })
            ->distinct('item_id')
            ->count('item_id');

            $allInspected = ($totalItems > 0 && $inspectedItems >= $totalItems);
        }

        return response()->json([
            'success'       => true,
            'message'       => 'Hasil inspeksi berhasil disimpan.',
            'report'        => $report,
            'all_inspected' => $allInspected,
        ]);
    }
}
