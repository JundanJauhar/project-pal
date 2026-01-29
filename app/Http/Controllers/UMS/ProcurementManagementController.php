<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use App\Models\Procurement;
use Illuminate\Http\Request;
use App\Models\Project;

class ProcurementManagementController extends Controller
{
    /**
     * Show procurement list with search & filter
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $projectCode = $request->input('project_code');

        $procurements = Procurement::with(['project'])
            ->select([
                'procurement_id',
                'project_id',
                'code_procurement',
                'name_procurement',
                'description',
            ])

            // 🔍 Global Search
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code_procurement', 'LIKE', "%{$search}%")
                      ->orWhere('name_procurement', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhereHas('project', function ($qp) use ($search) {
                          $qp->where('project_code', 'LIKE', "%{$search}%");
                      });
                });
            })

            // 🎯 Filter by Project Code
            ->when($projectCode, function ($query) use ($projectCode) {
                $query->whereHas('project', function ($q) use ($projectCode) {
                    $q->where('project_code', $projectCode);
                });
            })

            ->orderBy('created_at', 'desc')
            ->get();

        return view('ums.procurement.index', compact('procurements'));
    }

    /**
     * Delete procurement
     */
    public function destroy($id)
    {
        $procurement = Procurement::findOrFail($id);

        $procurement->delete();

        return redirect()
            ->route('ums.procurement.index')
            ->with('success', 'Procurement berhasil dihapus.');
    }

    public function byProject($projectId, Request $request)
    {
        $project = Project::findOrFail($projectId);

        $search = $request->input('search');

        $procurements = $project->procurements()
            ->when($search, function ($q) use ($search) {
                $q->where('code_procurement', 'LIKE', "%{$search}%")
                ->orWhere('name_procurement', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('ums.procurement.index', compact('procurements', 'project'));
    }
}