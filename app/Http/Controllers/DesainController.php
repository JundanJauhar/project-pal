<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Project;
use App\Models\Evatek;
use App\Models\EvatekItem;
use App\Models\Item; // Sesuaikan dengan model Anda
use App\Models\Procurement;
use App\Models\Vendor;
use App\Models\RequestProcurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $evatek = EvatekItem::firstOrCreate(
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
    public function inputItem($projectId)
    {
        // Cek authorization (opsional, jika belum pakai middleware)
        if (Auth::user()->roles !== 'supply_chain') {
            abort(403, 'Unauthorized action.');
        }

        // Ambil project dengan procurements
        $project = Project::with('procurements')->findOrFail($projectId);
        
        // Ambil semua vendor
        $vendors = Vendor::all();

        return view('desain.input-item', compact('project', 'vendors'));
    }

    // ✅ METHOD UNTUK MENYIMPAN DATA
    public function storeItem(Request $request, $projectId)
    {
        // Validasi
        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'item_name' => 'required|string|max:255',
            'deadline_date' => 'required|date',
        ]);
        
        $validated['project_id'] = $projectId;

        try {
            DB::beginTransaction();

            // Cari atau buat RequestProcurement
            $requestProcurement = RequestProcurement::firstOrCreate(
                [
                    'procurement_id' => $validated['procurement_id'],
                    'vendor_id' => $validated['vendor_id'],
                ],
                [
                    'project_id' => $validated['project_id'],
                    'request_name' => 'Request untuk ' . $validated['item_name'],
                    'created_date' => now(),
                    'deadline_date' => $validated['deadline_date'],
                    'request_status' => 'pending',
                ]
            );

            // Simpan Item dengan nilai default
            Item::create([
                'request_procurement_id' => $requestProcurement->request_id,
                'item_name' => $validated['item_name'],
                'amount' => 1,
                'unit' => 'unit',
                'unit_price' => 0,
                'total_price' => 0,
                'status' => 'not_approved',
            ]);

            DB::commit();

            return redirect()->route('desain.daftar-pengadaan', $projectId)
                ->with('success', 'Item berhasil ditambahkan!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing item: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan saat menyimpan item: ' . $e->getMessage()]);
        }
    }
}
