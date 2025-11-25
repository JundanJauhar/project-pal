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
    $totalProjects = Project::count();

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
    $totalProcurements = $totalProjects; 

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
    $procurement = Procurement::with([
        // Ganti 'department' yang kemungkinan salah, dengan 'project' jika itu adalah relasi yang benar
        'project.ownerDivision', 
        'requestProcurements.items',
        // Tambahkan relasi lain jika diperlukan, misal: progress
        'procurementProgress' 
    ])->findOrFail($procurementId);

    $checkpoints = Checkpoint::orderBy('point_sequence')->get();

    return view('sekdir.approval-detail', compact(
        'procurement',
        'checkpoints',
    ));
}

  public function approvalSubmit(Request $request, $procurement_id)
{
    // ... (Kode sebelumnya)

    $action = $request->input('action');
    $sekdirCheckpointId = 5; 

    // 1. Update data utama (link dan catatan)
    $procurement = Procurement::findOrFail($procurement_id);
    $procurement->procurement_link = $request->input('procurement_link');
    $procurement->notes = $request->input('notes');
    
    // 2. Cari atau inisialisasi record progress Sekdir
    $progress = ProcurementProgress::firstOrNew([
        'procurement_id' => $procurement->procurement_id,
        'checkpoint_id' => $sekdirCheckpointId,
    ]);

    if ($action === 'approve') {
        // Aksi Approval
        $progress->status = 'completed';
        
        // --- PERBAIKAN DI SINI ---
        // Ganti $procurement->status menjadi $procurement->status_procurement
        $procurement->status_procurement = 'approved'; 
        
        $message = 'Pengadaan ' . $procurement->code_procurement . ' berhasil disetujui dan dilanjutkan.';

    } elseif ($action === 'reject') {
        // Aksi Penolakan
        $progress->status = 'rejected';
        
        // --- PERBAIKAN DI SINI ---
        // Ganti $procurement->status menjadi $procurement->status_procurement
        $procurement->status_procurement = 'rejected';
        
        $message = 'Pengadaan ' . $procurement->code_procurement . ' berhasil DITOLAK.';
    }

    // 3. Simpan perubahan ke Database
    $progress->save();
    $procurement->save(); // Baris ini yang sebelumnya gagal karena nama kolom salah

    return redirect()->route('sekdir.approval')->with('success', $message);
}

}
