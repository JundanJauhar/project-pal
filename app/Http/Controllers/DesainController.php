<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Evatek;
use Illuminate\Support\Facades\Auth;

class DesainController extends Controller
{
    
    public function dashboard()
    {
        $projects = Project::with(['ownerDivision', 'evatek', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $projects->count(),
            'sedang_proses' => $projects->whereNotIn('status_project', ['completed'])->count(),
            'selesai' => $projects->where('status_project', 'completed')->count(),
            'ditolak' => $projects->where('status_project', 'rejected')->count(),
        ];

        return view('desain.dashboard', compact('projects', 'stats'));
    }

    public function inputEquipment()
    {
        return view('desain.input-equipment');
    }

   
    public function statusEvatek($projectId)
    {
        $project = Project::with(['ownerDivision', 'evatek', 'vendor'])
            ->findOrFail($projectId);

        $evatek = Evatek::firstOrCreate(
            ['project_id' => $projectId],
            [
                'status' => 'pending',
                'score' => 0,
                'evaluator_id' => Auth::id(),
            ]
        );

        return view('desain.status-evatek-detail', compact('project', 'evatek'));
    }
}
