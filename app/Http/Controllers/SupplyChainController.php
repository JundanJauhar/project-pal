<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\Procurement;
use App\Models\ProcurementProgress;
use App\Models\Checkpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplyChainController extends Controller
{
    /**
     * Display Supply Chain dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_vendors' => Vendor::count(),
            'total_procurements' => Procurement::count(),
            'pending_requests' => RequestProcurement::where('request_status', 'pending')->count(),
            'total_projects' => Project::count(),
        ];

        $projects = Project::with(['procurement'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentRequests = RequestProcurement::with(['procurement', 'vendor', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('supply_chain.dashboard', compact('stats', 'projects', 'recentRequests'));
    }

    public function kelolaVendor(Request $request)
    {
        $search = $request->query('search');

        $vendors = Vendor::when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name_vendor', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('name_vendor')
            ->get();

        $projects = Project::with(['procurement'])
            ->get();

        return view('supply_chain.vendor.kelola', compact('vendors', 'projects'));
    }
    public function pilihVendor(Request $request)
    {
        $search = $request->query('search');

        $vendors = Vendor::when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name_vendor', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('name_vendor')
            ->get();

        $projects = Project::with(['procurement'])
            ->get();

        return view('supply_chain.vendor.pilih', compact('vendors', 'projects'))
            ->with('hideNavbar', true);
    }

    public function formVendor(Request $request)
    {

        $vendorId = $request->query('id');
        $redirect = $request->query('redirect', 'kelola');

        $vendor = null;
        if ($vendorId) {
            $vendor = Vendor::find($vendorId);
            if (!$vendor) {
                return redirect()->route('supply-chain.vendor.kelola')
                    ->with('error', 'Vendor tidak ditemukan');
            }
        }

        return view('supply_chain.vendor.form', compact('redirect'))
            ->with('hideNavbar', true);
    }



    public function detailVendor(Request $request)
    {
        $vendorId = $request->query('id');

        if (!$vendorId) {
            return redirect()->route('supply-chain.vendor.pilih')
                ->with('error', 'Vendor ID tidak ditemukan');
        }

        $vendor = Vendor::find($vendorId);

        if (!$vendor) {
            return redirect()->route('supply-chain.vendor.pilih')
                ->with('error', 'Vendor tidak ditemukan');
        }

        return view('supply_chain.vendor.detail', compact('vendor'))
            ->with('hideNavbar', true);
    }

    public function storeVendor(Request $request)
    {
        try {
            $validated = $request->validate([
                'name_vendor' => 'required|string|max:255|unique:vendors,name_vendor',
                'is_importer' => 'nullable|boolean',
            ]);

            $vendor = Vendor::create([
                'name_vendor' => $validated['name_vendor'],
                'is_importer' => $request->has('is_importer') ? 1 : 0,
            ]);

            $redirect = $request->input('redirect', 'kelola');
            $routeName = $redirect === 'pilih' ? 'supply-chain.vendor.pilih' : 'supply-chain.vendor.kelola';

            return redirect()->route($routeName)
                ->with('success', 'Vendor "' . $vendor->name_vendor . '" berhasil ditambahkan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating vendor: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal menambahkan vendor: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function updateVendor(Request $request, $id)
    {
        $vendor = Vendor::find($id);
        
        if (!$vendor) {
            return redirect()->route('supply-chain.vendor.kelola')
                ->with('error', 'Vendor tidak ditemukan');
        }

        $validated = $request->validate([
            'name_vendor' => 'required|string|max:255',
            'is_importer' => 'nullable|boolean',
        ]);

        $validated['is_importer'] = $request->has('is_importer') ? 1 : 0;
        
        $vendor->update($validated);

        $redirect = $request->input('redirect', 'kelola');
        $route = $redirect === 'pilih' ? 'supply-chain.vendor.pilih' : 'supply-chain.vendor.kelola';

        return redirect()->route($route)
            ->with('success', 'Vendor berhasil diupdate');
    }

    /**
     * Manage material requests
     */
    public function materialRequests()
    {
        $requests = RequestProcurement::with(['procurement', 'items', 'vendor', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('supply_chain.material_requests', compact('requests'));
    }

    /**
     * Update material request
     */
    public function updateMaterialRequest(Request $request, $requestId)
    {
        $materialRequest = RequestProcurement::findOrFail($requestId);

        $validated = $request->validate([
            'request_status' => 'required|in:pending,approved,rejected,in_progress,completed',
            'deadline_date' => 'nullable|date',
        ]);

        $materialRequest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material request updated successfully'
        ]);
    }
}
