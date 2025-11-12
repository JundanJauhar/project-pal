<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Evatek;
use Illuminate\Support\Facades\Auth;

class DesainController extends Controller
{
    /**
     * Tampilkan dashboard Divisi Desain
     * Sama seperti dashboard Supply Chain
     */
    public function dashboard()
    {
        // Ambil semua project dari database
        $projects = Project::with(['ownerDivision', 'evatek', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung statistik
        $stats = [
            'total' => $projects->count(),
            'sedang_proses' => $projects->whereNotIn('status_project', ['completed'])->count(),
            'selesai' => $projects->where('status_project', 'completed')->count(),
            'ditolak' => $projects->where('status_project', 'rejected')->count(),
        ];

        // Kirim data ke view
        return view('desain.dashboard', compact('projects', 'stats'));
    }

    /**
     * Tampilkan halaman Input Equipment
     */
    public function inputEquipment()
    {
        // Kita akan buat nanti
        return view('desain.input-equipment');
    }

    /**
     * Tampilkan Status Evatek detail
     */
    public function statusEvatek($projectId)
    {
        $project = Project::with(['ownerDivision', 'evatek', 'vendor'])
            ->findOrFail($projectId);

        // Get or create Evatek
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
