<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class NotificationController extends Controller
{
    /**
     * Display all notifications for current user
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = collect();

        // 1. DATABASE NOTIFICATIONS
        $dbNotifications = Notification::where('user_id', $user->user_id)
            ->with(['sender'])
            ->orderBy('created_at', 'desc')
            ->limit(50) // Limit to avoid overload, or use pagination manually later
            ->get();
        // Optimization: Pre-fetch Evatek statuses for categorization
        $evatekIds = $dbNotifications->whereIn('title', ['Dokumen Evatek Diupload', 'Review Evatek Diperlukan'])->pluck('reference_id')->unique()->filter();
        $evatekActionMap = [];
        $evatekItems = collect(); // Initialize empty collection

        if ($evatekIds->isNotEmpty()) {
            $eItems = EvatekItem::whereIn('evatek_id', $evatekIds)->with('latestRevision')->get();
            foreach ($eItems as $e) {
                // Action needed if NOT approved/rejected AND latest revision is pending+has link
                $isAction = !in_array($e->status, ['approve', 'not_approve'])
                    && $e->latestRevision
                    && $e->latestRevision->status === 'pending'
                    && !empty($e->latestRevision->vendor_link);
                $evatekActionMap[$e->evatek_id] = $isAction;
            }
            $evatekItems = $eItems; // Assign to variable for reuse
        }

        // Also load all Evatek items referenced in any notification for badge determination
        $allEvatekRefIds = $dbNotifications->where('reference_type', 'App\\Models\\EvatekItem')->pluck('reference_id')->unique()->filter();
        if ($allEvatekRefIds->isNotEmpty()) {
            $additionalEvateks = EvatekItem::whereIn('evatek_id', $allEvatekRefIds)->with('latestRevision')->get();
            // Merge with existing evatekItems
            $evatekItems = $evatekItems->merge($additionalEvateks)->unique('evatek_id');
        }
        foreach ($dbNotifications as $notif) {
            // ✅ Extract badge info from notification reference
            $badgeLabel = null;
            $badgeColor = null;

            // Determine badge based on reference type and title
            if ($notif->reference_type === 'App\Models\EvatekItem' && $notif->reference_id) {
                $evatekItem = $evatekItems->firstWhere('evatek_id', $notif->reference_id);
                if ($evatekItem && $evatekItem->latestRevision) {
                    $revCode = $evatekItem->latestRevision->revision_code;
                    $revStatus = $evatekItem->latestRevision->status;

                    if ($revStatus === 'approve') {
                        $badgeLabel = 'APPROVE';
                        $badgeColor = '#10B981'; // green
                    } elseif ($revStatus === 'not_approve') {
                        $badgeLabel = 'NOT APPROVE';
                        $badgeColor = '#EF4444'; // red
                    } elseif ($revStatus === 'revisi' || $revStatus === 'pending') {
                        // Check if this is initial creation (R0 with no vendor link)
                        if ($revCode === 'R0' && empty($evatekItem->latestRevision->vendor_link)) {
                            $badgeLabel = 'BARU';
                            $badgeColor = '#3B82F6'; // blue
                        } else {
                            $badgeLabel = 'REVISI ' . $revCode;
                            $badgeColor = '#F59E0B'; // yellow
                        }
                    }
                }
            } elseif ($notif->reference_type === 'App\Models\ContractReview' && $notif->reference_id) {
                // Get contract review info
                $contractReview = \App\Models\ContractReview::with('revisions')->find($notif->reference_id);
                if ($contractReview) {
                    $latestRevision = $contractReview->revisions()->latest('created_at')->first();
                    if ($latestRevision) {
                        $revCode = $latestRevision->revision_code;
                        $revStatus = $latestRevision->result ?? 'pending';

                        if ($revStatus === 'approve') {
                            $badgeLabel = 'APPROVE';
                            $badgeColor = '#10B981'; // green
                        } elseif ($revStatus === 'not_approve') {
                            $badgeLabel = 'NOT APPROVE';
                            $badgeColor = '#EF4444'; // red
                        } elseif ($revCode === 'R0' && empty($latestRevision->vendor_link)) {
                            $badgeLabel = 'BARU';
                            $badgeColor = '#3B82F6'; // blue
                        } else {
                            $badgeLabel = 'REVISI ' . $revCode;
                            $badgeColor = '#F59E0B'; // yellow
                        }
                    }
                }
            }

            // Fallback badge based on title if no reference-based badge
            if (!$badgeLabel) {
                $badgeLabel = match (true) {
                    // Complete statuses
                    str_contains($notif->title, 'Evatek Selesai') || str_contains($notif->title, 'Evatek Completed') => 'EVATEK COMPLETE',
                    str_contains($notif->title, 'Review Kontrak') && str_contains($notif->message, 'selesai') => 'REVIEW KONTRAK COMPLETE',
                    // Negotiation
                    str_contains($notif->title, 'Siap Negosiasi') || str_contains($notif->title, 'Negosiasi') => 'NEGOSIASI',
                    // Standard statuses
                    str_contains($notif->title, 'Baru') || str_contains($notif->title, 'Dimulai') => 'BARU',
                    str_contains($notif->title, 'Approve') || str_contains($notif->title, 'Disetujui') => 'APPROVE',
                    str_contains($notif->title, 'Ditolak') || str_contains($notif->title, 'Reject') => 'NOT APPROVE',
                    default => null
                };
                $badgeColor = match ($badgeLabel) {
                    'BARU' => '#3B82F6',
                    'APPROVE' => '#10B981',
                    'NOT APPROVE' => '#EF4444',
                    'EVATEK COMPLETE' => '#8B5CF6', // purple
                    'REVIEW KONTRAK COMPLETE' => '#8B5CF6', // purple
                    'NEGOSIASI' => '#F59E0B', // orange/yellow
                    default => null
                };
            }

            $notifications->push((object)[
                'id' => $notif->notification_id,
                'is_stored' => true,
                'is_read' => $notif->is_read,
                'is_starred' => $notif->is_starred ?? false,
                'type' => $notif->type,
                'icon' => match ($notif->type) {
                    'success' => 'bi-check-circle-fill',
                    'danger' => 'bi-x-circle-fill',
                    'warning' => 'bi-exclamation-triangle-fill',
                    'info' => 'bi-info-circle-fill',
                    'action' => 'bi-hand-index-thumb-fill',
                    default => 'bi-bell-fill'
                },
                'color' => match ($notif->type) {
                    'success' => '#28a745',
                    'danger' => '#dc3545',
                    'warning' => '#ffc107',
                    'info' => '#17a2b8',
                    'action' => '#fd7e14',
                    default => '#6c757d'
                },
                'title' => $notif->title,
                'message' => $notif->message,
                'link' => (!empty($notif->action_url) && $notif->action_url !== '#') ? $notif->action_url : (
                    ($notif->reference_type === 'App\Models\EvatekItem' && $notif->reference_id)
                    ? route('desain.review-evatek', $notif->reference_id)
                    : (
                        ($notif->reference_type === 'App\Models\ContractReview' && $notif->reference_id)
                        ? route('supply-chain.contract-review.show', $notif->reference_id)
                        : (
                            ($notif->reference_type === 'App\Models\Procurement' && $notif->reference_id)
                            ? route('procurements.show', $notif->reference_id)
                            : '#'
                        )
                    )
                ),
                'date' => $notif->created_at,
                'action_label' => 'Lihat Detail',
                'badge_label' => $badgeLabel,
                'badge_color' => $badgeColor,
                'category' => match (true) {
                    // ✅ Evatek uploaded - Check status (Dynamic)
                    // ✅ Evatek uploaded - Check status (Dynamic)
                    ($notif->title === 'Dokumen Evatek Diupload' || $notif->title === 'Review Evatek Diperlukan' && ($evatekActionMap[$notif->reference_id] ?? false)) => 'division',
                    $notif->title === 'Dokumen Evatek Diupload' || $notif->title === 'Review Evatek Diperlukan' => 'inbox',
                    // ✅ Evatek - Link incomplete
                    $notif->title === 'Lengkapi Dokumen Evatek' || $notif->title === 'Perlu Isi Link Evatek' => 'division',
                    // ✅ Contract review - vendor uploaded, SCM needs to review
                    $notif->title === 'Review Kontrak Diperlukan' => 'division',
                    // ✅ Contract review - SCM waiting for vendor
                    $notif->title === 'Menunggu Vendor' => 'vendor',
                    // ✅ Contract review - SC needs to upload link
                    $notif->title === 'Lengkapi Dokumen Kontrak' => 'division',
                    // ✅ Contract review action (bukan negotiation)
                    ($notif->type === 'action' && $notif->title !== 'Siap Negosiasi') => 'division',
                    default => 'inbox'
                }
            ]);
        }

        // 2. EVATEK TASKS (Only for Desain users)
        // Check if user has role 'desain' or is in design division
        if ((isset($user->division) && stripos($user->division->name, 'desain') !== false)) {

            // Find Evatek Items with Pending Revisions OR Completed recently
            $evatekItems = EvatekItem::whereHas('latestRevision', function ($q) {
                $q->whereIn('status', ['pending', 'revisi'])
                    ->orWhere(function ($q2) {
                        $q2->whereIn('status', ['approve', 'not_approve'])
                            ->where('updated_at', '>=', now()->subDays(3));
                    });
            })
                ->with(['item', 'latestRevision', 'procurement.project', 'vendor'])
                ->get();

            foreach ($evatekItems as $evatek) {
                // Ensure latest revision exists
                $latest = $evatek->latestRevision;
                if (!$latest) continue;

                $vendorLink = trim($latest->vendor_link ?? '');
                $itemName = $evatek->item->item_name ?? 'Item';
                $revCode = $latest->revision_code;
                $vendorName = $evatek->vendor->name_vendor ?? 'Vendor';
                $revStatus = $latest->status;

                // ✅ Determine badge
                $badgeLabel = null;
                $badgeColor = null;

                if ($revStatus === 'approve') {
                    $badgeLabel = 'APPROVE';
                    $badgeColor = '#10B981'; // green
                } elseif ($revStatus === 'not_approve') {
                    $badgeLabel = 'NOT APPROVE';
                    $badgeColor = '#EF4444'; // red
                } elseif ($revCode === 'R0' && empty($vendorLink)) {
                    $badgeLabel = 'BARU';
                    $badgeColor = '#3B82F6'; // blue
                } else {
                    $badgeLabel = 'REVISI ' . $revCode;
                    $badgeColor = '#F59E0B'; // yellow
                }

                $notifications->push((object)[
                    'id' => 'task_ev_' . $evatek->evatek_id,
                    'is_stored' => false,
                    'is_read' => false,
                    'is_starred' => false,
                    'date' => $latest->updated_at ?? $latest->created_at,
                    'badge_label' => $badgeLabel,
                    'badge_color' => $badgeColor,

                    // Determine content dynamically
                    ...match (true) {
                        // 1. COMPLETED: Approved
                        ($latest->status === 'approve') => [
                            'type' => 'success',
                            'icon' => 'bi-check-circle-fill',
                            'color' => '#28a745',
                            'title' => 'Evatek Disetujui',
                            'message' => "Evatek '{$itemName}' ({$revCode}) telah DISETUJUI.",
                            'link' => route('desain.review-evatek', $evatek->evatek_id),
                            'action_label' => 'Lihat Detail',
                            'category' => 'inbox'
                        ],
                        // 2. COMPLETED: Rejected
                        ($latest->status === 'not_approve') => [
                            'type' => 'danger',
                            'icon' => 'bi-x-circle-fill',
                            'color' => '#dc3545',
                            'title' => 'Evatek Ditolak',
                            'message' => "Evatek '{$itemName}' ({$revCode}) DITOLAK.",
                            'link' => route('desain.review-evatek', $evatek->evatek_id),
                            'action_label' => 'Lihat Detail',
                            'category' => 'inbox'
                        ],
                        // 3. WAITING VENDOR: Vendor Link Empty
                        (empty($vendorLink)) => [
                            'type' => 'info',
                            'icon' => 'bi-clock-history',
                            'color' => '#6c757d',
                            'title' => 'Menunggu Vendor',
                            'message' => "Menunggu respon dari Vendor {$vendorName} untuk item '{$itemName}' ({$revCode}).",
                            'link' => route('desain.review-evatek', $evatek->evatek_id),
                            'action_label' => 'Lihat Status',
                            'category' => 'vendor' // Matches "Di Vendor"
                        ],
                        // 4. WAITING DESIGN: Vendor Link Exists (Default fallback for pending/revisi w/ link)
                        default => [
                            'type' => 'action',
                            'icon' => 'bi-pencil-square',
                            'color' => '#0d6efd',
                            'title' => 'Review Evatek Diperlukan',
                            'message' => "Vendor telah mengupload dokumen untuk item '{$itemName}' ({$revCode}). Silakan review.",
                            'link' => route('desain.review-evatek', $evatek->evatek_id),
                            'action_label' => 'Mulai Review',
                            'category' => 'division' // Matches "Di Divisi"
                        ]
                    }
                ]);
            }
        }


        // 2b. EVATEK MONITORING (For Supply Chain)
        // Supply Chain needs to know the process status (Vendor vs Desain)
        $isScm = ($user->roles === 'supply_chain') || (method_exists($user, 'hasRole') && $user->hasRole('supply_chain'));

        if ($isScm) {
            $allEvatek = EvatekItem::whereHas('latestRevision', function ($q) {
                $q->whereIn('status', ['pending', 'revisi'])
                    ->orWhere(function ($q2) {
                        $q2->whereIn('status', ['approve', 'not_approve'])
                            ->where('updated_at', '>=', now()->subDays(3));
                    });
            })
                ->with(['item', 'latestRevision', 'vendor', 'procurement.project'])
                ->get();

            foreach ($allEvatek as $evatek) {
                $latest = $evatek->latestRevision;
                if (!$latest) continue;

                $vendorLink = trim($latest->vendor_link ?? '');
                $itemName = $evatek->item->item_name ?? 'Item';
                $vendorName = $evatek->vendor->name_vendor ?? 'Vendor';
                $revCode = $latest->revision_code;

                if ($latest->status === 'approve') {
                    $notifications->push((object)[
                        'id' => 'mon_ev_ok_' . $evatek->evatek_id,
                        'is_stored' => false,
                        'is_read' => true,
                        'is_starred' => false,
                        'type' => 'success',
                        'icon' => 'bi-check-circle-fill',
                        'color' => '#28a745',
                        'title' => 'Evatek Completed',
                        'message' => "Evatek {$itemName} ({$revCode}) telah DISETUJUI.",
                        'link' => '#',
                        'date' => $latest->updated_at ?? $latest->created_at,
                        'action_label' => 'Detail',
                        'category' => 'monitoring'
                    ]);
                } elseif ($latest->status === 'not_approve') {
                    $notifications->push((object)[
                        'id' => 'mon_ev_fail_' . $evatek->evatek_id,
                        'is_stored' => false,
                        'is_read' => false,
                        'is_starred' => false,
                        'type' => 'danger',
                        'icon' => 'bi-x-circle-fill',
                        'color' => '#dc3545',
                        'title' => 'Evatek Rejected',
                        'message' => "Evatek {$itemName} ({$revCode}) DITOLAK.",
                        'link' => '#',
                        'date' => $latest->updated_at ?? $latest->created_at,
                        'action_label' => 'Detail',
                        'category' => 'monitoring'
                    ]);
                } elseif (empty($vendorLink)) {
                    // Status: At Vendor
                    $notifications->push((object)[
                        'id' => 'mon_ev_vend_' . $evatek->evatek_id,
                        'is_stored' => false,
                        'is_read' => false,
                        'is_starred' => false,
                        'type' => 'info', // Info only
                        'icon' => 'bi-eye',
                        'color' => '#6c757d',
                        'title' => 'Evatek: Menunggu Vendor',
                        'message' => "Evatek {$itemName} ({$revCode}) sedang menunggu respon Vendor {$vendorName}.",
                        'link' => '#', // SCM might not have access to Review page, or can view readonly? Assuming # or correct route
                        'date' => $latest->updated_at ?? $latest->created_at,
                        'action_label' => 'Monitor',
                        'category' => 'monitoring' // Changed from 'vendor' to avoid polluting SCM labels
                    ]);
                } else {
                    // Status: At Desain
                    $notifications->push((object)[
                        'id' => 'mon_ev_des_' . $evatek->evatek_id,
                        'is_stored' => false,
                        'is_read' => false,
                        'is_starred' => false,
                        'type' => 'info',
                        'icon' => 'bi-eye',
                        'color' => '#17a2b8',
                        'title' => 'Evatek: Di Divisi Desain',
                        'message' => "Evatek {$itemName} ({$revCode}) sedang direview oleh Divisi Desain.",
                        'link' => '#',
                        'date' => $latest->updated_at ?? $latest->created_at,
                        'action_label' => 'Monitor',
                        'category' => 'monitoring' // Changed from 'division' to avoid polluting SCM labels
                    ]);
                }
            }
        }

        // 3. CONTRACT REVIEW TASKS (Only for Supply Chain users)

        /*
        // 3. CONTRACT REVIEW TASKS (Only for Supply Chain users)
        // ✅ Computed tasks removed - Now using Stored Notifications logic (like vendor)
        if ($isScm) {
             // Computed tasks logic disabled to prevent duplicates
        }
        */

        // Adjust DB Notifications and Evatek to have category too
        // We need to re-loop or adjust the previous loops. 
        // Since I can't easily jump back to previous loops in a single replace without touching too much code,
        // I will map over the final collection to ensure 'category' exists if missing, 
        // OR better, I will edit the previous loops in subsequent steps or try to do it all now if possible.
        // Actually, looking at the previous file content, I can't easily reach the first loop in this single block because it's too far up.
        // I will just add the category for SCM here, and then do a second replace for the others.


        // Sort merged notifications by date desc
        $notifications = $notifications->sortByDesc('date');

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('notification_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount()
    {
        $count = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }
    public function toggleStar($id)
    {
        $notification = Notification::where('notification_id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->update([
            'is_starred' => !$notification->is_starred
        ]);

        return response()->json([
            'success' => true,
            'is_starred' => $notification->is_starred,
            'message' => 'Notification star toggled'
        ]);
    }
}
