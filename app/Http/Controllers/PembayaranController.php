<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{

    /**
     * Store Pembayaran
     */
    public function store(Request $request, $procurementId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Pre-processing percentage
        if ($request->filled('percentage')) {
            $request->merge([
                'percentage' => (float) $request->percentage
            ]);
        }

        $validated = $request->validate([
            'procurement_id'   => 'required|exists:procurement,procurement_id',
            'payment_type'     => 'required|in:SKBDN,L/C,TT',
            'percentage'       => 'required|numeric|min:1|max:100',
            'no_memo'          => 'nullable|string|max:100',
            'link'             => 'nullable|url|max:255',
            'target_date'      => 'nullable|date',
            'realization_date' => 'nullable|date',
        ], [
            'payment_type.required' => 'Jenis pembayaran harus dipilih',
            'percentage.required'   => 'Persentase pembayaran harus diisi',
            'percentage.max'        => 'Persentase tidak boleh lebih dari 100%',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($validated['procurement_id']);

            if ((int)$validated['procurement_id'] !== (int)$procurementId) {
                throw new \Exception('Invalid procurement reference.');
            }

            $kontrak = $procurement->kontraks()->latest()->first();

            if (!$kontrak) {
                throw new \Exception('Kontrak belum tersedia.');
            }

            $nilaiKontrak = $kontrak->nilai;
            $currency     = $kontrak->currency ?? 'IDR';

            $paymentValue = ($validated['percentage'] / 100) * $nilaiKontrak;

            $totalPercentage = Pembayaran::where('procurement_id', $validated['procurement_id'])
                ->sum('percentage');

            $totalAfterInsert = $totalPercentage + $validated['percentage'];

            if ($totalAfterInsert > 100) {
                throw new \Exception(
                    'Total persentase pembayaran melebihi 100%. ' .
                        'Sisa maksimum yang dapat ditambahkan adalah ' .
                        (100 - $totalPercentage) . '%'
                );
            }

            Pembayaran::create([
                'procurement_id'   => $validated['procurement_id'],
                'vendor_id'        => $kontrak->vendor_id,
                'payment_type'     => $validated['payment_type'],
                'percentage'       => $validated['percentage'],
                'payment_value'    => $paymentValue,
                'currency'         => $currency,
                'no_memo'          => $validated['no_memo'] ?? null,
                'link'             => $validated['link'] ?? null,
                'target_date'      => $validated['target_date'] ?? null,
                'realization_date' => $validated['realization_date'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with('success', 'Pembayaran berhasil disimpan')
                ->withFragment('pembayaran');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menyimpan Pembayaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Pembayaran
     */
    public function update(Request $request, $pembayaranId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        if ($request->filled('percentage')) {
            $request->merge([
                'percentage' => (float) $request->percentage
            ]);
        }

        $validated = $request->validate([
            'payment_type'     => 'required|in:SKBDN,L/C,TT',
            'percentage'       => 'required|numeric|min:1|max:100',
            'no_memo'          => 'nullable|string|max:100',
            'link'             => 'nullable|url|max:255',
            'target_date'      => 'nullable|date',
            'realization_date' => 'nullable|date',
        ]);

        try {
            $pembayaran = Pembayaran::findOrFail($pembayaranId);

            $kontrak = $pembayaran->procurement->kontraks()->latest()->first();

            if (!$kontrak) {
                throw new \Exception('Kontrak tidak ditemukan.');
            }

            $nilaiKontrak = $kontrak->nilai;

            $validated['payment_value'] =
                ($validated['percentage'] / 100) * $nilaiKontrak;

            $validated['currency'] = $kontrak->currency ?? 'IDR';

            $totalPercentage = Pembayaran::where('procurement_id', $pembayaran->procurement_id)
                ->where('id', '!=', $pembayaran->id)
                ->sum('percentage');

            $totalAfterUpdate = $totalPercentage + $validated['percentage'];

            if ($totalAfterUpdate > 100) {
                throw new \Exception(
                    'Total persentase pembayaran melebihi 100%.'
                );
            }

            $pembayaran->update($validated);

            return redirect()
                ->back()
                ->with('success', 'Pembayaran berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal memperbarui Pembayaran: ' . $e->getMessage());
        }
    }


    public function delete($pembayaranId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $pembayaran = Pembayaran::findOrFail($pembayaranId);
            $procurementId = $pembayaran->procurement_id;

            $pembayaran->delete();

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', 'Pembayaran berhasil dihapus')
                ->withFragment('pembayaran');
        } catch (\Exception $e) {
            Log::error('Error deleting pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus Pembayaran: ' . $e->getMessage());
        }
    }

    public function getByProcurement($procurementId)
    {
        try {
            $pembayarans = Pembayaran::where('procurement_id', $procurementId)
                ->with(['vendor'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($p) {
                    return [
                        'id'            => $p->id,
                        'vendor'        => $p->vendor->name_vendor ?? '-',
                        'payment_type'  => $p->payment_type,
                        'percentage'    => $p->percentage,
                        'payment_value' => number_format($p->payment_value, 0, ',', '.'),
                        'currency'      => $p->currency,
                        'no_memo'       => $p->no_memo ?? '-',
                        'link'          => $p->link ?? '-',
                        'target_date'   => $p->target_date?->format('d/m/Y') ?? '-',
                        'realization'   => $p->realization_date?->format('d/m/Y') ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data'    => $pembayarans,
                'count'   => $pembayarans->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pembayaran: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus Pembayaran: ' . $e->getMessage());
        }
    }
}
