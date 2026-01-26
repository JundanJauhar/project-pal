<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Checkpoint;
use App\Helpers\ActivityLogger;

class KontrakController extends Controller
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

        $exists = Kontrak::where('procurement_id', $procurementId)->exists();

        if ($exists) {
            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('warning', 'Kontrak sudah ada. Gunakan Edit untuk mengubah data.')
                ->withFragment('kontrak');
        }

        if ($request->filled('nilai')) {
            $request->merge([
                'nilai' => preg_replace('/\D/', '', $request->nilai)
            ]);
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
            'link' => 'nullable|url|max:255',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($procurementId);
            $validated['currency'] = $validated['currency'] ?? 'IDR';

            $kontrak = Kontrak::create($validated);

            DB::commit();

            ActivityLogger::log(
                module: 'Kontrak',
                action: 'create_kontrak',
                targetId: $kontrak->kontrak_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->route('procurements.show', $procurement->procurement_id)
                ->with('success', 'Kontrak berhasil dibuat.')
                ->withFragment('kontrak');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing Kontrak', [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal menyimpan kontrak: ' . $e->getMessage())->withInput();
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

        // === STEP 2: Load KONTRAK + PROCUREMENT ===
        $kontrak = Kontrak::with('procurement')->findOrFail($id);
        $procurement = $kontrak->procurement;

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

        if ($request->filled('nilai')) {
            $request->merge([
                'nilai' => preg_replace('/\D/', '', $request->nilai)
            ]);
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
            'link' => 'nullable|url|max:255',
            'remarks' => 'nullable|string',
        ]);

        unset($validated['vendor_id'], $validated['currency'], $validated['nilai']);

        try {
            DB::beginTransaction();

            $kontrak = Kontrak::findOrFail($id);
            $kontrak->update($validated);

            DB::commit();

            ActivityLogger::log(
                module: 'Kontrak',
                action: 'update_kontrak',
                targetId: $kontrak->kontrak_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->route('procurements.show', $kontrak->procurement_id)
                ->with('success', 'Kontrak berhasil diperbarui.')
                ->withFragment('kontrak');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating Kontrak', [
                'user_id' => $user->id,
                'kontrak_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal memperbarui kontrak: ' . $e->getMessage())->withInput();
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

        // === STEP 2: Load KONTRAK + PROCUREMENT ===
        $kontrak = Kontrak::with(['procurement'])->findOrFail($id);
        $procurement = $kontrak->procurement;

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
            $procId = $kontrak->procurement_id;
            $kontrak->delete();

            ActivityLogger::log(
                module: 'Kontrak',
                action: 'delete_kontrak',
                targetId: $id,
                details: [
                    'procurement_id' => $procId,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->route('procurements.show', $procId)
                ->with('success', 'Kontrak berhasil dihapus.')
                ->withFragment('kontrak');
        } catch (\Exception $e) {
            Log::error('Error deleting Kontrak', [
                'user_id' => $user->id,
                'kontrak_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Gagal menghapus kontrak: ' . $e->getMessage());
        }
    }

    public function getByProcurement($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        try {
            $procurement = Procurement::findOrFail($procurementId);

            $kontraks = Kontrak::where('procurement_id', $procurementId)
                ->with(['vendor', 'item'])
                ->orderBy('tgl_kontrak', 'desc')
                ->get()
                ->map(function ($kontrak) {
                    return [
                        'kontrak_id' => $kontrak->kontrak_id,
                        'no_po' => $kontrak->no_po ?? '-',
                        'item_id' => $kontrak->item_id,
                        'item_name' => $kontrak->item->item_name ?? '-',
                        'vendor_id' => $kontrak->vendor_id,
                        'vendor_name' => $kontrak->vendor->name_vendor ?? '-',
                        'tgl_kontrak' => $kontrak->tgl_kontrak ? $kontrak->tgl_kontrak->format('d/m/Y') : '-',
                        'maker' => $kontrak->maker ?? '-',
                        'nilai' => $kontrak->nilai ? 'Rp ' . number_format($kontrak->nilai, 0, ',', '.') : '-',
                        'currency' => $kontrak->currency,
                        'payment_term' => $kontrak->payment_term ?? '-',
                        'incoterms' => $kontrak->incoterms ?? '-',
                        'coo' => $kontrak->coo ?? '-',
                        'warranty' => $kontrak->warranty ?? '-',
                        'link' => $kontrak->link ?? '-',
                        'remarks' => $kontrak->remarks ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $kontraks,
                'count' => $kontraks->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting kontraks', [
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
