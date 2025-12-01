<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Vendor;
use App\Models\EvatekItem;
use Illuminate\Http\Request;

class EvatekController extends Controller
{
    public function create()
    {
        $items = Item::with('request')->get();
        $vendors = Vendor::all();

        return view('evatek.create', compact('items', 'vendors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'vendors' => 'required|array',
        ]);

        foreach ($request->vendors as $vendorId) {
            EvatekItem::create([
                'item_id' => $request->item_id,
                'vendor_id' => $vendorId,
                'status' => 'pending'
            ]);
        }

        return redirect()->route('evatek.index')
            ->with('success', 'Evatek berhasil ditambahkan.');
    }

    public function index()
    {
        $evatekItems = EvatekItem::with([
            'item.requestProcurement.vendor',
            'item.requestProcurement.procurement.project'
        ])->get();

        return view('evatek.index', compact('evatekItems'));
    }
}
