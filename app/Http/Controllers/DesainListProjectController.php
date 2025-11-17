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
        // Muat relasi requestProcurements (nama relasi yang konsisten)
        $project = Project::with('requestProcurements')->findOrFail($id);

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

        // Validasi input
        $data = $request->validate([
            'nama_barang.*' => 'required|string',
            'satuan.*' => 'required|string',
            'harga.*' => 'required|numeric',
            'harga_estimasi.*' => 'required|numeric',
            'spesifikasi.*' => 'required|string',
        ]);

        // Ambil request procurement paling baru milik project
        $requestProc = $project->requestProcurements()->latest()->firstOrFail();

        // Simpan ke tabel items
        foreach ($data['nama_barang'] as $index => $nama) {
            $requestProc->items()->create([
                'item_name' => $nama,
                'unit' => $data['satuan'][$index],
                'unit_price' => $data['harga'][$index],
                'total_price' => $data['harga_estimasi'][$index],
                'specification' => $data['spesifikasi'][$index],
            ]);
        }

        return back()->with('success', 'Pengadaan berhasil dikirim!');
    }

}
