<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Project;
use App\Models\Division;
use App\Models\Department;
use App\Models\Notification;
use App\Models\ProcurementProgress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcurementController extends Controller
{
    /**
     * Display a listing of procurements
     */
    public function index()
    {
        $procurements = Procurement::with(['project', 'department', 'requestProcurements.vendor', 'procurementProgress.checkpoint'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('procurements.index', compact('procurements'));
    }

    
     public function create()
    {
            
    $departments = Department::all();
    $projects = Project::withCount('procurements')->get();

    return view('procurements.create', compact('departments', 'projects'));
    }   


    /**
     * Store a newly created procurement
     */
   public function store(Request $request)
{
    // validasi: code_procurement tidak perlu di-unique terhadap projects (salah semula).
    // kita validasi unique terhadap tabel procurements.
    $validated = $request->validate([
        'project_code' => 'required|string|exists:projects,project_code',
        'code_procurement' => 'nullable|string', // akan di-generate server-side (boleh terisi sebagai suggestion)
        'name_procurement' => 'required|string|max:255',
        'description' => 'nullable|string',
        'department_procurement' => 'required|exists:departments,department_id',
        'priority' => 'required|in:rendah,sedang,tinggi',
        'end_date' => 'required|date|after:today',
    ]);

    // Atur start_date & default status
    $validated['start_date'] = Carbon::now()->format('Y-m-d');
    $validated['status_procurement'] = 'draft';

    $projectCode = $validated['project_code'];

    // Ambil nomor urut terakhir untuk project ini dari tabel procurements
    // Menggunakan query yang mencari kode yang diawali projectCode- dan mengambil max sequence
    // Asumsi format kode di DB: PROJECTCODE-<NN> (dua digit)
    $last = Procurement::where('code_procurement', 'like', $projectCode . '-%')
        ->orderByRaw("LENGTH(code_procurement) desc")
        ->orderBy('code_procurement', 'desc')
        ->first();

    if ($last && preg_match('/-(\d+)$/', $last->code_procurement, $m)) {
        $lastSeq = intval($m[1]);
    } else {
        // alternatif: hitung langsung jumlah existing - safer fallback
        $lastSeq = Procurement::where('code_procurement', 'like', $projectCode . '-%')->count();
    }

    $nextSeq = $lastSeq + 1;
    $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT); // 01, 02, ...

    $finalCode = $projectCode . '-' . $seqStr;

    // Pastikan unik — jika sudah ada (race condition kecil), lakukan loop increment sampai unik
    while (Procurement::where('code_procurement', $finalCode)->exists()) {
        $nextSeq++;
        $seqStr = str_pad($nextSeq, 2, '0', STR_PAD_LEFT);
        $finalCode = $projectCode . '-' . $seqStr;
    }

    $validated['code_procurement'] = $finalCode;

    // Optionally, simpan project_id if you have project PK: (find project by project_code)
    $project = Project::where('project_code', $projectCode)->first();
    if ($project) {
        // sesuaikan nama kolom foreign key di procurement model (misal: project_id)
        $validated['project_id'] = $project->project_id ?? $project->id ?? null;
    }

    // create procurement
    $procurement = Procurement::create($validated);

    // Redirect ke halaman show procurement
    return redirect()->route('procurements.show', $procurement->procurement_id)
        ->with('success', 'Procurement berhasil dibuat dengan kode ' . $finalCode);
}


    /**
     * Show the specified procurement
     */
    public function show($id)
    {
        $procurement = Procurement::with([
            'requestProcurements.items',
            'requestProcurements.vendor',
            'procurementProgress.checkpoint'
        ])->findOrFail($id);

        // Get all checkpoints ordered by sequence
        $checkpoints = \App\Models\Checkpoint::orderBy('point_sequence', 'asc')->get();

        // Get the current stage index based on procurement progress
        $currentStageIndex = 0;

        $latestProgress = $procurement->procurementProgress
            ->sortByDesc(fn($p) => $p->checkpoint?->point_sequence ?? 0)
            ->first();

        if ($latestProgress && $latestProgress->checkpoint) {
            $currentStageIndex = $latestProgress->checkpoint->point_sequence - 1;
        }


        return view('procurements.show', compact('procurement', 'checkpoints', 'currentStageIndex'));
    }


    /**
     * Get procurement progress (for AJAX)
     */
    public function getProgress($id)
    {
        $procurement = Procurement::findOrFail($id);

        $progress = $procurement->procurementProgress()
            ->with(['checkpoint', 'user'])
            ->orderBy('checkpoint_id')
            ->get();

        return response()->json($progress);
    }

    /**
     * Update procurement progress
     */
    public function updateProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'checkpoint_id' => 'required|exists:checkpoints,point_id',
            'status' => 'required|in:not_started,in_progress,completed,blocked',
            'note' => 'nullable|string',
        ]);

        $procurement = Procurement::findOrFail($id);

        $progress = $procurement->procurementProgress()
            ->where('checkpoint_id', $validated['checkpoint_id'])
            ->first();

        if ($progress) {
            $progress->update([
                'status' => $validated['status'],
                'note' => $validated['note'],
                'user_id' => Auth::id(),
                'end_date' => $validated['status'] === 'completed' ? now() : null,
            ]);
        } else {
            $procurement->procurementProgress()->create([
                'checkpoint_id' => $validated['checkpoint_id'],
                'status' => $validated['status'],
                'note' => $validated['note'],
                'user_id' => Auth::id(),
                'start_date' => now(),
                'end_date' => $validated['status'] === 'completed' ? now() : null,
            ]);
        }

        return redirect()->back()->with('success', 'Progress procurement berhasil diperbarui');
    }

    /**
     * Update procurement status (Accept/Reject)
     */
    public function update(Request $request, $id)
    {
        $procurement = Procurement::findOrFail($id);

        // Update status procurement
        $procurement->update([
            'status_procurement' => 'approved',
        ]);

        return redirect()->route('procurements.show', $id)
            ->with('success', 'Procurement berhasil disetujui');
    }

    /**
     * List procurements for a project
     */
    public function byProject($projectId)
    {
        $project = Project::findOrFail($projectId);

        $procurements = $project->procurements()
            ->with(['department', 'requestProcurements.vendor', 'procurementProgress'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('procurements.by-project', compact('project', 'procurements'));
    }

    /**
     * Search procurements (for user list and other procurement searches)
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $status = $request->query('status', '');
        $priority = $request->query('priority', '');
        $page = $request->query('page', 1);

        $procurementsQuery = Procurement::with(['department', 'requestProcurements.vendor']);

        if (!empty($q)) {
            $procurementsQuery->where('name_procurement', 'LIKE', "%{$q}%")
                ->orWhere('code_procurement', 'LIKE', "%{$q}%");
        }

        if (!empty($status)) {
            $procurementsQuery->where('status_procurement', $status);
        }

        if (!empty($priority)) {
            $procurementsQuery->where('priority', $priority);
        }

        $procurements = $procurementsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $page);

        $items = $procurements->map(function ($p) {
            $firstRequest = $p->requestProcurements ? $p->requestProcurements->first() : null;
            $vendor = ($firstRequest && $firstRequest->vendor) ? $firstRequest->vendor->name_vendor : '-';

            return [
                'procurement_id' => $p->procurement_id,
                'code_procurement' => $p->code_procurement,
                'name_procurement' => $p->name_procurement,
                'department_name' => $p->department ? $p->department->department_name : '-',
                'start_date' => $p->start_date ? $p->start_date->format('d/m/Y') : '-',
                'end_date' => $p->end_date ? $p->end_date->format('d/m/Y') : '-',
                'vendor' => $vendor,
                'priority' => $p->priority,
                'status_procurement' => $p->status_procurement,
            ];
        });

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $procurements->currentPage(),
                'per_page' => $procurements->perPage(),
                'total' => $procurements->total(),
                'last_page' => $procurements->lastPage(),
                'has_more' => $procurements->hasMorePages(),
            ]
        ]);
    }

    /**
     * Notify Supply Chain team
     */
    // private function notifySupplyChain($procurement, $message)
    // {
    //     $scUsers = \App\Models\User::where('roles', 'supply_chain')->get();

    //     foreach ($scUsers as $user) {
    //         Notification::create([
    //             'user_id' => $user->id,
    //             'sender_id' => Auth::id(),
    //             'type' => 'procurement_created',
    //             'title' => 'Procurement Baru',
    //             'message' => $message . ': ' . $procurement->name_procurement,
    //             'reference_type' => 'App\Models\Procurement',
    //             'reference_id' => $procurement->procurement_id,
    //         ]);
    //     }
    // }
}
