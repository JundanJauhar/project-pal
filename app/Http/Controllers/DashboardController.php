<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Procurement;
use App\Models\ProcurementProgress;
use App\Models\Notification;
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

        // Get statistics from Procurement table
        $stats = [
            'total_pengadaan' => Procurement::count(),
            'sedang_proses' => Procurement::get()->filter(fn($p) => $p->auto_status === 'in_progress')->count(),
            'selesai' => Procurement::get()->filter(fn($p) => $p->auto_status === 'completed')->count(),
            'ditolak' => Procurement::get()->filter(fn($p) => $p->auto_status === 'rejected')->count(),
        ];

        // Get recent procurements based on user role
        $procurements = $this->getProcurementsByRole($user);

        // Get unread notifications
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'procurements', 'notifications'));
    }

    /**
     * Get procurements based on user role
     */
    private function getProcurementsByRole($user)
    {
        $query = Procurement::with([
            'project',
            'department', 
            'requestProcurements' => function($query) {
                // Eager load vendor dengan benar
                $query->with('vendor');
            },
            'procurementProgress.checkpoint'
        ]);

        return $query->orderBy('start_date', 'desc')
            ->paginate(10);
    }

    /**
     * Search procurements with filters
     */
    public function search(Request $request)
    {
        $query = Procurement::with([
            'project',
            'department', 
            'requestProcurements' => function($query) {
                $query->with('vendor');
            },
            'procurementProgress.checkpoint'
        ]);
        
        // Search filter
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
        
        if ($request->filled('project')) {
            $query->whereHas('project', function ($q) use ($request) {
                $q->where('project_code', $request->project);
            });
        }
        
        // Ambil semua data dulu (dengan eager loading)
        $allProcurements = $query->orderBy('start_date', 'desc')->get();
        
        // Filter berdasarkan checkpoint SETELAH data di-load
        if ($request->filled('checkpoint')) {
            $checkpoint = $request->checkpoint;
            
            $allProcurements = $allProcurements->filter(function($p) use ($checkpoint) {
                // Handle special statuses
                if ($checkpoint === 'not_started') {
                    return $p->auto_status === 'not_started';
                }
                
                if ($checkpoint === 'completed') {
                    return $p->auto_status === 'completed';
                }
                
                if ($checkpoint === 'rejected') {
                    return $p->auto_status === 'rejected';
                }
                
                // Filter by current checkpoint name
                return $p->auto_status === 'in_progress' 
                    && $p->current_checkpoint === $checkpoint;
            });
        }
        
        // Manual pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $total = $allProcurements->count();
        $lastPage = ceil($total / $perPage);
        
        $procurements = $allProcurements
            ->slice(($page - 1) * $perPage, $perPage)
            ->values(); // Reset array keys
        
        // Transform data
        $data = $procurements->map(function($p) {
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
                'auto_status' => $p->auto_status,
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
     * Get dashboard data for specific department
     */
    public function departmentDashboard($departmentId)
    {
        $user = Auth::user();

        // Check if user has access to this department
        if ($user->roles !== 'supply_chain' && $user->division_id != $departmentId) {
            abort(403, 'Unauthorized access to department dashboard');
        }

        $procurements = Procurement::where('department_procurement', $departmentId)
            ->with([
                'department', 
                'requestProcurements' => function($query) {
                    $query->with('vendor');
                }
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
        $statusStats = Procurement::selectRaw('status_procurement, COUNT(*) as count')
            ->groupBy('status_procurement')
            ->get();

        $priorityStats = Procurement::selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get();

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
        $progress = ProcurementProgress::where('permintaan_pengadaan_id', $procurementId)
            ->with('checkpoint')
            ->orderBy('titik_id')
            ->get();

        return response()->json($progress);
    }
}