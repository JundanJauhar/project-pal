<?php
namespace App\Http\Controllers;

use App\Models\EvatekItem;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ActivityLogger;


class VendorEvatekController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $vendor = $user->vendor;

        if (!$vendor) {
            abort(403, 'Akun ini tidak terhubung ke data vendor.');
        }

        $evatekItems = EvatekItem::with([
            'item.requestProcurement.vendor',
            'item.requestProcurement.procurement.project',
        ])
            ->where('vendor_id', $vendor->id_vendor)
            ->orderBy('created_at', 'desc')
            ->get();

            ActivityLogger::log(
                module: 'Evatek',
                action: 'view_evatek_items',
                targetId: $vendor->id_vendor,
                details: [
                    'user_id' => Auth::id(),
                    'vendor_name' => $vendor->name_vendor,
                ]
            );

        return view('vendor.index', compact('evatekItems', 'vendor'));
    }
}