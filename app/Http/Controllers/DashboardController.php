<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProcurementProgress;
use App\Models\Procurement;
use App\Models\Vendor;
use App\Models\RequestProcurement;
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
            'total_project' => Project::count(),
            'total_procurement' => Procurement::count(),
            'total_vendor' => Vendor::count(),
            'total_requests' => RequestProcurement::count(),
        ];

        // Get recent projects
        $projects = Project::with('procurement')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent procurements
        $procurements = Procurement::with('division')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('stats', 'projects', 'procurements'));
    }

    /**
     * Get projects with procurement info
     */
    private function getProjectsByRole($user)
    {
        $query = Project::with('procurement');

        // All users can see all projects in simplified system
        // Role-based filtering can be added later if needed

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
        if ($user->role !== 'supply_chain' && $user->division_id != $divisionId) {
            abort(403, 'Unauthorized access to division dashboard');
        }

        // Get procurements related to division's department
        $procurements = Procurement::where('department_procurement', $divisionId)
        ->with('division')
        ->orderBy('created_at', 'desc')
        ->get();

        return view('dashboard.division', compact('procurements'));
    }

    /**
     * Get project statistics
     */
    public function getStatistics()
    {
        $stats = [
            'total_projects' => Project::count(),
            'total_procurements' => Procurement::count(),
            'total_vendors' => Vendor::count(),
            'vendor_importers' => Vendor::where('is_importer', true)->count(),
            'total_requests' => RequestProcurement::count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get procurement timeline progress
     */
    public function getProcurementTimeline($requestId)
    {
        $progress = ProcurementProgress::where('request_id', $requestId)
            ->with('checkpoint')
            ->orderBy('checkpoint_id')
            ->get();

        return response()->json($progress);
    }
}
