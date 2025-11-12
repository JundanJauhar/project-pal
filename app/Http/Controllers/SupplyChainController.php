<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\Negotiation;
use App\Models\Hps;
use App\Models\Contract;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplyChainController extends Controller
{
    /**
     * Display Supply Chain dashboard
     */
    public function dashboard()
    {
        $stats = [
            'pending_review' => Project::where('status_project', 'review_sc')->count(),
            'active_negotiations' => Negotiation::where('status', 'in_progress')->count(),
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

    public function pilihVendor()
    {
        $vendors = Vendor::orderBy('name_vendor')->get();
        return view('supply_chain.vendor.pilih', compact('vendors'));
    }

    public function createVendor()
    {
        return view('supply_chain.vendor.create');
    }

    public function storeVendor(Request $request)
    {
        $validated = $request->validate([
            'name_vendor' => 'required|string|unique:vendors,name_vendor',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'email' => 'nullable|email',
            'legal_status' => 'nullable|string',
            'is_importer' => 'nullable|boolean',
        ]);

        Vendor::create([
            'name_vendor' => $validated['name_vendor'],
            'address' => $validated['address'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'email' => $validated['email'] ?? null,
            'legal_status' => $validated['legal_status'] ?? null,
            'is_importer' => $validated['is_importer'] ?? false,
            'status' => 'pending',
        ]);

        return redirect()->route('supply-chain.vendor.pilih')->with('success', 'Vendor berhasil ditambahkan');
    }



    /**
     * Review project for SC
     */
    public function reviewProject($projectId)
    {
        $project = Project::with(['requestProcurements.items', 'hps'])
            ->findOrFail($projectId);

        return view('supply_chain.review_project', compact('project'));
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
     * Manage negotiations
     */
    public function negotiations()
    {
        $negotiations = Negotiation::with(['project', 'hps'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('supply_chain.negotiations', compact('negotiations'));
    }

    /**
     * Create negotiation
     */
    public function createNegotiation(Request $request, $projectId)
    {
        $validated = $request->validate([
            'hps_id' => 'required|exists:hps,hps_id',
            'vendor_offer' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $hps = Hps::findOrFail($validated['hps_id']);

        // Check if vendor offer exceeds HPS
        if ($validated['vendor_offer'] > $hps->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Penawaran vendor melebihi HPS. Perlu update HPS dari Desain.',
                'requires_hps_update' => true,
            ]);
        }

        $negotiation = Negotiation::create([
            'project_id' => $projectId,
            'hps_id' => $validated['hps_id'],
            'negotiated_price' => $validated['vendor_offer'],
            'status' => 'completed',
            'negotiation_date' => now(),
            'notes' => $validated['notes'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Negosiasi berhasil dibuat',
            'negotiation' => $negotiation
        ]);
    }

    /**
     * Request HPS update from Design team
     */
    public function requestHpsUpdate($projectId)
    {
        $project = Project::findOrFail($projectId);

        // Notify Design team
        $designUsers = \App\Models\User::where('roles', 'desain')->get();
        foreach ($designUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'sender_id' => Auth::id(),
                'type' => 'hps_update_required',
                'title' => 'Update HPS Diperlukan',
                'message' => 'Negosiasi melebihi HPS untuk proyek: ' . $project->name_project,
                'reference_type' => 'App\Models\Project',
                'reference_id' => $project->project_id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi update HPS telah dikirim ke tim Desain'
        ]);
    }

    /**
     * Manage material shipping
     */
    public function materialShipping()
    {
        $projects = Project::whereIn('status_project', ['pemesanan', 'pengiriman_material'])
            ->with(['contracts.vendor', 'requestProcurements.items'])
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
