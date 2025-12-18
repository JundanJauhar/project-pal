<?php

namespace App\Http\Controllers;

use App\Models\Negotiation;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NegotiationController extends Controller
{

    public function store(Request $request, $procurementId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        foreach (['hps', 'budget', 'harga_final'] as $field) {
            if ($request->filled($field)) {
                $request->merge([
                    $field => preg_replace('/\D/', '', $request->input($field))
                ]);
            }
        }

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'currency_hps' => 'nullable|string|max:10',
            'hps' => 'nullable|numeric|min:0',
            'currency_budget' => 'nullable|string|max:10',
            'budget' => 'nullable|numeric|min:0',
            'currency_harga_final' => 'nullable|string|max:10',
            'harga_final' => 'nullable|numeric|min:0',
            'tanggal_kirim' => 'nullable|date',
            'tanggal_terima' => 'nullable|date|after_or_equal:tanggal_kirim',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // ===== VALIDASI ROUTE vs FORM =====
            if ((int)$validated['procurement_id'] !== (int)$procurementId) {
                throw new \Exception('Invalid procurement reference.');
            }

            $procurement = Procurement::findOrFail($procurementId);
            $vendor = Vendor::findOrFail($validated['vendor_id']);

            Negotiation::create([
                'procurement_id' => $procurementId,
                'vendor_id' => $validated['vendor_id'],

                'currency_hps' => $validated['currency_hps'] ?? 'IDR',
                'hps' => $validated['hps'] ?? null,

                'currency_budget' => $validated['currency_budget'] ?? 'IDR',
                'budget' => $validated['budget'] ?? null,

                'currency_harga_final' => $validated['currency_harga_final'] ?? 'IDR',
                'harga_final' => $validated['harga_final'] ?? null,

                'tanggal_kirim' => $validated['tanggal_kirim'] ?? null,
                'tanggal_terima' => $validated['tanggal_terima'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', "Negotiation untuk vendor {$vendor->name_vendor} berhasil disimpan");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing negotiation: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan Negotiation: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $negotiationId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        foreach (['hps', 'budget', 'harga_final'] as $field) {
            if ($request->filled($field)) {
                $request->merge([
                    $field => preg_replace('/\D/', '', $request->input($field))
                ]);
            }
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'currency_hps' => 'nullable|string|max:10',
            'hps' => 'nullable|numeric|min:0',
            'currency_budget' => 'nullable|string|max:10',
            'budget' => 'nullable|numeric|min:0',
            'currency_harga_final' => 'nullable|string|max:10',
            'harga_final' => 'nullable|numeric|min:0',
            'tanggal_kirim' => 'nullable|date',
            'tanggal_terima' => 'nullable|date|after_or_equal:tanggal_kirim',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $neg = Negotiation::findOrFail($negotiationId);

            $neg->update([
                'vendor_id' => $validated['vendor_id'],

                'currency_hps' => $validated['currency_hps'] ?? 'IDR',
                'hps' => $validated['hps'] ?? null,

                'currency_budget' => $validated['currency_budget'] ?? 'IDR',
                'budget' => $validated['budget'] ?? null,

                'currency_harga_final' => $validated['currency_harga_final'] ?? 'IDR',
                'harga_final' => $validated['harga_final'] ?? null,

                'tanggal_kirim' => $validated['tanggal_kirim'] ?? null,
                'tanggal_terima' => $validated['tanggal_terima'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            return redirect()
                ->route('procurements.show', $neg->procurement_id)
                ->with('success', 'Negotiation berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating negotiation: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal memperbarui Negotiation: ' . $e->getMessage());
        }
    }

    public function delete($negotiationId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $neg = Negotiation::findOrFail($negotiationId);
            $procurementId = $neg->procurement_id;

            $neg->delete();

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', 'Negotiation berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting negotiation: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal menghapus Negotiation: ' . $e->getMessage());
        }
    }
}
