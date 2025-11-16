<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RequestProcurement;
use Illuminate\Http\Request;

class SekdirController extends Controller
{
    /**
     * Sekretaris Direktur Dashboard - overview stats
     */
    public function dashboard()
    {
        // Get stats for dashboard
        $stats = [
            'total_pengadaan' => Project::count(),
            'menunggu_persetujuan' => Project::where('status_project', 'persetujuan_sekretaris')->count(),
            'dalam_proses' => Project::where('status_project', '!=', 'persetujuan_sekretaris')
                ->where('status_project', '!=', 'selesai')
                ->where('status_project', '!=', 'rejected')
                ->count(),
            'selesai' => Project::where('status_project', 'selesai')->count(),
            'ditolak' => Project::where('status_project', 'rejected')->count(),
        ];

        // Get recent projects
        $recentProjects = Project::with(['ownerDivision', 'contracts.vendor'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('sekdir.dashboard', compact('stats', 'recentProjects'));
    }

    /**
     * Persetujuan Pengadaan - list requests awaiting approval
     */
    public function approvals()
    {
        // Get projects awaiting secretary director approval
        $approvals = Project::with(['ownerDivision', 'contracts.vendor'])
            ->where('status_project', 'persetujuan_sekretaris')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Stats for this page
        $stats = [
            'menunggu_total' => Project::where('status_project', 'persetujuan_sekretaris')->count(),
            'menunggu_hari_ini' => Project::where('status_project', 'persetujuan_sekretaris')
                ->whereDate('created_at', \Carbon\Carbon::today())
                ->count(),
        ];

        return view('sekdir.approvals', compact('approvals', 'stats'));
    }

    /**
     * Approve a project request
     */
    public function approve(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        // Validate
        $request->validate([
            'notes' => 'nullable|string',
            'approval_decision' => 'required|in:approved,rejected',
        ]);

        if ($request->approval_decision === 'approved') {
            // Move to next stage (pemilihan_vendor)
            $project->update([
                'status_project' => 'pemilihan_vendor',
            ]);
            $message = 'Project approved and moved to vendor selection stage.';
        } else {
            // Reject project
            $project->update([
                'status_project' => 'rejected',
            ]);
            $message = 'Project rejected.';
        }

        return redirect()->route('sekdir.approvals')
            ->with('success', $message);
    }

    /**
     * View detail of a project for approval
     */
    public function approvalDetail($projectId)
    {
        $project = Project::with([
            'ownerDivision',
            'contracts.vendor',
            'approvals',
        ])->findOrFail($projectId);

        return view('sekdir.approval-detail', compact('project'));
    }
}
