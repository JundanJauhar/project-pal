<?php

namespace App\Http\Controllers;

use App\Models\Negotiation;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NegotiationController extends Controller
{
    public function store(Request $request, $projectId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'hps' => 'nullable|numeric|min:0',
            'budget' => 'nullable|numeric|min:0',
            'harga_final' => 'nullable|numeric|min:0',
            'tanggal_kirim' => 'nullable|date',
            'tanggal_terima' => 'nullable|date|after_or_equal:tanggal_kirim',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $proc = Procurement::findOrFail($validated['procurement_id']);
            if ($proc->project_id != $projectId) {
                throw new \Exception('Procurement tidak sesuai dengan project.');
            }

            Negotiation::create([
                'procurement_id' => $validated['procurement_id'],
                'vendor_id' => $validated['vendor_id'],
                'hps' => $validated['hps'],
                'budget' => $validated['budget'],
                'harga_final' => $validated['harga_final'],
                'tanggal_kirim' => $validated['tanggal_kirim'],
                'tanggal_terima' => $validated['tanggal_terima'],
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return redirect()->route('procurements.show', $proc->procurement_id)
                ->with('success', 'Negotiation berhasil disimpan.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing negotiation: '.$e->getMessage());
            return back()->with('error', 'Gagal menyimpan negotiation: '.$e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $negotiationId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'hps' => 'nullable|numeric|min:0',
            'budget' => 'nullable|numeric|min:0',
            'harga_final' => 'nullable|numeric|min:0',
            'tanggal_kirim' => 'nullable|date',
            'tanggal_terima' => 'nullable|date|after_or_equal:tanggal_kirim',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $neg = Negotiation::findOrFail($negotiationId);
            $neg->update($validated);

            DB::commit();

            return redirect()->route('procurements.show', $neg->procurement_id)
                ->with('success', 'Negotiation berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating negotiation: '.$e->getMessage());
            return back()->with('error', 'Gagal memperbarui negotiation: '.$e->getMessage())->withInput();
        }
    }

    public function delete($negotiationId)
    {
        if (!in_array(Auth::user()->roles, ['supply_chain', 'admin'])) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $neg = Negotiation::findOrFail($negotiationId);
            $procId = $neg->procurement_id;

            $neg->delete();

            return redirect()->route('procurements.show', $procId)
                ->with('success', 'Negotiation berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Error deleting negotiation: '.$e->getMessage());
            return back()->with('error', 'Gagal menghapus negotiation: '.$e->getMessage());
        }
    }
}
