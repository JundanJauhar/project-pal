<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\Procurement;
use App\Models\Checkpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class InspectionController extends Controller
{
    /**
     * Klasifikasi status inspeksi per pengadaan (berdasarkan semua item-nya).
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

        // Tidak ada item sama sekali → treat sebagai BUTUH inspeksi
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

        // Campuran passed/failed → anggap masih proses
        return 'sedang';
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // checkpoint "Inspeksi Barang"
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')
            ->value('point_id') ?? 13;

        // =========================
        // ROLE QA
        // =========================
        if ($user->roles === 'qa') {
            /**
             * Base query:
             * Ambil SEMUA pengadaan yang punya progress di checkpoint "Inspeksi Barang",
             * tanpa membatasi status progress (completed / in_progress / not_started / blocked).
             *
             * Status kartu & kolom "Status Inspeksi" ditentukan dari hasil inspeksi item,
             * bukan dari status di procurement_progress.
             */
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

            // Ambil semua pengadaan di checkpoint ini (belum pakai filter search / priority / result)
            $qaProcurements = $baseQuery->get();

            // Hitung kartu berdasarkan STATUS PENGADAAN, bukan jumlah item
            $totalProcurements = $qaProcurements->count();
            $countButuh = 0;
            $countSedang = 0;
            $countLolos = 0;
            $countGagal = 0;

            foreach ($qaProcurements as $proc) {
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

            $cards = [
                'total'  => $totalProcurements,
                'butuh'  => $countButuh,
                'sedang' => $countSedang,
                'lolos'  => $countLolos,
                'gagal'  => $countGagal,
            ];

            // ====== LIST TABEL (koleksi yang bisa difilter) ======
            $collection = $qaProcurements;

            // Search (kode / nama pengadaan / project) – server-side
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

            // Filter PRIORITAS
            $priority = $request->query('priority', '');
            if ($priority !== '') {
                $priorityLower = mb_strtolower($priority);
                $collection = $collection->filter(function ($proc) use ($priorityLower) {
                    return mb_strtolower($proc->priority ?? '') === $priorityLower;
                })->values();
            }

            // Filter STATUS INSPEKSI (passed|failed|in_progress|not_inspected)
            $resultFilter = $request->query('result', '');
            if ($resultFilter !== '') {
                $collection = $collection->filter(function ($proc) use ($resultFilter) {
                    $status = $this->classifyProcurementStatus($proc);

                    return match ($resultFilter) {
                        'not_inspected' => $status === 'butuh',
                        'in_progress'   => $status === 'sedang',
                        'passed'        => $status === 'lolos',
                        'failed'        => $status === 'gagal',
                        default         => true,
                    };
                })->values();
            }

            // Paginate collection secara manual
            $page    = (int) $request->query('page', 1);
            $perPage = 20;
            $offset  = ($page - 1) * $perPage;

            $paged = $collection->slice($offset, $perPage)->values();

            $procurements = new LengthAwarePaginator(
                $paged,
                $collection->count(),
                $perPage,
                $page,
                [
                    'path'  => url()->current(),
                    'query' => $request->query(),
                ]
            );

            return view('qa.inspections', array_merge($cards, [
                'procurements' => $procurements,
            ]));
        }

        // =========================
        // ROLE SELAIN QA
        // (tetap bisa pakai kartu global, tapi daftar yang ditampilkan = inspection reports)
        // =========================
        $allProcurements = Procurement::with([
                'project',
                'department',
                'requestProcurements.items.inspectionReports',
                'requestProcurements.vendor',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalProcurements = $allProcurements->count();
        $countButuh = $countSedang = $countLolos = $countGagal = 0;

        foreach ($allProcurements as $proc) {
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

        $cards = [
            'total'  => $totalProcurements,
            'butuh'  => $countButuh,
            'sedang' => $countSedang,
            'lolos'  => $countLolos,
            'gagal'  => $countGagal,
        ];

        $inspections = InspectionReport::with(['project', 'project.department', 'item'])
            ->orderBy('inspection_date', 'desc')
            ->paginate(20);

        return view('qa.inspections', array_merge($cards, [
            'inspections' => $inspections,
        ]));
    }

    public function show($id)
    {
        abort(404);
    }
}
