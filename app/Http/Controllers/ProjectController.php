<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Procurement;
use App\Models\Division;
use App\Models\Notification;
use App\Models\ProcurementProgress;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityLogger;


class ProjectController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of procurements (displayed as projects)
     */
    public function index()
    {
        $projects = Procurement::with(['department', 'requestProcurements.vendor'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

            ActivityLogger::log(
                module: 'Project',
                action: 'view_project_list',
                targetId: null,
                details: ['user_id' => Auth::id()]
            );

        return view('procurements.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        return redirect()->route('procurements.create');
    }

    /**
     * Store a newly created project
     */
    public function store(Request $request)
    {
        return redirect()->route('procurements.store');
    }

    /**
     * Display the specified project
     */
    public function show($id)
    {
        $project = Project::with([
            'ownerDivision',
            'contracts',
            'evaluations'
        ])->findOrFail($id);

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

        $currentStageIndex = array_search($project->status_project, $stages) ?? 0;

        $progress = ProcurementProgress::where('permintaan_pengadaan_id', $id)
            ->with('checkpoint')
            ->orderBy('titik_id')
            ->get();

            ActivityLogger::log(
                module: 'Project',
                action: 'view_project_detail',
                targetId: $project->project_id,
                details: ['user_id' => Auth::id()]
            );

        return view('projects.show', compact('project', 'progress', 'stages', 'currentStageIndex'));
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        $divisions = Division::all();

        $this->authorize('update', $project);

        ActivityLogger::log(
            module: 'Project',
            action: 'open_project_edit_form',
            targetId: $project->project_id,
            details: ['user_id' => Auth::id()]
        );

        return view('projects.edit', compact('project', 'divisions'));
    }

    /**
     * Update the specified project
     */
    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $this->authorize('update', $project);

        $validated = $request->validate([
            'code_project' => "required|string|unique:projects,code_project,{$id},project_id",
            'name_project' => 'required|string|max:255',
            'description' => 'nullable|string',
            'owner_division_id' => 'required|exists:divisions,divisi_id',
            'priority' => 'required|in:rendah,sedang,tinggi',
            'end_date' => 'required|date|after:today',
        ]);

        $project->update($validated);

        ActivityLogger::log(
            module: 'Project',
            action: 'update_project',
            targetId: $project->project_id,
            details: [
                'user_id' => Auth::id(),
                'new_name' => $validated['name_project'],
                'priority' => $validated['priority']
            ]
        );

        return redirect()->route('projects.show', $project->project_id)
            ->with('success', 'Proyek berhasil diupdate');
    }

    /**
     * Update project status
     */
    public function updateStatus(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $this->authorize('update', $project); // ✅ Tambahkan authorization

        $validated = $request->validate([
            'status_project' => 'required|in:draft,review_sc,persetujuan_sekretaris,pemilihan_vendor,pengecekan_legalitas,pemesanan,pembayaran,selesai,rejected',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $project->status_project;
        $project->update(['status_project' => $validated['status_project']]);

        $this->handleStatusChangeNotification($project, $oldStatus, $validated['status_project']);

        ActivityLogger::log(
            module: 'Project',
            action: 'update_project_status',
            targetId: $project->project_id,
            details: [
                'user_id' => Auth::id(),
                'from' => $oldStatus,
                'to' => $validated['status_project']
            ]
        );

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

        $this->authorize('delete', $project);

        $project->delete();

        ActivityLogger::log(
            module: 'Project',
            action: 'delete_project',
            targetId: $project->project_id,
            details: ['user_id' => Auth::id()]
        );

        return redirect()->route('projects.index')
            ->with('success', 'Proyek berhasil dihapus');
    }

    /**
     * Search projects
     */
    public function search(Request $request)
    {
        $q = $request->query('q', '');
        $status = $request->query('status', '');
        $priority = $request->query('priority', '');
        $page = $request->query('page', 1);

        $projectsQuery = Procurement::with(['department', 'requestProcurements.vendor']);

        if (!empty($q)) {
            $projectsQuery->where('name_procurement', 'LIKE', "%{$q}%")
                ->orWhere('code_procurement', 'LIKE', "%{$q}%");
        }

        if (!empty($status)) {
            $projectsQuery->where('status_procurement', $status);
        }

        if (!empty($priority)) {
            $projectsQuery->where('priority', $priority);
        }

        $projects = $projectsQuery->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $page);

        $items = $projects->map(function ($p) {
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

        ActivityLogger::log(
            module: 'Project',
            action: 'search_projects',
            targetId: null,
            details: [
                'user_id' => Auth::id(),
                'query' => $q,
                'status' => $status,
                'priority' => $priority,
                'page' => $page,
            ]
        );

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

    /**
     * Upload review documents
     */
    public function uploadReview(Request $request)
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,project_id',
                'documents' => 'required|array|min:1',
                'documents.*' => 'file|mimes:pdf,doc,docx|max:10240',
            ]);

            $project = Project::findOrFail($request->project_id);
            $uploadedFiles = [];

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('review-documents/' . $project->project_id, $filename, 'public');
                    $uploadedFiles[] = [
                        'filename' => $file->getClientOriginalName(),
                        'path' => $path,
                        'uploaded_at' => now(),
                    ];
                }
            }

            // Store file references (optional: in database or just in filesystem)
            $project->update([
                'review_documents' => json_encode($uploadedFiles),
            ]);

            ActivityLogger::log(
                module: 'Project',
                action: 'upload_review_documents',
                targetId: $project->project_id,
                details: [
                    'user_id' => Auth::id(),
                    'files_uploaded' => array_column($uploadedFiles, 'filename'),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Dokumen review berhasil diunggah',
                'data' => $uploadedFiles,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Save review notes
     */
    public function saveReviewNotes(Request $request)
    {
        try {
            $request->validate([
                'project_id' => 'required|exists:projects,project_id',
                'review_notes' => 'required|string',
            ]);

            $project = Project::findOrFail($request->project_id);
            $project->update([
                'review_notes' => $request->review_notes,
            ]);

            ActivityLogger::log(
                module: 'Project',
                action: 'save_review_notes',
                targetId: $project->project_id,
                details: [
                    'user_id' => Auth::id(),
                    'review_notes' => $request->review_notes
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Catatan review berhasil disimpan',
                'data' => [
                    'review_notes' => $request->review_notes,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 422);
        }
    }
}
