<?php

namespace App\Http\Controllers;

use App\Models\PengadaanOc;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengadaanOcController extends Controller
{
    public function store(Request $request, $projectId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'vendor_id' => 'nullable|exists:vendors,id_vendor',
            'currency' => 'nullable|string|max:10',
            'nilai' => 'nullable|numeric|min:0',
            'tgl_kadep_to_kadiv' => 'nullable|date',
            'tgl_kadiv_to_cto' => 'nullable|date',
            'tgl_cto_to_ceo' => 'nullable|date',
            'tgl_acc' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
        ]);

        try {
            DB::beginTransaction();

            $proc = Procurement::findOrFail($validated['procurement_id']);
            if ($proc->project_id != $projectId) {
                throw new \Exception('Procurement tidak sesuai dengan project.');
            }

            // default currency
            $validated['currency'] = $validated['currency'] ?? 'IDR';

            PengadaanOc::create($validated);

            DB::commit();

            return redirect()->route('procurements.show', $proc->procurement_id)
                ->with('success', 'Pengadaan OC berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing PengadaanOc: '.$e->getMessage());
            return back()->with('error', 'Gagal menyimpan: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id_vendor',
            'currency' => 'nullable|string|max:10',
            'nilai' => 'nullable|numeric|min:0',
            'tgl_kadep_to_kadiv' => 'nullable|date',
            'tgl_kadiv_to_cto' => 'nullable|date',
            'tgl_cto_to_ceo' => 'nullable|date',
            'tgl_acc' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
        ]);

        try {
            DB::beginTransaction();

            $po = PengadaanOc::findOrFail($id);
            $validated['currency'] = $validated['currency'] ?? 'IDR';
            $po->update($validated);

            DB::commit();

            return redirect()->route('procurements.show', $po->procurement_id)
                ->with('success', 'Pengadaan OC berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating PengadaanOc: '.$e->getMessage());
            return back()->with('error', 'Gagal memperbarui: '.$e->getMessage())->withInput();
        }
    }

    public function delete($id)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $po = PengadaanOc::findOrFail($id);
            $procId = $po->procurement_id;
            $po->delete();

            return redirect()->route('procurements.show', $procId)
                ->with('success', 'Pengadaan OC berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting PengadaanOc: '.$e->getMessage());
            return back()->with('error', 'Gagal menghapus: '.$e->getMessage());
        }
    }

    // ajax list by procurement
    public function getByProcurement($procurementId)
    {
        $list = PengadaanOc::where('procurement_id', $procurementId)
            ->with(['vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $list]);
    }
}
