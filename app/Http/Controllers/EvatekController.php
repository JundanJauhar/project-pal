<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use App\Models\Project; // ✅ DITAMBAHKAN
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityLogger;

class EvatekController extends Controller
{
    /**
     * Show Evatek review page for a selected evatek item
     */
    public function review($evatekId)
    {
        // Ambil EvatekItem (bukan Item)
        $evatek = EvatekItem::with([
            'item',
            'vendor',
            'procurement',
        ])->findOrFail($evatekId);

        // Ambil item dari relasi evatek
        $item = $evatek->item;

        // Ambil revisi yang sudah ada
        $revisions = EvatekRevision::where('evatek_id', $evatek->evatek_id)
            ->orderBy('revision_id', 'ASC')
            ->get();

        // Jika belum ada revisi R0 → buat
        if ($revisions->isEmpty()) {
            $revision = EvatekRevision::create([
                'evatek_id'      => $evatek->evatek_id,
                'revision_code'  => 'R0',
                'vendor_link'    => null,
                'design_link'    => null,
                'status'         => 'pending',
                'date'           => now(),
            ]);

            $revisions = collect([$revision]);
        }

        ActivityLogger::log(
            module: 'Evatek',
            action: 'view_evatek_review',
            targetId: $evatek->evatek_id, // ✅ BUKAN $itemId
            details: [
                'user_id'          => Auth::id(),
                'revisions_count'  => $revisions->count(),
            ]
        );

        // Pastikan log tidak null
        if ($evatek->log === null) {
            $evatek->log = '';
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
            })->with([
                'item',
                'vendor',
                'procurement',
                'revisions' => function ($query) {
                    $query->latest('revision_id');
                }
            ])->get();

        return view('desain.daftar-permintaan', compact('project', 'evatekItems'));
    }

    public function saveLink(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        $revision->update([
            'vendor_link' => $request->vendor_link,
            'design_link' => $request->design_link,
        ]);

        ActivityLogger::log(
            module: 'Evatek',
            action: 'save_revision_links',
            targetId: $revision->revision_id,
            details: [
                'user_id'     => Auth::id(),
                'vendor_link' => $request->vendor_link,
                'design_link' => $request->design_link,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function saveLog(Request $request)
    {
        $evatek = EvatekItem::findOrFail($request->evatek_id);

        $evatek->update([
            'log' => $request->log,
        ]);

        return response()->json(['success' => true]);
    }

    public function approve(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        if (!$revision->evatek_id) {
            return response()->json(['success' => false, 'message' => 'Revision not linked to evatek item'], 400);
        }

        $revision->update([
            'status'      => 'approve',
            'approved_at' => now(),
        ]);

        $evatek = EvatekItem::findOrFail($revision->evatek_id);
        $evatek->update([
            'current_revision' => $revision->revision_code,
            'status'           => 'approve',
            'current_date'     => now()->toDateString(),
        ]);

        ActivityLogger::log(
            module: 'Evatek',
            action: 'approve_revision',
            targetId: $revision->revision_id,
            details: [
                'user_id'       => Auth::id(),
                'revision_code' => $revision->revision_code,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function reject(Request $request)
    {
        $revision = EvatekRevision::findOrFail($request->revision_id);

        if (!$revision->evatek_id) {
            return response()->json(['success' => false, 'message' => 'Revision not linked to evatek item'], 400);
        }

        $revision->update([
            'status'          => 'not approve',
            'not_approved_at' => now(),
        ]);

        $evatek = EvatekItem::findOrFail($revision->evatek_id);
        $evatek->update([
            'current_revision' => $revision->revision_code,
            'status'           => 'not_approve',
            'current_date'     => now()->toDateString(),
        ]);

        ActivityLogger::log(
            module: 'Evatek',
            action: 'reject_revision',
            targetId: $revision->revision_id,
            details: [
                'user_id'       => Auth::id(),
                'revision_code' => $revision->revision_code,
            ]
        );

        return response()->json(['success' => true]);
    }

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

        $num      = intval(substr($revision->revision_code, 1)) + 1;
        $nextCode = "R{$num}";

        $nextRev = EvatekRevision::create([
            'evatek_id'     => $evatek->evatek_id,
            'revision_code' => $nextCode,
            'status'        => 'pending',
            'date'          => now()->toDateString(),
        ]);

        $evatek->update([
            'current_revision' => $nextCode,
            'status'           => 'on_progress',
            'current_date'     => now()->toDateString(),
        ]);

        ActivityLogger::log(
            module: 'Evatek',
            action: 'request_revision',
            targetId: $revision->revision_id,
            details: [
                'user_id'       => Auth::id(),
                'from_revision' => $revision->revision_code,
                'to_revision'   => $nextRev->revision_code,
            ]
        );

        return response()->json([
            'success'      => true,
            'new_revision' => $nextRev,
        ]);
    }
}
