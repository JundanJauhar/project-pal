<?php

namespace App\Http\Controllers;

use App\Models\PengesahanKontrak;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Checkpoint;
use App\Helpers\ActivityLogger;

class PengesahanKontrakController extends Controller
{
    public function store(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('contract')) {
            abort(403, 'Anda tidak punya role contract.');
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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pengesahan Kontrak') {
            abort(403, 'Procurement tidak sedang di tahap Pengesahan Kontrak.');
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

            $pengesahanKontrak = PengesahanKontrak::create([
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

            ActivityLogger::log(
                module: 'Pengesahan Kontrak',
                action: 'create_pengesahan_kontrak',
                targetId: $pengesahanKontrak->pengesahan_kontrak_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with('success', 'Pengesahan Kontrak berhasil disimpan.')
                ->withFragment('pengesahan-kontrak');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing PengesahanKontrak', [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->with('error', 'Gagal menyimpan Pengesahan Kontrak: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('contract')) {
            abort(403, 'Anda tidak punya role contract.');
        }

        // === STEP 2: Load PENGESAHAN KONTRAK + PROCUREMENT ===
        $pk = PengesahanKontrak::with('procurement')->findOrFail($id);
        $procurement = $pk->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pengesahan Kontrak') {
            abort(403, 'Procurement tidak sedang di tahap Pengesahan Kontrak.');
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

            ActivityLogger::log(
                module: 'Pengesahan Kontrak',
                action: 'update_pengesahan_kontrak',
                targetId: $pk->pengesahan_kontrak_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $pk->procurement_id)
                ->with('success', 'Pengesahan Kontrak berhasil diperbarui.')
                ->withFragment('pengesahan-kontrak');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating PengesahanKontrak', [
                'user_id' => $user->id,
                'pengesahan_kontrak_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Gagal memperbarui Pengesahan Kontrak: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('contract')) {
            abort(403, 'Anda tidak punya role contract.');
        }

        // === STEP 2: Load PENGESAHAN KONTRAK + PROCUREMENT ===
        $pk = PengesahanKontrak::with(['procurement'])->findOrFail($id);
        $procurement = $pk->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pengesahan Kontrak') {
            abort(403, 'Procurement tidak sedang di tahap Pengesahan Kontrak.');
        }

        try {
            $procId = $pk->procurement_id;
            $pk->delete();

            ActivityLogger::log(
                module: 'Pengesahan Kontrak',
                action: 'delete_pengesahan_kontrak',
                targetId: $id,
                details: [
                    'procurement_id' => $procId,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procId)
                ->with('success', 'Pengesahan Kontrak berhasil dihapus.')
                ->withFragment('pengesahan-kontrak');
        } catch (\Exception $e) {
            Log::error('Error deleting PengesahanKontrak', [
                'user_id' => $user->id,
                'pengesahan_kontrak_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Gagal menghapus Pengesahan Kontrak: ' . $e->getMessage());
        }
    }

    public function getByProcurement($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        try {
            $procurement = Procurement::findOrFail($procurementId);

            $pengesahanKontraks = PengesahanKontrak::where('procurement_id', $procurementId)
                ->with(['vendor', 'kontrak'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($pk) {
                    return [
                        'pengesahan_kontrak_id' => $pk->pengesahan_kontrak_id,
                        'vendor_id' => $pk->vendor_id,
                        'vendor_name' => $pk->vendor->name_vendor ?? '-',
                        'nilai' => $pk->nilai ? 'Rp ' . number_format($pk->nilai, 0, ',', '.') : '-',
                        'currency' => $pk->currency,
                        'tgl_kadep_to_kadiv' => $pk->tgl_kadep_to_kadiv ? $pk->tgl_kadep_to_kadiv->format('d/m/Y') : '-',
                        'tgl_kadiv_to_cto' => $pk->tgl_kadiv_to_cto ? $pk->tgl_kadiv_to_cto->format('d/m/Y') : '-',
                        'tgl_cto_to_ceo' => $pk->tgl_cto_to_ceo ? $pk->tgl_cto_to_ceo->format('d/m/Y') : '-',
                        'tgl_acc' => $pk->tgl_acc ? $pk->tgl_acc->format('d/m/Y') : '-',
                        'remarks' => $pk->remarks ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $pengesahanKontraks,
                'count' => $pengesahanKontraks->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting pengesahan kontraks', [
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
