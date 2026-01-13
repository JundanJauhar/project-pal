<?php

namespace App\Http\Controllers;

use App\Models\MaterialDelivery;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialDeliveryController extends Controller
{
    public function store(Request $request, $procurementId)
    {
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
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

            MaterialDelivery::updateOrCreate(
                ['procurement_id' => $procurementId],
                [
                    'incoterms' => $validated['incoterms'] ?? null,
                    'imo_number' => $validated['imo_number'] ?? null,
                    'container_number' => $validated['container_number'] ?? null,
                    'etd' => $validated['etd'] ?? null,
                    'eta_sby_port' => $validated['eta_sby_port'] ?? null,
                    'eta_pal' => $validated['eta_pal'] ?? null,
                    'atd' => $validated['atd'] ?? null,
                    'ata_sby_port' => $validated['ata_sby_port'] ?? null,
                    'remark' => $validated['remark'] ?? null,
                ]
            );

            DB::commit();

            return redirect()
                ->route('procurements.show', $procurement->$procurementId)
                ->with('success', 'Pengiriman Material berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing material delivery: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan Pengiriman Material: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $deliveryId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
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

            return redirect()->route('procurements.show', $procurementId)
                ->with('success', 'Pengiriman Material berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating material delivery: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal memperbarui Pengiriman Material: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function delete($deliveryId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $delivery = MaterialDelivery::findOrFail($deliveryId);
            $procurementId = $delivery->procurement_id;

            $delivery->delete();

            return redirect()->route('procurements.show', $procurementId)
                ->with('success', 'Pengiriman Material berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting material delivery: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus Pengiriman Material: ' . $e->getMessage());
        }
    }

    public function getByProcurement($procurementId)
    {
        try {
            $deliveries = MaterialDelivery::where('procurement_id', $procurementId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($del) {
                    return [
                        'delivery_id' => $del->delivery_id,
                        'incoterms' => $del->incoterms ?? '-',
                        'etd' => optional($del->etd)->format('d/m/Y'),
                        'eta_sby_port' => optional($del->eta_sby_port)->format('d/m/Y'),
                        'eta_pal' => optional($del->eta_pal)->format('d/m/Y'),
                        'atd' => optional($del->atd)->format('d/m/Y'),
                        'ata_sby_port' => optional($del->ata_sby_port)->format('d/m/Y'),
                        'remark' => $del->remark ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $deliveries,
                'count' => count($deliveries)
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting material deliveries: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}