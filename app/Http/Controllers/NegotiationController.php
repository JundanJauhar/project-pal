<?php

namespace App\Http\Controllers;

use App\Models\Negotiation;
use App\Models\Procurement;
use App\Models\Vendor;
use App\Models\PengadaanOC;
use App\Models\PengesahanKontrak;
use App\Models\Kontrak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Checkpoint;
use App\Services\CheckpointTransitionService;
use App\Helpers\ActivityLogger;

class NegotiationController extends Controller
{

    public function store(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('negotiation')) {
            abort(403, 'Anda tidak punya role negotiation.');
        }

        // === STEP 2: Load PROCUREMENT ===
        $procurement = Procurement::findOrFail($procurementId);

        // === STEP 3: Get CURRENT CHECKPOINT ===
        $currentCheckpoint = $procurement->procurementProgress()
            ->with('checkpoint')
            ->where('status', 'in_progress')
            ->first();

        if (!$currentCheckpoint) {
            abort(400, 'Procurement tidak sedang di tahap apapun.');
        }

        // === STEP 4: Check CHECKPOINT DIVISION ===
        if ($currentCheckpoint->checkpoint->responsible_division !== $user->division_id) {
            abort(403, 'Procurement sedang ditangani divisi lain.');
        }

        // === STEP 5: Check CHECKPOINT NAME ===
        if ($currentCheckpoint->checkpoint->point_name !== 'Negotiation') {
            abort(403, 'Procurement tidak sedang di tahap Negotiation.');
        }

        // === CONSISTENCY CHECK: URL parameter vs request body ===
        if ((int)$procurementId !== (int)$request->input('procurement_id')) {
            abort(400, 'Invalid procurement reference.');
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
            'link' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($procurementId);
            $vendor = Vendor::findOrFail($validated['vendor_id']);

            $negotiation = Negotiation::create([
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
                'link' => $validated['link'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            DB::commit();

            ActivityLogger::log(
                module: 'Negotiation',
                action: 'create_negotiation',
                targetId: $negotiation->negotiation_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', "Negotiation untuk vendor {$vendor->name_vendor} berhasil disimpan")
                ->withFragment('negotiation');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing negotiation', [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan Negotiation: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $negotiationId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('negotiation')) {
            abort(403, 'Anda tidak punya role negotiation.');
        }

        // === STEP 2: Load NEGOTIATION + PROCUREMENT ===
        $neg = Negotiation::with('procurement')->findOrFail($negotiationId);
        $procurement = $neg->procurement;

        // === STEP 3: Get CURRENT CHECKPOINT ===
        $currentCheckpoint = $procurement->procurementProgress()
            ->with('checkpoint')
            ->where('status', 'in_progress')
            ->first();

        if (!$currentCheckpoint) {
            abort(400, 'Procurement tidak sedang di tahap apapun.');
        }

        // === STEP 4: Check CHECKPOINT DIVISION ===
        if ($currentCheckpoint->checkpoint->responsible_division !== $user->division_id) {
            abort(403, 'Procurement sedang ditangani divisi lain.');
        }

        // === STEP 5: Check CHECKPOINT NAME ===
        if ($currentCheckpoint->checkpoint->point_name !== 'Negotiation') {
            abort(403, 'Procurement tidak sedang di tahap Negotiation.');
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
            'link' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

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
                'link' => $validated['link'] ?? null,
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

            Kontrak::where('procurement_id', $neg->procurement_id)
                ->where('vendor_id', $neg->vendor_id)
                ->update([
                    'nilai' => $neg->harga_final,
                    'currency' => $neg->currency_harga_final,
                ]);

            DB::commit();

            ActivityLogger::log(
                module: 'Negotiation',
                action: 'update_negotiation',
                targetId: $neg->negotiation_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

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


    /**
     * Get Negotiations for Procurement (AJAX - READ ONLY)
     * GET /negotiation/procurement/{procurementId}
     * 
     * READ-ONLY endpoint - No authorization check needed
     * Tampilkan semua negotiation untuk procurement tersebut
     */
    public function getByProcurement($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        try {
            $procurement = Procurement::findOrFail($procurementId);

            $negotiations = Negotiation::where('procurement_id', $procurementId)
                ->with(['vendor'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($neg) {
                    return [
                        'negotiation_id' => $neg->negotiation_id,
                        'vendor_id' => $neg->vendor_id,
                        'vendor_name' => $neg->vendor->name_vendor ?? '-',
                        'hps' => $neg->hps ? 'Rp ' . number_format($neg->hps, 0, ',', '.') : '-',
                        'currency_hps' => $neg->currency_hps,
                        'budget' => $neg->budget ? 'Rp ' . number_format($neg->budget, 0, ',', '.') : '-',
                        'currency_budget' => $neg->currency_budget,
                        'harga_final' => $neg->harga_final ? 'Rp ' . number_format($neg->harga_final, 0, ',', '.') : '-',
                        'currency_harga_final' => $neg->currency_harga_final,
                        'tanggal_kirim' => $neg->tanggal_kirim ? $neg->tanggal_kirim->format('d/m/Y') : '-',
                        'tanggal_terima' => $neg->tanggal_terima ? $neg->tanggal_terima->format('d/m/Y') : '-',
                        'lead_time' => $neg->lead_time ?? '-',
                        'link' => $neg->link ?? '-',
                        'notes' => $neg->notes ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $negotiations,
                'count' => $negotiations->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting negotiations', [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($negotiationId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('negotiation')) {
            abort(403, 'Anda tidak punya role negotiation.');
        }

        // === STEP 2: Load NEGOTIATION + PROCUREMENT ===
        $neg = Negotiation::with(['procurement', 'vendor'])->findOrFail($negotiationId);
        $procurement = $neg->procurement;

        // === STEP 3: Get CURRENT CHECKPOINT ===
        $currentCheckpoint = $procurement->procurementProgress()
            ->with('checkpoint')
            ->where('status', 'in_progress')
            ->first();

        if (!$currentCheckpoint) {
            abort(400, 'Procurement tidak sedang di tahap apapun.');
        }

        // === STEP 4: Check CHECKPOINT DIVISION ===
        if ($currentCheckpoint->checkpoint->responsible_division !== $user->division_id) {
            abort(403, 'Procurement sedang ditangani divisi lain.');
        }

        // === STEP 5: Check CHECKPOINT NAME ===
        if ($currentCheckpoint->checkpoint->point_name !== 'Negotiation') {
            abort(403, 'Procurement tidak sedang di tahap Negotiation.');
        }

        try {
            $procurementId = $neg->procurement_id;
            $vendorName = $neg->vendor->name_vendor ?? 'Unknown';

            $neg->delete();

            ActivityLogger::log(
                module: 'Negotiation',
                action: 'delete_negotiation',
                targetId: $negotiationId,
                details: [
                    'procurement_id' => $procurementId,
                    'vendor_name' => $vendorName,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', 'Negotiation berhasil dihapus')
                ->withFragment('negotiation');
        } catch (\Exception $e) {
            Log::error('Error deleting negotiation', [
                'user_id' => $user->id,
                'negotiation_id' => $negotiationId,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Gagal menghapus Negotiation: ' . $e->getMessage());
        }
    }
}
