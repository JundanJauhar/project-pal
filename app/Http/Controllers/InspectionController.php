<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\Procurement;
use App\Models\Checkpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Helpers\ActivityLogger;

class InspectionController extends Controller
{
    /**
     * Classify procurement inspection status based on item results
     * 
     * Hasil:
     *  - 'butuh'  : belum ada item yang diinspeksi (BELUM DIINSPEKSI)
     *  - 'sedang' : sebagian sudah diinspeksi / hasil campuran
     *  - 'lolos'  : semua item LOLOS
     *  - 'gagal'  : semua item TIDAK LOLOS
     */
    protected function classifyProcurementStatus(Procurement $proc): string
    {
        $items = $proc->requestProcurements->flatMap->items;
        $totalItems = $items->count();

        // Tidak ada item sama sekali
        if ($totalItems === 0) {
            return 'butuh';
        }

        // Ambil hasil inspeksi terbaru untuk tiap item
        $latestResults = $items->map(function ($it) {
            $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
            return $latest?->result ?? null;
        });

        $inspectedCount = $latestResults->filter(fn($r) => !is_null($r))->count();

        // Belum ada item yang diinspeksi
        if ($inspectedCount === 0) {
            return 'butuh';
        }

        // Sebagian sudah diinspeksi, sebagian belum
        if ($inspectedCount < $totalItems) {
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
     * Display inspection dashboard
     * 
     * Authorization:
     * - QA role → Lihat procurement di checkpoint "Kedatangan Material"
     * - Non-QA → Lihat inspection reports (read-only)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        $userDivision = $user->division?->division_name;

        // === AUTHORIZATION CHECK: DIVISION + ROLE ===
        // Hanya user dari QA division yang bisa akses fitur ini
        if (
            $userDivision !== 'Quality Assurance'
            || !$user->hasRole('qa_inspector')
        ) {
            // User bukan QA → tampilkan view alternatif (read-only)
            return $this->indexForNonQA($user);
        }

        // === ROLE: QA_INSPECTOR ===
        // User dari Quality Assurance division dengan role qa_inspector

        $inspectionCheckpointId = Checkpoint::where('point_name', 'Kedatangan Material')
            ->value('point_id');

        // === BUILD BASE QUERY: Procurement di checkpoint inspeksi ===
        $baseQuery = Procurement::with([
            'project',
            'department',
            'requestProcurements.items.inspectionReports',
            'requestProcurements.vendor',
            'procurementProgress',
        ])
            ->whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
                $q->where('checkpoint_id', $inspectionCheckpointId);
            })
            ->orderBy('created_at', 'desc');

