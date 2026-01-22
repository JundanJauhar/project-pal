<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Procurement;
use App\Models\RequestProcurement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\ActivityLogger;

use App\Models\EvatekItem;

class DesainController extends Controller
{
    public function dashboard()
    {
        $projects = Project::with(['ownerDivision', 'evatek', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'total' => $projects->count(),
            'sedang_proses' => $projects->whereNotIn('status_project', ['completed'])->count(),
            'selesai' => $projects->where('status_project', 'completed')->count(),
            'ditolak' => $projects->where('status_project', 'rejected')->count(),
        ];

        ActivityLogger::log(
            module: 'Desain',
            action: 'open_dashboard',
            targetId: null,
            details: ['user_id' => Auth::id()]
        );

        return view('desain.dashboard', compact('projects', 'stats'));
    }

    public function notifications()
    {
        // 1. STORED NOTIFICATIONS (System Events)
        $storedNotifs = \App\Models\Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        $notifications = collect();
        
        foreach($storedNotifs as $sn) {
            $notifications->push((object)[
                'id' => $sn->notification_id,
                'is_stored' => true,
                'is_read' => $sn->is_read,
                'type' => $sn->type == 'info' ? 'info' : $sn->type,
                'icon' => 'bi-info-circle-fill',
                'color' => '#17a2b8', // Info blue
                'title' => $sn->title,
                'message' => $sn->message,
                'link' => (!empty($sn->action_url) && $sn->action_url !== '#') ? $sn->action_url : (
                    ($sn->reference_type === 'App\Models\EvatekItem' && $sn->reference_id) 
                        ? route('desain.review-evatek', $sn->reference_id)
                        : '#'
                ),
                'date' => $sn->created_at,
                'action_label' => 'Lihat'
            ]);
        }

        // 2. COMPUTED TASKS (Evatek Items)
        $evatekItems = EvatekItem::with(['item', 'procurement.project', 'latestRevision', 'vendor'])
            ->get();

        foreach ($evatekItems as $evatek) {
            $latest = $evatek->latestRevision;
            if (!$latest) continue;

            $itemName = $evatek->item->item_name ?? 'Item';
            $vendorName = $evatek->vendor->name_vendor ?? 'Vendor';
            $revCode = $latest->revision_code;
            $link = route('desain.review-evatek', $evatek->evatek_id);
            $date = $latest->updated_at ?? $latest->created_at;

            // PENDING (Potential Action)
            if ($latest->status == 'pending') {
                // If vendor link is PRESENT -> ACTION NEEDED (Review)
                if (!empty($latest->vendor_link)) {
                    $notifications->push((object)[
                        'is_stored' => false,
                        'is_read' => false,
                        'type' => 'action',
                        'icon' => 'bi-file-earmark-check-fill',
                        'color' => '#d32f2f', // Red for action
                        'title' => 'Review Diperlukan',
                        'message' => "Vendor {$vendorName} telah mengunggah dokumen untuk item {$itemName} ({$revCode}). Silakan review.",
                        'link' => $link,
                        'date' => $date,
                        'action_label' => 'Review Sekarang'
                    ]);
                } 
                // If vendor link is EMPTY -> WAITING
                // (Optional: Hide waiting tasks to reduce clutter if stored notification covers "upload" event?)
                // But stored notification is from PAST uploads.
                // Keeping "Waiting" logic for visibility.
                else {
                    $notifications->push((object)[
                        'is_stored' => false,
                        'is_read' => true, // Info only
                        'type' => 'info',
                        'icon' => 'bi-hourglass-split',
                        'color' => '#6c757d', // Grey
                        'title' => 'Menunggu Vendor',
                        'message' => "Menunggu vendor {$vendorName} mengunggah dokumen untuk item {$itemName} ({$revCode}).",
                        'link' => $link,
                        'date' => $date,
                        'action_label' => 'Lihat Detail'
                    ]);
                }
            }
            // REVISI (Waiting)
            elseif ($latest->status == 'revisi') {
                $notifications->push((object)[
                    'is_stored' => false,
                    'is_read' => true,
                    'type' => 'info',
                    'icon' => 'bi-arrow-repeat',
                    'color' => '#ffc107', // Yellow
                    'title' => 'Menunggu Revisi',
                    'message' => "Menunggu revisi dari vendor {$vendorName} untuk item {$itemName} ({$revCode}).",
                    'link' => $link,
                    'date' => $date,
                    'action_label' => 'Lihat Detail'
                ]);
            }
            // APPROVED/NOT APPROVED are handled by Stored Notifications if we add logic for them? 
            // In EvatekController::approve/reject, we notified VENDOR. 
            // We did NOT notify Desain (self). So we rely on Computed for History?
            // "Events" for success/reject could be computed here.
            
            elseif ($latest->status == 'approve') {
                $notifications->push((object)[
                    'is_stored' => false,
                    'is_read' => true,
                    'type' => 'success',
                    'icon' => 'bi-check-circle-fill',
                    'color' => '#28a745',
                    'title' => 'Selesai',
                    'message' => "Evatek untuk item {$itemName} ({$revCode}) telah disetujui.",
                    'link' => $link,
                    'date' => $date,
                    'action_label' => 'Lihat Detail'
                ]);
            }
            elseif ($latest->status == 'not approve') {
                $notifications->push((object)[
                    'is_stored' => false,
                    'is_read' => true,
                    'type' => 'danger',
                    'icon' => 'bi-x-circle-fill',
                    'color' => '#dc3545',
                    'title' => 'Ditolak',
                    'message' => "Evatek untuk item {$itemName} ({$revCode}) DITOLAK.",
                    'link' => $link,
                    'date' => $date,
                    'action_label' => 'Lihat Detail'
                ]);
            }
        }

        // Sort by date descending
        $notifications = $notifications->sortByDesc('date');

        return view('desain.notifications', compact('notifications'));
    }
}
