<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use App\Helpers\ActivityLogger;

class DesainListProjectController extends Controller
{
    public function list()
    {
        $projects = Project::all();

        ActivityLogger::log(
            module: 'Desain',
            action: 'view_list_project',
            targetId: null,
            details: ['user_id' => Auth::id()]
        );

        return view('desain.list-project', compact('projects'));
    }

    public function daftarPengadaan($id)
{
    $project = Project::with([
        'requests',                         // relasi project → request_procurement
        'requests.items',                   // relasi request → items
        'requests.vendor',                  // relasi request → vendor
        'procurements',                     // relasi project → procurement
        'procurements.requestProcurements', // relasi procurement → request_procurement
        'procurements.requestProcurements.items',
        'procurements.requestProcurements.vendor',
    ])->findOrFail($id);

    ActivityLogger::log(
        module: 'Desain',
        action: 'view_procurement_requests',
        targetId: $id,
        details: ['user_id' => Auth::id()]
    );

    return view('desain.daftar-permintaan', compact('project'));
}



    public function reviewEvatek($itemId)
{
    // ambil item + relasi requestProcurement + vendor
    $item = Item::with([
        'requestProcurement',
        'requestProcurement.vendor'
    ])->findOrFail($itemId);

    // cari atau buat evatek_items untuk item ini
    $evatek = EvatekItem::firstOrCreate(
        ['item_id' => $item->item_id],
        [
            'project_id' => optional($item->requestProcurement)->project_id,
            'current_revision' => 'R0',
            'current_status' => 'On Progress',
            'current_date' => now(),
            'log' => null,
        ]
    );

    // ambil revisi terkait evatek (urut naik)
    $revisions = EvatekRevision::where('evatek_id', $evatek->evatek_id)
        ->orderBy('revision_id', 'ASC')
        ->get();

    // jika tidak ada revisi (mis. baru dibuat) -> buat R0 default
    if ($revisions->isEmpty()) {
        $r = EvatekRevision::create([
            'evatek_id'     => $evatek->evatek_id,
            'revision_code' => 'R0',
            'vendor_link'   => null,
            'design_link'   => null,
            'status'        => 'pending',
            'date'          => now(),
        ]);
        $revisions = collect([$r]);
    }
    
    ActivityLogger::log(
        module: 'Desain',
        action: 'review_evatek_item',
        targetId: $itemId,
        details: ['user_id' => Auth::id()]
    );

    return view('desain.review-evatek', compact('item', 'evatek', 'revisions'));
}

   public function kirimPengadaan(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        // Validasi input dengan array length check (PERBAIKAN #6)
        $data = $request->validate([
            'nama_barang' => 'required|array|min:1',
            'nama_barang.*' => 'required|string',
            'satuan' => 'required|array|size:' . count($request->nama_barang ?? []),
            'satuan.*' => 'required|string',
            'harga' => 'required|array|size:' . count($request->nama_barang ?? []),
            'harga.*' => 'required|numeric',
            'harga_estimasi' => 'required|array|size:' . count($request->nama_barang ?? []),
            'harga_estimasi.*' => 'required|numeric',
            'spesifikasi' => 'required|array|size:' . count($request->nama_barang ?? []),
            'spesifikasi.*' => 'required|string',
        ]);

        // Buat atau ambil RequestProcurement (PERBAIKAN #5)
        $requestProc = $project->requestProcurements()
            ->where('request_status', 'draft')
            ->latest()
            ->first();

        if (!$requestProc) {
            $requestProc = $project->requestProcurements()->create([
                'request_name' => 'Permintaan ' . $project->project_name,
                'created_date' => now(),
                'request_status' => 'draft',
                'department_id' => Auth::user()->department_id ?? 1,
            ]);
        }

        // Simpan ke tabel items (PERBAIKAN #7)
        foreach ($data['nama_barang'] as $index => $nama) {
            $requestProc->items()->create([
                'item_name' => $nama,
                'unit' => $data['satuan'][$index],
                'amount' => 1, // PERBAIKAN #7
                'unit_price' => $data['harga'][$index],
                'total_price' => $data['harga_estimasi'][$index],
                'specification' => $data['spesifikasi'][$index],
            ]);
        }

        ActivityLogger::log(
            module: 'Desain',
            action: 'submit_procurement',
            targetId: $id,
            details: [
                'user_id' => Auth::id(),
                'item_count' => count($data['nama_barang']),
                'request_proc_id' => $requestProc->request_id ?? null,
            ]
        );

        return back()->with('success', 'Pengadaan berhasil dikirim!');
    }
    public function approveItem(Request $request, $itemId)
{
    $item = \App\Models\Item::findOrFail($itemId);

    $item->update([
        'status' => 'approved',
        'approved_by' => Auth::id(),
        'approved_at' => now(),
    ]);

    ActivityLogger::log(
        module: 'Desain',
        action: 'approve_item',
        targetId: $itemId,
        details: [
            'approved_by' => Auth::id(),
            'item_status' => 'approved',
        ]
    );

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

    ActivityLogger::log(
        module: 'Desain',
        action: 'reject_item',
        targetId: $itemId,
        details: [
            'rejected_by' => Auth::id(),
            'item_status' => 'not_approved',
        ]
    );

    return redirect()->back()->with('success', 'Item di-reject');
}
}
