<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Project;
use App\Models\RequestProcurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Checkpoint;
use App\Models\Approval;
use App\Models\ProcurementProgress;
use App\Helpers\ActivityLogger;


class SekdirController extends Controller
{
    public function dashboard()
    {
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

        $recentProjects = Project::with(['ownerDivision'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

            ActivityLogger::log(
                module: 'Sekdir',
                action: 'view_dashboard',
                targetId: null,
                details: ['user_id' => Auth::id()]
            );

        return view('sekdir.dashboard', compact('stats', 'recentProjects'));
    }

    // ...
public function approval()
{
    // Ambil semua procurement yang sedang berada di CP4 (Pengesahan Kontrak)
    $procurements = Procurement::with([
        'project.ownerDivision',
        'requestProcurements.vendor',
        'procurementProgress.checkpoint',
    ])
    ->whereHas('procurementProgress', function ($q) {
        $q->where('checkpoint_id', 4)
          ->where('status', 'in_progress');
    })
    ->orderBy('created_at', 'desc')
    ->get();

    // Hitung statistik
    $stats = [
        'total'    => ProcurementProgress::where('checkpoint_id', 4)->distinct('procurement_id')->count('procurement_id'),
        'pending'  => $procurements->count(),
        'approved' => ProcurementProgress::where('checkpoint_id', 4)->where('status', 'completed')->count(),
        'rejected' => ProcurementProgress::where('checkpoint_id', 4)->where('status', 'rejected')->count(),
    ];

    $totalProcurements = $stats['total'];

    ActivityLogger::log(
        module: 'Sekdir',
        action: 'view_procurement_approval_list',
        targetId: null,
        details: ['user_id' => Auth::id()]
    );

    return view('sekdir.approval', compact('procurements', 'stats', 'totalProcurements'));
}

    public function approve(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $request->validate([
            'document_link' => 'required|url',
            'notes' => 'nullable|string',
            'approval_decision' => 'required|in:approved,rejected',
        ]);

        Approval::create([
            'module' => 'project',
            'module_id' => $project->project_id,
            'approver_id' => Auth::id(),
            'status' => $request->approval_decision,
            'approval_document_link' => $request->document_link,
            'approval_notes' => $request->notes,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        if ($request->approval_decision === 'approved') {
            $project->update([
                'status_project' => 'pemilihan_vendor',
                'approval_document_link' => $request->document_link,
                'approval_notes' => $request->notes,
                'approved_at' => now(),
                'approved_by' => Auth::id(),
            ]);
            $message = 'Project berhasil disetujui dan dipindahkan ke tahap pemilihan vendor.';
        } else {
            $project->update([
                'status_project' => 'rejected',
                'approval_document_link' => $request->document_link,
                'approval_notes' => $request->notes,
                'rejected_at' => now(),
                'rejected_by' => Auth::id(),
            ]);
            $message = 'Project ditolak.';
        }

        ActivityLogger::log(
            module: 'Project',
            action: $request->approval_decision === 'approved' ? 'approve_project' : 'reject_project',
            targetId: $project->project_id,
            details: [
                'user_id' => Auth::id(),
                'decision' => $request->approval_decision,
                'document_link' => $request->document_link,
                'notes' => $request->notes,
            ]
        );

        return redirect()->route('sekdir.approval')
            ->with('success', $message);
    }

 public function approvalDetail($procurementId)
{
    // Memuat Procurement dengan relasi yang diperlukan, termasuk progress-nya
    $procurement = Procurement::with([
        'project.ownerDivision', 
        'requestProcurements.items',
        'procurementProgress' // WAJIB: Memuat progress yang sudah ada
    ])->findOrFail($procurementId);

    // Memuat semua checkpoints yang berurutan
    $checkpoints = Checkpoint::orderBy('point_sequence')->get();

    // --- LOGIKA MENENTUKAN STAGE SAAT INI ---
    
    // 1. Ambil semua ID Checkpoint yang sudah 'completed' atau 'rejected'
    $completedCheckpointIds = $procurement->procurementProgress
        ->whereIn('status', ['completed', 'rejected']) // Mengambil yang sudah selesai/ditolak
        ->pluck('checkpoint_id');

    // 2. Tentukan Checkpoint ID yang sedang 'in_progress'
    $inProgressCheckpoint = $procurement->procurementProgress
        ->where('status', 'in_progress')
        ->first();
    
    $currentCheckpointId = $inProgressCheckpoint ? $inProgressCheckpoint->checkpoint_id : null;
    
    // 3. Tentukan Index (Urutan) dari Checkpoint yang sedang aktif
    $currentStageIndex = null;
    if ($currentCheckpointId) {
        // Cari urutan (index 0-based) checkpoint yang sedang aktif di collection $checkpoints
        $currentStageIndex = $checkpoints->search(function ($checkpoint) use ($currentCheckpointId) {
            return $checkpoint->checkpoint_id === $currentCheckpointId;
        });
    }

    // Jika tidak ada yang 'in_progress', asumsikan stage berikutnya adalah yang pertama belum selesai
    if ($currentStageIndex === false || $currentStageIndex === null) {
        $lastCompleted = $checkpoints->whereIn('checkpoint_id', $completedCheckpointIds)->last();
        $lastCompletedIndex = $lastCompleted ? $checkpoints->search($lastCompleted) : -1;
        
        // Stage berikutnya (jika ada) adalah index setelah yang terakhir selesai.
        if ($lastCompletedIndex < $checkpoints->count() - 1) {
            $currentStageIndex = $lastCompletedIndex + 1;
        }
    }
    // --- AKHIR LOGIKA STAGE ---
    
    
    // Jika currentStageIndex adalah boolean (false), ubah menjadi null
    if ($currentStageIndex === false) {
        $currentStageIndex = null;
    }

    ActivityLogger::log(
        module: 'Procurement',
        action: 'view_procurement_approval_detail',
        targetId: $procurement->procurement_id,
        details: ['user_id' => Auth::id()]
    );

    return view('sekdir.approval-detail', compact(
        'procurement',
        'checkpoints',
        'currentStageIndex' // VARIABEL BARU YANG DILEWATKAN
    ));
}

public function approvalSubmit(Request $request, $procurement_id)
{
    $request->validate([
        'procurement_link' => 'required|url',
        'notes' => 'nullable|string',
        'action' => 'required|in:approve,reject'
    ]);

    $procurement = Procurement::findOrFail($procurement_id);

    $sekdirCheckpointId = 4; // Pengesahan Kontrak
    $nextCheckpointId = 5;   // Pengiriman Material

    // Update progress CP4
    $progress = ProcurementProgress::firstOrNew([
        'procurement_id' => $procurement->procurement_id,
        'checkpoint_id'  => $sekdirCheckpointId,
    ]);

    if ($request->action === 'approve') {
        $progress->status = 'completed';

        // Buat / update CP5
        ProcurementProgress::updateOrCreate(
            [
                'procurement_id' => $procurement->procurement_id,
                'checkpoint_id' => $nextCheckpointId,
            ],
            [
                'status' => 'in_progress',
                'start_date' => now(),
            ]
        );

        $message = "Pengadaan {$procurement->code_procurement} disetujui dan masuk Pengiriman Material.";
    } else {
        $progress->status = 'rejected';
        $message = "Pengadaan {$procurement->code_procurement} ditolak.";
    }

    $progress->save();

    // Tambahan: simpan notes/link di procurement
    $procurement->update([
        'procurement_link' => $request->procurement_link,
        'notes' => $request->notes
    ]);

    ActivityLogger::log(
        module: 'Procurement',
        action: $request->action === 'approve' ? 'approve_procurement' : 'reject_procurement',
        targetId: $procurement->procurement_id,
        details: [
            'user_id' => Auth::id(),
            'decision' => $request->action,
            'checkpoint' => 4,
            'link' => $request->procurement_link,
            'notes' => $request->notes,
        ]
    );

    return redirect()->route('sekdir.approval')->with('success', $message);
}


}
