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

    public function daftarPengadaan($id)
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

    public function approveItem(Request $request, $itemId)
{
    $item = \App\Models\Item::findOrFail($itemId);
    
    $item->update([
        'status' => 'approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    return redirect()->back()->with('success', 'Item berhasil di-approve');
}

public function rejectItem(Request $request, $itemId)
{
    $item = \App\Models\Item::findOrFail($itemId);
    
    $item->update([
        'status' => 'not_approved',
        'approved_by' => null,
        'approved_at' => null,
    ]);

    return redirect()->back()->with('success', 'Item di-reject');
}

}
