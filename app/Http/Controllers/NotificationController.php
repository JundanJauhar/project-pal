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

        foreach ($dbNotifications as $notif) {
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
                'category' => 'inbox'
            ]);
        }

        // 2. EVATEK TASKS (Only for Desain users)
        // Check if user has role 'desain' or is in design division
        if ((isset($user->division) && stripos($user->division->name, 'desain') !== false)) {
            
            // Find Evatek Items with Pending Revisions that have Vendor Link
            // Pending means: status 'pending' (waiting for design review)
            // AND vendor_link is not null (vendor has submitted)
            $pendingEvatek = EvatekItem::whereHas('latestRevision', function($q) {
                    $q->whereIn('status', ['pending', 'revisi']) // status pending means waiting review
                      ->whereNotNull('vendor_link')
                      ->where('vendor_link', '!=', ''); // Ensure strictly not empty
                })
                ->with(['item', 'latestRevision', 'procurement.project'])
                ->get();

            foreach ($allPendingEvatek as $evatek) {
                $latest = $evatek->latestRevision;
                if (!$latest) continue;

                $vendorLink = trim($latest->vendor_link ?? '');
                $itemName = $evatek->item->item_name ?? 'Item';
                $revCode = $latest->revision_code;
                $vendorName = $evatek->vendor->name_vendor ?? 'Vendor';

                // CHECK STATUS FIRST
                if ($latest->status === 'approve') {
                    $notifications->push((object)[
                        'id' => 'task_ev_done_' . $evatek->evatek_id,
                        'is_stored' => false,
                        'is_read' => true, // Mark as "read" visually or just distinct? User said "notif berubah". Keep it unread-like but Green.
                        'is_starred' => false,
                        'type' => 'success',
                        'icon' => 'bi-check-circle-fill',
                        'color' => '#28a745',
                        'title' => 'Evatek Disetujui',
                        'message' => "Evatek '{$itemName}' ({$revCode}) telah DISETUJUI.",
                        'link' => route('desain.review-evatek', $evatek->evatek_id),
                        'date' => $latest->updated_at ?? $latest->created_at,
                        'action_label' => 'Lihat Detail',
                        'category' => 'inbox'
                    ]);
                } elseif ($latest->status === 'not_approve') {
                    $notifications->push((object)[
                        'id' => 'task_ev_reject_' . $evatek->evatek_id,
                        'is_stored' => false,
                        'is_read' => false,
                        'is_starred' => false,
                        'type' => 'danger',
                        'icon' => 'bi-x-circle-fill',
                        'color' => '#dc3545',
                        'title' => 'Evatek Ditolak',
                        'message' => "Evatek '{$itemName}' ({$revCode}) DITOLAK.",
                        'link' => route('desain.review-evatek', $evatek->evatek_id),
                        'date' => $latest->updated_at ?? $latest->created_at,
                        'action_label' => 'Lihat Detail',
                        'category' => 'inbox'
                    ]);
                } elseif (empty($vendorLink)) {
                    // CASE A: Vendor Link Empty => "Menunggu Vendor" (Task 2)
                    $notifications->push((object)[
                        'id' => 'task_ev_wait_' . $evatek->evatek_id,
                        'is_stored' => false,
                        'is_read' => false,
                        'is_starred' => false,
                        'type' => 'info',
                        'icon' => 'bi-clock-history',
                        'color' => '#6c757d',
                        'title' => 'Menunggu Vendor',
                        'message' => "Menunggu respon dari Vendor {$vendorName} untuk item '{$itemName}' ({$revCode}).",
                        'link' => route('desain.review-evatek', $evatek->evatek_id),
                        'date' => $latest->updated_at ?? $latest->created_at,
                        'action_label' => 'Lihat Status',
                        'category' => 'vendor'
                    ]);
                } else {
                    // CASE B: Vendor Link FILLED => "Review Evatek" / "Input Link" (Task 1)
                    $msgTitle = 'Review Evatek Diperlukan';
                    $msgBody = "Vendor telah mengupload dokumen untuk item '{$itemName}' ({$revCode}). Silakan review.";

                    if (empty($latest->design_link)) {
                        $msgTitle = 'Input Link Desain Diperlukan';
                        $msgBody = "Vendor telah upload ({$itemName}). Harap Divisi input link desain/review.";
                        $actionLabel = 'Input Link';
                    } else {
                        $actionLabel = 'Mulai Review';
                    }

                    $notifications->push((object)[
                        'id' => 'task_ev_' . $evatek->evatek_id,
                        'is_stored' => false,
                        'is_read' => false,
                        'is_starred' => false,
                        'type' => 'action',
                        'icon' => 'bi-pencil-square',
                        'color' => '#0d6efd',
                        'title' => $msgTitle,
                        'message' => $msgBody,
                        'link' => route('desain.review-evatek', $evatek->evatek_id),
                        'date' => $latest->updated_at ?? $latest->created_at,
                        'action_label' => $actionLabel ?? 'Mulai Review',
                        'category' => 'division'
                    ]);
                }
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

        if ($isScm) {
            $reviews = \App\Models\ContractReview::where('status', '!=', 'completed')
                ->with(['procurement.project', 'vendor', 'revisions'])
                ->get();

            foreach ($reviews as $review) {
                // Get latest revision (sort by ID desc)
                $latest = $review->revisions->sortByDesc('contract_review_revision_id')->first();

                if (!$latest) continue;

                $projName = $review->procurement->project->project_name ?? 'Project';
                $vendorName = $review->vendor->name_vendor ?? 'Vendor';

                // LOGIC: Show notification for ALL states but customize appearance
                // 1. Action Needed (Vendor uploaded, SCM needs to review)
                if (!empty($latest->vendor_link) && in_array($latest->result, ['pending', 'revisi'])) {
                    $title = 'Review Kontrak Diperlukan';
                    $msg = "Vendor {$vendorName} telah mengupload dokumen kontrak {$projName} ({$latest->revision_code}). Silakan review.";
                    $type = 'action';
                    $color = '#fd7e14'; // Orange
                    $icon = 'bi-file-earmark-text';
                    $actionLabel = 'Review Kontrak';
                }
                // 2. Waiting (Vendor needs to upload)
                elseif (empty($latest->vendor_link) && in_array($latest->result, ['pending', 'revisi'])) {
                    $title = 'Menunggu Vendor';
                    $msg = "Menunggu Vendor {$vendorName} mengupload dokumen kontrak {$projName} ({$latest->revision_code}).";
                    $type = 'info';
                    $color = '#6c757d'; // Gray
                    $icon = 'bi-clock-history';
                    $actionLabel = 'Lihat Detail';
                }
                // 3. Approved
                elseif ($latest->result == 'approve') {
                    // Persistent notification per user request
                    $title = 'Kontrak Disetujui';
                    $msg = "Review kontrak {$projName} ({$latest->revision_code}) dengan Vendor {$vendorName} telah DISETUJUI.";
                    $type = 'success';
                    $color = '#28a745'; // Green
                    $icon = 'bi-check-circle-fill';
                    $actionLabel = 'Lihat Detail';
                }
                // 4. Rejected
                elseif ($latest->result == 'not_approve') {
                    $title = 'Kontrak Ditolak';
                    $msg = "Review kontrak {$projName} ({$latest->revision_code}) dengan Vendor {$vendorName} DITOLAK.";
                    $type = 'danger';
                    $color = '#dc3545'; // Red
                    $icon = 'bi-x-circle-fill';
                    $actionLabel = 'Lihat Detail';
                } else {
                    continue;
                }

                $category = 'inbox'; // Default
                if ($type === 'action') {
                    $category = 'division';
                } elseif ($title === 'Menunggu Vendor') {
                    $category = 'vendor';
                }

                $notifications->push((object)[
                    'id' => 'task_cr_scm_' . $review->contract_review_id . '_' . $latest->revision_code,
                    'is_stored' => false,
                    'is_read' => false,
                    'type' => $type,
                    'icon' => $icon,
                    'color' => $color,
                    'title' => $title,
                    'message' => $msg,
                    'link' => route('supply-chain.contract-review.show', $review->contract_review_id),
                    'date' => $latest->updated_at ?? $latest->created_at,
                    'action_label' => $actionLabel,
                    'category' => $category
                ]);
            }
        }

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
