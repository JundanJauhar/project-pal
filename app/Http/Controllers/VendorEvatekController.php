<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\RequestProcurement;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
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
            ->with(['item', 'procurement', 'project'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Statistics untuk vendor ini
        $stats = [
            'total_evatek' => EvatekItem::where('vendor_id', $vendor->id_vendor)->count(),
            'pending' => EvatekItem::where('vendor_id', $vendor->id_vendor)->where('status', 'pending')->count(),
            'approved' => EvatekItem::where('vendor_id', $vendor->id_vendor)->where('status', 'approved')->count(),
            'rejected' => EvatekItem::where('vendor_id', $vendor->id_vendor)->where('status', 'rejected')->count(),
        ];


        return view('vendor.index', compact('vendor', 'evatekItems', 'stats'));
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
}
