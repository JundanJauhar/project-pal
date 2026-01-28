<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Division;
use App\Models\Role;
use App\Models\Procurement;
use App\Models\UMS\ActivityLog;
use App\Models\UMS\AuditLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ================= KPI =================
        $totalUsers     = User::count();
        $activeUsers    = User::where('status', 'active')->count();
        $totalDivisions = Division::count();
        $totalRoles     = Role::count();

        // ================= PROCUREMENT STATS =================
        $totalProcurements      = Procurement::count();
        $activeProcurements     = Procurement::where('status_procurement', 'in_progress')->count();
        $completedProcurements  = Procurement::where('status_procurement', 'completed')->count();

        // ================= RECENT ACTIVITY =================
        $recentActivities = ActivityLog::with('actor')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // ================= RECENT SECURITY EVENTS (AUDIT) =================
        $recentAudits = AuditLog::with('actor')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // ================= LOGIN ACTIVITY (LAST 7 DAYS) =================
        $loginStats = AuditLog::where('action', 'like', '%login%')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ================= MODULE OVERVIEW =================
        $moduleOverview = [
            [
                'name'   => 'User Management',
                'status' => 'Active',
                'info'   => $totalUsers . ' users',
            ],
            [
                'name'   => 'Divisi Management',
                'status' => 'Active',
                'info'   => $totalDivisions . ' divisions',
            ],
            [
                'name'   => 'Procurement',
                'status' => 'Active',
                'info'   => $activeProcurements . ' in progress',
            ],
            [
                'name'   => 'Audit Logs',
                'status' => 'Active',
                'info'   => 'Security tracking',
            ],
            [
                'name'   => 'Activity Logs',
                'status' => 'Active',
                'info'   => 'Operational logs',
            ],
            [
                'name'   => 'Sessions Monitoring',
                'status' => 'Active',
                'info'   => 'Live sessions',
            ],
            [
                'name'   => 'System Settings',
                'status' => 'Active',
                'info'   => 'Configuration',
            ],
        ];

        return view('ums.dashboard.index', compact(
            'totalUsers',
            'activeUsers',
            'totalDivisions',
            'totalRoles',
            'totalProcurements',
            'activeProcurements',
            'completedProcurements',
            'recentActivities',
            'recentAudits',
            'loginStats',
            'moduleOverview'
        ));
    }
}
