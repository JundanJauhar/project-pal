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
            'sedang_proses' => Procurement::where('status_procurement', 'in_progress')->count(),
            'selesai' => Procurement::where('status_procurement', 'completed')->count(),
            'ditolak' => Procurement::where('status_procurement', 'rejected')->count(),
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
        $query = Procurement::with(['department', 'requestProcurements.vendor']);

        // Filter based on role
        switch ($user->roles) {
            case 'user':
                // User only sees procurements from their division/department
                $query->where('department_procurement', $user->division_id);
                break;

            case 'supply_chain':
            case 'treasury':
            case 'accounting':
            case 'qa':
            case 'sekretaris':
            case 'desain':
                // These roles can see all procurements
                break;

            default:
                $query->where('department_procurement', $user->division_id);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
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
            ->with(['department', 'requestProcurements.vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

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
