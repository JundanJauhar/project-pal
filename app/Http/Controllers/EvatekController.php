<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use App\Models\Procurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    public function daftarPermintaan($projectId)
    {
        // Get all procurements for this project
        $procurements = Procurement::where('project_id', $projectId)->get();
        $procurementIds = $procurements->pluck('procurement_id');

        // Get all evatek items for these procurements
        $evatekItems = EvatekItem::with([
            'item',
            'vendor',
            'procurement',
            'latestRevision'
        ])
            ->whereIn('procurement_id', $procurementIds)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get unread notifications for current user related to these items
        $unreadEvatekIds = \App\Models\Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->where(function ($q) use ($evatekItems) {
                // Check by reference (preferred)
                $q->where('reference_type', 'App\Models\EvatekItem')
                    ->whereIn('reference_id', $evatekItems->pluck('evatek_id'));

                // fallback for older notifs: check action_url
                $q->orWhere(function ($subq) use ($evatekItems) {
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

        // Get unique PICs for filter dropdown
        $uniquePics = $evatekItems->pluck('pic_evatek')
            ->filter() // Remove null/empty values
            ->unique()
            ->sort()
            ->values();

        return view('desain.daftar-permintaan', compact('evatekItems', 'unreadEvatekIds', 'uniquePics'));
    }

    /**
     * Save vendor/design link for a revision
     * ✅ AUTO-UPDATE evatek_status setelah link disimpan
     */
    public function saveLink(Request $request)
    {
        \Log::info("DEBUG SAVELINK: ", $request->all());

        try {
            if (!$request->revision_id) {
                return response()->json(['success' => false, 'message' => 'Revision ID missing']);
            }

            $revision = EvatekRevision::findOrFail($request->revision_id);
            $evatek = EvatekItem::findOrFail($revision->evatek_id);

            // Perform update using Query Builder to ensure persistence
            $updateData = [];
            if ($request->has('vendor_link')) {
                $updateData['vendor_link'] = $request->vendor_link;
            }
            if ($request->has('design_link')) {
                $updateData['design_link'] = $request->design_link;
            }

            if (!empty($updateData)) {
                $updateData['updated_at'] = now();
                \Illuminate\Support\Facades\DB::table('evatek_revisions')
                    ->where('revision_id', $request->revision_id)
                    ->update($updateData);

                // Reload model to get fresh data for logger/status
                $revision = $revision->fresh();
            }

            // ✅ AUTO-UPDATE EVATEK STATUS
            $this->updateEvatekStatus($evatek);

            // ✅ Notification Logic (Desain updates Link)
            if ($request->has('design_link')) {
                // 1. Delete all previous notifications for this Evatek item to prevent duplicates
                \App\Models\Notification::whereIn('title', ['Perlu Isi Link Evatek', 'Review Evatek Diperlukan', 'Menunggu Vendor', 'Lengkapi Dokumen Evatek'])
                    ->where('reference_type', 'App\Models\EvatekItem')
                    ->where('reference_id', $evatek->evatek_id)
                    ->delete();

                // 2. Check Vendor Link Status
                $revFresh = \App\Models\EvatekRevision::find($request->revision_id);
                $isVendorFilled = !empty($revFresh->vendor_link);

                // 3. Notify Desain Users
                $desainDiv = \App\Models\Division::where('division_name', 'LIKE', '%Desain%')->first();
                if ($desainDiv) {
                    $desainUsers = \App\Models\User::where('division_id', $desainDiv->division_id)->get();
                    foreach ($desainUsers as $user) {
                        if ($isVendorFilled) {
                            // Both links ready -> Review Needed
                            \App\Models\Notification::create([
                                'user_id' => $user->user_id,
                                'title' => 'Review Evatek Diperlukan',
                                'reference_type' => 'App\Models\EvatekItem',
                                'reference_id' => $evatek->evatek_id,
                                'message' => "Dokumen evatek '{$evatek->item->item_name}' ({$revFresh->revision_code}) lengkap. Silakan review.",
                                'action_url' => route('desain.review-evatek', $evatek->evatek_id),
                                'type' => 'info',
                                'is_read' => false,
                                'created_at' => now(),
                            ]);
                        } else {
                            // Vendor link empty -> Wait Vendor
                            \App\Models\Notification::create([
                                'user_id' => $user->user_id,
                                'title' => 'Menunggu Vendor',
                                'reference_type' => 'App\Models\EvatekItem',
                                'reference_id' => $evatek->evatek_id,
                                'message' => "Menunggu Vendor {$evatek->vendor->name_vendor} mengupload dokumen evatek '{$evatek->item->item_name}' ({$revFresh->revision_code}).",
                                'action_url' => route('desain.review-evatek', $evatek->evatek_id),
                                'type' => 'info',
                                'is_read' => false,
                                'created_at' => now(),
                            ]);
                        }
                    }
                }
            }

            try {
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
            } catch (\Exception $e) {
                // Ignore logger error to prevent blocking main action
                \Log::error("ActivityLogger failed: " . $e->getMessage());
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            \Log::error("Save Evatek Link Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

        // ✅ DELETE notifikasi "Menunggu Review Desain" untuk item ini
        // Agar tidak ada duplikasi - hanya 1 notifikasi aktif per item
        \App\Models\VendorNotification::where('vendor_id', $evatek->vendor_id)
            ->where('title', 'Menunggu Review Desain')
            ->where('link', route('vendor.evatek.review', $evatek->evatek_id))
            ->delete();

        // ✅ Notify Vendor (Keep per-item for Vendor visibility)
        \App\Models\VendorNotification::create([
            'vendor_id' => $evatek->vendor_id,
            'type' => 'success',
            'title' => 'Evatek Disetujui',
            'message' => "Evatek untuk item '{$evatek->item->item_name}' ({$revision->revision_code}) telah DISETUJUI.",
            'link' => route('vendor.evatek.review', $evatek->evatek_id),
            'created_at' => now(),
        ]);

        // ✅ Notify SC for History (Per Item)
        $scmDiv = \App\Models\Division::where('division_name', 'LIKE', '%Supply%')->first();
        if ($scmDiv) {
            $scmUsers = \App\Models\User::where('division_id', $scmDiv->division_id)->get();
            foreach ($scmUsers as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'success',
                    'title' => 'Evatek Item Disetujui',
                    'message' => "Evatek untuk item '{$evatek->item->item_name}' ({$revision->revision_code}) telah disetujui oleh Desain.",
                    'action_url' => route('procurements.show', $evatek->procurement_id),
                    'reference_type' => 'App\Models\EvatekItem',
                    'reference_id' => $evatek->evatek_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }
        }

        // NOTE: Per-item notifications for Desain and SC are removed to reduce noise.
        // Notifications are now sent only when ALL items in the procurement are complete.

        // Ambil procurement terkait
        $procurement = $evatek->procurement;

        if ($procurement && $procurement->evatekItems()->count() > 0) {
            $allEvatek = $procurement->evatekItems()->with('latestRevision')->get();

            // ✅ Check if ALL evatek items have 'approve' status in their latest revision
            $allApproved = $allEvatek->every(function ($evatekItem) {
                return $evatekItem->latestRevision
                    && $evatekItem->latestRevision->status === 'approve';
            });

            if ($allApproved) {
                // ✅ All evatek items are approved - send notifications and transition checkpoint

                $service = new \App\Services\CheckpointTransitionService($procurement);
                $service->completeCurrentAndMoveNext("Semua item sudah punya vendor approve di Evatek");

                // ✅ Notify Desain Division (All Items Complete)
                $desainDiv = \App\Models\Division::where('division_name', 'LIKE', '%Desain%')->first();
                if ($desainDiv) {
                    $desainUsers = \App\Models\User::where('division_id', $desainDiv->division_id)->get();
                    foreach ($desainUsers as $user) {
                        \App\Models\Notification::create([
                            'user_id' => $user->user_id,
                            'type' => 'success',
                            'title' => 'Evatek Selesai',
                            'message' => "Seluruh item pada pengadaan '{$procurement->procurement_name}' telah selesai Evatek.",
                            'action_url' => route('desain.review-evatek', $evatek->evatek_id), // Or list view
                            'reference_type' => 'App\Models\Procurement', // Changed to Procurement as it wraps all
                            'reference_id' => $procurement->procurement_id,
                            'is_read' => false,
                        ]);
                    }
                }

                // ✅ Notify SC: Ready for Negotiation (All Items Complete)
                $scmDivAll = \App\Models\Division::where('division_name', 'LIKE', '%Supply%')->first();
                if ($scmDivAll) {
                    $scmUsersAll = \App\Models\User::where('division_id', $scmDivAll->division_id)->get();
                    foreach ($scmUsersAll as $user) {
                        $n = \App\Models\Notification::create([
                            'user_id' => $user->user_id,
                            'type' => 'action',
                            'title' => 'Siap Negosiasi',
                            'message' => "Seluruh item pada pengadaan '{$procurement->procurement_name}' telah selesai Evatek. Segera lakukan Negosiasi.",
                            'action_url' => route('procurements.show', $procurement->procurement_id) . '#negotiation',
                            'reference_type' => 'App\Models\Procurement',
                            'reference_id' => $procurement->procurement_id,
                            'is_read' => false,
                        ]);
                        $n->created_at = now()->addSeconds(5);
                        $n->save();
                    }
                }
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

        // ✅ DELETE notifikasi "Menunggu Review Desain" untuk item ini
        \App\Models\VendorNotification::where('vendor_id', $evatek->vendor_id)
            ->where('title', 'Menunggu Review Desain')
            ->where('link', route('vendor.evatek.review', $evatek->evatek_id))
            ->delete();

        // Notify Vendor
        \App\Models\VendorNotification::create([
            'vendor_id' => $evatek->vendor_id,
            'type' => 'danger',
            'title' => 'Evatek Ditolak ',
            'message' => "Evatek untuk item '{$evatek->item->item_name}' ({$revision->revision_code}) DITOLAK.",
            'link' => route('vendor.evatek.review', $evatek->evatek_id),
            'created_at' => now(),
        ]);

        // ✅ Notify SC for History (Per Item)
        $scmDiv = \App\Models\Division::where('division_name', 'LIKE', '%Supply%')->first();
        if ($scmDiv) {
            $scmUsers = \App\Models\User::where('division_id', $scmDiv->division_id)->get();
            foreach ($scmUsers as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'danger',
                    'title' => 'Evatek Item Ditolak',
                    'message' => "Evatek untuk item '{$evatek->item->item_name}' ({$revision->revision_code}) ditolak oleh Desain.",
                    'action_url' => route('procurements.show', $evatek->procurement_id),
                    'reference_type' => 'App\Models\EvatekItem',
                    'reference_id' => $evatek->evatek_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }
        }

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

        // ✅ Notify Desain Users: Lengkapi Dokumen (for new revision)
        $desainDiv = \App\Models\Division::where('division_name', 'LIKE', '%Desain%')->first();
        if ($desainDiv) {
            $desainUsers = \App\Models\User::where('division_id', $desainDiv->division_id)->get();
            foreach ($desainUsers as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'info',
                    'title' => 'Lengkapi Dokumen Evatek',
                    'message' => "Silakan lengkapi dokumen revisi evatek '{$evatek->item->item_name}' ({$nextCode}).",
                    'action_url' => route('desain.review-evatek', $evatek->evatek_id),
                    'reference_type' => 'App\Models\EvatekItem',
                    'reference_id' => $evatek->evatek_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }
        }

        // ✅ DELETE notifikasi lama untuk item ini
        \App\Models\VendorNotification::where('vendor_id', $evatek->vendor_id)
            ->whereIn('title', ['Menunggu Review Desain', 'Evatek Disetujui', 'Evatek Ditolak'])
            ->where('link', route('vendor.evatek.review', $evatek->evatek_id))
            ->delete();

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
