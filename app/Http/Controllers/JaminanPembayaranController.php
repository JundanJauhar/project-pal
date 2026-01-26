<?php

namespace App\Http\Controllers;

use App\Models\JaminanPembayaran;
use App\Models\Pembayaran;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ActivityLogger;

class JaminanPembayaranController extends Controller
{
    /**
     * Store Jaminan Pembayaran
     * POST /jaminan-pembayaran/{procurementId}
     * 
     * Authorization Pattern:
     * 1. Check ROLE
     * 2. Load PROCUREMENT
     * 3. Get CURRENT CHECKPOINT
     * 4. Check CHECKPOINT DIVISION
     * 5. Check CHECKPOINT NAME
     * 
     * 🔒 Vendor OTOMATIS diambil dari Pembayaran (tidak bisa dipilih manual)
     */
    public function store(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('pembayaran')) {
            abort(403, 'Anda tidak punya role pembayaran.');
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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pembayaran DP') {
            abort(403, 'Procurement tidak sedang di tahap Pembayaran DP.');
        }

        // === CONSISTENCY CHECK: URL parameter vs request body ===
        if ((int)$procurementId !== (int)$request->input('procurement_id')) {
            abort(400, 'Invalid procurement reference.');
        }

        /**
         * ✅ VALIDASI TANPA vendor_id (vendor otomatis dari pembayaran)
         */
        $validated = $request->validateWithBag('jaminan', [
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'advance_guarantee' => 'nullable|boolean',
            'performance_bond' => 'nullable|boolean',
            'warranty_bond' => 'nullable|boolean',
            'target_terbit' => 'nullable|date',
            'realisasi_terbit' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:target_terbit',
        ], [
            'procurement_id.required' => 'Procurement tidak valid',
            'expiry_date.after' => 'Expiry date harus setelah target terbit',
        ]);

