<?php

namespace App\Http\Controllers;

use App\Models\MaterialDelivery;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Checkpoint;
use App\Helpers\ActivityLogger;

class MaterialDeliveryController extends Controller
{
    public function store(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('delivery')) {
            abort(403, 'Anda tidak punya role delivery.');
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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pengiriman Material') {
            abort(403, 'Procurement tidak sedang di tahap Pengiriman Material.');
        }

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'incoterms' => 'nullable|string|max:50',
            'imo_number' => 'nullable|string|regex:/^[0-9]{7}$/',
            'container_number' => 'nullable|string|max:50|regex:/^[A-Z0-9]+$/',
            'etd' => 'nullable|date',
            'eta_sby_port' => 'nullable|date|after_or_equal:etd',
            'eta_pal' => 'nullable|date|after_or_equal:eta_sby_port',
            'atd' => 'nullable|date',
            'ata_sby_port' => 'nullable|date|after_or_equal:atd',
            'remark' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            if ((int) $validated['procurement_id'] !== (int) $procurementId) {
                throw new \Exception('Invalid procurement reference.');
            }

            $procurement = Procurement::findOrFail($procurementId);

            $defaultIncoterms = optional($procurement->contract)->incoterms;

            $delivery = MaterialDelivery::create([
                'procurement_id' => $procurementId,
                'incoterms' => $validated['incoterms'] ?? $defaultIncoterms,
                'imo_number' => $validated['imo_number'] ?? null,
                'container_number' => $validated['container_number'] ?? null,
                'etd' => $validated['etd'] ?? null,
                'eta_sby_port' => $validated['eta_sby_port'] ?? null,
                'eta_pal' => $validated['eta_pal'] ?? null,
                'atd' => $validated['atd'] ?? null,
                'ata_sby_port' => $validated['ata_sby_port'] ?? null,
                'remark' => $validated['remark'] ?? null,
            ]);

            DB::commit();

            ActivityLogger::log(
                module: 'Material Delivery',
                action: 'create_material_delivery',
                targetId: $delivery->delivery_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', 'Pengiriman Material berhasil disimpan')
                ->withFragment('material-delivery');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing material delivery', [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan Pengiriman Material: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $deliveryId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('delivery')) {
            abort(403, 'Anda tidak punya role delivery.');
        }

        // === STEP 2: Load MATERIAL DELIVERY + PROCUREMENT ===
        $delivery = MaterialDelivery::with('procurement')->findOrFail($deliveryId);
        $procurement = $delivery->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pengiriman Material') {
            abort(403, 'Procurement tidak sedang di tahap Pengiriman Material.');
        }

        $validated = $request->validate([
            'incoterms' => 'nullable|string|max:50',
            'imo_number' => 'nullable|string|regex:/^[0-9]{7}$/',
            'container_number' => 'nullable|string|max:50|regex:/^[A-Z0-9]+$/',
            'etd' => 'nullable|date',
            'eta_sby_port' => 'nullable|date|after_or_equal:etd',
            'eta_pal' => 'nullable|date|after_or_equal:eta_sby_port',
            'atd' => 'nullable|date',
            'ata_sby_port' => 'nullable|date|after_or_equal:atd',
            'remark' => 'nullable|string|max:1000',
        ], [
            'imo_number.regex' => 'IMO Number harus 7 digit angka',
            'container_number.regex' => 'Container Number harus berupa huruf kapital dan angka',
            'eta_sby_port.after_or_equal' => 'ETA SBY Port harus setelah atau sama dengan ETD',
            'eta_pal.after_or_equal' => 'ETA PAL harus setelah atau sama dengan ETA SBY Port',
            'ata_sby_port.after_or_equal' => 'ATA SBY Port harus setelah atau sama dengan ATD',
        ]);

        try {
            DB::beginTransaction();

            $delivery = MaterialDelivery::findOrFail($deliveryId);
            $procurementId = $delivery->procurement_id;

            $delivery->update($validated);

            DB::commit();

            ActivityLogger::log(
                module: 'Material Delivery',
                action: 'update_material_delivery',
                targetId: $delivery->delivery_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->route('procurements.show', $procurementId)
                ->with('success', 'Pengiriman Material berhasil diperbarui')
                ->withFragment('material-delivery');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating material delivery', [
                'user_id' => $user->id,
                'delivery_id' => $deliveryId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui Pengiriman Material: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function delete($deliveryId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('delivery')) {
            abort(403, 'Anda tidak punya role delivery.');
        }

        // === STEP 2: Load MATERIAL DELIVERY + PROCUREMENT ===
        $delivery = MaterialDelivery::with(['procurement'])->findOrFail($deliveryId);
        $procurement = $delivery->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Pengiriman Material') {
            abort(403, 'Procurement tidak sedang di tahap Pengiriman Material.');
        }

        try {
            $procurementId = $delivery->procurement_id;

            $delivery->delete();

            ActivityLogger::log(
                module: 'Material Delivery',
                action: 'delete_material_delivery',
                targetId: $deliveryId,
                details: [
                    'procurement_id' => $procurementId,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()
                ->route('procurements.show', $procurementId)
                ->with('success', 'Pengiriman Material berhasil dihapus')
                ->withFragment('material-delivery');
        } catch (\Exception $e) {
            Log::error('Error deleting material delivery', [
                'user_id' => $user->id,
                'delivery_id' => $deliveryId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus Pengiriman Material: ' . $e->getMessage());
        }
    }

    public function getByProcurement($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        try {
            $procurement = Procurement::findOrFail($procurementId);

            $deliveries = MaterialDelivery::where('procurement_id', $procurementId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($del) {
                    return [
                        'delivery_id' => $del->delivery_id,
                        'incoterms' => $del->incoterms ?? '-',
                        'imo_number' => $del->imo_number ?? '-',
                        'container_number' => $del->container_number ?? '-',
                        'etd' => $del->etd ? $del->etd->format('d/m/Y') : '-',
                        'eta_sby_port' => $del->eta_sby_port ? $del->eta_sby_port->format('d/m/Y') : '-',
                        'eta_pal' => $del->eta_pal ? $del->eta_pal->format('d/m/Y') : '-',
                        'atd' => $del->atd ? $del->atd->format('d/m/Y') : '-',
                        'ata_sby_port' => $del->ata_sby_port ? $del->ata_sby_port->format('d/m/Y') : '-',
                        'remark' => $del->remark ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $deliveries,
                'count' => $deliveries->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting material deliveries', [
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
