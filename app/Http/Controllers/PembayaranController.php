<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\Procurement;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Checkpoint;
use App\Helpers\ActivityLogger;

class PembayaranController extends Controller
{

    /**
     * Store Pembayaran
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

            $pembayaran = Pembayaran::create([
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

            ActivityLogger::log(
                module: 'Pembayaran',
                action: 'create_pembayaran',
                targetId: $pembayaran->id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with('success', 'Pembayaran berhasil disimpan')
                ->withFragment('pembayaran');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing pembayaran', [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('pembayaran')) {
            abort(403, 'Anda tidak punya role pembayaran.');
        }

        // === STEP 2: Load PEMBAYARAN + PROCUREMENT ===
        $pembayaran = Pembayaran::with('procurement')->findOrFail($pembayaranId);
        $procurement = $pembayaran->procurement;

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

            ActivityLogger::log(
                module: 'Pembayaran',
                action: 'update_pembayaran',
                targetId: $pembayaran->id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->back()
                ->with('success', 'Pembayaran berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating pembayaran', [
                'user_id' => $user->id,
                'pembayaran_id' => $pembayaranId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui Pembayaran: ' . $e->getMessage());
        }
    }


    public function delete($pembayaranId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('pembayaran')) {
            abort(403, 'Anda tidak punya role pembayaran.');
        }

        // === STEP 2: Load PEMBAYARAN + PROCUREMENT ===
        $pembayaran = Pembayaran::with(['procurement'])->findOrFail($pembayaranId);
        $procurement = $pembayaran->procurement;

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
            $procurementId = $pembayaran->procurement_id;

            $pembayaran->delete();

            ActivityLogger::log(
                module: 'Pembayaran',
                action: 'delete_pembayaran',
                targetId: $pembayaranId,
                details: [
                    'procurement_id' => $procurementId,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', 'Pembayaran berhasil dihapus')
                ->withFragment('pembayaran');
        } catch (\Exception $e) {
            Log::error('Error deleting pembayaran', [
                'user_id' => $user->id,
                'pembayaran_id' => $pembayaranId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus Pembayaran: ' . $e->getMessage());
        }
    }

    public function getByProcurement($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        try {
            $procurement = Procurement::findOrFail($procurementId);

            $pembayarans = Pembayaran::where('procurement_id', $procurementId)
                ->with(['vendor'])
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'vendor' => $p->vendor->name_vendor ?? '-',
                        'payment_type' => $p->payment_type,
                        'percentage' => $p->percentage,
                        'payment_value' => number_format($p->payment_value, 0, ',', '.'),
                        'currency' => $p->currency,
                        'no_memo' => $p->no_memo ?? '-',
                        'link' => $p->link ?? '-',
                        'target_date' => $p->target_date?->format('d/m/Y') ?? '-',
                        'realization' => $p->realization_date?->format('d/m/Y') ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $pembayarans,
                'count' => $pembayarans->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pembayaran', [
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
}
