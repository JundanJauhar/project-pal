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

class DashboardController extends Controller
{
    /**
     * Display dashboard overview
     */
    public function index()
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

        // Get recent procurements based on user role (paginated)
        $procurements = $this->getProcurementsByRole($user);

        // Get unread notifications (jika ada)
        $notifications = [];
        if (class_exists('App\Models\Notification')) {
            $notifications = Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        return view('dashboard.index', compact('stats', 'procurements', 'notifications', 'checkpoints', 'projects', 'priority'));
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

        return $query->orderBy('start_date', 'desc')
            ->paginate(20);
    }

    /**
     * Search procurements with filters
     */
    public function search(Request $request)
    {
        $query = Procurement::with([
            'project',
            'department', 
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ]);
        
        // Search filter (q parameter)
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name_procurement', 'like', '%' . $searchTerm . '%')
                  ->orWhere('code_procurement', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        } 
        
        // Project filter
        if ($request->filled('project')) {
            $query->whereHas('project', function ($q) use ($request) {
                $q->where('project_code', $request->project);
            });
        }
        
        // Get all data first (with eager loading)
        $allProcurements = $query->orderBy('created_at', 'desc')->get();
        
        // Filter by checkpoint AFTER data is loaded
        if ($request->filled('checkpoint')) {
            $checkpointFilter = $request->checkpoint;
            
            $allProcurements = $allProcurements->filter(function($p) use ($checkpointFilter) {
                // Handle special statuses
                if ($checkpointFilter === 'completed') {
                    return $p->status_procurement === 'completed';
                }
                
                if ($checkpointFilter === 'cancelled') {
                    return $p->status_procurement === 'cancelled';
                }
                
                // Filter by current checkpoint name for in_progress items
                $service = new CheckpointTransitionService($p);
                $currentCheckpoint = $service->getCurrentCheckpoint();
                $currentCheckpointName = $currentCheckpoint ? $currentCheckpoint->point_name : '-';
                
                return $p->status_procurement === 'in_progress' 
                    && $currentCheckpointName === $checkpointFilter;
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
        
        // Transform data untuk JSON response
        $data = $procurements->map(function($p) {
            $service = new CheckpointTransitionService($p);
            $currentCheckpoint = $service->getCurrentCheckpoint();
            $checkpointName = $currentCheckpoint ? $currentCheckpoint->point_name : '-';

            return [
                'procurement_id' => $p->procurement_id,
                'project_code' => $p->project->project_code ?? '-',
                'code_procurement' => $p->code_procurement,
                'name_procurement' => $p->name_procurement,
                'department_name' => $p->department->department_name ?? '-',
                'start_date' => $p->start_date ? $p->start_date->format('d/m/Y') : '-',
                'end_date' => $p->end_date ? $p->end_date->format('d/m/Y') : '-',
                'vendor_name' => $p->requestProcurements->first()?->vendor->name_vendor ?? '-',
                'priority' => $p->priority ?? 'rendah',
                'status_procurement' => $p->status_procurement,
                'current_checkpoint' => $checkpointName,
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
}