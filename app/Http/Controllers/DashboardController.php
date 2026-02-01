<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Project;
use App\Models\Procurement;
use App\Models\RequestProcurement;
use App\Models\Department;
use App\Models\ProcurementProgress;
use App\Models\Notification;
use App\Services\CheckpointTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityLogger;

class DashboardController extends Controller
{
    /**
     * Display dashboard overview (READ-ONLY & GLOBAL)
     * 
     * Prinsip Dashboard:
     * - GLOBAL: semua procurement dapat dilihat oleh semua user
     * - READ-ONLY: tidak ada action create/edit/delete di dashboard
     * - NO AUTHORIZATION: tidak ada pembatasan division atau role
     * - ROLE ONLY UNTUK: aksi (create/edit/approve), bukan visibility
     * 
     * Flow:
     * 1. Load ALL procurement (tanpa filter divisi/role)
     * 2. Vendor user → filter by vendor_id (untuk kenyamanan, bukan security)
     * 3. Hitung statistik global
     * 4. Tampilkan data dengan eager loading untuk performa
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        $checkpoints = Checkpoint::all();
        $projects = Project::all();
        $departments = Department::all();
        $priority = Procurement::select('priority')->distinct()->get();

        // === GLOBAL PROCUREMENT QUERY (NO AUTHORIZATION FILTER) ===
        // Semua user bisa lihat semua procurement
        $allProcurementsQuery = Procurement::with([
            'project',
            'department',
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ]);

        // === OPTIONAL VENDOR FILTER (UX convenience, bukan security) ===
        // Jika user adalah vendor, tampilkan hanya procurement-nya untuk kenyamanan
        // Tapi ini bukan pembatasan keamanan (user bisa lihat semua jika mau)
        if ($user->hasRole('vendor')) {
            $allProcurementsQuery->whereHas('requestProcurements', function ($query) use ($user) {
                $query->where('vendor_id', $user->vendor_id);
            });
        }

        $allProcurements = $allProcurementsQuery->get();

        // === CALCULATE STATISTICS (GLOBAL, NO FILTER) ===
        $stats = [
            'total_pengadaan' => $allProcurements->count(),
            'sedang_proses' => $allProcurements->where('status_procurement', 'in_progress')->count(),
            'selesai' => $allProcurements->where('status_procurement', 'completed')->count(),
            'ditolak' => $allProcurements->where('status_procurement', 'cancelled')->count(),
        ];

        // Get recent procurements for display (paginated)
        $procurements = $this->getProcurementsForDisplay($user);

        // Get unread notifications
        $notifications = [];
        if (class_exists('App\Models\Notification')) {
            $notifications = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('dashboard.index', compact('stats', 'procurements', 'notifications', 'checkpoints', 'projects', 'priority', 'departments'));
    }

    /**
     * Get procurements untuk display (READ-ONLY)
     * 
     * Logic:
     * - Semua user lihat semua procurement
     * - Vendor user: optional filter untuk kenyamanan (bukan security)
     * - NO role-based filtering
     */
    private function getProcurementsForDisplay($user)
    {
        $query = Procurement::with([
            'project',
            'department',
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ]);

        // === OPTIONAL VENDOR FILTER (UX convenience only) ===
        if ($user->hasRole('vendor')) {
            $query->whereHas('requestProcurements', function ($q) use ($user) {
                $q->where('vendor_id', $user->vendor_id);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    /**
     * Search procurements (READ-ONLY & GLOBAL)
     * 
     * Prinsip:
     * - Semua user bisa search semua procurement
     * - Filter: keyword, priority, project, checkpoint (business filter, bukan authorization)
     * - Vendor filter: optional convenience untuk vendor user
     * - NO division/role-based query filtering
     */
    public function search(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        $query = Procurement::with([
            'project',
            'department',
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ]);

        // === OPTIONAL VENDOR FILTER (UX convenience only) ===
        if ($user->hasRole('vendor')) {
            $query->whereHas('requestProcurements', function ($q) use ($user) {
                $q->where('vendor_id', $user->vendor_id);
            });
        }

        // === FILTER PENCARIAN (Business logic, tidak ada authorization) ===

        // Search by keyword
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name_procurement', 'like', "%$searchTerm%")
                    ->orWhere('code_procurement', 'like', "%$searchTerm%");
            });
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department_procurement', $request->department);
        }


        // Filter by project
        if ($request->filled('project')) {
            $query->whereHas('project', function ($q) use ($request) {
                $q->where('project_code', $request->project);
            });
        }

        // Load all matching records
        $allProcurements = $query->orderBy('created_at', 'desc')->get();

        // Filter by checkpoint (setelah load, karena pakai accessor)
        if ($request->filled('checkpoint')) {
            $checkpointFilter = $request->checkpoint;

            $allProcurements = $allProcurements->filter(function ($p) use ($checkpointFilter) {
                if ($checkpointFilter === 'completed') {
                    return $p->status_procurement === 'completed';
                }

                if ($checkpointFilter === 'cancelled') {
                    return $p->status_procurement === 'cancelled';
                }

                return $p->status_procurement === 'in_progress'
                    && ($p->current_checkpoint === $checkpointFilter);
            });
        }

        // === PAGINATION MANUAL ===
        $page = $request->get('page', 1);
        $perPage = 10;
        $total = $allProcurements->count();
        $lastPage = $total > 0 ? ceil($total / $perPage) : 1;

        $procurements = $allProcurements
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        // Log activity (info saja, tanpa security context)
        ActivityLogger::log(
            module: 'Dashboard',
            action: 'search_procurement',
            targetId: null,
            details: [
                'search' => $request->q ?? null,
                'priority' => $request->priority ?? null,
                'project' => $request->project ?? null,
                'checkpoint' => $request->checkpoint ?? null,
                'department' => $request->department ?? null,
                'page' => $page,
                'user_id' => $user->id,
                'has_vendor_filter' => $user->hasRole('vendor')
            ]
        );

        // Format response
        $data = $procurements->map(function ($p) {
            return [
                'procurement_id' => $p->procurement_id,
                'project_code' => $p->project->project_code ?? '-',
                'code_procurement' => $p->code_procurement,
                'name_procurement' => $p->name_procurement,
                'department_name' => $p->department->department_name ?? '-',
                'start_date' => optional($p->start_date)->format('d/m/Y'),
                'end_date' => optional($p->end_date)->format('d/m/Y'),
                'vendor_name' => $p->requestProcurements->first()?->vendor?->name_vendor ?? '-',
                'priority' => $p->priority,
                'status_procurement' => $p->status_procurement,
                'current_checkpoint' => $p->current_checkpoint,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => $page < $lastPage,
            ]
        ]);
    }

    /**
     * Get dashboard data untuk department tertentu (READ-ONLY & GLOBAL)
     * 
     * Prinsip:
     * - Semua user bisa lihat procurement dari department mana saja
     * - Department filter = UI categorization, bukan authorization
     * - NO division-based access control
     */
    public function departmentDashboard($departmentId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        $query = Procurement::where('department_id', $departmentId)
            ->with([
                'project',
                'department',
                'requestProcurements.vendor',
                'procurementProgress.checkpoint'
            ]);

        // === OPTIONAL VENDOR FILTER (UX convenience only) ===
        if ($user->hasRole('vendor')) {
            $query->whereHas('requestProcurements', function ($q) use ($user) {
                $q->where('vendor_id', $user->vendor_id);
            });
        }

        $procurements = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.department', compact('procurements'));
    }

    /**
     * Get procurement statistics by status (READ-ONLY & GLOBAL)
     * 
     * Prinsip:
     * - Menampilkan statistik GLOBAL untuk semua procurement
     * - Semua user lihat angka yang sama
     * - NO division filtering
     */
    public function getStatistics()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === GLOBAL QUERY (NO FILTER) ===
        $query = Procurement::with(['procurementProgress.checkpoint']);

        // === OPTIONAL VENDOR FILTER (UX convenience only) ===
        if ($user->hasRole('vendor')) {
            $query->whereHas('requestProcurements', function ($q) use ($user) {
                $q->where('vendor_id', $user->vendor_id);
            });
        }

        $allProcurements = $query->get();

        // Status statistics
        $statusStats = [
            [
                'status' => 'in_progress',
                'label' => 'Sedang Proses',
                'count' => $allProcurements->where('status_procurement', 'in_progress')->count()
            ],
            [
                'status' => 'completed',
                'label' => 'Selesai',
                'count' => $allProcurements->where('status_procurement', 'completed')->count()
            ],
            [
                'status' => 'cancelled',
                'label' => 'Dibatalkan',
                'count' => $allProcurements->where('status_procurement', 'cancelled')->count()
            ],
        ];

        // Priority statistics (GLOBAL)
        $priorityStats = Procurement::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->map(function ($item) {
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
     * Get procurement timeline progress (READ-ONLY)
     * 
     * Prinsip:
     * - Semua user bisa lihat timeline dari procurement mana saja
     * - NO authorization check untuk divisi/role
     */
    public function getProcurementTimeline($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === NO AUTHORIZATION CHECK - Semua user bisa lihat ===
        $procurement = Procurement::with(['procurementProgress.checkpoint'])
            ->where('procurement_id', $procurementId)
            ->firstOrFail();

        // Get timeline progress
        $progress = $procurement->procurementProgress()
            ->with(['checkpoint', 'user'])
            ->orderBy('checkpoint_id')
            ->get()
            ->map(function ($p) {
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

        // Log activity (info only, bukan security check)
        ActivityLogger::log(
            module: 'Dashboard',
            action: 'view_procurement_timeline',
            targetId: $procurementId,
            details: [
                'user_id' => $user->id,
            ]
        );

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
     * Get procurements by project (READ-ONLY & GLOBAL)
     * 
     * Prinsip:
     * - Semua user bisa lihat procurement dari project mana saja
     * - NO division-based filtering
     */
    public function byProject($projectCode)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        $project = Project::where('project_code', $projectCode)->firstOrFail();

        $query = Procurement::where('project_id', $project->project_id)
            ->with([
                'department',
                'requestProcurements.vendor',
                'procurementProgress.checkpoint'
            ]);

        // === OPTIONAL VENDOR FILTER (UX convenience only) ===
        if ($user->hasRole('vendor')) {
            $query->whereHas('requestProcurements', function ($q) use ($user) {
                $q->where('vendor_id', $user->vendor_id);
            });
        }

        $procurements = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Count stats (GLOBAL)
        $statsQuery = Procurement::where('project_id', $project->project_id);

        // Apply vendor filter untuk stats juga (consistency)
        if ($user->hasRole('vendor')) {
            $statsQuery->whereHas('requestProcurements', function ($q) use ($user) {
                $q->where('vendor_id', $user->vendor_id);
            });
        }

        $stats = [
            'total' => $procurements->total(),
            'in_progress' => (clone $statsQuery)->where('status_procurement', 'in_progress')->count(),
            'completed' => (clone $statsQuery)->where('status_procurement', 'completed')->count(),
            'cancelled' => (clone $statsQuery)->where('status_procurement', 'cancelled')->count(),
        ];

        ActivityLogger::log(
            module: 'Dashboard',
            action: 'view_procurements_by_project',
            targetId: $project->project_id,
            details: [
                'project_code' => $projectCode,
                'user_id' => $user->id,
            ]
        );

        return view('procurements.by-project', compact('project', 'procurements', 'stats'));
    }

    /**
     * Get checkpoint distribution untuk analytics charts (READ-ONLY & GLOBAL)
     * 
     * Prinsip:
     * - Menampilkan distribusi GLOBAL
     * - Semua user lihat data yang sama
     */
    public function getCheckpointDistribution()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        $query = Procurement::where('status_procurement', 'in_progress')
            ->with(['procurementProgress.checkpoint']);

        // === OPTIONAL VENDOR FILTER (UX convenience only) ===
        if ($user->hasRole('vendor')) {
            $query->whereHas('requestProcurements', function ($q) use ($user) {
                $q->where('vendor_id', $user->vendor_id);
            });
        }

        $procurements = $query->get();

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

        $result = collect($distribution)->map(function ($count, $checkpoint) {
            return [
                'checkpoint' => $checkpoint,
                'count' => $count
            ];
        })->values();

        return response()->json($result);
    }
}
