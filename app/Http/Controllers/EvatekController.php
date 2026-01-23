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
            ->orderBy('revision_id', 'DESC')
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

        ActivityLogger::log(
            module: 'Evatek',
            action: 'view_evatek_review',
            targetId: $evatek->evatek_id,
            details: [
                'user_id' => Auth::id(),
                'revisions_count' => $revisions->count(),
            ]
        );

        if ($evatek->log === null) {
            $evatek->log = '';
        }

        return view('desain.review-evatek', compact('item', 'evatek', 'revisions'));
    }

    /**
     * Show daftar permintaan evatek
     */
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

        // Get unread notifications for current user related to these items
        $unreadEvatekIds = \App\Models\Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->where(function($q) use ($evatekItems) {
                // Check by reference (preferred)
                $q->where('reference_type', 'App\Models\EvatekItem')
                  ->whereIn('reference_id', $evatekItems->pluck('evatek_id'));
                
                // fallback for older notifs: check action_url
                $q->orWhere(function($subq) use ($evatekItems) {
                     foreach ($evatekItems as $item) {
                        $subq->orWhere('action_url', 'LIKE', '%/evatek/item/' . $item->evatek_id . '%');
                     }
                });
            })
            ->get()
            ->map(function ($notif) {
                if ($notif->reference_id && $notif->reference_type === 'App\Models\EvatekItem') {
                    return $notif->reference_id;
                }
                // Extract from URL if reference not set
                if (preg_match('/\/evatek\/item\/(\d+)/', $notif->action_url ?? '', $matches)) {
                    return (int)$matches[1];
                }
                return null;
            })
            ->filter()
            ->unique()
            ->toArray();

        return view('desain.daftar-permintaan', compact('evatekItems', 'unreadEvatekIds'));
    }

    /**
     * Save vendor/design link for a revision
     * ✅ AUTO-UPDATE evatek_status setelah link disimpan
     */
    public function saveLink(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);
        $evatek = EvatekItem::findOrFail($revision->evatek_id);

        $revision->update([
            'vendor_link' => $request->vendor_link,
            'design_link' => $request->design_link,
        ]);

        // ✅ AUTO-UPDATE EVATEK STATUS
        $this->updateEvatekStatus($evatek);

        ActivityLogger::log(
            module: 'Evatek',
            action: 'save_revision_links',
            targetId: $revision->revision_id,
            details: [
                'user_id' => Auth::id(),
                'vendor_link' => $request->vendor_link,
                'design_link' => $request->design_link,
                'evatek_status' => $evatek->evatek_status,
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Save activity log to database
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
     * ✅ UPDATE evatek_status setelah approve
     */
    public function approve(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        if (!$revision->evatek_id) {
            return response()->json(['success' => false, 'message' => 'Revision not linked to evatek item'], 400);
        }

        $revision->update([
            'status' => 'approve',
            'approved_at' => now(),
        ]);

        $evatek = EvatekItem::findOrFail($revision->evatek_id);
        $evatek->update([
            'current_revision' => $revision->revision_code,
            'status' => 'approve',
            'current_date' => now()->toDateString(),
        ]);

        // Notify Vendor
        \App\Models\VendorNotification::create([
            'vendor_id' => $evatek->vendor_id,
            'type' => 'success',
            'title' => 'Evatek Disetujui',
            'message' => "Evatek untuk item '{$evatek->item->item_name}' ({$revision->revision_code}) telah DISETUJUI.",
            'link' => route('vendor.evatek.review', $evatek->evatek_id),
            'created_at' => now(),
        ]);

        // Ambil procurement terkait
        $procurement = $evatek->procurement;

        if ($procurement && $procurement->evatekItems()->count() > 0) {
            $allEvatek = $procurement->evatekItems()->get();
            $allItemIds = $allEvatek->pluck('item_id')->unique();
            $itemIdsWithApproved = $allEvatek
                ->where('status', 'approve')
                ->pluck('item_id')
                ->unique();

            $missingItems = $allItemIds->diff($itemIdsWithApproved);

            if ($missingItems->isEmpty()) {
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
     * ✅ UPDATE evatek_status setelah reject
     */
    public function reject(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        if (!$revision->evatek_id) {
            return response()->json(['success' => false, 'message' => 'Revision not linked to evatek item'], 400);
        }

        $revision->update([
            'status' => 'not approve',
            'not_approved_at' => now(),
        ]);

        $evatek = EvatekItem::findOrFail($revision->evatek_id);
        $evatek->update([
            'current_revision' => $revision->revision_code,
            'status' => 'not_approve',
            'current_date' => now()->toDateString(),
        ]);

        // Notify Vendor
        \App\Models\VendorNotification::create([
            'vendor_id' => $evatek->vendor_id,
            'type' => 'danger',
            'title' => 'Evatek Ditolak',
            'message' => "Evatek untuk item '{$evatek->item->item_name}' ({$revision->revision_code}) DITOLAK.",
            'link' => route('vendor.evatek.review', $evatek->evatek_id),
            'created_at' => now(),
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

    /**
     * Request revision (Revise)
     * ✅ RESET evatek_status ke null saat ada revisi (link dihapus, akan update otomatis)
     */
    public function revise(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        if (!$revision->evatek_id) {
            return response()->json(['success' => false, 'message' => 'Revision not linked to evatek item'], 400);
        }

        $revision->update([
            'status' => 'revisi',
        ]);

        $evatek = EvatekItem::findOrFail($revision->evatek_id);

        $num = intval(substr($revision->revision_code, 1)) + 1;
        $nextCode = "R{$num}";

        $nextRev = EvatekRevision::create([
            'evatek_id' => $evatek->evatek_id,
            'revision_code' => $nextCode,
            'status' => 'pending',
            'date' => now()->toDateString(),
        ]);

        $evatek->update([
            'current_revision' => $nextCode,
            'status' => 'on_progress',
            'current_date' => now()->toDateString(),
            'evatek_status' => null,  // ✅ Reset ke null (kosong)
        ]);

        // Notify Vendor
        \App\Models\VendorNotification::create([
            'vendor_id' => $evatek->vendor_id,
            'type' => 'warning',
            'title' => 'Revisi Diperlukan',
            'message' => "Evatek untuk item '{$evatek->item->item_name}' ({$revision->revision_code}) meminta REVISI. Silakan cek revisi baru {$nextCode}.",
            'link' => route('vendor.evatek.review', $evatek->evatek_id),
            'created_at' => now(),
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

    // ===== HELPER METHODS =====

    /**
     * ✅ HELPER: Auto-update evatek_status berdasarkan link input
     * 
     * Logic:
     * - Default (awal) → NULL (tampil -)
     * - Jika vendor_link kosong → 'evatek-vendor' (vendor belum input)
     * - Jika design_link kosong → 'evatek-desain' (design belum input)
     * - Jika kedua ada → NULL (tampil -)
     * - Jika status sudah approve/not_approve → NULL (tampil -)
     */
    private function updateEvatekStatus(EvatekItem $evatek)
    {
        // Jika status sudah final (approve/not_approve), status evatek kosong
        if ($evatek->status === 'approve' || $evatek->status === 'not_approve') {
            $evatek->evatek_status = null;
            $evatek->save();
            return;
        }

        $latestRevision = $evatek->revisions()->latest('revision_id')->first();

        if (!$latestRevision) {
            $evatek->evatek_status = null;  // Default kosong
            $evatek->save();
            return;
        }

        $hasVendorLink = !empty($latestRevision->vendor_link);
        $hasDesignLink = !empty($latestRevision->design_link);

        // Jika kedua ada, status kosong (semua lengkap)
        if ($hasVendorLink && $hasDesignLink) {
            $evatek->evatek_status = null;
        } elseif (!$hasVendorLink && $hasDesignLink) {
            // Design sudah ada tapi vendor kosong
            $evatek->evatek_status = 'evatek-vendor';
        } elseif ($hasVendorLink && !$hasDesignLink) {
            // Vendor sudah ada tapi design kosong
            $evatek->evatek_status = 'evatek-desain';
        } else {
            // Kedua kosong → null (default)
            $evatek->evatek_status = null;
        }

        $evatek->save();
    }

    /**
     * ✅ HELPER: Check if vendor link exists
     */
    private function hasVendorLink(EvatekItem $evatek)
    {
        $latestRevision = $evatek->revisions()->latest('revision_id')->first();
        return $latestRevision && !empty($latestRevision->vendor_link);
    }

    /**
     * ✅ HELPER: Check if design link exists
     */
    private function hasDesignLink(EvatekItem $evatek)
    {
        $latestRevision = $evatek->revisions()->latest('revision_id')->first();
        return $latestRevision && !empty($latestRevision->design_link);
    }
}