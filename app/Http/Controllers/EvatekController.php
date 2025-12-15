<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityLogger;



class EvatekController extends Controller
{
    /**
     * Show Evatek review page for a selected evatek item
     * ✅ PERBAIKAN: Gunakan $evatekId bukan $itemId
     */
    public function review($evatekId)
    {
        $evatek = EvatekItem::with([
            'item',
            'vendor',
            'procurement',
        ])->findOrFail($evatekId);

        $item = $evatek->item;

        $revisions = EvatekRevision::where('evatek_id', $evatek->evatek_id)
            ->orderBy('revision_id', 'ASC')
            ->get();

        if ($revisions->isEmpty()) {
            $revision = EvatekRevision::create([
                'evatek_id' => $evatek->evatek_id,
                'revision_code' => 'R0',
                'vendor_link' => null,
                'design_link' => null,
                'status' => 'pending',
                'date' => now(),
            ]);

            $revisions = collect([$revision]);
        }

        // 🔧 FIX: gunakan $evatek->evatek_id, bukan $itemId
        ActivityLogger::log(
            module: 'Evatek',
            action: 'view_evatek_review',
            targetId: $evatek->evatek_id,
            details: [
                'user_id' => Auth::id(),
                'revisions_count' => $revisions->count(),
            ]
        );

        // Opsional: kalau kolom log boleh null, baris ini bisa dihapus
        if ($evatek->log === null) {
            $evatek->log = '';
        }

        return view('desain.review-evatek', compact('item', 'evatek', 'revisions'));
    }




    public function daftarPermintaan($procurementId)
    {
        $evatekItems = EvatekItem::with([
            'item',
            'vendor',
            'procurement',
            'latestRevision'
        ])
            ->where('procurement_id', $procurementId)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('desain.daftar-permintaan', compact('evatekItems'));
    }




    /**
     * Save vendor/design link for a revision
     */
    public function saveLink(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        $revision->update([
            'vendor_link' => $request->vendor_link,
            'design_link' => $request->design_link,
        ]);

        ActivityLogger::log(
            module: 'Evatek',
            action: 'save_revision_links',
            targetId: $revision->revision_id,
            details: [
                'user_id' => Auth::id(),
                'vendor_link' => $request->vendor_link,
                'design_link' => $request->design_link,
            ]
        );

        return response()->json(['success' => true]);
    }


    /**
     * ✅ NEW: Save activity log to database
     */
    public function saveLog(Request $request)
    {
        $evatek = EvatekItem::findOrFail($request->evatek_id);

        $evatek->update([
            'log' => $request->log,
        ]);

        return response()->json(['success' => true]);
    }


    /**
     * Approve revision
     */
    public function approve(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        // Ensure evatek_id is set
        if (!$revision->evatek_id) {
            return response()->json(['success' => false, 'message' => 'Revision not linked to evatek item'], 400);
        }

        $revision->update([
            'status' => 'approve',
            'approved_at' => now(),
        ]);

        // Update summary on evatek_items
        $evatek = EvatekItem::findOrFail($revision->evatek_id);
        $evatek->update([
            'current_revision' => $revision->revision_code,
            'status' => 'approve',
            'current_date' => now()->toDateString(),
        ]);

        // Ambil procurement terkait
        $procurement = $evatek->procurement;

        // Ambil semua EVATEK milik procurement ini
        $allEvatek = $procurement->evatekItems()->get();

        // Kalau belum ada sama sekali, jangan apa-apa
        if ($allEvatek->isNotEmpty()) {

            // Semua item yang punya EVATEK di procurement ini
            $allItemIds = $allEvatek->pluck('item_id')->unique();

            // Item yang sudah punya minimal satu vendor approve
            $itemIdsWithApproved = $allEvatek
                ->where('status', 'approve')
                ->pluck('item_id')
                ->unique();

            // Cek apakah semua item sudah punya minimal satu vendor approve
            $missingItems = $allItemIds->diff($itemIdsWithApproved);

            if ($missingItems->isEmpty()) {
                // Semua item sudah ada vendor approve → auto pindah checkpoint
                $service = new \App\Services\CheckpointTransitionService($procurement);
                $service->completeCurrentAndMoveNext("Semua item sudah punya vendor approve di Evatek");
            }
        }



        ActivityLogger::log(
            module: 'Evatek',
            action: 'approve_revision',
            targetId: $revision->revision_id,
            details: [
                'user_id' => Auth::id(),
                'revision_code' => $revision->revision_code,
            ]
        );

        return response()->json(['success' => true]);
    }


    /**
     * Reject revision
     */
    public function reject(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        // Ensure evatek_id is set
        if (!$revision->evatek_id) {
            return response()->json(['success' => false, 'message' => 'Revision not linked to evatek item'], 400);
        }

        $revision->update([
            'status' => 'not approve',
            'not_approved_at' => now(),
        ]);

        // Update summary
        $evatek = EvatekItem::findOrFail($revision->evatek_id);
        $evatek->update([
            'current_revision' => $revision->revision_code,
            'status' => 'not_approve',
            'current_date' => now()->toDateString(),
        ]);

        ActivityLogger::log(
            module: 'Evatek',
            action: 'reject_revision',
            targetId: $revision->revision_id,
            details: [
                'user_id' => Auth::id(),
                'revision_code' => $revision->revision_code,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function revise(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        // Ensure evatek_id is set
        if (!$revision->evatek_id) {
            return response()->json(['success' => false, 'message' => 'Revision not linked to evatek item'], 400);
        }

        // Current becomes "revisi"
        $revision->update([
            'status' => 'revisi',
        ]);

        $evatek = EvatekItem::findOrFail($revision->evatek_id);

        // Generate next revision code
        $num = intval(substr($revision->revision_code, 1)) + 1;
        $nextCode = "R{$num}";

        // Create next revision row
        $nextRev = EvatekRevision::create([
            'evatek_id' => $evatek->evatek_id,
            'revision_code' => $nextCode,
            'status' => 'pending',
            'date' => now()->toDateString(),
        ]);

        // Update summary
        $evatek->update([
            'current_revision' => $nextCode,
            'status' => 'on_progress',
            'current_date' => now()->toDateString(),
        ]);

        ActivityLogger::log(
            module: 'Evatek',
            action: 'request_revision',
            targetId: $revision->revision_id,
            details: [
                'user_id' => Auth::id(),
                'from_revision' => $revision->revision_code,
                'to_revision' => $nextRev->revision_code,
            ]
        );

        return response()->json([
            'success' => true,
            'new_revision' => $nextRev,
        ]);
    }
}
