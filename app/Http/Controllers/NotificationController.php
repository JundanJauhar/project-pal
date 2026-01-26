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
                'type' => $notif->type,
                'icon' => match($notif->type) {
                    'success' => 'bi-check-circle-fill',
                    'danger' => 'bi-x-circle-fill',
                    'warning' => 'bi-exclamation-triangle-fill',
                    'info' => 'bi-info-circle-fill',
                    'action' => 'bi-hand-index-thumb-fill',
                    default => 'bi-bell-fill'
                },
                'color' => match($notif->type) {
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
                'action_label' => 'Lihat Detail'
            ]);
        }

        // 2. EVATEK TASKS (Only for Desain users)
        // Check if user has role 'desain' or is in design division
        if ($user->hasRole('desain') || (isset($user->division) && stripos($user->division->name, 'desain') !== false)) {
            
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

            foreach ($pendingEvatek as $evatek) {
                $latest = $evatek->latestRevision;
                // Double check memory side
                if (!$latest || empty($latest->vendor_link)) continue;
                
                // If status is pending, it means Desain needs to review
                // If it was 'revisi', it might mean vendor needs to revise (so Desain doesn't see it as task)
                // BUT if status is 'revisi' usually it means Desain ASKED for revision.
                // However, if vendor UPLOADED new link, status might still be pending?
                // In EvatekController::revise(), status becomes 'revisi', then creates NEW revision with 'pending'.
                // So we are looking for 'pending' status.
                
                if ($latest->status !== 'pending') continue;

                $itemName = $evatek->item->item_name ?? 'Item';
                $revCode = $latest->revision_code;
                
                $notifications->push((object)[
                    'id' => 'task_ev_' . $evatek->evatek_id,
                    'is_stored' => false,
                    'is_read' => false, // Tasks are unread until done
                    'type' => 'action',
                    'icon' => 'bi-pencil-square',
                    'color' => '#0d6efd', // Blue for action
                    'title' => 'Review Evatek Diperlukan',
                    'message' => "Vendor telah mengupload dokumen untuk item '{$itemName}' ({$revCode}). Silakan review.",
                    'link' => route('desain.review-evatek', $evatek->evatek_id),
                    'date' => $latest->updated_at ?? $latest->created_at,
                    'action_label' => 'Mulai Review'
                ]);
            }
        }

        // 3. CONTRACT REVIEW TASKS (Only for Supply Chain users)
        $isScm = ($user->roles === 'supply_chain') || (method_exists($user, 'hasRole') && $user->hasRole('supply_chain'));
        
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
                }
                else {
                    continue;
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
                    'action_label' => $actionLabel
                ]);
            }
        }

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
}
