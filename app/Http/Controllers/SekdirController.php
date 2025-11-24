<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Project;
use App\Models\RequestProcurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Checkpoint;

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
     * Halaman approval untuk sekretaris_direksi
     */
    public function approval()
    {
        // Ambil procurement yang status PROJECT-nya menunggu sekretaris
        $procurements = Procurement::with([
            'project.ownerDivision',
            'project.contracts.vendor',
            'requestProcurements.vendor'

        ])
            ->whereHas('project', function ($q) {
                $q->where('status_project', 'persetujuan_sekretaris');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Statistik
        $stats = [
            'total' => Project::count(),
            'pending' => Project::where('status_project', 'persetujuan_sekretaris')->count(),
            'approved' => Project::where('status_project', 'pemilihan_vendor')->count(),
            'rejected' => Project::where('status_project', 'rejected')->count(),
        ];

        return view('sekdir.approval', compact('procurements', 'stats'));
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
            'document_link' => 'required|url',
            'notes' => 'nullable|string',
            'approval_decision' => 'required|in:approved,rejected',
        ]);

        if ($request->approval_decision === 'approved') {
            // Move to next stage (pemilihan_vendor)
            $project->update([
                'status_project' => 'pemilihan_vendor',
                'approval_document_link' => $request->document_link,
                'approval_notes' => $request->notes,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);
            $message = 'Project berhasil disetujui dan dipindahkan ke tahap pemilihan vendor.';
        } else {
            // Reject project
            $project->update([
                'status_project' => 'rejected',
                'approval_document_link' => $request->document_link,
                'approval_notes' => $request->notes,
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
            ]);
            $message = 'Project ditolak.';
        }


        return redirect()->route('sekdir.approval')
            ->with('success', $message);
    }

    /**
     * View detail of a project for approval
     */
    public function approvalDetail($procurementId)
    {
        $procurement = Procurement::with([
            'project.ownerDivision',
            'project.contracts.vendor',
            'department',
            'requestProcurements.items'
        ])->findOrFail($procurementId);

        $checkpoints = Checkpoint::orderBy('point_sequence')->get();

        return view('sekdir.approval-detail', compact(
            'procurement',
            'checkpoints',

        ));;
    }

public function approvalSubmit(Request $request, $projectId)
{
    // validasi
    $request->validate([
        'procurement_link' => 'required|url',
        'notes' => 'nullable|string',
    ]);

    $procurement = Procurement::where('project_id', $projectId)->firstOrFail();

    // simpan link dan catatan
    $procurement->procurement_link = $request->procurement_link;
    $procurement->notes = $request->notes;

    // jika procurement_link terisi → status completed
    if (!empty($request->procurement_link)) {
        $procurement->status_procurement = 'completed';
    } else {
        $procurement->status_procurement = 'reviewed';
    }

    $procurement->save();

    return redirect()
        ->route('sekdir.approval')
        ->with('success', 'Review berhasil disimpan!');
}

}
