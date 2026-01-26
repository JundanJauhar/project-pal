<?php

namespace App\Http\Controllers;

use App\Models\InquiryQuotation;
use App\Models\Procurement;
use App\Models\Vendor;
use App\Services\CheckpointTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ActivityLogger;

class InquiryQuotationController extends Controller
{
    /**
     * Store Inquiry Quotation
     * POST /inquiry-quotation/{procurementId}
     * 
     * Authorization Pattern (CHECKPOINT-BASED):
     * 1. Check user punya role 'inquiry'
     * 2. Load procurement (global, tidak ada division filter)
     * 3. Get current checkpoint
     * 4. Check checkpoint division = user division
     * 5. Check checkpoint name = "Inquiry & Quotation"
     * 6. Process inquiry
     */
    public function store(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('inquiry')) {
            abort(403, 'Anda tidak punya role inquiry.');
        }

        // === STEP 2: Load PROCUREMENT (GLOBAL - no division filter) ===
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
        if ($currentCheckpoint->checkpoint->point_name !== 'Inquiry & Quotation') {
            abort(403, 'Procurement tidak sedang di tahap Inquiry & Quotation.');
        }

        // === CONSISTENCY CHECK: URL parameter vs request body ===
        if ((int)$procurementId !== (int)$request->input('procurement_id')) {
            abort(400, 'Invalid procurement reference.');
        }

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'tanggal_inquiry' => 'required|date',
            'tanggal_quotation' => 'nullable|date|after_or_equal:tanggal_inquiry',
            'target_quotation' => 'nullable|date|after:tanggal_inquiry',
            'lead_time' => 'nullable|string|max:100',
            'nilai_harga' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'link' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:1000',
        ], [
            'procurement_id.required' => 'Procurement tidak valid',
            'vendor_id.required' => 'Vendor harus dipilih',
            'tanggal_inquiry.required' => 'Tanggal inquiry harus diisi',
        ]);

        try {
            DB::beginTransaction();

            $vendor = Vendor::findOrFail($validated['vendor_id']);
            $validated['currency'] = $validated['currency'] ?? 'IDR';

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
                    'link' => $validated['link'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            );

            DB::commit();

            ActivityLogger::log(
                module: 'Inquiry',
                action: 'create_inquiry_quotation',
                targetId: $inquiryQuotation->inquiry_quotation_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->route('procurements.show', $procurement->procurement_id)
                ->with('success', "Inquiry untuk vendor {$vendor->name_vendor} berhasil disimpan")
                ->withFragment('inquiry');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing inquiry quotation', [
                'user_id' => $user->id,
                'procurement_id' => $procurementId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menyimpan inquiry: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Inquiry Quotation
     * PUT /inquiry-quotation/{inquiryQuotationId}
     * 
     * Authorization Pattern (CHECKPOINT-BASED):
     * 1. Check user punya role 'inquiry'
     * 2. Load inquiry + procurement
     * 3. Get current checkpoint
     * 4. Check checkpoint division = user division
     * 5. Check checkpoint name = "Inquiry & Quotation"
     * 6. Update inquiry
     */
    public function update(Request $request, $inquiryQuotationId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('inquiry')) {
            abort(403, 'Anda tidak punya role inquiry.');
        }

        // === STEP 2: Load INQUIRY + PROCUREMENT ===
        $inquiryQuotation = InquiryQuotation::with('procurement')->findOrFail($inquiryQuotationId);
        $procurement = $inquiryQuotation->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Inquiry & Quotation') {
            abort(403, 'Procurement tidak sedang di tahap Inquiry & Quotation.');
        }

        $validated = $request->validate([
            'tanggal_inquiry' => 'required|date',
            'tanggal_quotation' => 'nullable|date|after_or_equal:tanggal_inquiry',
            'target_quotation' => 'nullable|date|after:tanggal_inquiry',
            'lead_time' => 'nullable|string|max:100',
            'nilai_harga' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'link' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $validated['currency'] = $validated['currency'] ?? 'IDR';
            $inquiryQuotation->update($validated);

            ActivityLogger::log(
                module: 'Inquiry',
                action: 'update_inquiry_quotation',
                targetId: $inquiryQuotation->inquiry_quotation_id,
                details: [
                    'procurement_id' => $procurement->procurement_id,
                    'checkpoint_id' => $currentCheckpoint->checkpoint_id,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->back()
                ->with('success', 'Inquiry berhasil diperbarui')
                ->withFragment('inquiry');
        } catch (\Exception $e) {
            Log::error('Error updating inquiry quotation', [
                'user_id' => $user->id,
                'inquiry_quotation_id' => $inquiryQuotationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal memperbarui inquiry: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete Inquiry Quotation
     * DELETE /inquiry-quotation/{inquiryQuotationId}
     * 
     * Authorization: Same as update
     */
    public function delete($inquiryQuotationId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('inquiry')) {
            abort(403, 'Anda tidak punya role inquiry.');
        }

        // === STEP 2: Load INQUIRY + PROCUREMENT ===
        $inquiryQuotation = InquiryQuotation::with(['procurement', 'vendor'])->findOrFail($inquiryQuotationId);
        $procurement = $inquiryQuotation->procurement;

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
        if ($currentCheckpoint->checkpoint->point_name !== 'Inquiry & Quotation') {
            abort(403, 'Procurement tidak sedang di tahap Inquiry & Quotation.');
        }

        try {
            $procurementId = $inquiryQuotation->procurement_id;
            $vendorName = $inquiryQuotation->vendor->name_vendor ?? 'Unknown';

            $inquiryQuotation->delete();

            ActivityLogger::log(
                module: 'Inquiry',
                action: 'delete_inquiry_quotation',
                targetId: $inquiryQuotationId,
                details: [
                    'procurement_id' => $procurementId,
                    'vendor_name' => $vendorName,
                    'user_id' => $user->id,
                    'division_id' => $user->division_id,
                ]
            );

            return redirect()->route('procurements.show', $procurementId)
                ->with('success', 'Inquiry berhasil dihapus')
                ->withFragment('inquiry');
        } catch (\Exception $e) {
            Log::error('Error deleting inquiry quotation', [
                'user_id' => $user->id,
                'inquiry_quotation_id' => $inquiryQuotationId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Gagal menghapus inquiry: ' . $e->getMessage());
        }
    }

    /**
     * Get Inquiry Quotations for Procurement (AJAX - READ ONLY)
     * GET /inquiry-quotation/procurement/{procurementId}
     * 
     * READ-ONLY endpoint - No authorization check needed
     * Tampilkan semua inquiry untuk procurement tersebut
     */
    public function getByProcurement($procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        try {
            $procurement = Procurement::findOrFail($procurementId);

            $inquiryQuotations = InquiryQuotation::where('procurement_id', $procurementId)
                ->with(['vendor'])
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
                        'link' => $iq->link ?? '-',
                        'notes' => $iq->notes ?? '-',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $inquiryQuotations,
                'count' => $inquiryQuotations->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting inquiry quotations', [
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