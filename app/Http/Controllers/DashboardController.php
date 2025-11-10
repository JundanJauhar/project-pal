<?php

namespace App\Http\Controllers;

use App\Models\Project;
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

        // Get statistics
        $stats = [
            'total_pengadaan' => Project::count(),
            'sedang_proses' => Project::where('status_project', 'in_progress')->count(),
            'selesai' => Project::where('status_project', 'completed')->count(),
            'ditolak' => Project::where('status_project', 'rejected')->count(),
        ];

        // Get recent projects based on user role
        $projects = $this->getProjectsByRole($user);

        // Get unread notifications
        $notifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'projects', 'notifications'));
    }

    /**
     * Get projects based on user role
     */
    private function getProjectsByRole($user)
    {
        $query = Project::with(['ownerDivision', 'contracts']);

        // Filter based on role
        switch ($user->roles) {
            case 'user':
                // User only sees projects from their division
                $query->where('owner_division_id', $user->division_id);
                break;

            case 'supply_chain':
            case 'treasury':
            case 'accounting':
            case 'qa':
            case 'sekretaris_direksi':
                // These roles can see all projects
                break;

            default:
                $query->where('owner_division_id', $user->division_id);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get dashboard data for specific division
     */
    public function divisionDashboard($divisionId)
    {
        $user = Auth::user();

        // Check if user has access to this division
        if ($user->roles !== 'supply_chain' && $user->division_id != $divisionId) {
            abort(403, 'Unauthorized access to division dashboard');
        }

        $projects = Project::where('owner_division_id', $divisionId)
            ->with(['ownerDivision', 'contracts'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.division', compact('projects'));
    }

    /**
     * Get project statistics by status
     */
    public function getStatistics()
    {
        $statusStats = Project::selectRaw('status_project, COUNT(*) as count')
            ->groupBy('status_project')
            ->get();

        $priorityStats = Project::selectRaw('priority, COUNT(*) as count')
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
    public function getProcurementTimeline($projectId)
    {
        $progress = ProcurementProgress::where('permintaan_pengadaan_id', $projectId)
            ->with('checkpoint')
            ->orderBy('titik_id')
            ->get();

        return response()->json($progress);
    }
}
