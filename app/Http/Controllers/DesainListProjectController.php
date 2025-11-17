<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class DesainListProjectController extends Controller
{
    public function list()
    {
        $projects = Project::all();

        return view('desain.list-project', compact('projects'));
    }

    public function daftarPermintaan($id)
    {
        $project = Project::with([
            'procurements.requestProcurements.vendor',
            'procurements.requestProcurements.items'
        ])->findOrFail($id);

        return view('desain.daftar-permintaan', compact('project'));
    }

    public function reviewEvatek($requestId)
    {
        $request = \App\Models\RequestProcurement::with(['vendor'])
            ->where('request_id', $requestId)
            ->firstOrFail();

        return view('desain.review-evatek', compact('request'));
    }

}
