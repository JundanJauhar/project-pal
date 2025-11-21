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
    public function index(Request $request)
    {
        $user = Auth::user();

        // checkpoint "Inspeksi Barang"
        $inspectionCheckpointId = Checkpoint::where('point_name', 'Inspeksi Barang')->value('point_id') ?? 13;

        // common: load all procurements with nested relations needed for status computation
        // Note: for QA we will still restrict the listing to procurements at the inspection checkpoint,
        // but cards should reflect overall procurement statuses across system.
        $allProcurements = Procurement::with([
            'department',
            'requestProcurements.items.inspectionReports',
            'requestProcurements.vendor'
        ])->orderBy('created_at', 'desc')->get();

        // Compute counts (per-procurement classification)
        $totalProcurements = $allProcurements->count();
        $countButuh = 0; // belum ada item yang diinspeksi
        $countSedang = 0; // sebagian item sudah diinspeksi / mixed
        $countLolos = 0;
        $countGagal = 0;

        foreach ($allProcurements as $proc) {
            // flatten items
            $items = $proc->requestProcurements->flatMap->items;
            $totalItems = $items->count();

            // gather latest result per item (null if never inspected)
            $latestResults = $items->map(function ($it) {
                $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
                return $latest?->result ?? null;
            });

            $inspectedCount = $latestResults->filter(fn($r) => !is_null($r))->count();

            if ($totalItems === 0) {
                // if no items, treat as butuh inspeksi (nothing to inspect yet)
                $countButuh++;
                continue;
            }

            if ($inspectedCount === 0) {
                $countButuh++;
                continue;
            }

            if ($inspectedCount < $totalItems) {
                // some inspected, some not => sedang proses
                $countSedang++;
                continue;
            }

            // all items have latest results
            $allPassed = $latestResults->every(fn($r) => $r === 'passed');
            $allFailed = $latestResults->every(fn($r) => $r === 'failed');

            if ($allPassed) {
                $countLolos++;
            } elseif ($allFailed) {
                $countGagal++;
            } else {
                // mixed (some passed, some failed) -> treat as 'sedang proses' (consistent, not ambiguous)
                $countSedang++;
            }
        }

        // Build the cards values to pass to view
        $cards = [
            'total' => $totalProcurements,
            'butuh' => $countButuh,
            'sedang' => $countSedang,
            'lolos' => $countLolos,
            'gagal' => $countGagal,
        ];

        // === Build listing (for QA only show procurements at inspection checkpoint) ===
        if ($user->roles === 'qa') {
            // base query for QA listing (procurements at inspection checkpoint)
            $baseQuery = Procurement::with(['department', 'requestProcurements.items.inspectionReports', 'requestProcurements.vendor'])
                ->whereHas('procurementProgress', function ($q) use ($inspectionCheckpointId) {
                    $q->where('checkpoint_id', $inspectionCheckpointId)
                      ->whereIn('status', ['not_started', 'in_progress', 'blocked']);
                })
                ->orderBy('created_at', 'desc');

            // fetch and then apply collection filters (search, priority, result)
            $collection = $baseQuery->get();

            // apply search q
            $q = trim($request->query('q', ''));
            if ($q !== '') {
                $collection = $collection->filter(function ($proc) use ($q) {
                    $qLower = mb_strtolower($q);
                    return mb_stripos($proc->code_procurement, $q) !== false
                        || mb_stripos($proc->name_procurement, $q) !== false;
                })->values();
            }

            // filter priority
            $priority = $request->query('priority', '');
            if ($priority) {
                $collection = $collection->filter(function ($proc) use ($priority) {
                    return mb_strtolower($proc->priority ?? '') === mb_strtolower($priority);
                })->values();
            }

            // filter result (passed|failed|in_progress|not_inspected)
            $resultFilter = $request->query('result', '');
            if ($resultFilter) {
                $collection = $collection->filter(function ($proc) use ($resultFilter) {
                    $items = $proc->requestProcurements->flatMap->items;
                    $totalItems = $items->count();
                    $results = $items->map(function ($it) {
                        $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
                        return $latest?->result ?? null;
                    });
                    $inspectedCount = $results->filter(fn($r) => !is_null($r))->count();

                    if ($resultFilter === 'not_inspected') {
                        return $inspectedCount === 0;
                    }

                    if ($inspectedCount === 0) {
                        return false;
                    }

                    if ($inspectedCount < $totalItems) {
                        return $resultFilter === 'in_progress';
                    }

                    // all items inspected
                    if ($results->every(fn($r) => $r === 'passed')) {
                        return $resultFilter === 'passed';
                    }
                    if ($results->every(fn($r) => $r === 'failed')) {
                        return $resultFilter === 'failed';
                    }
                    // mixed -> treat as in_progress
                    return $resultFilter === 'in_progress';
                })->values();
            }

            // paginate collection (server-side)
            $page = (int) $request->query('page', 1);
            $perPage = 20;
            $offset = ($page - 1) * $perPage;
            $paged = $collection->slice($offset, $perPage)->values();

            $procurements = new LengthAwarePaginator($paged, $collection->count(), $perPage, $page, [
                'path' => url()->current(),
                'query' => $request->query(),
            ]);

            return view('qa.inspections', array_merge($cards, [
                'procurements' => $procurements,
            ]));
        }

        // === Non-QA branch: show inspection reports listing (unchanged) ===
        // We'll show inspections (paginated) but still pass the cards variable for top UI
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
