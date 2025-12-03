<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use Illuminate\Http\Request;
use App\Models\Project;

class EvatekController extends Controller
{
    /**
     * Show Evatek review page for a selected item
     */
    public function review($itemId)
    {
        // Ambil item + relasi request + vendor + procurement
        $item = Item::with([
            'requestProcurement',
            'requestProcurement.vendor',
            'requestProcurement.procurement',
        ])->findOrFail($itemId);

        // Get procurement_id from request_procurement
        $procurementId = $item->requestProcurement->procurement_id;
        $vendorId = $item->requestProcurement->vendor_id;

        // Ambil atau buat evatek_items dengan composite key
        $evatek = EvatekItem::firstOrCreate(
            [
                'item_id' => $itemId,
                'procurement_id' => $procurementId,
                'vendor_id' => $vendorId,
            ],
            [
                'current_revision' => 'R0',
                'status' => 'on_progress',
                'current_date' => now(),
            ]
        );

        // Ambil revisi yang sudah ada
        $revisions = EvatekRevision::where('evatek_id', $evatek->evatek_id)
            ->orderBy('revision_id', 'ASC')
            ->get();

        // Jika belum ada revisi R0 → buat
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

        return view('desain.review-evatek', compact('item', 'evatek', 'revisions'));
    }


    /**
     * Show daftar permintaan (items) untuk project - Evatek listing
     */
    public function daftarPermintaan($projectId)
    {
        $project = Project::findOrFail($projectId);
        
        // Get evatek items for this project through procurement
        $evatekItems = EvatekItem::whereIn('procurement_id', function ($query) use ($projectId) {
            $query->select('procurement_id')
                  ->from('procurement')
                  ->where('project_id', $projectId);
        })->with(['item', 'vendor', 'procurement', 'revisions' => function ($query) {
            $query->latest('revision_id');
        }])->get();

        return view('desain.daftar-permintaan', compact('project', 'evatekItems'));
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

        return response()->json(['success' => true]);
    }



    /**
     * Mark revision as "Revision Needed" and create new revision (R1, R2…)
     */
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

        return response()->json([
            'success' => true,
            'new_revision' => $nextRev,
        ]);
    }
}
