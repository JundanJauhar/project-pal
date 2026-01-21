<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\RequestProcurement;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use App\Models\ContractReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorEvatekController extends Controller
{
    /**
     * Display vendor evatek index (halaman utama vendor)
     */
    public function index()
    {
        // Get vendor yang login menggunakan guard 'vendor'
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            abort(403, 'Vendor not authenticated');
        }

        // Ambil evatek items HANYA untuk vendor ini berdasarkan id_vendor
        $evatekItems = EvatekItem::where('vendor_id', $vendor->id_vendor)
            ->with(['item', 'procurement', 'project','latestRevision'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Ambil contract reviews untuk vendor ini
        $contractReviews = ContractReview::where('vendor_id', $vendor->id_vendor)
            ->with([
                'procurement.project',
                'procurement.requestProcurements.items',
                'project',
                'revisions'
            ])
            ->orderBy('start_date', 'desc')
            ->get();

        // Statistics untuk vendor ini
           $stats = [
            'total_evatek' => EvatekItem::where('vendor_id', $vendor->id_vendor)->count(),
            'pending' => EvatekItem::where('vendor_id', $vendor->id_vendor)
                ->whereHas('latestRevision', function($q) {
                    $q->where('status', 'pending');
                })
                ->count(),
            'approved' => EvatekItem::where('vendor_id', $vendor->id_vendor)
                ->whereHas('latestRevision', function($q) {
                    $q->where('status', 'approve');
                })
                ->count(),
            'rejected' => EvatekItem::where('vendor_id', $vendor->id_vendor)
                ->whereHas('latestRevision', function($q) {
                    $q->where('status', 'not approve');
                })
                ->count(),
            'revisi' => EvatekItem::where('vendor_id', $vendor->id_vendor)
                ->whereHas('latestRevision', function($q) {
                    $q->where('status', 'revisi');
                })
                ->count(),
        ];

        // Statistics untuk Contract Review vendor ini
        $contractStats = [
            'total_review' => ContractReview::where('vendor_id', $vendor->id_vendor)->count(),
            'on_progress' => ContractReview::where('vendor_id', $vendor->id_vendor)
                ->where('status', 'on_progress')
                ->count(),
            'waiting_feedback' => ContractReview::where('vendor_id', $vendor->id_vendor)
                ->where('status', 'waiting_feedback')
                ->count(),
            'approved' => ContractReview::where('vendor_id', $vendor->id_vendor)
                ->whereHas('revisions', function($q) {
                    $q->where('result', 'approve');
                })
                ->count(),
            'revisi' => ContractReview::where('vendor_id', $vendor->id_vendor)
                ->whereHas('revisions', function($q) {
                    $q->where('result', 'revisi');
                })
                ->count(),
            'rejected' => ContractReview::where('vendor_id', $vendor->id_vendor)
                ->whereHas('revisions', function($q) {
                    $q->where('result', 'not_approve');
                })
                ->count(),
        ];


        return view('vendor.index', compact('vendor', 'evatekItems', 'contractReviews', 'stats', 'contractStats'));
    }
    

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

        // Initialize log if null
        if ($evatek->log === null) {
            $evatek->log = '';
        }

        return view('desain.review-evatek', compact('item', 'evatek', 'revisions'));
    }

    /**
     * Display vendor dashboard
     */
    public function dashboard()
    {
        // Get vendor yang login menggunakan guard 'vendor'
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            abort(403, 'Vendor not found');
        }

        // Ambil request procurement untuk vendor ini
        $requestProcurements = RequestProcurement::where('vendor_id', $vendor->id_vendor)
            ->with(['procurement', 'procurement.project', 'items'])
            ->orderBy('created_date', 'desc')
            ->paginate(10);

        // Ambil evatek items untuk vendor ini
        $evatekItems = EvatekItem::where('vendor_id', $vendor->id_vendor)
            ->with(['item', 'procurement', 'project',])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Statistics
        $stats = [
            'total_requests' => RequestProcurement::where('vendor_id', $vendor->id_vendor)->count(),
            'pending_requests' => RequestProcurement::where('vendor_id', $vendor->id_vendor)
                ->where('request_status', 'pending')->count(),
            'approved_requests' => RequestProcurement::where('vendor_id', $vendor->id_vendor)
                ->where('request_status', 'approved')->count(),
            'total_evatek' => EvatekItem::where('vendor_id', $vendor->id_vendor)->count(),
        ];

        return view('vendor.dashboard', compact('vendor', 'requestProcurements', 'evatekItems', 'stats'));
    }

    /**
     * Display detail request procurement
     */
    public function showRequest($requestId)
    {
        $vendor = Auth::guard('vendor')->user();

        $request = RequestProcurement::where('request_id', $requestId)
            ->where('vendor_id', $vendor->id_vendor)
            ->with(['procurement', 'procurement.project', 'items'])
            ->firstOrFail();

        return view('vendor.request-detail', compact('request', 'vendor'));
    }

    /**
     * Display evatek items for vendor
     */
    public function evatek()
    {
        $vendor = Auth::guard('vendor')->user();

        $evatekItems = EvatekItem::where('vendor_id', $vendor->id_vendor)
            ->with(['item', 'procurement', 'project'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('vendor.evatek', compact('vendor', 'evatekItems'));
    }

    /**
     * Display vendor profile
     */
    public function profile()
    {
        $vendor = Auth::guard('vendor')->user();

        if (!$vendor) {
            abort(403, 'Vendor not found');
        }

        return view('vendor.profile', compact('vendor'));
    }

    /**
     * Update vendor profile
     */
    public function updateProfile(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();

        if (!$vendor) {
            abort(403, 'Vendor not found');
        }

        $validated = $request->validate([
            'name_vendor' => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'required|email|max:100',
        ]);

        $vendor->update($validated);

        return redirect()->route('vendor.profile')
            ->with('success', 'Profil vendor berhasil diperbarui!');
    }

    /**
     * Review evatek for vendor (vendor-specific view)
     */
    public function reviewEvatek($evatekId)
    {
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            abort(403, 'Vendor not authenticated');
        }

        // Get evatek item and verify it belongs to this vendor
        $evatek = EvatekItem::with([
            'item',
            'vendor',
            'procurement',
        ])->findOrFail($evatekId);

        // Security check: pastikan evatek ini milik vendor yang login
        if ($evatek->vendor_id != $vendor->id_vendor) {
            abort(403, 'Unauthorized access to this evatek item');
        }

        $item = $evatek->item;

        // Get all revisions
        $revisions = EvatekRevision::where('evatek_id', $evatek->evatek_id)
            ->orderBy('revision_id', 'ASC')
            ->get();

        // Create initial revision if none exists
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

        // Initialize log if null
        if ($evatek->log === null) {
            $evatek->log = '';
        }

        return view('vendor.review-evatek', compact('item', 'evatek', 'revisions', 'vendor'));
    }

    /**
     * Save vendor link only (vendor can only update their own link)
     */
    public function saveVendorLink(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not authenticated'], 403);
        }

        $validated = $request->validate([
            'revision_id' => 'required|exists:evatek_revisions,revision_id',
            'vendor_link' => 'nullable|string|max:500',
        ]);

        $revision = EvatekRevision::findOrFail($validated['revision_id']);
        
        // Verify this revision belongs to vendor's evatek
        $evatek = EvatekItem::findOrFail($revision->evatek_id);
        if ($evatek->vendor_id != $vendor->id_vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Update only vendor_link, not design_link
        $revision->vendor_link = $validated['vendor_link'];
        $revision->save();

        return response()->json([
            'success' => true,
            'message' => 'Link berhasil disimpan',
            'revision' => $revision
        ]);
    }

    /**
     * Save log (for vendor communication)
     */
    public function saveLog(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not authenticated'], 403);
        }

        $validated = $request->validate([
            'evatek_id' => 'required|exists:evatek_items,evatek_id',
            'log' => 'required|string',
        ]);

        $evatek = EvatekItem::findOrFail($validated['evatek_id']);
        
        // Verify this evatek belongs to vendor
        if ($evatek->vendor_id != $vendor->id_vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $evatek->log = $validated['log'];
        $evatek->save();

        return response()->json([
            'success' => true,
            'message' => 'Log berhasil disimpan'
        ]);
    }

    /**
     * Display contract review detail for vendor
     */
    public function reviewContract($contractReviewId)
    {
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            abort(403, 'Vendor not authenticated');
        }

        $contractReview = ContractReview::with([
            'vendor',
            'procurement.project',
            'procurement.requestProcurements.items',
            'project',
            'revisions' => function($query) {
                $query->orderBy('revision_code', 'desc');
            }
        ])->findOrFail($contractReviewId);

        // Verify this contract review belongs to vendor
        if ($contractReview->vendor_id != $vendor->id_vendor) {
            abort(403, 'Unauthorized');
        }

        $revisions = $contractReview->revisions;

        if ($revisions->isEmpty()) {
            $revision = \App\Models\ContractReviewRevision::create([
                'contract_review_id' => $contractReview->contract_review_id,
                'revision_code' => 'R0',
                'vendor_link' => null,
                'sc_link' => null,
                'result' => 'pending',
                'created_by' => null,
            ]);

            $revisions = collect([$revision]);
        }

        // Initialize log if null
        if ($contractReview->log === null) {
            $contractReview->log = '';
        }

        return view('vendor.contract-review.show', compact('contractReview', 'revisions', 'vendor'));
    }

    /**
     * Save vendor link for contract review
     */
    public function saveContractLink(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not authenticated'], 403);
        }

        $validated = $request->validate([
            'revision_id' => 'required|exists:contract_review_revisions,contract_review_revision_id',
            'vendor_link' => 'nullable|string',
        ]);

        $revision = \App\Models\ContractReviewRevision::with('contractReview')->findOrFail($validated['revision_id']);
        
        // Verify this contract review belongs to vendor
        if ($revision->contractReview->vendor_id != $vendor->id_vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $revision->vendor_link = $validated['vendor_link'];
        
        // Set tanggal feedback vendor saat pertama kali vendor save link
        if (!$revision->date_vendor_feedback && $validated['vendor_link']) {
            $revision->date_vendor_feedback = now()->toDateString();
        }
        
        $revision->save();

        return response()->json([
            'success' => true,
            'message' => 'Link berhasil disimpan'
        ]);
    }

    /**
     * Save activity log for contract review
     */
    public function saveContractLog(Request $request)
    {
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            return response()->json(['success' => false, 'message' => 'Vendor not authenticated'], 403);
        }

        $validated = $request->validate([
            'contract_review_id' => 'required|exists:contract_reviews,contract_review_id',
            'log' => 'required|string',
        ]);

        $contractReview = ContractReview::findOrFail($validated['contract_review_id']);
        
        // Verify this contract review belongs to vendor
        if ($contractReview->vendor_id != $vendor->id_vendor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $contractReview->log = $validated['log'];
        $contractReview->save();

        return response()->json([
            'success' => true,
            'message' => 'Log berhasil disimpan'
        ]);
    }

    public function notifications()
    {
        $vendor = Auth::guard('vendor')->user();
        
        if (!$vendor) {
            abort(403, 'Vendor not authenticated');
        }

        $reviews = ContractReview::where('vendor_id', $vendor->id_vendor)
            ->with(['procurement.project', 'revisions' => function($q) {
                $q->orderBy('contract_review_revision_id', 'desc');
            }])
            ->get();

        $notifications = collect();

        foreach ($reviews as $review) {
            $latest = $review->revisions->first();
            if (!$latest) continue;

            $previous = $review->revisions->skip(1)->first();
            $projName = $review->procurement->project->project_name ?? 'Project';
            $revCode = $latest->revision_code;
            $link = route('vendor.contract-review.review', $review->contract_review_id);
            $date = $latest->updated_at ?? $latest->created_at;

            // STATUS: APPROVED
            if ($latest->result == 'approve') {
                $notifications->push((object)[
                    'type' => 'success',
                    'icon' => 'bi-check-circle-fill',
                    'color' => '#28a745', // Green
                    'title' => 'Kontrak Disetujui',
                    'message' => "Review kontrak untuk {$projName} ({$revCode}) telah DISETUJUI.",
                    'link' => $link,
                    'date' => $date,
                    'action_label' => 'Lihat Detail'
                ]);
            }
            // STATUS: NOT APPROVED
            elseif ($latest->result == 'not_approve') {
                $notifications->push((object)[
                    'type' => 'danger',
                    'icon' => 'bi-x-circle-fill',
                    'color' => '#dc3545', // Red
                    'title' => 'Kontrak Ditolak',
                    'message' => "Review kontrak untuk {$projName} ({$revCode}) DITOLAK.",
                    'link' => $link,
                    'date' => $date,
                    'action_label' => 'Lihat Detail'
                ]);
            }
            // STATUS: REVISI (Explicit result)
            // Note: Usually a new revision is created immediately, but if logic allows sticking on 'revisi':
            elseif ($latest->result == 'revisi') {
                $notifications->push((object)[
                    'type' => 'warning',
                    'icon' => 'bi-exclamation-triangle-fill',
                    'color' => '#ffc107', // Yellow
                    'title' => 'Perlu Revisi',
                    'message' => "Review kontrak untuk {$projName} ({$revCode}) meminta REVISI.",
                    'link' => $link,
                    'date' => $date,
                    'action_label' => 'Perbaiki Sekarang'
                ]);
            }
            // STATUS: PENDING (Potential Action Needed)
            elseif (!$latest->result || $latest->result == 'pending') {
                // If vendor link is EMPTY -> ACTION NEEDED
                if (empty($latest->vendor_link)) {
                    $isRevisi = ($previous && $previous->result == 'revisi');
                    $title = $isRevisi ? 'Revisi Diperlukan' : 'Butuh Upload Link';
                    $msg = $isRevisi 
                        ? "Permintaan revisi baru ({$revCode}) untuk {$projName}. Silakan upload dokumen perbaikan."
                        : "Silakan upload dokumen review kontrak untuk {$projName} ({$revCode}).";

                    $notifications->push((object)[
                        'type' => 'action',
                        'icon' => 'bi-upload',
                        'color' => '#d32f2f', // Red for action
                        'title' => $title,
                        'message' => $msg,
                        'link' => $link,
                        'date' => $latest->created_at, // Use created_at for pending items
                        'action_label' => 'Upload Dokumen'
                    ]);
                }
            }
        }

        // Sort by date descending
        $notifications = $notifications->sortByDesc('date');

        return view('vendor.notifications', compact('notifications'));
    }
}