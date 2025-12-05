<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Project;
use App\Models\Procurement;
use App\Models\ProcurementProgress;
use App\Models\Notification;
use App\Services\CheckpointTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Approval;
use App\Helpers\ActivityLogger;


class SekdirController extends Controller
{
    /**
     * Display approval page for Sekretaris Direksi
     */
    public function approval()
    {
        // Get procurements that are PENDING (in_progress) at checkpoint 4
        $pendingProcurements = Procurement::with([
            'project.ownerDivision',
            'department', 
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ])
        ->whereHas('procurementProgress', function($query) {
            $query->where('checkpoint_id', 4)
                  ->where('status', 'in_progress'); // Only pending
        })
        ->orderBy('created_at', 'desc')
        ->paginate(15, ['*'], 'pending_page');

        // Get procurements that are APPROVED (completed) at checkpoint 4
        $approvedProcurements = Procurement::with([
            'project.ownerDivision',
            'department', 
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ])
        ->whereHas('procurementProgress', function($query) {
            $query->where('checkpoint_id', 4)
                  ->where('status', 'completed'); // Only approved
        })
        ->orderBy('updated_at', 'desc')
        ->paginate(15, ['*'], 'approved_page');

        // Get procurements that are REJECTED at checkpoint 4
        $rejectedProcurements = Procurement::with([
            'project.ownerDivision',
            'department', 
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ])
        ->whereHas('procurementProgress', function($query) {
            $query->where('checkpoint_id', 4)
                  ->where('status', 'rejected'); // Only rejected
        })
        ->orderBy('updated_at', 'desc')
        ->paginate(15, ['*'], 'rejected_page');

        // Calculate statistics
        $totalPending = Procurement::whereHas('procurementProgress', function($query) {
            $query->where('checkpoint_id', 4)->where('status', 'in_progress');
        })->count();

        $totalApproved = Procurement::whereHas('procurementProgress', function($query) {
            $query->where('checkpoint_id', 4)->where('status', 'completed');
        })->count();

        $totalRejected = Procurement::whereHas('procurementProgress', function($query) {
            $query->where('checkpoint_id', 4)->where('status', 'rejected');
        })->count();

        $totalProcurements = $totalPending + $totalApproved + $totalRejected;
        
        $stats = [
            'pending' => $totalPending,
            'approved' => $totalApproved,
            'rejected' => $totalRejected,
        ];

        return view('sekdir.approval', compact(
            'pendingProcurements', 
            'approvedProcurements', 
            'rejectedProcurements',
            'totalProcurements', 
            'stats'
        ));
    }