        try {
            DB::beginTransaction();

            /**
             * ✅ AMBIL VENDOR OTOMATIS DARI PEMBAYARAN
             * Jaminan pembayaran bisa multiple per procurement
             */
            $pembayaran = Pembayaran::where('procurement_id', $validated['procurement_id'])
                ->first();

            if (!$pembayaran) {
                throw new \Exception('Pembayaran belum tersedia. Silakan buat pembayaran terlebih dahulu.');
            }

            $vendorId = $pembayaran->vendor_id;
            $vendor = Vendor::findOrFail($vendorId);

            /**
             * ✅ GUNAKAN create() BUKAN updateOrCreate
             * Setiap klik Create = row baru (multiple records allowed)
             * Vendor bisa sama, tapi record terpisah (untuk audit trail)
             */
            $jaminan = JaminanPembayaran::create([
                'procurement_id' => $validated['procurement_id'],
                'vendor_id' => $vendorId,
                'advance_guarantee' => $request->boolean('advance_guarantee'),
                'performance_bond' => $request->boolean('performance_bond'),
                'warranty_bond' => $request->boolean('warranty_bond'),
                'target_terbit' => $validated['target_terbit'] ?? null,
                'realisasi_terbit' => $validated['realisasi_terbit'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
            ]);

            DB::commit();

            // Log activity dengan division context
            ActivityLogger::log(
                module: 'JaminanPembayaran',
                action: 'create_jaminan_pembayaran',
                targetId: $jaminan->jaminan_pembayaran_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'vendor_id' => $vendor->id_vendor,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with(
                    'success',
                    "Jaminan pembayaran untuk vendor {$vendor->name_vendor} berhasil disimpan"
                );
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing jaminan pembayaran: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menyimpan Jaminan Pembayaran: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Jaminan Pembayaran
     * PUT /jaminan-pembayaran/{jaminanId}
     * 
     * Authorization Pattern:
     * 1. Check ROLE
     * 2. Load JAMINAN + PROCUREMENT
     * 3. Get CURRENT CHECKPOINT
     * 4. Check CHECKPOINT DIVISION
     * 5. Check CHECKPOINT NAME
     * 
     * 🔒 Vendor TIDAK BOLEH DIUBAH (consistency & audit)
     */
    public function update(Request $request, $jaminanId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('pembayaran')) {
            abort(403, 'Anda tidak punya role pembayaran.');
        }

        // === STEP 2: Load JAMINAN + PROCUREMENT ===
        $jaminan = JaminanPembayaran::with('procurement')->findOrFail($jaminanId);
        $procurement = $jaminan->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pembayaran DP') {
            abort(403, 'Procurement tidak sedang di tahap Pembayaran DP.');
        }

        $validated = $request->validateWithBag('jaminan', [
            'advance_guarantee' => 'nullable|boolean',
            'performance_bond' => 'nullable|boolean',
            'warranty_bond' => 'nullable|boolean',
            'target_terbit' => 'nullable|date',
            'realisasi_terbit' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:target_terbit',
        ]);

        try {
            /**
             * ✅ UPDATE HANYA FIELD YANG BOLEH
             * vendor_id TIDAK DIUBAH
             */
            $jaminan->update([
                'advance_guarantee' => $request->boolean('advance_guarantee'),
                'performance_bond' => $request->boolean('performance_bond'),
                'warranty_bond' => $request->boolean('warranty_bond'),
                'target_terbit' => $validated['target_terbit'] ?? null,
                'realisasi_terbit' => $validated['realisasi_terbit'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
            ]);

            // Log activity
            ActivityLogger::log(
                module: 'JaminanPembayaran',
                action: 'update_jaminan_pembayaran',
                targetId: $jaminan->jaminan_pembayaran_id,
                details: [
                    'procurement_id' => $jaminan->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->back()
                ->with('success', 'Jaminan Pembayaran berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating jaminan pembayaran: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'jaminan_pembayaran_id' => $jaminanId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Jaminan Pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Jaminan Pembayaran
     * DELETE /jaminan-pembayaran/{jaminanId}
     * 
     * Authorization Pattern:
     * 1. Check ROLE
     * 2. Load JAMINAN + PROCUREMENT
     * 3. Get CURRENT CHECKPOINT
     * 4. Check CHECKPOINT DIVISION
     * 5. Check CHECKPOINT NAME
     */
    public function delete($jaminanId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('pembayaran')) {
            abort(403, 'Anda tidak punya role pembayaran.');
        }

        // === STEP 2: Load JAMINAN + PROCUREMENT ===
        $jaminan = JaminanPembayaran::with(['procurement', 'vendor'])->findOrFail($jaminanId);
        $procurement = $jaminan->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pembayaran DP') {
            abort(403, 'Procurement tidak sedang di tahap Pembayaran DP.');
        }

        try {
            $procurementId = $jaminan->procurement_id;
            $vendorName = $jaminan->vendor->name_vendor ?? 'Unknown Vendor';

            $jaminan->delete();

            // Log activity
            ActivityLogger::log(
                module: 'JaminanPembayaran',
                action: 'delete_jaminan_pembayaran',
                targetId: $jaminanId,
                details: [
                    'procurement_id' => $procurementId,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'vendor_name' => $vendorName,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', 'Jaminan Pembayaran berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting jaminan pembayaran: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'jaminan_pembayaran_id' => $jaminanId,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus Jaminan Pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * Get Jaminan Pembayaran by Procurement (AJAX - READ ONLY)
     * GET /jaminan-pembayaran/procurement/{procurementId}
     * 
     * READ-ONLY endpoint - No authorization check needed
     * Tampilkan semua jaminan pembayaran untuk procurement tersebut
     */
    public function getByProcurement($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        try {
            $procurement = Procurement::findOrFail($procurementId);

            // === FETCH JAMINAN PEMBAYARAN ===
            $jaminans = JaminanPembayaran::where('procurement_id', $procurementId)
                ->with('vendor')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($j) {
                    return [
                        'jaminan_pembayaran_id' => $j->jaminan_pembayaran_id,
                        'vendor' => $j->vendor->name_vendor ?? '-',
                        'advance_guarantee' => $j->advance_guarantee,
                        'performance_bond' => $j->performance_bond,
                        'warranty_bond' => $j->warranty_bond,
                        'target_terbit' => $j->target_terbit?->format('d/m/Y') ?? '-',
                        'realisasi_terbit' => $j->realisasi_terbit?->format('d/m/Y') ?? '-',
                        'expiry_date' => $j->expiry_date?->format('d/m/Y') ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $jaminans,
                'count' => $jaminans->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting jaminan pembayaran: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
