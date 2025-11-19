<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\NcrReport;
use App\Models\Project;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    /**
     * Display inspection reports (LIST)
     */
    public function index()
    {
        $inspections = InspectionReport::with([
            'project',
            'project.department',
            'vendor',
        ])
        ->orderBy('inspection_date', 'desc')
        ->paginate(20);

        // View kamu berada di folder: resources/views/qa/inspections.blade.php
        return view('qa.inspections', compact('inspections'));
    }


    /**
     * DISABLED — show detail (tidak dipakai)
     */
    public function show($id)
    {
        abort(404); // Karena tidak digunakan
    }


    /**
     * Store inspection report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'item_id' => 'nullable|exists:items,item_id',
            'inspection_date' => 'required|date',
            'result' => 'required|in:passed,failed,conditional,pending',
            'findings' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ncr_required' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $request) {

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('inspection_reports', 'public');
            }

            $inspection = InspectionReport::create([
                'project_id' => $validated['project_id'],
                'item_id' => $validated['item_id'],
                'inspection_date' => $validated['inspection_date'],
                'inspector_id' => Auth::id(),
                'result' => $validated['result'],
                'findings' => $validated['findings'],
                'notes' => $validated['notes'],
                'attachment_path' => $attachmentPath,
                'ncr_required' => $validated['ncr_required'] ?? ($validated['result'] === 'failed'),
            ]);

            // Update status proyek & NCR
            $project = Project::find($validated['project_id']);

            if ($validated['result'] === 'passed') {
                $project->update(['status_project' => 'verifikasi_dokumen']);
                $this->notifyAccounting($project, 'Inspeksi barang passed, verifikasi dokumen diperlukan');
            }

            if ($validated['result'] === 'failed' && $inspection->ncr_required) {
                $this->createNcrReport($inspection);
            }
        });

        return redirect()->route('inspections.index')
            ->with('success', 'Laporan inspeksi berhasil dibuat');
    }


    /**
     * Create NCR report
     */
    private function createNcrReport($inspection)
    {
        return NcrReport::create([
            'inspection_id' => $inspection->inspection_id,
            'project_id' => $inspection->project_id,
            'item_id' => $inspection->item_id,
            'ncr_date' => now(),
            'nonconformance_description' => $inspection->findings ?? 'Material tidak sesuai spesifikasi',
            'severity' => 'major',
            'status' => 'open',
            'created_by' => $inspection->inspector_id,
        ]);
    }


    /**
     * View NCR reports
     */
    public function ncrReports()
    {
        $ncrReports = NcrReport::with([
            'inspection',
            'project',
            'item',
            'creator',
            'assignedUser'
        ])
        ->orderBy('ncr_date', 'desc')
        ->paginate(20);

        return view('qa.ncr_reports', compact('ncrReports'));
    }


    /**
     * Show NCR report
     */
    public function showNcr($id)
    {
        $ncr = NcrReport::with([
            'inspection',
            'project',
            'item',
            'creator',
            'verifier',
            'assignedUser'
        ])
        ->findOrFail($id);

        return view('qa.ncr_show', compact('ncr'));
    }


    /**
     * Update NCR
     */
    public function updateNcr(Request $request, $id)
    {
        $ncr = NcrReport::findOrFail($id);

        $validated = $request->validate([
            'root_cause' => 'nullable|string',
            'corrective_action' => 'nullable|string',
            'preventive_action' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'target_completion_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date',
            'status' => 'required|in:open,in_progress,resolved,verified,closed',
        ]);

        $ncr->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'NCR berhasil diupdate'
        ]);
    }


    /**
     * Verify NCR
     */
    public function verifyNcr(Request $request, $id)
    {
        $ncr = NcrReport::findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        if ($validated['action'] === 'approve') {
            $ncr->update([
                'status' => 'closed',
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);
        } else {
            $ncr->update(['status' => 'in_progress']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status NCR berhasil diperbarui'
        ]);
    }


    /**
     * Notify accounting
     */
    private function notifyAccounting($project, $message)
    {
        $accountingUsers = \App\Models\User::where('roles', 'accounting')->get();

        foreach ($accountingUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'sender_id' => Auth::id(),
                'type' => 'document_verification',
                'title' => 'Verifikasi Dokumen',
                'message' => $message . ' - Proyek: ' . $project->name_project,
                'reference_type' => 'App\Models\Project',
                'reference_id' => $project->project_id,
            ]);
        }
    }
}
