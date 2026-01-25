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

        $exists = PengesahanKontrak::where('procurement_id', $procurementId)->exists();
        if ($exists) {
            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('warning', 'Pengesahan Kontrak sudah ada.')
                ->withFragment('pengesahan-kontrak');
        }

        $validated = $request->validate([
            'procurement_id'        => 'required|exists:procurement,procurement_id',
            'vendor_id'             => 'nullable|exists:vendors,id_vendor',
            'currency'              => 'nullable|string|max:10',
            'nilai'                 => 'nullable|numeric|min:0',
            'tgl_kadep_to_kadiv'    => 'nullable|date',
            'tgl_kadiv_to_cto'      => 'nullable|date',
            'tgl_cto_to_ceo'        => 'nullable|date',
            'tgl_acc'               => 'nullable|date',
            'remarks'               => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($procurementId);

            PengesahanKontrak::create([
                'procurement_id'     => $procurement->procurement_id,
                'vendor_id'          => $validated['vendor_id'] ?? null,
                'currency'           => $validated['currency'] ?? 'IDR',
                'nilai'              => $validated['nilai'] ?? null,
                'tgl_kadep_to_kadiv' => $validated['tgl_kadep_to_kadiv'] ?? null,
                'tgl_kadiv_to_cto'   => $validated['tgl_kadiv_to_cto'] ?? null,
                'tgl_cto_to_ceo'     => $validated['tgl_cto_to_ceo'] ?? null,
                'tgl_acc'            => $validated['tgl_acc'] ?? null,
                'remarks'            => $validated['remarks'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with('success', 'Pengesahan Kontrak berhasil disimpan.')
                ->withFragment('pengesahan-kontrak');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing PengesahanKontrak: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal menyimpan Pengesahan Kontrak.')
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'vendor_id'             => 'nullable|exists:vendors,id_vendor',
            'currency'              => 'nullable|string|max:10',
            'nilai'                 => 'nullable|numeric|min:0',
            'tgl_kadep_to_kadiv'    => 'nullable|date',
            'tgl_kadiv_to_cto'      => 'nullable|date',
            'tgl_cto_to_ceo'        => 'nullable|date',
            'tgl_acc'               => 'nullable|date',
            'remarks'               => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $pk = PengesahanKontrak::findOrFail($id);

            unset($validated['vendor_id'], $validated['currency']);
            if (
                !isset($validated['nilai']) ||
                $validated['nilai'] === null ||
                $validated['nilai'] === ''
            ) {
                unset($validated['nilai']);
            }

            $pk->update($validated);

            DB::commit();

            return redirect()
                ->route('procurements.show', $pk->procurement_id)
                ->with('success', 'Pengesahan Kontrak berhasil diperbarui.')
                ->withFragment('pengesahan-kontrak');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating PengesahanKontrak: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal memperbarui Pengesahan Kontrak.')
                ->withInput();
        }
    }

    public function delete($id)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $pk = PengesahanKontrak::findOrFail($id);
            $procId = $pk->procurement_id;
            $pk->delete();

            return redirect()
                ->route('procurements.show', $procId)
                ->with('success', 'Pengesahan Kontrak berhasil dihapus.')
                ->withFragment('pengesahan-kontrak');
        } catch (\Exception $e) {
            Log::error('Error deleting PengesahanKontrak: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal menghapus Pengesahan Kontrak.');
        }
    }

    public function getByProcurement($procurementId)
    {
        $list = PengesahanKontrak::where('procurement_id', $procurementId)
            ->with(['vendor', 'kontrak'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $list,
        ]);
    }
}
