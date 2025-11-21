<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
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

        // Cari checkpoint "Inspeksi Barang" (fallback ke id 13 bila tidak ditemukan)
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')->value('point_id') ?? 13;

        // COMMON KPIs (hitung di awal agar selalu tersedia untuk view)
        $totalProcurements = Procurement::count();

        $butuhInspeksiCount = Procurement::whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
            $q->where('checkpoint_id', $inspectionCheckpointId)
              ->whereIn('status', ['not_started', 'in_progress', 'blocked']);
        })->count();

        // Hitung jumlah item yang pernah dilaporkan passed / failed (distinct per item_id)
        $lolosCount = InspectionReport::where('result', 'passed')
            ->distinct('item_id')->count('item_id');

        $gagalCount = InspectionReport::where('result', 'failed')
            ->distinct('item_id')->count('item_id');

        // Hitung jumlah procurement "sedang proses inspeksi" (memiliki item yang sudah diinspeksi
        // tetapi belum semua item di procurement tersebut diinspeksi).
        // Kita cari procurement yang memiliki setidaknya 1 item yang punya report dan setidaknya
        // 1 item yang belum punya report.
        $sedangProsesCount = Procurement::whereHas('requestProcurements.items', function ($q) {
                $q->whereHas('inspectionReports');
            })
            ->whereHas('requestProcurements.items', function ($q) {
                $q->whereDoesntHave('inspectionReports');
            })
            ->count();

        // =========================
        //  UNTUK USER ROLE QA
        //  Tampilkan hanya pengadaan yang berada pada checkpoint "Inspeksi Barang"
        //  dengan dukungan filter GET (q, priority, result)
        // =========================
        if ($user->roles === 'qa') {

            // Base query: procurement yang punya progress pada checkpoint inspeksi dan belum selesai untuk checkpoint itu
            $baseQuery = Procurement::with(['department', 'requestProcurements.vendor', 'requestProcurements.items.inspectionReports'])
                ->whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
                    $q->where('checkpoint_id', $inspectionCheckpointId)
                      ->whereIn('status', ['not_started', 'in_progress', 'blocked']);
                })
                ->orderBy('created_at', 'desc');

            // Filters
            $q = $request->query('q');
            $priority = $request->query('priority');
            $result = $request->query('result');

            if ($q) {
                $baseQuery->where(function ($qq) use ($q) {
                    $qq->where('code_procurement', 'like', "%{$q}%")
                       ->orWhere('name_procurement', 'like', "%{$q}%");
                });
            }

            if ($priority) {
                $baseQuery->where('priority', $priority);
            }

            if ($result) {
                switch ($result) {
                    case 'failed':
                        // procurement that has at least one failed report
                        $baseQuery->whereHas('requestProcurements.items.inspectionReports', function ($rr) {
                            $rr->where('result', 'failed');
                        });
                        break;

                    case 'passed':
                        // procurement that has inspectionReports and none failed
                        $baseQuery->whereHas('requestProcurements.items.inspectionReports')
                                  ->whereDoesntHave('requestProcurements.items.inspectionReports', function ($rr) {
                                      $rr->where('result', 'failed');
                                  });
                        break;

                    case 'not_inspected':
                        // procurement with zero inspection reports on any item
                        $baseQuery->whereDoesntHave('requestProcurements.items.inspectionReports');
                        break;

                    case 'in_progress':
                        // procurement that has some items inspected and some not inspected
                        $baseQuery->whereHas('requestProcurements.items', function ($qq) {
                                $qq->whereHas('inspectionReports');
                            })
                            ->whereHas('requestProcurements.items', function ($qq) {
                                $qq->whereDoesntHave('inspectionReports');
                            });
                        break;

                    default:
                        // ignore unknown
                }
            }

            // Pagination untuk tabel (20 per page)
            $procurements = (clone $baseQuery)->paginate(20)->withQueryString();

            // Pastikan butuhInspeksiCount konsisten (dari baseQuery tanpa pagination)
            $butuhInspeksiCount = (clone $baseQuery)->get()->count();

            return view('qa.inspections', compact(
                'procurements',
                'butuhInspeksiCount',
                'totalProcurements',
                'lolosCount',
                'gagalCount',
                'sedangProsesCount'
            ));
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

        // Untuk non-QA, juga siapkan KPI agar blade dapat menampilkan top-cards
        $totalInspections = $inspections->total();
        $butuhInspeksiCountPending = InspectionReport::where('result', 'pending')->count();

        // Kirimkan variabel agar blade konsisten
        return view('qa.inspections', [
            'inspections' => $inspections,
            'butuhInspeksiCount' => $butuhInspeksiCountPending,
            'totalInspections' => $totalInspections,
            'totalProcurements' => $totalProcurements,
            'lolosCount' => $lolosCount,
            'gagalCount' => $gagalCount,
            'sedangProsesCount' => $sedangProsesCount,
        ]);
    }

    /**
     * DISABLED — show detail (tidak dipakai)
     */
    public function show($id)
    {
        abort(404);
    }

    // Note: metode lain (store, createNcrReport, etc.) tidak diubah di sini.
}
