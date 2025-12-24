<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KontrakController extends Controller
{
    public function store(Request $request, $procurementId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'no_po' => 'nullable|string|max:255',
            'item_id' => 'nullable|exists:items,item_id',
            'vendor_id' => 'nullable|exists:vendors,id_vendor',
            'tgl_kontrak' => 'nullable|date',
            'maker' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'nilai' => 'nullable|numeric|min:0',
            'payment_term' => 'nullable|string|max:255',
            'incoterms' => 'nullable|string|max:255',
            'coo' => 'nullable|string|max:255',
            'warranty' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($procurementId);

            $validated['currency'] = $validated['currency'] ?? 'IDR';

            Kontrak::create($validated);

            DB::commit();

            return redirect()->route('procurements.show', $procurement->procurement_id)
                ->with('success', 'Kontrak berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing Kontrak: '.$e->getMessage());
            return back()->with('error', 'Gagal menyimpan kontrak: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'no_po' => 'nullable|string|max:255',
            'item_id' => 'nullable|exists:items,item_id',
            'vendor_id' => 'nullable|exists:vendors,id_vendor',
            'tgl_kontrak' => 'nullable|date',
            'maker' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'nilai' => 'nullable|numeric|min:0',
            'payment_term' => 'nullable|string|max:255',
            'incoterms' => 'nullable|string|max:255',
            'coo' => 'nullable|string|max:255',
            'warranty' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $kontrak = Kontrak::findOrFail($id);
            $validated['currency'] = $validated['currency'] ?? 'IDR';
            $kontrak->update($validated);

            DB::commit();

            return redirect()->route('procurements.show', $kontrak->procurement_id)
                ->with('success', 'Kontrak berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Kontrak: '.$e->getMessage());
            return back()->with('error', 'Gagal memperbarui kontrak: '.$e->getMessage())->withInput();
        }
    }

    public function delete($id)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $kontrak = Kontrak::findOrFail($id);
            $procId = $kontrak->procurement_id;
            $kontrak->delete();

            return redirect()->route('procurements.show', $procId)
                ->with('success', 'Kontrak berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting Kontrak: '.$e->getMessage());
            return back()->with('error', 'Gagal menghapus kontrak: '.$e->getMessage());
        }
    }

    public function getByProcurement($procurementId)
    {
        $list = Kontrak::where('procurement_id', $procurementId)
            ->with(['vendor', 'item'])
            ->orderBy('tgl_kontrak', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $list]);
    }
}