    /**
     * Display dashboard with charts (for sekdir/dashboard route)
     */
    public function dashboard()
    {
        $user = Auth::user();
        $checkpoints = Checkpoint::all();
        $projects = Project::all();
        $priority = Project::select('priority')->distinct()->get();

        // Get ALL procurements with checkpoint data
        $allProcurements = Procurement::with([
            'project',
            'department', 
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ])->get();

        // Calculate statistics based on status_procurement column
        $stats = [
            'total_pengadaan' => $allProcurements->count(),
            'sedang_proses' => $allProcurements->where('status_procurement', 'in_progress')->count(),
            'selesai' => $allProcurements->where('status_procurement', 'completed')->count(),
            'ditolak' => $allProcurements->where('status_procurement', 'cancelled')->count(),
        ];

        // Get recent procurements (paginated)
        $procurements = Procurement::with([
            'project',
            'department', 
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return view('sekdir.dashboard', compact('stats', 'procurements', 'checkpoints', 'projects', 'priority'));
    }

    /**
     * Display dashboard overview (OLD - redirects to approval)
     */
    public function index()
    {
        return redirect()->route('sekdir.approval');
    }

    /**
     * Get procurements based on user role
     */
    private function getProcurementsByRole($user)
    {
        $query = Procurement::with([
            'project',
            'department', 
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ]);

        // Filter by vendor_id if user has vendor_id
        if ($user->vendor_id) {
            $query->whereHas('requestProcurements', function($q) use ($user) {
                $q->where('vendor_id', $user->vendor_id);
            });
        }

        return $query->orderBy('start_date', 'desc')
            ->paginate(10);
    }

    /**
     * Search procurements with filters
     */
public function search(Request $request)
{
    $user = Auth::user();
    
    $query = Procurement::with([
        'project',
        'department',
        'requestProcurements.vendor',
        'procurementProgress.checkpoint'
    ]);

    // Filter by vendor_id if user has vendor_id
    if ($user->vendor_id) {
        $query->whereHas('requestProcurements', function($q) use ($user) {
            $q->where('vendor_id', $user->vendor_id);
        });
    }

    // Filter search
    if ($request->filled('q')) {
        $searchTerm = $request->q;
        $query->where(function ($q) use ($searchTerm) {
            $q->where('name_procurement', 'like', "%$searchTerm%")
              ->orWhere('code_procurement', 'like', "%$searchTerm%");
        });
    }

    // Filter priority
    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }

    // Filter project
    if ($request->filled('project')) {
        $query->whereHas('project', function ($q) use ($request) {
            $q->where('project_code', $request->project);
        });
    }

    // Load data
    $allProcurements = $query->orderBy('created_at', 'desc')->get();

    // Filter checkpoint AFTER loading (karena pakai accessor)
    if ($request->filled('checkpoint')) {
        $checkpointFilter = $request->checkpoint;

        $allProcurements = $allProcurements->filter(function ($p) use ($checkpointFilter) {

            // status completed
            if ($checkpointFilter === 'completed') {
                return $p->status_procurement === 'completed';
            }

            // status cancelled
            if ($checkpointFilter === 'cancelled') {
                return $p->status_procurement === 'cancelled';
            }

            // filter berdasarkan accessor current_checkpoint
            return $p->status_procurement === 'in_progress'
                && ($p->current_checkpoint === $checkpointFilter);
        });
    }

    // Manual pagination
    $page = $request->get('page', 1);
    $perPage = 10;
    $total = $allProcurements->count();
    $lastPage = $total > 0 ? ceil($total / $perPage) : 1;

    $procurements = $allProcurements
        ->slice(($page - 1) * $perPage, $perPage)
        ->values();

    // Format JSON response
    $data = $procurements->map(function ($p) {

        return [
            'procurement_id'   => $p->procurement_id,
            'project_code'     => $p->project->project_code ?? '-',
            'code_procurement' => $p->code_procurement,
            'name_procurement' => $p->name_procurement,
            'department_name'  => $p->department->department_name ?? '-',
            'start_date'       => optional($p->start_date)->format('d/m/Y'),
            'end_date'         => optional($p->end_date)->format('d/m/Y'),
            'vendor_name'      => $p->requestProcurements->first()?->vendor?->name_vendor ?? '-',
            'priority'         => $p->priority,
            'status_procurement' => $p->status_procurement,
            'current_checkpoint' => $p->current_checkpoint, // ← FIX UTAMA
        ];
    });

    return response()->json([
        'data' => $data,
        'pagination' => [
            'current_page' => (int)$page,
            'last_page'    => $lastPage,
            'per_page'     => $perPage,
            'total'        => $total,
            'has_more'     => $page < $lastPage,
        ]
    ]);
}


    /**
     * Get dashboard data for specific department
     */
    public function departmentDashboard($departmentId)
    {
        $user = Auth::user();

        $procurements = Procurement::where('department_procurement', $departmentId)
            ->with([
                'project',
                'department', 
                'requestProcurements.vendor',
                'procurementProgress.checkpoint'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.department', compact('procurements'));
    }

    /**
     * Get procurement statistics by status
     */
    public function getStatistics()
    {
        $allProcurements = Procurement::with(['procurementProgress.checkpoint'])->get();
        
        // Calculate using status_procurement column
        $statusStats = [
            ['status' => 'in_progress', 'label' => 'Sedang Proses', 'count' => $allProcurements->where('status_procurement', 'in_progress')->count()],
            ['status' => 'completed', 'label' => 'Selesai', 'count' => $allProcurements->where('status_procurement', 'completed')->count()],
            ['status' => 'cancelled', 'label' => 'Dibatalkan', 'count' => $allProcurements->where('status_procurement', 'cancelled')->count()],
        ];

        $priorityStats = Procurement::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->map(function($item) {
                return [
                    'priority' => $item->priority,
                    'label' => ucfirst($item->priority),
                    'count' => $item->count
                ];
            });

        return response()->json([
            'status' => $statusStats,
            'priority' => $priorityStats,
        ]);
    }

    /**
     * Get procurement timeline progress
     */
    public function getProcurementTimeline($procurementId)
    {
        $procurement = Procurement::findOrFail($procurementId);
        
        $progress = $procurement->procurementProgress()
            ->with(['checkpoint', 'user'])
            ->orderBy('checkpoint_id')
            ->get()
            ->map(function($p) {
                return [
                    'checkpoint_name' => $p->checkpoint->point_name ?? '-',
                    'checkpoint_sequence' => $p->checkpoint->point_sequence ?? 0,
                    'status' => $p->status,
                    'started_at' => $p->start_date ? $p->start_date->format('d/m/Y H:i') : null,
                    'completed_at' => $p->end_date ? $p->end_date->format('d/m/Y H:i') : null,
                    'note' => $p->note,
                    'user_name' => $p->user->name ?? '-',
                ];
            });

        $service = new CheckpointTransitionService($procurement);
        $currentCheckpoint = $service->getCurrentCheckpoint();

        return response()->json([
            'procurement' => [
                'code' => $procurement->code_procurement,
                'name' => $procurement->name_procurement,
                'status' => $procurement->status_procurement,
                'current_checkpoint' => $currentCheckpoint ? $currentCheckpoint->point_name : '-',
                'current_checkpoint_sequence' => $currentCheckpoint ? $currentCheckpoint->point_sequence : 0,
            ],
            'timeline' => $progress
        ]);
    }

    /**
     * Get procurements by project
     */
    public function byProject($projectCode)
    {
        $project = Project::where('project_code', $projectCode)->firstOrFail();
        
        $procurements = Procurement::where('project_id', $project->project_id)
            ->with([
                'department', 
                'requestProcurements.vendor',
                'procurementProgress.checkpoint'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'total' => $procurements->total(),
            'in_progress' => Procurement::where('project_id', $project->project_id)
                ->where('status_procurement', 'in_progress')->count(),
            'completed' => Procurement::where('project_id', $project->project_id)
                ->where('status_procurement', 'completed')->count(),
            'cancelled' => Procurement::where('project_id', $project->project_id)
                ->where('status_procurement', 'cancelled')->count(),
        ];

        return view('procurements.by-project', compact('project', 'procurements', 'stats'));
    }

    /**
     * Get checkpoint distribution (untuk analytics/dashboard charts)
     */
    public function getCheckpointDistribution()
    {
        $procurements = Procurement::where('status_procurement', 'in_progress')
            ->with(['procurementProgress.checkpoint'])
            ->get();

        $distribution = [];
        
        foreach ($procurements as $proc) {
            $service = new CheckpointTransitionService($proc);
            $currentCheckpoint = $service->getCurrentCheckpoint();
            $checkpointName = $currentCheckpoint ? $currentCheckpoint->point_name : '-';
            
            if ($checkpointName !== '-') {
                if (!isset($distribution[$checkpointName])) {
                    $distribution[$checkpointName] = 0;
                }
                $distribution[$checkpointName]++;
            }
        }

        $result = collect($distribution)->map(function($count, $checkpoint) {
            return [
                'checkpoint' => $checkpoint,
                'count' => $count
            ];
        })->values();

        return response()->json($result);
    }

    // ===== Removed duplicate dashboard() and approval() methods =====
    // Main methods are defined earlier in this file

    public function approve(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'document_link' => 'required|url',
            'notes' => 'nullable|string',
            'approval_decision' => 'required|in:approved,rejected',
        ]);

        Approval::create([
            'module' => 'project',
            'module_id' => $project->project_id,
            'approver_id' => Auth::id(),
            'status' => $request->approval_decision,
            'approval_document_link' => $request->document_link,
            'approval_notes' => $request->notes,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        if ($request->approval_decision === 'approved') {
            $project->update([
                'status_project' => 'pemilihan_vendor',
                'approval_document_link' => $request->document_link,
                'approval_notes' => $request->notes,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);
            $message = 'Project berhasil disetujui dan dipindahkan ke tahap pemilihan vendor.';
        } else {
            $project->update([
                'status_project' => 'rejected',
                'approval_document_link' => $request->document_link,
                'approval_notes' => $request->notes,
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
            ]);
            $message = 'Project ditolak.';
        }

        ActivityLogger::log(
            module: 'Project',
            action: $request->approval_decision === 'approved' ? 'approve_project' : 'reject_project',
            targetId: $project->project_id,
            details: [
                'user_id' => Auth::id(),
                'decision' => $request->approval_decision,
                'document_link' => $request->document_link,
                'notes' => $request->notes,
            ]
        );

        return redirect()->route('sekdir.approval')
            ->with('success', $message);
    }

 public function approvalDetail($procurementId)
{
    // Memuat Procurement dengan relasi yang diperlukan, termasuk progress-nya
    $procurement = Procurement::with([
        'project.ownerDivision', 
        'requestProcurements.items',
        'procurementProgress' // WAJIB: Memuat progress yang sudah ada
    ])->findOrFail($procurementId);

    // Memuat semua checkpoints yang berurutan
    $checkpoints = Checkpoint::orderBy('point_sequence')->get();

    // --- LOGIKA MENENTUKAN STAGE SAAT INI ---
    
    // 1. Ambil semua ID Checkpoint yang sudah 'completed' atau 'rejected'
    $completedCheckpointIds = $procurement->procurementProgress
        ->whereIn('status', ['completed', 'rejected']) // Mengambil yang sudah selesai/ditolak
        ->pluck('checkpoint_id');

    // 2. Tentukan Checkpoint ID yang sedang 'in_progress'
    $inProgressCheckpoint = $procurement->procurementProgress
        ->where('status', 'in_progress')
        ->first();
    
    $currentCheckpointId = $inProgressCheckpoint ? $inProgressCheckpoint->checkpoint_id : null;
    
    // 3. Tentukan Index (Urutan) dari Checkpoint yang sedang aktif
    $currentStageIndex = null;
    if ($currentCheckpointId) {
        // Cari urutan (index 0-based) checkpoint yang sedang aktif di collection $checkpoints
        $currentStageIndex = $checkpoints->search(function ($checkpoint) use ($currentCheckpointId) {
            return $checkpoint->checkpoint_id === $currentCheckpointId;
        });
    }

    // Jika tidak ada yang 'in_progress', asumsikan stage berikutnya adalah yang pertama belum selesai
    if ($currentStageIndex === false || $currentStageIndex === null) {
        $lastCompleted = $checkpoints->whereIn('checkpoint_id', $completedCheckpointIds)->last();
        $lastCompletedIndex = $lastCompleted ? $checkpoints->search($lastCompleted) : -1;
        
        // Stage berikutnya (jika ada) adalah index setelah yang terakhir selesai.
        if ($lastCompletedIndex < $checkpoints->count() - 1) {
            $currentStageIndex = $lastCompletedIndex + 1;
        }
    }
    // --- AKHIR LOGIKA STAGE ---
    
    
    // Jika currentStageIndex adalah boolean (false), ubah menjadi null
    if ($currentStageIndex === false) {
        $currentStageIndex = null;
    }

    ActivityLogger::log(
        module: 'Procurement',
        action: 'view_procurement_approval_detail',
        targetId: $procurement->procurement_id,
        details: ['user_id' => Auth::id()]
    );

    return view('sekdir.approval-detail', compact(
        'procurement',
        'checkpoints',
        'currentStageIndex' // VARIABEL BARU YANG DILEWATKAN
    ));
}

public function approvalSubmit(Request $request, $procurement_id)
{
    $request->validate([
        'procurement_link' => 'required|url',
        'notes' => 'nullable|string',
        'action' => 'required|in:approve,reject'
    ]);

    $procurement = Procurement::findOrFail($procurement_id);

    $sekdirCheckpointId = 4; // Pengesahan Kontrak
    $nextCheckpointId = 5;   // Pengiriman Material

    // Update progress CP4
    $progress = ProcurementProgress::firstOrNew([
        'procurement_id' => $procurement->procurement_id,
        'checkpoint_id'  => $sekdirCheckpointId,
    ]);

    if ($request->action === 'approve') {
        $progress->status = 'completed';

        // Buat / update CP5
        ProcurementProgress::updateOrCreate(
            [
                'procurement_id' => $procurement->procurement_id,
                'checkpoint_id' => $nextCheckpointId,
            ],
            [
                'status' => 'in_progress',
                'start_date' => now(),
            ]
        );

        $message = "Pengadaan {$procurement->code_procurement} disetujui dan masuk Pengiriman Material.";
    } else {
        $progress->status = 'rejected';
        $message = "Pengadaan {$procurement->code_procurement} ditolak.";
    }

    $progress->save();

    // Tambahan: simpan notes/link di procurement
    $procurement->update([
        'procurement_link' => $request->procurement_link,
        'notes' => $request->notes
    ]);

    ActivityLogger::log(
        module: 'Procurement',
        action: $request->action === 'approve' ? 'approve_procurement' : 'reject_procurement',
        targetId: $procurement->procurement_id,
        details: [
            'user_id' => Auth::id(),
            'decision' => $request->action,
            'checkpoint' => 4,
            'link' => $request->procurement_link,
            'notes' => $request->notes,
        ]
    );

    return redirect()->route('sekdir.approval')->with('success', $message);
}

}