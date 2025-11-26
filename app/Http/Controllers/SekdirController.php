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

        $recentProjects = Project::with(['ownerDivision', 'contracts.vendor'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('sekdir.dashboard', compact('stats', 'recentProjects'));
    }

    // ...
public function approval()
{
    // Cek Status Pengadaan yang Sedang di Checkpoint 5 (Pengesahan Kontrak)
    // Hanya tampilkan yang status progressnya 'in_progress' di checkpoint 5
    $procurements = Procurement::with([
        'project.ownerDivision', 
        'requestProcurements.vendor',
        'procurementProgress.checkpoint', 
    ])
    ->whereHas('procurementProgress', function ($q) {
        // Filter ketat: Hanya yang sedang aktif di Checkpoint 5
        $q->where('status', 'in_progress')->where('checkpoint_id', 5);
    })
    ->orderBy('created_at', 'desc')
    ->get();

    // --- PERHITUNGAN STATISTIK ---
    // Menggunakan model Project untuk statistik dashboard yang lebih luas
    $totalProjects = Procurement::count();

    $stats = [
        'total' => $totalProjects, 
        // Pending: Jumlah Pengadaan yang saat ini ditampilkan di list (menunggu approval Sekdir)
        'pending' => $procurements->count(), 
        
        // Disetujui (Approved): Asumsi status 'approved' di tabel Procurement/Project
        // Lebih baik hitung yang sudah selesai di checkpoint 5
        'approved' => ProcurementProgress::where('checkpoint_id', 5)->where('status', 'completed')->count(),
        
        // Ditolak (Rejected):
        'rejected' => ProcurementProgress::where('checkpoint_id', 5)->where('status', 'rejected')->count(),
    ];
    
    // TotalProcurements harus sesuai dengan variabel yang digunakan di Blade (dashboard view)
    $totalProcurements = ProcurementProgress::where('checkpoint_id', 5)->count(); 

    return view('sekdir.approval', compact('procurements', 'stats', 'totalProcurements'));
}



    public function approvals()
    {
        $approvals = Project::with(['ownerDivision', 'contracts.vendor'])
            ->where('status_project', 'persetujuan_sekretaris')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'menunggu_total' => Project::where('status_project', 'persetujuan_sekretaris')->count(),
            'menunggu_hari_ini' => Project::where('status_project', 'persetujuan_sekretaris')
                ->whereDate('created_at', \Carbon\Carbon::today())
                ->count(),
        ];

        return view('sekdir.approvals', compact('approvals', 'stats'));
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


    return view('sekdir.approval-detail', compact(
        'procurement',
        'checkpoints',
        'currentStageIndex' // VARIABEL BARU YANG DILEWATKAN
    ));
}

  public function approvalSubmit(Request $request, $procurement_id)
{
    // ... (Validasi dan pengambilan data) ...

    $action = $request->input('action');
    $sekdirCheckpointId = 5; // Pengesahan Kontrak
    $nextCheckpointId = 6;   // Pengiriman Material

    // ... (Update data utama dan firstOrNew progress Checkpoint 5) ...

    // 1. Update data utama (link dan catatan)
    $procurement = Procurement::findOrFail($procurement_id);
    $procurement->procurement_link = $request->input('procurement_link');
    $procurement->notes = $request->input('notes');
    
    // 2. Cari atau inisialisasi record progress Sekdir (ID 5)
    $progress = ProcurementProgress::firstOrNew([
        'procurement_id' => $procurement->procurement_id,
        'checkpoint_id' => $sekdirCheckpointId,
    ]);

    if ($action === 'approve') {
        // --- Aksi Approval (Checkpoint 5 Completed) ---
        $progress->status = 'completed';
        
        // Update status di tabel utama procurements
        $procurement->status_procurement = 'approved'; 
        
        // --- LOGIKA MAJU KE CHECKPOINT 6 ---
        // 1. Buat record baru untuk Checkpoint 6 (Pengiriman Material)
        $nextProgress = ProcurementProgress::firstOrCreate(
            [
                'procurement_id' => $procurement->procurement_id,
                'checkpoint_id' => $nextCheckpointId,
            ],
            [
                'status' => 'in_progress',
                'start_date' => now(),
            ]
        );
        // Pastikan statusnya di-update jika sudah ada record tetapi statusnya bukan 'in_progress' (misalnya 'pending' atau NULL)
        if ($nextProgress->status !== 'in_progress') {
             $nextProgress->status = 'in_progress';
             $nextProgress->save();
        }
        // --- AKHIR LOGIKA MAJU KE CHECKPOINT 6 ---

        $message = 'Pengadaan ' . $procurement->code_procurement . ' berhasil disetujui dan dilanjutkan ke tahap Pengiriman Material.';

    }
    // 3. Simpan perubahan ke Database
    $progress->save();
    $procurement->save();

    return redirect()->route('sekdir.approval')->with('success', $message);
}

}
