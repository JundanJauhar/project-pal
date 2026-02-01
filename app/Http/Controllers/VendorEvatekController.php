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
            ->with(['item', 'procurement', 'project', 'latestRevision'])
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
                ->whereHas('latestRevision', function ($q) {
                    $q->where('status', 'pending');
                })
                ->count(),
            'approved' => EvatekItem::where('vendor_id', $vendor->id_vendor)
                ->whereHas('latestRevision', function ($q) {
                    $q->where('status', 'approve');
                })
                ->count(),
            'rejected' => EvatekItem::where('vendor_id', $vendor->id_vendor)
                ->whereHas('latestRevision', function ($q) {
                    $q->where('status', 'not approve');
                })
                ->count(),
            'revisi' => EvatekItem::where('vendor_id', $vendor->id_vendor)
                ->whereHas('latestRevision', function ($q) {
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
                ->whereHas('revisions', function ($q) {
                    $q->where('result', 'approve');
                })
                ->count(),
            'revisi' => ContractReview::where('vendor_id', $vendor->id_vendor)
                ->whereHas('revisions', function ($q) {
                    $q->where('result', 'revisi');
                })
                ->count(),
            'rejected' => ContractReview::where('vendor_id', $vendor->id_vendor)
                ->whereHas('revisions', function ($q) {
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

        // Base validation rules
        $rules = [
            'name_vendor' => 'required|string|max:100',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'required|email|max:100',
        ];

        // Add password validation if user wants to change password
        if ($request->filled('current_password') || $request->filled('new_password')) {
            $rules['current_password'] = 'required|string';
            $rules['new_password'] = 'required|string|min:6|confirmed';
        }

        $validated = $request->validate($rules, [
            'name_vendor.required' => 'Nama perusahaan wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'current_password.required' => 'Password saat ini wajib diisi untuk mengubah password.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Verify current password if changing password
        if ($request->filled('current_password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $vendor->password)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['current_password' => 'Password saat ini tidak sesuai.']);
            }
        }

        // Update basic profile info
        $vendor->name_vendor = $validated['name_vendor'];
        $vendor->address = $validated['address'] ?? $vendor->address;
        $vendor->phone_number = $validated['phone_number'] ?? $vendor->phone_number;
        $vendor->email = $validated['email'];

        // Update password if provided
        if ($request->filled('new_password')) {
            $vendor->password = \Illuminate\Support\Facades\Hash::make($validated['new_password']);
        }

        $vendor->save();

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

        // ✅ AUTO MARK-AS-READ: Mark notification as read when vendor opens this page
        \App\Models\VendorNotification::where('vendor_id', $vendor->id_vendor)
            ->where('title', 'Menunggu Review Desain')
            ->where('link', route('vendor.evatek.review', $evatekId))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);


        $item = $evatek->item;

        // Get all revisions
        $revisions = EvatekRevision::where('evatek_id', $evatek->evatek_id)
            ->orderBy('revision_id', 'DESC')
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

        // ✅ UPDATE EVATEK STATUS - Match logic from EvatekController
        $this->updateEvatekStatusForVendor($evatek);


        // NOTIFY DESAIN USERS
        $desainUsers = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('role_code', 'desain');
        })
            ->orWhereHas('division', function ($q) {
                $q->where('name', 'LIKE', '%desain%');
            })
            ->get();
        foreach ($desainUsers as $user) {
            // Check if design_link is filled
            $isDesignFilled = !empty($revision->design_link);
            $notifTitle = $isDesignFilled ? 'Review Evatek Diperlukan' : 'Perlu Isi Link Evatek';
            $notifMsg = $isDesignFilled
                ? "Vendor {$vendor->name_vendor} mengupload dokumen utk '{$evatek->item->item_name}' ({$revision->revision_code}). Dokumen Lengkap. Silakan Review."
                : "Vendor {$vendor->name_vendor} mengupload dokumen utk '{$evatek->item->item_name}' ({$revision->revision_code}). Silakan lengkapi link Desain.";

            // ✅ DELETE OLD Notifications (Clean up stale states)
            \App\Models\Notification::where('user_id', $user->user_id)
                ->where('reference_type', 'App\Models\EvatekItem')
                ->where('reference_id', $evatek->evatek_id)
                ->whereIn('title', ['Menunggu Vendor', 'Lengkapi Dokumen Evatek'])
                ->delete();

            // Check uniqueness
            $exists = \App\Models\Notification::where('user_id', $user->user_id)
                ->where('reference_type', 'App\Models\EvatekItem')
                ->where('reference_id', $evatek->evatek_id)
                ->where('title', $notifTitle)
                ->where('message', 'LIKE', "%({$revision->revision_code})%")
                ->exists();

            if (!$exists) {
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'sender_id' => null,
                    'type' => 'info',
                    'title' => $notifTitle,
                    'message' => $notifMsg,
                    'action_url' => route('desain.review-evatek', $evatek->evatek_id),
                    'reference_type' => 'App\Models\EvatekItem',
                    'reference_id' => $evatek->evatek_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }
        }


        // ✅ DELETE notifikasi "Evatek Baru" atau "Revisi Diperlukan" untuk item ini
        // Agar tidak ada duplikasi - hanya 1 notifikasi aktif per item di label
        \App\Models\VendorNotification::where('vendor_id', $vendor->id_vendor)
            ->whereIn('title', ['Evatek Baru', 'Revisi Diperlukan'])
            ->where('link', route('vendor.evatek.review', $evatek->evatek_id))
            ->delete();

        // ✅ NOTIFY VENDOR: Menunggu Review Desain (Unik per Revisi)
        $existsVendor = \App\Models\VendorNotification::where('vendor_id', $vendor->id_vendor)
            ->where('title', 'Menunggu Review Desain')
            ->where('link', route('vendor.evatek.review', $evatek->evatek_id))
            ->where('message', 'LIKE', "%({$revision->revision_code})%")
            ->exists();

        if (!$existsVendor) {
            \App\Models\VendorNotification::create([
                'vendor_id' => $vendor->id_vendor,
                'type' => 'info',
                'title' => 'Menunggu Review Desain',
                'message' => "Dokumen Evatek untuk item '{$evatek->item->item_name}' ({$revision->revision_code}) sedang direview oleh tim Desain.",
                'link' => route('vendor.evatek.review', $evatek->evatek_id),
                'is_read' => false,
                'created_at' => now(),
            ]);
        }

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
            'revisions' => function ($query) {
                $query->orderBy('revision_code', 'desc');
            }
        ])->findOrFail($contractReviewId);

        // Verify this contract review belongs to vendor
        if ($contractReview->vendor_id != $vendor->id_vendor) {
            abort(403, 'Unauthorized');
        }

        // ✅ AUTO MARK-AS-READ: Mark notification as read when vendor opens this page
        \App\Models\VendorNotification::where('vendor_id', $vendor->id_vendor)
            ->where('title', 'Menunggu Review SCM')
            ->where('link', route('vendor.contract-review.review', $contractReviewId))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);


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

        // ✅ DELETE old notifications for this contract review to prevent duplicates
        \App\Models\Notification::whereIn('title', ['Lengkapi Dokumen Kontrak', 'Review Kontrak Diperlukan', 'Menunggu Vendor'])
            ->where('reference_type', 'App\Models\ContractReview')
            ->where('reference_id', $revision->contract_review_id)
            ->delete();

        // NOTIFY SUPPLY CHAIN USERS - Check SC link status
        $scmUsers = \App\Models\User::whereHas('division', function ($q) {
            $q->where('division_name', 'LIKE', '%Supply Chain%');
        })->get();

        // Fresh reload to check SC link status
        $revisionFresh = \App\Models\ContractReviewRevision::find($revision->contract_review_revision_id);
        $isSCLinkFilled = !empty($revisionFresh->sc_link);

        foreach ($scmUsers as $user) {
            if ($isSCLinkFilled) {
                // Both links filled -> Review Needed
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'sender_id' => null,
                    'type' => 'action',
                    'title' => 'Review Kontrak Diperlukan',
                    'message' => "Dokumen kontrak '{$revision->contractReview->procurement->procurement_name}' ({$revision->revision_code}) lengkap. Silakan review.",
                    'action_url' => route('supply-chain.contract-review.show', $revision->contract_review_id),
                    'reference_type' => 'App\Models\ContractReview',
                    'reference_id' => $revision->contract_review_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            } else {
                // SC link empty -> Need to complete
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'sender_id' => null,
                    'type' => 'action',
                    'title' => 'Lengkapi Dokumen Kontrak',
                    'message' => "Silakan lengkapi dokumen revisi kontrak '{$revision->contractReview->procurement->procurement_name}' ({$revision->revision_code}).",
                    'action_url' => route('supply-chain.contract-review.show', $revision->contract_review_id),
                    'reference_type' => 'App\Models\ContractReview',
                    'reference_id' => $revision->contract_review_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }
        }


        // ✅ NOTIFY VENDOR: Menunggu Review SCM (Unik per Revisi)
        $existsVendor = \App\Models\VendorNotification::where('vendor_id', $vendor->id_vendor)
            ->where('title', 'Menunggu Review SCM')
            ->where('link', route('vendor.contract-review.review', $revision->contract_review_id))
            ->where('message', 'LIKE', "%({$revision->revision_code})%")
            ->exists();

        if (!$existsVendor) {
            \App\Models\VendorNotification::create([
                'vendor_id' => $vendor->id_vendor,
                'type' => 'info',
                'title' => 'Menunggu Review SCM',
                'message' => "Dokumen kontrak ({$revision->revision_code}) sedang direview oleh Supply Chain.",
                'link' => route('vendor.contract-review.review', $revision->contract_review_id),
                'is_read' => false,
                'created_at' => now(),
            ]);
        }


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

        $notifications = collect();

        // 1. STORED NOTIFICATIONS (Events: Approved, Rejected, etc.)
        $storedNotifs = \App\Models\VendorNotification::where('vendor_id', $vendor->id_vendor)
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($storedNotifs as $sn) {
            $category = 'inbox';

            // Check if this is an Action Item (Revisi/Evatek Baru)
            if (in_array($sn->title, ['Revisi Diperlukan', 'Evatek Baru'])) {
                $category = 'vendor'; // Default to action needed

                // EXTRACT ID to check current status
                // Try to parse ID from link: /vendor/evatek/{id}/review
                if (preg_match('/\/vendor\/evatek\/(\d+)\/review/', $sn->link, $matches)) {
                    $evatekId = $matches[1];
                    // Check if action already taken
                    $evatekItem = EvatekItem::find($evatekId);
                    if ($evatekItem) {
                        $latest = $evatekItem->latestRevision;
                        // ✅ Jika vendor sudah upload link, PINDAHKAN ke inbox (hilang dari Perlu Tindakan)
                        if ($latest && !empty($latest->vendor_link)) {
                            $category = 'inbox'; // Pindah ke Kotak Masuk, keluar dari "Perlu Tindakan"
                        }
                    }
                }
            }
            // ✅ Vendor sudah upload, menunggu review desain
            elseif ($sn->title === 'Menunggu Review Desain') {
                // Cek apakah sudah selesai diproses
                if (preg_match('/\/vendor\/evatek\/(\d+)\/review/', $sn->link, $matches)) {
                    $evatekId = $matches[1];
                    $evatekItem = EvatekItem::find($evatekId);
                    if ($evatekItem) {
                        $latest = $evatekItem->latestRevision;
                        // Jika sudah approved/rejected, pindahkan ke inbox (jangan delete)
                        if ($latest && in_array($latest->status, ['approve', 'not_approve'])) {
                            $category = 'inbox'; // ✅ Masuk Kotak Masuk, keluar dari label "Menunggu Review"
                        } else {
                            $category = 'division'; // Masih menunggu review
                        }
                    } else {
                        $category = 'division';
                    }
                } else {
                    $category = 'division';
                }
            }
            // ✅ Contract Review: Menunggu Review SCM
            elseif ($sn->title === 'Menunggu Review SCM') {
                // Cek apakah sudah selesai diproses
                if (preg_match('/\/vendor\/contract-review\/(\d+)\/review/', $sn->link, $matches)) {
                    $contractReviewId = $matches[1];
                    $contractReview = \App\Models\ContractReview::find($contractReviewId);
                    if ($contractReview) {
                        $latest = $contractReview->revisions()->orderBy('contract_review_revision_id', 'desc')->first();
                        // Jika sudah approved/rejected, pindahkan ke inbox (jangan delete)
                        if ($latest && in_array($latest->result, ['approve', 'not_approve'])) {
                            $category = 'inbox'; // ✅ Masuk Kotak Masuk, keluar dari label "Menunggu Review"
                        } else {
                            $category = 'division'; // Masih menunggu review
                        }
                    } else {
                        $category = 'division';
                    }
                } else {
                    $category = 'division';
                }
            }
            // ✅ Evatek sudah selesai (Disetujui/Ditolak) - HANYA inbox, tidak di label
            elseif (in_array($sn->title, ['Evatek Disetujui', 'Evatek Ditolak'])) {
                $category = 'inbox'; // Tidak masuk label "Menunggu Review"
            }

            $notifications->push((object)[
                'id' => $sn->id,
                'is_stored' => true, // Flag to identify DB record
                'is_read' => $sn->is_read,
                'is_starred' => $sn->is_starred ?? false,
                'type' => $sn->type, // success, danger, warning, info
                'icon' => match ($sn->type) {
                    'success' => 'bi-check-circle-fill',
                    'danger' => 'bi-x-circle-fill',
                    'warning' => 'bi-exclamation-triangle-fill',
                    'action' => 'bi-upload',
                    default => 'bi-info-circle-fill'
                },
                'color' => match ($sn->type) {
                    'success' => '#28a745',
                    'danger' => '#dc3545',
                    'warning' => '#ffc107',
                    'action' => '#d32f2f',
                    default => '#17a2b8'
                },
                'title' => $sn->title,
                'message' => $sn->message,
                'link' => $sn->link,
                'date' => $sn->created_at,
                'action_label' => 'Lihat Detail',
                'category' => $category
            ]);
        }

        // 2. COMPUTED TASKS (Contract Review Pending)
        // Only show if Action is Needed.
        $reviews = ContractReview::where('vendor_id', $vendor->id_vendor)
            ->with(['procurement.project', 'revisions' => function ($q) {
                $q->orderBy('contract_review_revision_id', 'desc');
            }])
            ->get();

        foreach ($reviews as $review) {
            $latest = $review->revisions->first();
            if (!$latest) continue;

            $previous = $review->revisions->skip(1)->first();
            $projName = $review->procurement->project->project_name ?? 'Project';
            $revCode = $latest->revision_code;
            $link = route('vendor.contract-review.review', $review->contract_review_id);

            // LOGIC: Show notification for ALL states but customize appearance
            $category = 'inbox'; // Default

            // 1. Pending / Revisi (Action Needed)
            if ((!$latest->result || $latest->result == 'pending' || $latest->result == 'revisi') && empty($latest->vendor_link)) {
                $isRevisi = ($latest->result == 'revisi') || ($previous && $previous->result == 'revisi');
                $title = $isRevisi ? 'Revisi Kontrak Diperlukan' : 'Butuh Upload Kontrak';
                $msg = "Silakan upload dokumen review kontrak untuk {$projName} ({$revCode}).";
                $type = 'action';
                $color = '#d32f2f'; // Red
                $icon = 'bi-upload';
                $actionLabel = 'Upload Dokumen';
                $category = 'vendor';
            }
            // 2. Waiting (Vendor uploaded, waiting SCM)
            elseif (!empty($latest->vendor_link) && in_array($latest->result, ['pending', 'revisi'])) {
                // ✅ TIDAK BUAT COMPUTED TASK - Andalkan stored notification yang dibuat saat vendor upload
                // Stored notification sudah dibuat di saveContractLink() method
                // Ini memastikan is_read tracking bekerja sempurna
                continue;
            }
            // 3. Approved - ✅ SELESAI, hilang dari "Menunggu Review"
            elseif ($latest->result == 'approve') {
                $title = 'Kontrak Disetujui';
                $msg = "Review kontrak {$projName} ({$revCode}) telah DISETUJUI.";
                $type = 'success';
                $color = '#28a745'; // Green
                $icon = 'bi-check-circle-fill';
                $actionLabel = 'Lihat Detail';
                $category = 'inbox'; // ✅ Masuk Kotak Masuk (tidak di label)
            }
            // 4. Rejected (Not Approved) - ✅ SELESAI, hilang dari "Menunggu Review"
            elseif ($latest->result == 'not_approve') {
                $title = 'Kontrak Ditolak';
                $msg = "Review kontrak {$projName} ({$revCode}) DITOLAK.";
                $type = 'danger';
                $color = '#dc3545'; // Red
                $icon = 'bi-x-circle-fill';
                $actionLabel = 'Lihat Detail';
                $category = 'inbox'; // ✅ Masuk Kotak Masuk (tidak di label)
            } else {
                continue; // Skip unknown states
            }

            $notifications->push((object)[
                'id' => 'task_cr_' . $review->contract_review_id . '_' . $latest->revision_code, // Unique per revision
                'is_stored' => false,
                'is_read' => false,
                'type' => $type,
                'icon' => $icon,
                'color' => $color,
                'title' => $title,
                'message' => $msg,
                'link' => $link,
                'date' => $latest->updated_at ?? $latest->created_at,
                'action_label' => $actionLabel,
                'category' => $category
            ]);
        }

        // 3. COMPUTED TASKS (Evatek Pending)
        // ✅ TIDAK PERLU COMPUTED TASKS - Semua notifikasi evatek dibuat via stored notifications:
        // - "Evatek Baru" → Dibuat saat create evatek
        // - "Revisi Diperlukan" → Dibuat saat desain minta revisi
        // - "Evatek Disetujui" → Dibuat saat approve
        // - "Evatek Ditolak" → Dibuat saat not approve
        // - "Menunggu Review Desain" → Dibuat saat vendor upload link

        // Semua stored notifications sudah di-handle di bagian stored notifications di atas
        // Tidak perlu computed tasks untuk evatek lagi

        // Sort by date descending
        $notifications = $notifications->sortByDesc('date');

        return view('vendor.notifications', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $vendor = Auth::guard('vendor')->user();
        if (!$vendor) abort(403);

        $notif = \App\Models\VendorNotification::where('vendor_id', $vendor->id_vendor)
            ->where('id', $id)
            ->firstOrFail();

        $notif->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
    public function toggleStar($id)
    {
        $vendor = Auth::guard('vendor')->user();
        if (!$vendor) abort(403);

        $notif = \App\Models\VendorNotification::where('vendor_id', $vendor->id_vendor)
            ->where('id', $id)
            ->firstOrFail();

        $notif->update([
            'is_starred' => !$notif->is_starred
        ]);

        return response()->json([
            'success' => true,
            'is_starred' => $notif->is_starred
        ]);
    }

    /**
     * Update evatek_status based on vendor and design link availability
     * Mirrors logic from EvatekController::updateEvatekStatus
     */
    private function updateEvatekStatusForVendor(EvatekItem $evatek)
    {
        // If status is already final (approve/not_approve), evatek_status should be null
        if ($evatek->status === 'approve' || $evatek->status === 'not_approve') {
            $evatek->evatek_status = null;
            $evatek->save();
            return;
        }

        $latestRevision = $evatek->revisions()->latest('revision_id')->first();

        if (!$latestRevision) {
            $evatek->evatek_status = null;  // Default empty
            $evatek->save();
            return;
        }

        $hasVendorLink = !empty($latestRevision->vendor_link);
        $hasDesignLink = !empty($latestRevision->design_link);

        // If both are present, status is empty (all complete)
        if ($hasVendorLink && $hasDesignLink) {
            $evatek->evatek_status = null;
        } elseif (!$hasVendorLink && $hasDesignLink) {
            // Design link exists but vendor link is empty
            $evatek->evatek_status = 'evatek-vendor';
        } elseif ($hasVendorLink && !$hasDesignLink) {
            // Vendor link exists but design link is empty
            $evatek->evatek_status = 'evatek-desain';
        } else {
            // Both are empty → null (default)
            $evatek->evatek_status = null;
        }

        $evatek->save();
    }
}
