<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\NcrReport;
use App\Models\Project;
use App\Models\Notification;
use App\Models\Procurement;
use App\Models\Checkpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InspectionController extends Controller
{
    /**
     * Display inspection reports (LIST) or procurements for QA
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cari checkpoint "Inspeksi Barang"
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')->value('point_id') ?? 13;

        // =========================
        //  UNTUK USER ROLE QA
        // =========================
        if ($user->roles === 'qa') {

            // Query procurement yang butuh inspeksi
            $baseQuery = Procurement::with(['department', 'requestProcurements.vendor'])
                ->whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
                    $q->where('checkpoint_id', $inspectionCheckpointId)
                      ->whereIn('status', ['not_started', 'in_progress', 'blocked']);
                })
                ->orderBy('created_at', 'desc');

            // Pagination untuk tabel
            $procurements = (clone $baseQuery)->paginate(20);

            // KPI: jumlah procurement yang butuh inspeksi
            $butuhInspeksiCount = (clone $baseQuery)->count();

            // Total pengadaan secara global
            $totalProcurements = Procurement::count();

            // ❗ TAMBAHAN BARU — hitung hasil inspeksi seluruh item
            $lolosCount = InspectionReport::where('result', 'passed')
                ->distinct('item_id')->count('item_id');

            $gagalCount = InspectionReport::where('result', 'failed')
                ->distinct('item_id')->count('item_id');

            return view('qa.inspections', compact(
                'procurements',
                'butuhInspeksiCount',
                'totalProcurements',
                'lolosCount',
                'gagalCount'
            ));
        }

        // =========================
        //  UNTUK ROLE SELAIN QA
        // =========================
        $inspections = InspectionReport::with([
            'project',
            'project.department',
            'item',
        ])
        ->orderBy('inspection_date', 'desc')
        ->paginate(20);

        $totalInspections = $inspections->total();
        $butuhInspeksiCount = InspectionReport::where('result', 'pending')->count();
        $lolosCount = InspectionReport::where('result', 'passed')->count();
        $gagalCount = InspectionReport::where('result', 'failed')->count();

        return view('qa.inspections', compact(
            'inspections',
            'butuhInspeksiCount',
            'totalInspections',
            'lolosCount',
            'gagalCount'
        ));
    }

    /**
     * DISABLED — show detail
     */
    public function show($id)
    {
        abort(404);
    }
}