        // === DIVISION BOUNDARY: Hanya procurement dari divisi sendiri ===
        $qaProcurements = $baseQuery
            ->whereHas('department', function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            })
            ->get();

        // === CALCULATE CARDS: Status based on procurement classification ===
        $cards = $this->calculateInspectionCards($qaProcurements);

        // === FILTER COLLECTION: Search, priority, result ===
        $collection = $this->filterProcurements($qaProcurements, $request);

        // === MANUAL PAGINATION ===
        $page = (int)$request->query('page', 1);
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $paged = $collection->slice($offset, $perPage)->values();

        $procurements = new LengthAwarePaginator(
            $paged,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => url()->current(),
                'query' => $request->query(),
            ]
        );

        // === ACTIVITY LOGGING ===
        ActivityLogger::log(
            module: 'Inspection',
            action: 'view_inspection_dashboard_qa',
            targetId: null,
            details: [
                'user_id' => $user->id,
                'division_id' => $user->division_id,
                'filters' => [
                    'search' => $request->query('q', ''),
                    'priority' => $request->query('priority', ''),
                    'result' => $request->query('result', ''),
                ],
                'cards' => $cards,
            ]
        );

        return view('qa.inspections', array_merge($cards, [
            'procurements' => $procurements,
        ]));
    }

    /**
     * View untuk non-QA user (read-only)
     * 
     * Non-QA user hanya bisa lihat inspection reports,
     * bukan QA dashboard. Akses limited ke divisi mereka.
     */
    private function indexForNonQA($user)
    {
        // === DIVISION BOUNDARY: Hanya dari divisi sendiri ===
        $allProcurements = Procurement::with([
            'project',
            'department',
            'requestProcurements.items.inspectionReports',
            'requestProcurements.vendor',
        ])
            ->whereHas('department', function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // === CALCULATE CARDS: Global stats untuk divisi ===
        $cards = $this->calculateInspectionCards($allProcurements);

        // === INSPECTION REPORTS: Read-only list ===
        $inspections = InspectionReport::with(['item.requestProcurement.procurement.project', 'item.requestProcurement.procurement.department'])
            ->whereHas('item.requestProcurement.procurement.department', function ($q) use ($user) {
                $q->where('division_id', $user->division_id);
            })
            ->orderBy('inspection_date', 'desc')
            ->paginate(20);

        // === ACTIVITY LOGGING ===
        ActivityLogger::log(
            module: 'Inspection',
            action: 'view_inspection_reports_nonqa',
            targetId: null,
            details: [
                'user_id' => $user->id,
                'division_id' => $user->division_id,
                'report_count' => $inspections->total(),
            ]
        );

        return view('qa.inspections', array_merge($cards, [
            'inspections' => $inspections,
        ]));
    }

    /**
     * Calculate inspection status cards
     * 
     * Menghitung jumlah procurement berdasarkan status inspeksi:
     * - butuh: belum inspeksi
     * - sedang: partial inspeksi
     * - lolos: semua lolos
     * - gagal: semua gagal
     */
    private function calculateInspectionCards($procurements)
    {
        $countButuh = 0;
        $countSedang = 0;
        $countLolos = 0;
        $countGagal = 0;

        foreach ($procurements as $proc) {
            $status = $this->classifyProcurementStatus($proc);

            switch ($status) {
                case 'butuh':
                    $countButuh++;
                    break;
                case 'sedang':
                    $countSedang++;
                    break;
                case 'lolos':
                    $countLolos++;
                    break;
                case 'gagal':
                    $countGagal++;
                    break;
            }
        }

        return [
            'total' => $procurements->count(),
            'butuh' => $countButuh,
            'sedang' => $countSedang,
            'lolos' => $countLolos,
            'gagal' => $countGagal,
        ];
    }

    /**
     * Filter procurement collection berdasarkan search, priority, result
     */
    private function filterProcurements($collection, Request $request)
    {
        // Filter: search by code/name/project
        $q = trim($request->query('q', ''));
        if ($q !== '') {
            $collection = $collection->filter(function ($proc) use ($q) {
                $qLower = mb_strtolower($q);

                $codeMatch = mb_stripos($proc->code_procurement, $qLower) !== false;
                $nameMatch = mb_stripos($proc->name_procurement, $qLower) !== false;
                $projectMatch = mb_stripos(optional($proc->project)->project_name ?? '', $qLower) !== false;
                $projectCodeMatch = mb_stripos(optional($proc->project)->project_code ?? '', $qLower) !== false;

                return $codeMatch || $nameMatch || $projectMatch || $projectCodeMatch;
            })->values();
        }

        // Filter: priority
        $priority = $request->query('priority', '');
        if ($priority !== '') {
            $priorityLower = mb_strtolower($priority);
            $collection = $collection->filter(function ($proc) use ($priorityLower) {
                return mb_strtolower($proc->priority ?? '') === $priorityLower;
            })->values();
        }

        // Filter: inspection result status
        $resultFilter = $request->query('result', '');
        if ($resultFilter !== '') {
            $collection = $collection->filter(function ($proc) use ($resultFilter) {
                $status = $this->classifyProcurementStatus($proc);

                return match ($resultFilter) {
                    'not_inspected' => $status === 'butuh',
                    'in_progress' => $status === 'sedang',
                    'passed' => $status === 'lolos',
                    'failed' => $status === 'gagal',
                    default => true,
                };
            })->values();
        }

        return $collection;
    }

    /**
     * Show inspection detail (404 untuk sekarang)
     * TODO: Implementasi detail page jika diperlukan
     */
    public function show($id)
    {
        abort(404, 'Endpoint not implemented');
    }
}