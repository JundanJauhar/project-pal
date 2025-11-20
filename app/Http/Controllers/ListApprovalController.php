<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Procurement;

class ListApprovalController extends Controller
{
    /**
     * Show list approval for QA
     */
    public function index()
    {
        // Ambil semua pengadaan dengan ITEMS melalui request_procurement
        $procurements = Procurement::with([
            'items',                 
            'items.requestProcurement.vendor', 
            'department',
        ])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('qa.list-approval', compact('procurements'));
    }
}
