<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectManagementController extends Controller
{
    /**
     * Show project list
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $projects = Project::withCount('procurements')
            ->when($search, function ($q) use ($search) {
                $q->where('project_code', 'LIKE', "%{$search}%")
                  ->orWhere('project_name', 'LIKE', "%{$search}%");
            })

            // ✅ FIX: URUTKAN BERDASARKAN KODE PROJECT
            ->orderBy('project_code', 'asc')

            ->get();

        return view('ums.project.index', compact('projects'));
    }

    /**
     * Store new project
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_code' => 'required|string|max:50|unique:projects,project_code',
            'project_name' => 'required|string|max:100',
            'description'  => 'nullable|string',
        ]);

        Project::create([
            'project_code' => $request->project_code,
            'project_name' => $request->project_name,
            'description'  => $request->description,
        ]);

        return back()->with('success', 'Project berhasil ditambahkan.');
    }

    /**
     * Delete project
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        if ($project->procurements()->count() > 0) {
            return back()->with('error', 'Project masih memiliki procurement.');
        }

        $project->delete();

        return back()->with('success', 'Project berhasil dihapus.');
    }
}
