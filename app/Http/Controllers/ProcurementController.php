<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Project;
use App\Models\Division;
use App\Models\Department;
use App\Models\Notification;
use App\Models\ProcurementProgress;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Checkpoint;
use App\Services\CheckpointTransitionService;
use App\Models\Vendor;
use App\Models\EvatekItem;
use App\Models\InquiryQuotation;
use App\Models\Negotiation;
use App\Models\MaterialDelivery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\ActivityLogger;


class ProcurementController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $query = Procurement::with(['project', 'department', 'requestProcurements.vendor', 'procurementProgress.checkpoint']);
        
        // Filter by vendor_id if user has vendor_id
        if ($user->vendor_id) {
            $query->whereHas('requestProcurements', function($q) use ($user) {
                $q->where('id_vendor', $user->vendor_id);
            });
        }
        
        $procurements = $query->orderBy('created_at', 'desc')
            ->paginate(20);

            ActivityLogger::log(
                module: 'Procurement',
                action: 'view_procurement_list',
                targetId: null,
                details: ['user_id' => Auth::id()]
            );

        return view('procurements.index', compact('procurements'));
    }

    public function create()
    {
        $departments = Department::all();
        $projects = Project::withCount('procurements')->get();

        ActivityLogger::log(
            module: 'Procurement',
            action: 'open_procurement_create_form',
            targetId: null,
            details: ['user_id' => Auth::id()]
        );

        return view('procurements.create', compact('departments', 'projects'));
    }

    /**
     * Store a newly created procurement with items
     * Automatically complete checkpoint 1 and move to checkpoint 2 (Evatek)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_code' => 'required|string|exists:projects,project_code',
            'code_procurement' => 'nullable|string',
            'name_procurement' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_procurement' => 'required|exists:departments,department_id',
            'priority' => 'required|in:rendah,sedang,tinggi',
            'end_date' => 'required|date|after:today',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit' => 'required|string|max:50',
        ], [
            'items.required' => 'Minimal harus ada 1 item untuk procurement',
            'items.min' => 'Minimal harus ada 1 item untuk procurement',
        ]);

        DB::beginTransaction();
        try {
            $validated['start_date'] = Carbon::now()->format('Y-m-d');
            $validated['status_procurement'] = 'in_progress';

            $projectCode = $validated['project_code'];

            // Generate kode procurement
            $last = Procurement::where('code_procurement', 'like', $projectCode . '-%')
                ->orderByRaw("LENGTH(code_procurement) desc")
                ->orderBy('code_procurement', 'desc')
                ->first();

            if ($last && preg_match('/-(\d+)$/', $last->code_procurement, $m)) {
                $lastSeq = intval($m[1]);
            } else {
                $lastSeq = Procurement::where('code_procurement', 'like', $projectCode . '-%')->count();
            }

            $nextSeq = $lastSeq + 1;
            $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
            $finalCode = $projectCode . '-' . $seqStr;

            while (Procurement::where('code_procurement', $finalCode)->exists()) {
                $nextSeq++;
                $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
                $finalCode = $projectCode . '-' . $seqStr;
            }

            $validated['code_procurement'] = $finalCode;

            $project = Project::where('project_code', $projectCode)->first();
            if ($project) {
                $validated['project_id'] = $project->project_id ?? $project->id ?? null;
            }

            // Create procurement
            $procurement = Procurement::create($validated);

            // Create RequestProcurement
            $requestProcurement = RequestProcurement::create([
                'procurement_id' => $procurement->procurement_id,
                'project_id' => $project->project_id ?? null,
                'department_id' => $validated['department_procurement'],
                'vendor_id' => null,
                'request_name' => $validated['name_procurement'],
                'created_date' => Carbon::now(),
                'deadline_date' => $validated['end_date'],
            ]);

            // Save items
            foreach ($request->items as $itemData) {
                Item::create([
                    'request_procurement_id' => $requestProcurement->request_id,
                    'item_name' => $itemData['item_name'],
                    'item_description' => $itemData['description'] ?? null,
                    'specification' => $itemData['description'] ?? null,
                    'amount' => $itemData['quantity'],
                    'unit' => $itemData['unit'],
                ]);
            }

            // ========== AUTO CHECKPOINT TRANSITION ==========
            // Complete Checkpoint 1 (Penawaran Permintaan) and move to Checkpoint 2 (Evatek)
            $service = new CheckpointTransitionService($procurement);
            $transitionResult = $service->transition(1, [
                'notes' => 'Procurement dibuat dan siap untuk evaluasi teknis',
            ]);

            if (!$transitionResult['success']) {
                throw new \Exception('Gagal melakukan transisi checkpoint: ' . $transitionResult['message']);
            }

            DB::commit();

            ActivityLogger::log(
                module: 'Procurement',
                action: 'create_procurement',
                targetId: $procurement->procurement_id,
                details: [
                    'user_id' => Auth::id(),
                    'code_procurement' => $procurement->code_procurement,
                    'project_id' => $procurement->project_id,
                    'department_id' => $validated['department_procurement'],
                    'priority' => $validated['priority'],
                    'total_items' => count($validated['items']),
                ]
            );

            return redirect()->route('procurements.show', $procurement->procurement_id)
                ->with('success', 'Procurement berhasil dibuat dengan kode ' . $finalCode . '. Procurement telah masuk ke tahap Evatek.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Procurement creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan procurement: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified procurement with all related data
     */
    public function show($id)
    {
        $procurement = Procurement::with([
            'requestProcurements.items',
            'requestProcurements.vendor',
            'procurementProgress.checkpoint',
            'project',
            'department',
            'evatekItems',
            'inquiryQuotations',
            'negotiations',
            'materialDeliveries'
        ])->findOrFail($id);

        $checkpoints = Checkpoint::orderBy('point_sequence', 'asc')->get();

        $currentStageIndex = 0;
        $service = new CheckpointTransitionService($procurement);
        $currentCheckpoint = $service->getCurrentCheckpoint();
        $currentCheckpointSequence = $currentCheckpoint?->point_sequence ?? null;

        if ($currentCheckpoint) {
            $currentStageIndex = $currentCheckpoint->point_sequence;
        }

        $vendors = Vendor::where('legal_status', 'verified')
            ->orderBy('name_vendor', 'asc')
            ->get();

        // Query evatek items that exist for this procurement
        $evatekItems = EvatekItem::where('procurement_id', $procurement->procurement_id)
            ->with([
                'item',
                'vendor',
                'revisions' => function ($query) {
                    $query->orderBy('revision_id', 'desc');
                }
            ])
            ->get();

        $inquiryQuotations = InquiryQuotation::where('procurement_id', $procurement->procurement_id)
            ->with('vendor')
            ->orderBy('tanggal_inquiry', 'desc')
            ->get();

        $negotiations = Negotiation::where('procurement_id', $procurement->procurement_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $materialDeliveries = MaterialDelivery::where('procurement_id', $procurement->procurement_id)
            ->orderBy('created_at', 'desc')
            ->get();

        ActivityLogger::log(
            module: 'Procurement',
            action: 'view_procurement_detail',
            targetId: $procurement->procurement_id,
            details: ['user_id' => Auth::id()]
        );

        return view('procurements.show', compact(
            'procurement', 
            'checkpoints', 
            'currentStageIndex', 
            'vendors',
            'evatekItems',
            'inquiryQuotations',
            'negotiations',
            'materialDeliveries',
            'currentCheckpointSequence'
        ));
    }

    public function getProgress($id)
    {
        $procurement = Procurement::findOrFail($id);

        $progress = $procurement->procurementProgress()
            ->with(['checkpoint', 'user'])
            ->orderBy('checkpoint_id')
            ->get();

            ActivityLogger::log(
                module: 'Procurement',
                action: 'view_procurement_progress',
                targetId: $procurement->procurement_id,
                details: ['user_id' => Auth::id()]
            );

        return response()->json($progress);
    }

    public function updateProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'checkpoint_id' => 'required|exists:checkpoints,point_id',
            'status' => 'required|in:not_started,in_progress,completed,blocked',
            'note' => 'nullable|string',
        ]);

        $procurement = Procurement::findOrFail($id);

        $progress = $procurement->procurementProgress()
            ->where('checkpoint_id', $validated['checkpoint_id'])
            ->first();

        if ($progress) {
            $progress->update([
                'status' => $validated['status'],
                'note' => $validated['note'],
                'user_id' => Auth::id(),
                'end_date' => $validated['status'] === 'completed' ? now() : null,
            ]);
        } else {
            $procurement->procurementProgress()->create([
                'checkpoint_id' => $validated['checkpoint_id'],
                'status' => $validated['status'],
                'note' => $validated['note'],
                'user_id' => Auth::id(),
                'start_date' => now(),
                'end_date' => $validated['status'] === 'completed' ? now() : null,
            ]);
        }

        ActivityLogger::log(
            module: 'Procurement',
            action: 'update_procurement_progress',
            targetId: $procurement->procurement_id,
            details: [
                'user_id' => Auth::id(),
                'checkpoint_id' => $validated['checkpoint_id'],
                'status' => $validated['status'],
            ]
        );

        return redirect()->back()->with('success', 'Progress procurement berhasil diperbarui');
    }

    public function byProject($projectId)
    {
        $project = Project::findOrFail($projectId);

        $procurements = $project->procurements()
            ->with(['department', 'requestProcurements.vendor', 'procurementProgress.checkpoint'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

            ActivityLogger::log(
                module: 'Procurement',
                action: 'view_procurements_by_project',
                targetId: $project->project_id,
                details: ['user_id' => Auth::id()]
            );

        return view('procurements.by-project', compact('project', 'procurements'));
    }

    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $status = $request->query('status', '');
        $priority = $request->query('priority', '');
        $page = $request->query('page', 1);

        $procurementsQuery = Procurement::with(['project', 'department', 'requestProcurements.vendor', 'procurementProgress.checkpoint']);

        if (!empty($q)) {
            $procurementsQuery->where('name_procurement', 'LIKE', "%{$q}%")
                ->orWhere('code_procurement', 'LIKE', "%{$q}%");
        }

        if (!empty($status)) {
            $procurementsQuery->where('status_procurement', $status);
        }

        if (!empty($priority)) {
            $procurementsQuery->where('priority', $priority);
        }

        $procurements = $procurementsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $page);

        $items = $procurements->map(function ($p) {
            $firstRequest = $p->requestProcurements ? $p->requestProcurements->first() : null;
            $vendor = ($firstRequest && $firstRequest->vendor) ? $firstRequest->vendor->name_vendor : '-';

            $service = new CheckpointTransitionService($p);
            $currentCheckpoint = $service->getCurrentCheckpoint();
            $checkpointName = $currentCheckpoint ? $currentCheckpoint->point_name : '-';

            return [
                'procurement_id' => $p->procurement_id,
                'project_code' => $p->project->project_code ?? '-',
                'code_procurement' => $p->code_procurement,
                'name_procurement' => $p->name_procurement,
                'department_name' => $p->department ? $p->department->department_name : '-',
                'start_date' => $p->start_date ? $p->start_date->format('d/m/Y') : '-',
                'end_date' => $p->end_date ? $p->end_date->format('d/m/Y') : '-',
                'vendor_name' => $vendor,
                'priority' => $p->priority,
                'status_procurement' => $p->status_procurement,
                'current_checkpoint' => $checkpointName,
            ];
        });

        ActivityLogger::log(
            module: 'Procurement',
            action: 'search_procurements',
            targetId: null,
            details: [
                'user_id' => Auth::id(),
                'query' => $q,
                'status' => $status,
                'priority' => $priority,
                'page' => $page,
            ]
        );

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $procurements->currentPage(),
                'per_page' => $procurements->perPage(),
                'total' => $procurements->total(),
                'last_page' => $procurements->lastPage(),
                'has_more' => $procurements->hasMorePages(),
            ]
        ]);
    }
}