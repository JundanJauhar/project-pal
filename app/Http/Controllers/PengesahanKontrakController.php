<?php

namespace App\Http\Controllers;

use App\Models\PengesahanKontrak;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengesahanKontrakController extends Controller
{
    public function store(Request $request, $procurementId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'kontrak_id' => 'nullable|exists:kontrak,kontrak_id',
            'vendor_id' => 'nullable|exists:vendors,id_vendor',
            'currency' => 'nullable|string|max:10',
            'nilai' => 'nullable|numeric|min:0',
            'tgl_kadep_to_kadiv' => 'nullable|date',
            'tgl_kadiv_to_cto' => 'nullable|date',
            'tgl_cto_to_ceo' => 'nullable|date',
            'tgl_acc' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($procurementId);

            $validated['currency'] = $validated['currency'] ?? 'IDR';

            PengesahanKontrak::create($validated);

            DB::commit();

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with('success', 'Pengadaan OC berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing PengesahanKontrak: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'kontrak_id' => 'nullable|exists:kontrak,kontrak_id',
            'vendor_id' => 'nullable|exists:vendors,id_vendor',
            'currency' => 'nullable|string|max:10',
            'nilai' => 'nullable|numeric|min:0',
            'tgl_kadep_to_kadiv' => 'nullable|date',
            'tgl_kadiv_to_cto' => 'nullable|date',
            'tgl_cto_to_ceo' => 'nullable|date',
            'tgl_acc' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $p = PengesahanKontrak::findOrFail($id);
            $validated['currency'] = $validated['currency'] ?? 'IDR';
            $p->update($validated);

            DB::commit();

            return redirect()->route('procurements.show', $p->procurement_id)
                ->with('success', 'Pengesahan kontrak berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating PengesahanKontrak: ' . $e->getMessage());
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }

    public function delete($id)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $p = PengesahanKontrak::findOrFail($id);
            $procId = $p->procurement_id;
            $p->delete();

            return redirect()->route('procurements.show', $procId)
                ->with('success', 'Pengesahan kontrak berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting PengesahanKontrak: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    public function getByProcurement($procurementId)
    {
        $list = PengesahanKontrak::where('procurement_id', $procurementId)
            ->with(['vendor', 'kontrak'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $list]);
    }
}
