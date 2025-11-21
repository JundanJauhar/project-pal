<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesainListProjectController extends Controller
{
    public function list()
    {
        $projects = Project::all();

        return view('desain.list-project', compact('projects'));
    }

    public function daftarPengadaan($id)
    {
        $project = Project::with('requests')->findOrFail($id);

        return view('desain.daftar-permintaan', compact('project'));
    }

    public function reviewEvatek($requestId)
    {
        $request = \App\Models\RequestProcurement::with(['vendor'])
            ->where('request_id', $requestId)
            ->firstOrFail();

        return view('desain.review-evatek', compact('request'));
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
                'department_id' => auth()->user()->department_id ?? 1,
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
