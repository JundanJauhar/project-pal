<?php

namespace App\Http\Controllers;

use App\Models\Negotiation;
use App\Models\Procurement;
use App\Models\Vendor;
use App\Models\PengadaanOC;
use App\Models\PengesahanKontrak;
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

        // CATATAN: Blade sudah mengirim nilai RAW (tanpa format)
        // JavaScript menyimpan raw value ke hidden input
        // Jadi nilai di sini sudah bersih!
        // 
        // foreach di bawah AMAN UNTUK DOUBLE-CHECK, tapi sebenernya tidak perlu
        // Namun dibiarkan untuk keamanan maksimal

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
            'lead_time' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

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
                'lead_time' => $validated['lead_time'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', "Negotiation untuk vendor {$vendor->name_vendor} berhasil disimpan")
                ->withFragment('negotiation');
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
            abort(403);
        }

        // CATATAN: Blade sudah mengirim nilai RAW (tanpa format)
        // JavaScript menyimpan raw value ke hidden input
        // 
        // foreach di bawah AMAN UNTUK DOUBLE-CHECK, tapi sebenernya tidak perlu
        // Namun dibiarkan untuk keamanan maksimal

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
            'lead_time' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

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
                'lead_time' => $validated['lead_time'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // SINKRONISASI KE PENGADAAN OC & PENGESAHAN KONTRAK
            // Ini sangat penting! Update nilai harga final ke dokumen berikutnya
            PengadaanOC::where('procurement_id', $neg->procurement_id)
                ->where('vendor_id', $neg->vendor_id)
                ->update([
                    'nilai' => $neg->harga_final,
                    'currency' => $neg->currency_harga_final,
                ]);

            PengesahanKontrak::where('procurement_id', $neg->procurement_id)
                ->where('vendor_id', $neg->vendor_id)
                ->update([
                    'nilai' => $neg->harga_final,
                    'currency' => $neg->currency_harga_final,
                ]);

            DB::commit();

            return redirect()
                ->route('procurements.show', $neg->procurement_id)
                ->with('success', 'Negotiation & nilai terkait berhasil disinkronisasi')
                ->withFragment('negotiation');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Negotiation sync error: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal update negotiation: ' . $e->getMessage());
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
                ->with('success', 'Negotiation berhasil dihapus')
                ->withFragment('negotiation');
        } catch (\Exception $e) {
            Log::error('Error deleting negotiation: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal menghapus Negotiation: ' . $e->getMessage());
        }
    }
}
