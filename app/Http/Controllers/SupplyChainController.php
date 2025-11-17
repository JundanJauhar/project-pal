<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\Hps;
use App\Models\Contract;
use App\Models\Notification;
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
            'pending_review' => Project::where('status_project', 'review_sc')->count(),
            'active_negotiations' => \App\Models\Procurement::where('status_procurement', 'in_progress')->count(),
            'pending_contracts' => Contract::where('status', 'draft')->count(),
            'material_requests' => RequestProcurement::where('request_status', 'submitted')->count(),
        ];

        $projects = Project::whereIn('status_project', ['review_sc', 'pemilihan_vendor', 'pengecekan_legalitas'])
            ->with(['ownerDivision'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('supply_chain.dashboard', compact('stats', 'projects'));
    }

    public function kelolaVendor(Request $request)
    {
        $search = $request->query('search');

        $vendors = Vendor::where('legal_status', 'verified')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name_vendor', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%")
                        ->orWhere('phone_number', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('name_vendor')
            ->get();

        $projects = Project::whereIn('status_project', ['pemilihan_vendor'])
            ->with(['ownerDivision'])
            ->get();

        return view('supply_chain.vendor.kelola', compact('vendors', 'projects'));
    }
    public function pilihVendor(Request $request)
    {
        $search = $request->query('search');

        $vendors = Vendor::where('legal_status', 'verified')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name_vendor', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%")
                        ->orWhere('phone_number', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('name_vendor')
            ->get();

        $projects = Project::whereIn('status_project', ['pemilihan_vendor'])
            ->with(['ownerDivision'])
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
                'address' => 'nullable|string|max:500',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'legal_status' => 'required|in:verified,pending,rejected',
                'is_importer' => 'nullable|boolean',
            ]);

            $vendor = Vendor::create([
                'name_vendor' => $validated['name_vendor'],
                'address' => $validated['address'] ?? null,
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
                'legal_status' => $validated['legal_status'],
                'is_importer' => $request->has('is_importer') ? 1 : 0,
            ]);

            $lastVendor = Vendor::orderBy('id_vendor', 'desc')->first();
            $lastNumber = $lastVendor ? intval(substr($lastVendor->id_vendor, 2)) : 0;
            $validated['id_vendor'] = 'V-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);


            $validated['is_importer'] = $request->has('is_importer') ? 1 : 0;
            $validated['legal_status'] = $validated['legal_status'] ?? 'pending';

            Vendor::create($validated);

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



    /**
     * Review project for SC
     */
    public function reviewProject($projectId)
    {
        $project = Project::with(['hps'])
            ->findOrFail($projectId);

        return view('supply_chain.review_project', compact('project'))
            ->with('hideNavbar', true);
    }

    /**
     * Approve project review
     */
    public function approveReview(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($project, $validated) {
            $project->update([
                'status_project' => 'persetujuan_sekretaris'
            ]);

            // Notify Sekretaris Direksi
            $sekretaris = \App\Models\User::where('roles', 'sekretaris_direksi')->get();
            foreach ($sekretaris as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'sender_id' => Auth::id(),
                    'type' => 'approval_required',
                    'title' => 'Persetujuan Diperlukan',
                    'message' => 'Proyek menunggu persetujuan Sekretaris: ' . $project->name_project,
                    'reference_type' => 'App\Models\Project',
                    'reference_id' => $project->project_id,
                ]);
            }
        });

        return redirect()->route('supply-chain.dashboard')
            ->with('success', 'Review project berhasil disetujui');
    }

    /**
     * Manage material requests
     */
    public function materialRequests()
    {
        $requests = RequestProcurement::with(['project', 'items'])
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
            'request_status' => 'required|in:draft,submitted,approved,rejected,completed',
            'notes' => 'nullable|string',
        ]);

        $materialRequest->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material request updated successfully'
        ]);
    }

    /**
     * Manage vendors
     */
    public function vendors()
    {
        $vendors = Vendor::orderBy('name_vendor')->paginate(20);
        return view('supply_chain.vendors', compact('vendors'));
    }

    /**
     * Select vendor for project
     */
    public function selectVendor(Request $request, $projectId)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,vendor_id',
        ]);

        $project = Project::findOrFail($projectId);

        // Create or update contract with vendor
        Contract::create([
            'project_id' => $project->project_id,
            'vendor_id' => $validated['vendor_id'],
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        $project->update([
            'status_project' => 'pengecekan_legalitas'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vendor selected successfully'
        ]);
    }

    /**
     * Manage negotiations (Procurement Progress Monitoring)
     */
    public function negotiations()
    {
        $negotiations = \App\Models\Procurement::with(['project', 'procurementProgress.checkpoint', 'requestProcurements.vendor'])
            ->where('status_procurement', 'in_progress')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('supply_chain.negotiations', compact('negotiations'));
    }

    /**
     * Manage material shipping
     */
    public function materialShipping()
    {
        $projects = Project::whereIn('status_project', ['pemesanan', 'pengiriman_material'])
            ->with(['contracts.vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('supply_chain.material_shipping', compact('projects'));
    }

    /**
     * Update material arrival
     */
    public function updateMaterialArrival(Request $request, $projectId)
    {
        $validated = $request->validate([
            'arrival_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $project = Project::findOrFail($projectId);
        $project->update(['status_project' => 'inspeksi_barang']);

        // Notify QA team
        $qaUsers = \App\Models\User::where('roles', 'qa')->get();
        foreach ($qaUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'sender_id' => Auth::id(),
                'type' => 'inspection_required',
                'title' => 'Inspeksi Material Diperlukan',
                'message' => 'Material telah tiba untuk proyek: ' . $project->name_project,
                'reference_type' => 'App\Models\Project',
                'reference_id' => $project->project_id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status kedatangan material berhasil diupdate'
        ]);
    }
}
