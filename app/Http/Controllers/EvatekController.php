<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use Illuminate\Http\Request;

class EvatekController extends Controller
{
    /**
     * Show Evatek review page for a selected item
     */
    public function review($itemId)
    {
        // Ambil item + relasi request + vendor
        $item = Item::with([
            'requestProcurement',
            'requestProcurement.vendor',
        ])->findOrFail($itemId);

        // Ambil atau buat evatek_items
        $evatek = EvatekItem::firstOrCreate(
            ['item_id' => $itemId],
            [
                'project_id' => $item->requestProcurement->project_id,
                'current_revision' => 'R0',
                'current_status' => 'On Progress',
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

        $revision->update([
            'status' => 'approved',
            'date' => now(),
        ]);

        // Update summary on evatek_items
        $evatek = $revision->evatek;
        $evatek->update([
            'current_revision' => $revision->revision_code,
            'current_status' => 'Completed',
            'current_date' => $revision->date,
        ]);

        return response()->json(['success' => true]);
    }



    /**
     * Reject revision
     */
    public function reject(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        $revision->update([
            'status' => 'rejected',
            'date' => now(),
        ]);

        // Update summary
        $revision->evatek->update([
            'current_revision' => $revision->revision_code,
            'current_status' => 'Rejected',
            'current_date' => $revision->date,
        ]);

        return response()->json(['success' => true]);
    }



    /**
     * Mark revision as "Revision Needed" and create new revision (R1, R2…)
     */
    public function revise(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        // Current becomes "revisi"
        $revision->update([
            'status' => 'revisi',
            'date' => now(),
        ]);

        $evatek = $revision->evatek;

        // Generate next revision code
        $num = intval(substr($revision->revision_code, 1)) + 1;
        $nextCode = "R{$num}";

        // Create next revision row
        $nextRev = EvatekRevision::create([
            'evatek_id' => $evatek->evatek_id,
            'revision_code' => $nextCode,
            'status' => 'pending',
            'date' => now(),
        ]);

        // Update summary
        $evatek->update([
            'current_revision' => $nextCode,
            'current_status' => 'Revision Needed',
            'current_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'new_revision' => $nextRev,
        ]);
    }
}
