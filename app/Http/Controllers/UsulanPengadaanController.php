<?php

namespace App\Http\Controllers;

use App\Models\UsulanPengadaan;
use App\Models\Procurement;
use App\Models\RequestProcurement;
use App\Models\Vendor;
use App\Models\Checkpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ActivityLogger;

class UsulanPengadaanController extends Controller
{
    public function store(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('pengadaan')) {
            abort(403, 'Anda tidak punya role pengadaan.');
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
        if ($currentCheckpoint->checkpoint->point_name !== 'Usulan Pengadaan / OC') {
            abort(403, 'Procurement tidak sedang di tahap Usulan Pengadaan / OC.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'currency' => 'nullable|string|max:10',
            'nilai' => 'nullable|numeric',
            'tgl_kadep_to_kadiv' => 'nullable|date',
            'tgl_kadiv_to_cto' => 'nullable|date',
            'tgl_cto_to_ceo' => 'nullable|date',
            'tgl_acc' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
        ], [
            'vendor_id.required' => 'Vendor wajib dipilih',
            'vendor_id.exists' => 'Vendor yang dipilih tidak ditemukan',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($procurementId);

            $UsulanPengadaan = UsulanPengadaan::create([
                'procurement_id' => $procurement->procurement_id,
                'vendor_id' => $validated['vendor_id'],
                'currency' => $validated['currency'] ?? 'IDR',
                'nilai' => $validated['nilai'] ?? null,
                'tgl_kadep_to_kadiv' => $validated['tgl_kadep_to_kadiv'] ?? null,
                'tgl_kadiv_to_cto' => $validated['tgl_kadiv_to_cto'] ?? null,
                'tgl_cto_to_ceo' => $validated['tgl_cto_to_ceo'] ?? null,
                'tgl_acc' => $validated['tgl_acc'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            RequestProcurement::where('procurement_id', $procurementId)
                ->update([
                    'vendor_id' => $validated['vendor_id']
                ]);

            $vendor = Vendor::findOrFail($validated['vendor_id']);

            DB::commit();

            ActivityLogger::log(
                module: 'Usulan Pengadaan',
                action: 'create_usulan_pengadaan',
                targetId: $UsulanPengadaan->usulan_pengadaan_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'vendor_id' => $validated['vendor_id'],
                    'vendor_name' => $vendor->name_vendor,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with('success', "Usulan Pengadaan untuk vendor {$vendor->name_vendor} berhasil disimpan dan tersinkronisasi.")
                ->withFragment('pengadaan-oc');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing UsulanPengadaan', [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('pengadaan')) {
            abort(403, 'Anda tidak punya role pengadaan.');
        }

        // === STEP 2: Load USULAN PENGADAAN + PROCUREMENT ===
        $po = UsulanPengadaan::with('procurement')->findOrFail($id);
        $procurement = $po->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Usulan Pengadaan / OC') {
            abort(403, 'Procurement tidak sedang di tahap Usulan Pengadaan / OC.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'currency' => 'nullable|string|max:10',
            'nilai' => 'nullable|numeric|min:0',
            'tgl_kadep_to_kadiv' => 'nullable|date',
            'tgl_kadiv_to_cto' => 'nullable|date',
            'tgl_cto_to_ceo' => 'nullable|date',
            'tgl_acc' => 'nullable|date',
            'remarks' => 'nullable|string',
        ], [
            'vendor_id.required' => 'Vendor wajib dipilih',
            'vendor_id.exists' => 'Vendor yang dipilih tidak ditemukan',
        ]);

        try {
            DB::beginTransaction();

            $po = UsulanPengadaan::findOrFail($id);

            $validated['currency'] = $validated['currency'] ?? 'IDR';

            // Jika nilai tidak dikirim, retain nilai lama
            if (!isset($validated['nilai']) || $validated['nilai'] === null || $validated['nilai'] === '') {
                unset($validated['nilai']);
            }

            $po->update($validated);

            RequestProcurement::where('procurement_id', $po->procurement_id)
                ->update([
                    'vendor_id' => $validated['vendor_id']
                ]);

            $vendor = Vendor::findOrFail($validated['vendor_id']);

            DB::commit();

            ActivityLogger::log(
                module: 'Usulan Pengadaan',
                action: 'update_usulan_pengadaan',
                targetId: $po->usulan_pengadaan_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'vendor_id' => $validated['vendor_id'],
                    'vendor_name' => $vendor->name_vendor,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->route('procurements.show', $po->procurement_id)
                ->with('success', "Usulan Pengadaan berhasil diperbarui dan vendor {$vendor->name_vendor} tersinkronisasi.")
                ->withFragment('pengadaan-oc');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating UsulanPengadaan', [
                'user_id' => $user->id,
                'usulan_pengadaan_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Gagal update Usulan Pengadaan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function delete($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('pengadaan')) {
            abort(403, 'Anda tidak punya role pengadaan.');
        }

        // === STEP 2: Load USULAN PENGADAAN + PROCUREMENT ===
        $po = UsulanPengadaan::with(['procurement'])->findOrFail($id);
        $procurement = $po->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Usulan Pengadaan / OC') {
            abort(403, 'Procurement tidak sedang di tahap Usulan Pengadaan / OC.');
        }

        try {
            $po = UsulanPengadaan::findOrFail($id);
            $procId = $po->procurement_id;
            $vendorName = $po->vendor->name_vendor ?? 'Unknown';

            $po->delete();

            ActivityLogger::log(
                module: 'Usulan Pengadaan',
                action: 'delete_usulan_pengadaan',
                targetId: $id,
                details: [
                    'procurement_id' => $procId,
                    'vendor_name' => $vendorName,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->route('procurements.show', $procId)
                ->with('success', 'Usulan Pengadaan berhasil dihapus.')
                ->withFragment('pengadaan-oc');
        } catch (\Exception $e) {
            Log::error('Error deleting UsulanPengadaan', [
                'user_id' => $user->id,
                'usulan_pengadaan_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // ajax list by procurement
    public function getByProcurement($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        try {
            $procurement = Procurement::findOrFail($procurementId);

            $UsulanPengadaan = UsulanPengadaan::where('procurement_id', $procurementId)
                ->with(['vendor'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($po) {
                    return [
                        'usulan_pengadaan_id' => $po->usulan_pengadaan_id,
                        'vendor_id' => $po->vendor_id,
                        'vendor_name' => $po->vendor->name_vendor ?? '-',
                        'nilai' => $po->nilai ? $po->currency . ' ' . number_format($po->nilai, 0, ',', '.') : '-',
                        'currency' => $po->currency,
                        'tgl_kadep_to_kadiv' => $po->tgl_kadep_to_kadiv ? $po->tgl_kadep_to_kadiv->format('d/m/Y') : '-',
                        'tgl_kadiv_to_cto' => $po->tgl_kadiv_to_cto ? $po->tgl_kadiv_to_cto->format('d/m/Y') : '-',
                        'tgl_cto_to_ceo' => $po->tgl_cto_to_ceo ? $po->tgl_cto_to_ceo->format('d/m/Y') : '-',
                        'tgl_acc' => $po->tgl_acc ? $po->tgl_acc->format('d/m/Y') : '-',
                        'remarks' => $po->remarks ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $UsulanPengadaan,
                'count' => $UsulanPengadaan->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting usulan pengadaan', [
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