<?php

namespace App\Http\Controllers;

use App\Models\InquiryQuotation;
use App\Models\Procurement;
use App\Models\Vendor;
use App\Models\EvatekItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InquiryQuotationController extends Controller
{

    public function store(Request $request, $procurementId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // CATATAN: Blade sudah mengirim nilai RAW (tanpa format)
        // JavaScript menyimpan raw value ke hidden input
        // Jadi nilai di sini sudah bersih!

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'tanggal_inquiry' => 'required|date',
            'tanggal_quotation' => 'nullable|date|after_or_equal:tanggal_inquiry',
            'target_quotation' => 'nullable|date|after:tanggal_inquiry',
            'lead_time' => 'nullable|string|max:100',
            'nilai_harga' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
        ], [
            'procurement_id.required' => 'Pilih procurement terlebih dahulu',
            'vendor_id.required' => 'Pilih vendor terlebih dahulu',
            'tanggal_inquiry.required' => 'Tanggal inquiry harus diisi',
            'tanggal_quotation.after_or_equal' => 'Tanggal quotation harus setelah atau sama dengan tanggal inquiry',
            'target_quotation.after' => 'Target quotation harus setelah tanggal inquiry',
            'nilai_harga.numeric' => 'Nilai harga harus berupa angka',
            'nilai_harga.min' => 'Nilai harga tidak boleh negatif',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::findOrFail($validated['procurement_id']);
            $vendor = Vendor::findOrFail($validated['vendor_id']);

            if ((int)$validated['procurement_id'] !== (int)$procurementId) {
                throw new \Exception('Invalid procurement reference.');
            }

            // Set default currency
            if (empty($validated['currency'])) {
                $validated['currency'] = 'IDR';
            }

            $inquiryQuotation = InquiryQuotation::updateOrCreate(
                [
                    'procurement_id' => $validated['procurement_id'],
                    'vendor_id' => $validated['vendor_id'],
                ],
                [
                    'tanggal_inquiry' => $validated['tanggal_inquiry'],
                    'tanggal_quotation' => $validated['tanggal_quotation'] ?? null,
                    'target_quotation' => $validated['target_quotation'] ?? null,
                    'lead_time' => $validated['lead_time'] ?? null,
                    'nilai_harga' => $validated['nilai_harga'] ?? null,
                    'currency' => $validated['currency'],
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            DB::commit();

            return redirect()->route('procurements.show', $procurement->procurement_id)
                ->with('success', "Inquiry & Quotation untuk vendor {$vendor->name_vendor} berhasil disimpan")
                ->withFragment('inquiry');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing inquiry quotation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menyimpan Inquiry & Quotation: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function update(Request $request, $inquiryQuotationId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // CATATAN: Blade sudah mengirim nilai RAW (tanpa format)
        // JavaScript menyimpan raw value ke hidden input
        // Controller TIDAK PERLU membersihkan lagi!

        $validated = $request->validate([
            'tanggal_inquiry' => 'required|date',
            'tanggal_quotation' => 'nullable|date|after_or_equal:tanggal_inquiry',
            'target_quotation' => 'nullable|date|after:tanggal_inquiry',
            'lead_time' => 'nullable|string|max:100',
            'nilai_harga' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $inquiryQuotation = InquiryQuotation::findOrFail($inquiryQuotationId);

            // Set default currency
            if (empty($validated['currency'])) {
                $validated['currency'] = 'IDR';
            }

            $inquiryQuotation->update($validated);

            return redirect()
                ->back()
                ->with('success', 'Inquiry & Quotation berhasil diperbarui')
                ->withFragment('inquiry');
        } catch (\Exception $e) {
            Log::error('Error updating inquiry quotation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Inquiry & Quotation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Inquiry Quotation
     * DELETE /inquiry-quotation/{inquiryQuotationId}
     */
    public function delete($inquiryQuotationId)
    {
        // Authorization check
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        try {
            $inquiryQuotation = InquiryQuotation::findOrFail($inquiryQuotationId);
            $procurementId = $inquiryQuotation->procurement_id;

            $inquiryQuotation->delete();

            return redirect()->route('procurements.show', $procurementId)
                ->with('success', 'Inquiry & Quotation berhasil dihapus')
                ->withFragment('inquiry');
        } catch (\Exception $e) {
            Log::error('Error deleting inquiry quotation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menghapus Inquiry & Quotation: ' . $e->getMessage());
        }
    }

    /**
     * Get Inquiry Quotations for Procurement (AJAX)
     * GET /inquiry-quotation/procurement/{procurementId}
     */
    public function getByProcurement($procurementId)
    {
        try {
            $inquiryQuotations = InquiryQuotation::where('procurement_id', $procurementId)
                ->with(['procurement', 'vendor'])
                ->orderBy('tanggal_inquiry', 'desc')
                ->get()
                ->map(function ($iq) {
                    return [
                        'inquiry_quotation_id' => $iq->inquiry_quotation_id,
                        'vendor_id' => $iq->vendor_id,
                        'vendor_name' => $iq->vendor->name_vendor ?? '-',
                        'tanggal_inquiry' => $iq->tanggal_inquiry->format('d/m/Y'),
                        'tanggal_quotation' => $iq->tanggal_quotation ? $iq->tanggal_quotation->format('d/m/Y') : '-',
                        'target_quotation' => $iq->target_quotation ? $iq->target_quotation->format('d/m/Y') : '-',
                        'lead_time' => $iq->lead_time ?? '-',
                        'nilai_harga' => $iq->nilai_harga ? 'Rp ' . number_format($iq->nilai_harga, 0, ',', '.') : '-',
                        'currency' => $iq->currency,
                        'notes' => $iq->notes ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $inquiryQuotations,
                'count' => count($inquiryQuotations)
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting inquiry quotations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}