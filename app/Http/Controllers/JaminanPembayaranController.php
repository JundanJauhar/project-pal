<?php

namespace App\Http\Controllers;

use App\Models\JaminanPembayaran;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JaminanPembayaranController extends Controller
{

    /**
     * Store Jaminan Pembayaran
     */
    public function store(Request $request, $procurementId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validateWithBag('jaminan', [
            'procurement_id'    => 'required|exists:procurement,procurement_id',
            'vendor_id'         => 'required|exists:vendors,id_vendor',
            'advance_guarantee' => 'nullable|boolean',
            'performance_bond'  => 'nullable|boolean',
            'warranty_bond'     => 'nullable|boolean',
            'target_terbit'     => 'nullable|date',
            'realisasi_terbit'  => 'nullable|date',
            'expiry_date'       => 'nullable|date|after:target_terbit',
        ], [
            'procurement_id.required' => 'Pilih procurement terlebih dahulu',
            'vendor_id.required'      => 'Pilih vendor terlebih dahulu',
            'expiry_date.after'       => 'Expiry date harus setelah target terbit',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($validated['procurement_id']);
            $vendor      = Vendor::findOrFail($validated['vendor_id']);

            if ((int)$validated['procurement_id'] !== (int)$procurementId) {
                throw new \Exception('Invalid procurement reference.');
            }

            $jaminan = JaminanPembayaran::updateOrCreate(
                [
                    'procurement_id' => $validated['procurement_id'],
                    'vendor_id'      => $validated['vendor_id'],
                ],
                [
                    'advance_guarantee' => $request->boolean('advance_guarantee'),
                    'performance_bond'  => $request->boolean('performance_bond'),
                    'warranty_bond'     => $request->boolean('warranty_bond'),
                    'target_terbit'     => $validated['target_terbit'] ?? null,
                    'realisasi_terbit'  => $validated['realisasi_terbit'] ?? null,
                    'expiry_date'       => $validated['expiry_date'] ?? null,
                ]
            );

            DB::commit();

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with(
                    'success',
                    "Jaminan pembayaran untuk vendor {$vendor->name_vendor} berhasil disimpan"
                );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing jaminan pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menyimpan Jaminan Pembayaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Jaminan Pembayaran
     */
    public function update(Request $request, $jaminanId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validateWithBag('jaminan', [
            'advance_guarantee' => 'nullable|boolean',
            'performance_bond'  => 'nullable|boolean',
            'warranty_bond'     => 'nullable|boolean',
            'target_terbit'     => 'nullable|date',
            'realisasi_terbit'  => 'nullable|date',
            'expiry_date'       => 'nullable|date|after:target_terbit',
        ]);

        try {
            $jaminan = JaminanPembayaran::findOrFail($jaminanId);

            $jaminan->update([
                'advance_guarantee' => $request->boolean('advance_guarantee'),
                'performance_bond'  => $request->boolean('performance_bond'),
                'warranty_bond'     => $request->boolean('warranty_bond'),
                'target_terbit'     => $validated['target_terbit'] ?? null,
                'realisasi_terbit'  => $validated['realisasi_terbit'] ?? null,
                'expiry_date'       => $validated['expiry_date'] ?? null,
            ]);

            return redirect()
                ->back()
                ->with('success', 'Jaminan Pembayaran berhasil diperbarui');

        } catch (\Exception $e) {
            Log::error('Error updating jaminan pembayaran: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Jaminan Pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Jaminan Pembayaran
     */
    public function delete($jaminanId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $jaminan = JaminanPembayaran::findOrFail($jaminanId);
            $procurementId = $jaminan->procurement_id;

            $jaminan->delete();

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', 'Jaminan Pembayaran berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Error deleting jaminan pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus Jaminan Pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Get Jaminan Pembayaran by Procurement (AJAX)
     */
    public function getByProcurement($procurementId)
    {
        try {
            $jaminans = JaminanPembayaran::where('procurement_id', $procurementId)
                ->with('vendor')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($j) {
                    return [
                        'jaminan_pembayaran_id' => $j->jaminan_pembayaran_id,
                        'vendor'              => $j->vendor->name_vendor ?? '-',
                        'advance_guarantee'   => $j->advance_guarantee,
                        'performance_bond'    => $j->performance_bond,
                        'warranty_bond'       => $j->warranty_bond,
                        'target_terbit'       => $j->target_terbit?->format('d/m/Y') ?? '-',
                        'realisasi_terbit'    => $j->realisasi_terbit?->format('d/m/Y') ?? '-',
                        'expiry_date'         => $j->expiry_date?->format('d/m/Y') ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data'    => $jaminans,
                'count'   => $jaminans->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting jaminan pembayaran: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
