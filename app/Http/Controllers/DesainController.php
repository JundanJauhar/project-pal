<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Procurement;
use App\Models\RequestProcurement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ActivityLogger;

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

        ActivityLogger::log(
            module: 'Desain',
            action: 'open_dashboard',
            targetId: null,
            details: ['user_id' => Auth::id()]
        );

        return view('desain.dashboard', compact('projects', 'stats'));
    }


}
