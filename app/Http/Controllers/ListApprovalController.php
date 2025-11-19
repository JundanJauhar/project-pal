<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Procurement;

class ListApprovalController extends Controller
{
    /**
     * List Approval — hanya pengadaan yang BUTUH inspeksi
     */
    public function index(Request $request)
    {
        // Filter query: hanya procurement yang memiliki ITEM BELUM DIINSPEKSI
        $procurements = Procurement::whereHas('items', function ($q) {
            $q->whereDoesntHave('inspectionReports'); // Item yang belum diperiksa saja
        })
            ->with([
                'department',
                'requestProcurements.vendor',

                // Ambil hanya item yang BELUM diinspeksi
                'items' => function ($q) {
                    $q->whereDoesntHave('inspectionReports');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Total pengadaan butuh inspeksi
        $totalInspectionNeeded = $procurements->count();

        return view('qa.list-approval', compact('procurements', 'totalInspectionNeeded'));
    }
}
