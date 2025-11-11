<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Division;
use App\Models\ProcurementProgress;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller{
    /**
     * Display a listing of projects
     */
    public function index()
    {
        $projects = Project::with(['ownerDivision', 'contracts'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        $divisions = Division::all();
        return view('projects.create', compact('divisions'));
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code_project' => 'required|string|unique:projects,code_project',
            'name_project' => 'required|string|max:255',
            'description' => 'nullable|string',
            'owner_division_id' => 'required|exists:divisions,divisi_id',
            'priority' => 'required|in:rendah,sedang,tinggi',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $validated['status_project'] = 'draft';

        $project = Project::create($validated);

        // Send notification to Supply Chain
        $this->notifySupplyChain($project, 'Proyek baru telah dibuat dan menunggu review');

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Proyek berhasil dibuat');
    }

    /**
     * Display the specified project
     */
    public function show($id){
    $project = Project::with([
        'ownerDivision',
        'contracts',
        'hps',
        'evaluations',
        'requestProcurements.items'
    ])->findOrFail($id);

    // Daftar stage untuk timeline tampilan di Blade
    $stages = [
        'draft',
        'review_sc',
        'persetujuan_sekretaris',
        'pemilihan_vendor',
        'pengecekan_legalitas',
        'pemesanan',
        'pembayaran',
        'selesai'
    ];

    // Cari posisi stage berdasarkan status_project dari database
    $currentStageIndex = array_search($project->status_project, $stages);

    // Kalau tidak ditemukan, set 0 agar tidak error
    if ($currentStageIndex === false) {
        $currentStageIndex = 0;
    }

    // Get procurement progress (kalau masih dipakai untuk table lain)
    $progress = ProcurementProgress::where('permintaan_pengadaan_id', $id)
        ->with('checkpoint')
        ->orderBy('titik_id')
        ->get();

    return view('projects.show', compact('project', 'progress', 'stages', 'currentStageIndex'));
}



    /**
     * Show the form for editing the specified project
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $divisions = Division::all();

        // Check authorization
        $this->authorize('update', $project);

        return view('projects.edit', compact('project', 'divisions'));
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Check authorization
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name_project' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:rendah,sedang,tinggi',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status_project' => 'nullable|in:draft,review_sc,persetujuan_sekretaris,pemilihan_vendor,pengecekan_legalitas,pemesanan,pembayaran,selesai,rejected',
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Proyek berhasil diupdate');
    }

    /**
     * Update project status
     */
    public function updateStatus(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'status_project' => 'required|in:draft,review_sc,persetujuan_sekretaris,pemilihan_vendor,pengecekan_legalitas,pemesanan,pembayaran,selesai,rejected',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $project->status_project;
        $project->update(['status_project' => $validated['status_project']]);

        // Create notification based on status change
        $this->handleStatusChangeNotification($project, $oldStatus, $validated['status_project']);

        return response()->json([
            'success' => true,
            'message' => 'Status proyek berhasil diupdate',
            'project' => $project
        ]);
    }

    /**
     * Delete the specified project
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        // Check authorization
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Proyek berhasil dihapus');
    }

    /**
     * Search projects
     */
    public function search(Request $request)
    {
        $q = $request->get('q');
        $status = $request->get('status');
        $priority = $request->get('priority');
        $page = $request->get('page', 1);

        $projectsQuery = Project::with(['ownerDivision', 'contracts.vendor']);

        if ($q) {
            $projectsQuery->where(function ($sub) use ($q) {
                $sub->where('name_project', 'LIKE', "%{$q}%")
                    ->orWhere('code_project', 'LIKE', "%{$q}%");
            });
        }

        if ($status) {
            $projectsQuery->where('status_project', $status);
        }

        if ($priority) {
            $projectsQuery->where('priority', $priority);
        }

        $projects = $projectsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $page);

        // Map to simpler structure for frontend
        $items = $projects->map(function ($p) {
            $vendor = $p->contracts->first()->vendor->name_vendor ?? null;
            return [
                'project_id' => $p->project_id,
                'code_project' => $p->code_project,
                'name_project' => $p->name_project,
                'owner_division' => $p->ownerDivision->nama_divisi ?? '-',
                'start_date' => optional($p->start_date)->format('d/m/Y'),
                'end_date' => optional($p->end_date)->format('d/m/Y'),
                'vendor' => $vendor ?? '-',
                'priority' => $p->priority,
                'status_project' => $p->status_project,
            ];
        });

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'last_page' => $projects->lastPage(),
                'has_more' => $projects->hasMorePages(),
            ]
        ]);
    }

    /**
     * Notify Supply Chain team
     */
    private function notifySupplyChain($project, $message)
    {
        $scUsers = \App\Models\User::where('roles', 'supply_chain')->get();

        foreach ($scUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'sender_id' => Auth::id(),
                'type' => 'project_created',
                'title' => 'Proyek Baru',
                'message' => $message . ': ' . $project->name_project,
                'reference_type' => 'App\Models\Project',
                'reference_id' => $project->project_id,
            ]);
        }
    }

    /**
     * Handle status change notifications
     */
    private function handleStatusChangeNotification($project, $oldStatus, $newStatus)
    {
        $notifications = [
            'review_sc' => ['roles' => ['supply_chain'], 'message' => 'Proyek menunggu review SC'],
            'persetujuan_sekretaris' => ['roles' => ['sekretaris_direksi'], 'message' => 'Proyek menunggu persetujuan Sekretaris Direksi'],
            'pemilihan_vendor' => ['roles' => ['supply_chain'], 'message' => 'Proyek dalam tahap pemilihan vendor'],
            'pembayaran' => ['roles' => ['accounting', 'treasury'], 'message' => 'Proyek menunggu pembayaran'],
        ];

        if (isset($notifications[$newStatus])) {
            $config = $notifications[$newStatus];
            $users = \App\Models\User::whereIn('roles', $config['roles'])->get();

            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'sender_id' => Auth::id(),
                    'type' => 'status_change',
                    'title' => 'Update Status Proyek',
                    'message' => $config['message'] . ': ' . $project->name_project,
                    'reference_type' => 'App\Models\Project',
                    'reference_id' => $project->project_id,
                ]);
            }
        }
    }
}
