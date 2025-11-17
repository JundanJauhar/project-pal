<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Project;
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

    /**
     * Show the specified procurement
     */
    public function show($id)
    {
        $procurement = Procurement::with([
            'project',
            'department',
            'requestProcurements.vendor',
            'requestProcurements.items',
            'procurementProgress.checkpoint',
            'procurementProgress.user'
        ])->findOrFail($id);

        // Get all checkpoints for timeline
        $checkpoints = \App\Models\Checkpoint::orderBy('point_sequence')->get();
        
        // Get completed and current progress
        $completedProgress = $procurement->procurementProgress()
            ->where('status', 'completed')
            ->count();

        return view('procurements.show', compact('procurement', 'checkpoints', 'completedProgress'));
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
}
