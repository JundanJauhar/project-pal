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
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    /**
     * Display inspection reports (LIST) or procurements for QA
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cari checkpoint "Inspeksi Barang" (fallback ke id 13 bila tidak ditemukan)
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')->value('point_id') ?? 13;

        // =========================
        //  UNTUK USER ROLE QA
        //  Tampilkan hanya pengadaan yang berada pada checkpoint "Inspeksi Barang"
        // =========================
        if ($user->roles === 'qa') {

            // Base query: procurement yang punya progress pada checkpoint inspeksi dan belum selesai untuk checkpoint itu
            $baseQuery = Procurement::with(['department', 'requestProcurements.vendor'])
                ->whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
                    $q->where('checkpoint_id', $inspectionCheckpointId)
                      ->where(function ($qq) {
                          // status yang menunjukkan butuh perhatian (belum completed)
                          $qq->whereIn('status', ['not_started', 'in_progress', 'blocked']);
                      });
                })
                ->orderBy('created_at', 'desc');

            // Pagination untuk tabel
            $procurements = (clone $baseQuery)->paginate(20);

            // KPI: jumlah procurement yang butuh inspeksi (sumber kebenaran)
            $butuhInspeksiCount = (clone $baseQuery)->get()->count();

            // Juga ambil total pengadaan (opsional untuk card)
            $totalProcurements = Procurement::count();

            return view('qa.inspections', compact('procurements', 'butuhInspeksiCount', 'totalProcurements'));
        }

        // =========================
        //  UNTUK ROLE SELAIN QA
        //  Tampilkan daftar inspection reports
        // =========================
        $inspections = InspectionReport::with([
            'project',
            'project.department',
            'item',
        ])
        ->orderBy('inspection_date', 'desc')
        ->paginate(20);

        // For non-QA we can still show counts based on inspections
        $totalInspections = $inspections->total();
        $butuhInspeksiCount = InspectionReport::where('result', 'pending')->count();
        $lolosCount = InspectionReport::where('result', 'passed')->count();
        $gagalCount = InspectionReport::where('result', 'failed')->count();

        // Reuse same blade: blade memeriksa keberadaan $procurements untuk mode QA
        return view('qa.inspections', compact('inspections', 'butuhInspeksiCount', 'totalInspections', 'lolosCount', 'gagalCount'));
    }

    /**
     * DISABLED — show detail (tidak dipakai)
     */
    public function show($id)
    {
        abort(404);
    }

    // ... (metode store, createNcrReport, ncrReports, showNcr, updateNcr, verifyNcr, notifyAccounting)
    // Tidak saya ubah — tetap seperti file Anda sebelumnya.
}
