<?php

namespace App\Http\Controllers;

use App\Models\InspectionReport;
use App\Models\NcrReport;
use App\Models\Project;
use App\Models\Item;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    /**
     * Display inspection reports
     */
    public function index()
    {
        $inspections = InspectionReport::with(['project', 'item', 'inspector'])
            ->orderBy('inspection_date', 'desc')
            ->paginate(20);

        return view('inspections.index', compact('inspections'));
    }

    /**
     * Show form to create inspection
     */
    public function create($projectId)
    {
        $project = Project::with(['requestProcurements.items'])->findOrFail($projectId);
        return view('inspections.create', compact('project'));
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
            'result' => 'required|in:passed,failed,conditional',
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

            // Update project status based on inspection result
            $project = Project::find($validated['project_id']);

            if ($validated['result'] === 'passed') {
                $project->update(['status_project' => 'verifikasi_dokumen']);

                // Notify Accounting for document verification
                $this->notifyAccounting($project, 'Inspeksi barang passed, verifikasi dokumen diperlukan');
            } elseif ($validated['result'] === 'failed') {
                // Create NCR if needed
                if ($inspection->ncr_required) {
                    $this->createNcrReport($inspection);
                }
            }
        });

        return redirect()->route('inspections.index')
            ->with('success', 'Laporan inspeksi berhasil dibuat');
    }

    /**
     * Show inspection report
     */
    public function show($id)
    {
        $inspection = InspectionReport::with([
            'project',
            'item',
            'inspector',
            'ncrReports'
        ])->findOrFail($id);

        return view('inspections.show', compact('inspection'));
    }

    /**
     * Update inspection report
     */
    public function update(Request $request, $id)
    {
        $inspection = InspectionReport::findOrFail($id);

        $validated = $request->validate([
            'result' => 'required|in:passed,failed,conditional',
            'findings' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $inspection->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Laporan inspeksi berhasil diupdate'
        ]);
    }

    /**
     * Create NCR report
     */
    private function createNcrReport($inspection)
    {
        $ncr = NcrReport::create([
            'inspection_id' => $inspection->inspection_id,
            'project_id' => $inspection->project_id,
            'item_id' => $inspection->item_id,
            'ncr_date' => now(),
            'nonconformance_description' => $inspection->findings ?? 'Material tidak sesuai spesifikasi',
            'severity' => 'major',
            'status' => 'open',
            'created_by' => $inspection->inspector_id,
        ]);

        // Notify Supply Chain
        $scUsers = \App\Models\User::where('roles', 'supply_chain')->get();
        foreach ($scUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'sender_id' => $inspection->inspector_id,
                'type' => 'ncr_created',
                'title' => 'NCR Baru',
                'message' => 'NCR telah dibuat untuk proyek: ' . $inspection->project->name_project . ' (NCR: ' . $ncr->ncr_number . ')',
                'reference_type' => 'App\Models\NcrReport',
                'reference_id' => $ncr->ncr_id,
            ]);
        }

        return $ncr;
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
        ])->orderBy('ncr_date', 'desc')
            ->paginate(20);

        return view('inspections.ncr_reports', compact('ncrReports'));
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
        ])->findOrFail($id);

        return view('inspections.ncr_show', compact('ncr'));
    }

    /**
     * Update NCR report
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

        // If status is resolved, notify QA for verification
        if ($validated['status'] === 'resolved') {
            $qaUsers = \App\Models\User::where('roles', 'qa')->get();
            foreach ($qaUsers as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'sender_id' => Auth::id(),
                    'type' => 'ncr_verification',
                    'title' => 'Verifikasi NCR Diperlukan',
                    'message' => 'NCR ' . $ncr->ncr_number . ' telah diselesaikan dan menunggu verifikasi',
                    'reference_type' => 'App\Models\NcrReport',
                    'reference_id' => $ncr->ncr_id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'NCR berhasil diupdate'
        ]);
    }

    /**
     * Verify NCR closure
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

            $message = 'NCR berhasil diverifikasi dan ditutup';
        } else {
            $ncr->update([
                'status' => 'in_progress',
            ]);

            $message = 'NCR ditolak, perlu tindakan lebih lanjut';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Notify Accounting team
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
