<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use App\Models\Evatek;
use App\Models\Item; // Sesuaikan dengan model Anda
use App\Models\Procurement;
use Illuminate\Http\Request;
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

    // ✅ TAMBAHKAN METHOD INI
    public function inputItem()
    {

        $procurements = Procurement::with('project')->get(); // Load relasi wajib
        // Cek authorization (opsional, jika belum pakai middleware)
        if (Auth::user()->roles !== 'supply_chain') {
            abort(403, 'Unauthorized action.');
        }

        // Ambil data yang diperlukan untuk form
        $projects = Project::with([
            'procurements.project', // <--- WAJIB biar tidak lazy load
            'requests',                         // relasi project → request_procurement
            'requests.items',                   // relasi request → items
            'requests.vendor',                  // relasi request → vendor
            'procurements',                     // relasi project → procurement
            'procurements.requestProcurements', // relasi procurement → request_procurement
            'procurements.requestProcurements.items',
            'procurements.requestProcurements.vendor',
        ])->get();
        $departments
            = Department::all();

        return view('desain.input-item', compact('projects', 'departments', 'procurements'))
            ->with('hideNavbar', true);;
    }

    // ✅ METHOD UNTUK MENYIMPAN DATA
    public function storeItem(Request $request)
    {
        // Validasi
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,project_id',
            'item_name' => 'required|string|max:255',
            // tambahkan validasi lainnya sesuai kebutuhan
        ]);

        // Simpan data
        Item::create($validated);

        return redirect()->route('desain.list-project')
            ->with('success', 'Item berhasil ditambahkan!');
    }
}
