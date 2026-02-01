<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Procurement;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\Notification;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use App\Models\ContractReview;
use App\Models\ContractReviewRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CheckpointTransitionService;
use App\Helpers\ActivityLogger;

class SupplyChainController extends Controller
{

    /**
     * Show form untuk input item baru
     */
    public function inputItem($projectId)
    {
        if (Auth::user()->division->name !== 'Supply Chain') {
            abort(403, 'Unauthorized action.');
        }

        $project = Project::findOrFail($projectId);
        $procurements = Procurement::where('project_id', $projectId)
            ->with('requestProcurements.items')
            ->orderBy('code_procurement', 'asc')
            ->get();
        $vendors = Vendor::orderBy('name_vendor', 'asc')
            ->get();

        if ($procurements->isNotEmpty()) {
            return redirect()->route('procurements.show', $procurements->first()->procurement_id);
        }

        return redirect()->route('procurements.index')->with('error', 'Tidak ada procurement untuk project ini.');
    }

    /**
     * Get items untuk procurement tertentu (AJAX)
     */
    public function getProcurementItems($procurementId)
    {
        try {
            $procurement = Procurement::findOrFail($procurementId);

            $requestProcurements = RequestProcurement::where('procurement_id', $procurementId)
                ->with(['items', 'vendor'])
                ->get();

            $items = [];
            foreach ($requestProcurements as $req) {
                foreach ($req->items as $item) {
                    $items[] = [
                        'item_id' => $item->item_id,
                        'item_name' => $item->item_name,
                        'amount' => $item->amount,
                        'unit' => $item->unit,
                        'description' => $item->item_description,
                        'specification' => $item->specification,
                        'request_id' => $req->request_id,
                        'vendor_id' => $req->vendor_id,
                        'vendor_name' => $req->vendor->name_vendor ?? 'Unknown',
                        'status' => $item->status,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'items' => $items,
                'count' => count($items)
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getProcurementItems: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeItem(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 2: Load PROCUREMENT ===
        $procurement = Procurement::findOrFail($procurementId);

        $validated = $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'vendor_ids' => 'required|array|min:1',
            'vendor_ids.*' => 'exists:vendors,id_vendor',
        ]);

        $procurement = Procurement::with('project')->findOrFail($procurementId);

        // authorization by procurement → project (additional check if needed)
        if (
            Auth::user()->department_id !== $procurement->project->department_id
            && Auth::user()->division->name !== 'Supply Chain'
        ) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($validated['item_id']);

            foreach ($validated['vendor_ids'] as $vendorId) {
                RequestProcurement::firstOrCreate([
                    'procurement_id' => $procurementId,
                    'vendor_id' => $vendorId,
                ], [
                    'request_name' => "Request {$item->item_name}",
                    'department_id' => Auth::user()->department_id,
                    'created_date' => now(),
                    'deadline_date' => $procurement->end_date,
                    'request_status' => 'pending',
                ]);
            }

            DB::commit();
            return back()->with('success', 'Item berhasil ditambahkan ke procurement');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }



    public function storeEvatekItem(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('evatek')) {
            abort(403, 'Anda tidak punya role evatek.');
        }

        // === STEP 2: Load PROCUREMENT ===
        $procurement = Procurement::findOrFail($procurementId);

        // === STEP 3: Get CURRENT CHECKPOINT ===
        $currentCheckpoint = $procurement->procurementProgress()
            ->with('checkpoint')
            ->where('status', 'in_progress')
            ->first();

        if (!$currentCheckpoint) {
            abort(400, 'Procurement tidak sedang di tahap apapun.');
        }

        // === STEP 4: Check CHECKPOINT DIVISION ===
        if ($currentCheckpoint->checkpoint->responsible_division !== $user->division_id) {
            abort(403, 'Procurement sedang ditangani divisi lain.');
        }

        // === STEP 5: Check CHECKPOINT NAME ===
        if ($currentCheckpoint->checkpoint->point_name !== 'Evatek') {
            abort(403, 'Procurement tidak sedang di tahap Evatek.');
        }

        $validated = $request->validate([
            'item_id' => 'required|exists:items,item_id',
            'vendor_ids' => 'required|array|min:1',
            'vendor_ids.*' => 'required|exists:vendors,id_vendor',
            'pic_evatek' => 'required|in:EO,HC,MO,HO,SEWACO',
            'sc_design_link' => 'nullable|url',  // ✅ TAMBAHAN
            'target_date' => 'nullable|date|after_or_equal:today',
        ]);

        DB::beginTransaction();
        try {
            $procurement = Procurement::with('project')->findOrFail($procurementId);
            $item = Item::findOrFail($validated['item_id']);

            if (
                Auth::user()->department_id !== $procurement->project->department_id
                && Auth::user()->division->name !== 'Supply Chain'
            ) {
                abort(403);
            }

            $createdVendors = [];

            foreach ($validated['vendor_ids'] as $vendorId) {

                $exists = EvatekItem::where([
                    'procurement_id' => $procurementId,
                    'item_id' => $item->item_id,
                    'vendor_id' => $vendorId,
                ])->exists();

                if ($exists) {
                    continue;
                }

                $evatek = EvatekItem::create([
                    'procurement_id' => $procurementId,
                    'project_id' => $procurement->project_id,
                    'item_id' => $item->item_id,
                    'vendor_id' => $vendorId,
                    'pic_evatek' => $validated['pic_evatek'],
                    'sc_design_link' => $validated['sc_design_link'] ?? null,  // ✅ TAMBAHAN
                    'evatek_status' => null,
                    'start_date' => now(),
                    'target_date' => $validated['target_date'] ?? $procurement->end_date,
                    'current_revision' => 'R0',
                    'status' => 'on_progress',
                    'current_date' => null,
                ]);

                // ✅ Create initial revision R0
                EvatekRevision::create([
                    'evatek_id' => $evatek->evatek_id,
                    'revision_code' => 'R0',
                    'status' => 'pending',
                    'date' => now()->toDateString(),
                ]);

                // ✅ Notify Desain Users
                $desainUsers = \App\Models\User::whereHas('roles', function ($q) {
                    $q->where('role_code', 'desain');
                })
                    ->orWhereHas('division', function ($q) {
                        $q->where('name', 'LIKE', '%desain%');
                    })
                    ->get();
                $vendorName = \App\Models\Vendor::find($vendorId)->name_vendor ?? 'Vendor';

                foreach ($desainUsers as $user) {
                    // 1. Create 'Lengkapi Dokumen' notification FIRST (appearing lower/older)

                    // 2. Create 'Proses Evatek Dimulai' notification SECOND (appearing higher/newer)
                    Notification::create([
                        'user_id' => $user->user_id,
                        'sender_id' => Auth::id(),
                        'type' => 'info',
                        'title' => 'Proses Evatek Dimulai',
                        'message' => "Proses Evatek dimulai untuk item '{$item->item_name}' dengan vendor {$vendorName}.Silahkan isi dokumen evatek.",
                        'action_url' => route('desain.review-evatek', $evatek->evatek_id),
                        'reference_type' => 'App\Models\EvatekItem',
                        'reference_id' => $evatek->evatek_id,
                        'is_read' => false,
                        'created_at' => now(),
                    ]);
                }

                // ✅ Notify Vendor
                \App\Models\VendorNotification::create([
                    'vendor_id' => $vendorId,
                    'type' => 'info',
                    'title' => 'Evatek Baru',
                    'message' => "Anda telah ditunjuk untuk proses Evatek item '{$item->item_name}'. Silakan unggah dokumen teknis.",
                    'link' => route('vendor.evatek.review', $evatek->evatek_id),
                    'created_at' => now(),
                ]);

                $createdVendors[] = $vendorId;
            }

            if (empty($createdVendors)) {
                return back()->with('warning', 'Evatek untuk item dan vendor tersebut sudah ada.');
            }

            ActivityLogger::log(
                module: 'Evatek',
                action: 'create_evatek_item',
                targetId: $item->item_id,
                details: [
                    'procurement_id' => $procurementId,
                    'vendors' => $createdVendors,
                    'pic_evatek' => $validated['pic_evatek'],
                    'user_id' => Auth::id(),
                ]
            );

            DB::commit();

            $vendorNames = Vendor::whereIn('id_vendor', $createdVendors)
                ->pluck('name_vendor')
                ->implode(', ');

            return redirect()
                ->route('procurements.show', $procurement->procurement_id)
                ->with('success', "Evatek item '{$item->item_name}' berhasil dibuat untuk: {$vendorNames}")
                ->withFragment('evatek');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('storeEvatekItem error: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal menyimpan Evatek item')
                ->withInput();
        }
    }


    /**
     * Show create procurement form
     */
    public function createPengadaan()
    {
        ActivityLogger::log(
            module: 'Procurement',
            action: 'view_create_procurement_form',
            targetId: null,
            details: ['user_id' => Auth::id()]
        );

        return view('supply_chain.create');
    }

    /**
     * Kelola Vendor
     */
    public function kelolaVendor(Request $request)
    {
        $search = $request->query('search');

        $vendors = Vendor::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name_vendor', 'LIKE', "%{$search}%")
                        ->orWhere('address', 'LIKE', "%{$search}%")
                        ->orWhere('phone_number', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('id_vendor', 'asc')
            ->get();

        $procurements = Procurement::whereIn('status_procurement', ['pemilihan_vendor'])
            ->with(['department'])
            ->get();

        ActivityLogger::log(
            module: 'Vendor',
            action: 'view_vendor_list',
            targetId: null,
            details: ['search' => $search, 'user_id' => Auth::id()]
        );

        return view('supply-chain.vendor.kelola', compact('vendors', 'procurements'));
    }

    /**
     * Pilih Vendor
     */
    public function pilihVendor($procurementId, Request $request)
    {
        $procurement = Procurement::with(['project', 'department'])
            ->findOrFail($procurementId);

        $search = $request->input('search');

        $vendors = Vendor::query()
            ->when($search, function ($query, $search) {
                return $query->where('name_vendor', 'like', "%{$search}%")
                    ->when(is_numeric($search), function ($q) use ($search) {
                        $q->orWhere('id_vendor', $search);
                    })
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderBy('id_vendor', 'asc')
            ->get();

        $stats = [
            'total' => Vendor::count(),
            'importer' => Vendor::where('is_importer', true)->count(),
            'local' => Vendor::where('is_importer', false)->count(),
        ];

        ActivityLogger::log(
            module: 'Vendor',
            action: 'view_select_vendor_form',
            targetId: $procurement->procurement_id,
            details: ['user_id' => Auth::id()]
        );

        return view('supply-chain.vendor.pilih', compact('vendors', 'stats', 'procurement'))
            ->with('hideNavbar', true);
    }

    public function simpanVendor($procurementId, Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        $procurement = Procurement::findOrFail($procurementId);

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id_vendor'
        ]);

        $procurement = Procurement::findOrFail($procurementId);
        $vendor = Vendor::findOrFail($validated['vendor_id']);

        $existingRequest = RequestProcurement::where('procurement_id', $procurementId)->first();

        if ($existingRequest) {
            $existingRequest->update([
                'vendor_id' => $validated['vendor_id'],
                'request_status' => 'submitted'
            ]);
        } else {
            RequestProcurement::create([
                'procurement_id' => $procurementId,
                'vendor_id' => $validated['vendor_id'],
                'request_name' => "Request untuk {$procurement->name_procurement}",
                'department_id' => auth()->user()->department_id,
                'request_status' => 'submitted',
                'created_date' => now(),
                'deadline_date' => $procurement->end_date,
            ]);
        }

        ActivityLogger::log(
            module: 'Vendor',
            action: 'select_vendor',
            targetId: $procurement->procurement_id,
            details: [
                'user_id' => Auth::id(),
                'vendor_id' => $validated['vendor_id'],
            ]
        );

        $transition = new CheckpointTransitionService($procurement);
        $transition->completeCurrentAndMoveNext("Vendor {$vendor->name_vendor} dipilih");

        return redirect()->route('supply-chain.dashboard');
    }

    /**
     * Form Vendor
     */
    public function formVendor(Request $request)
    {
        $vendorId = $request->query('id');
        $vendor = $vendorId ? Vendor::find($vendorId) : null;

        if ($vendorId && !$vendor) {
            return redirect()->route('supply-chain.vendor.kelola')
                ->with('error', 'Vendor tidak ditemukan.');
        }

        ActivityLogger::log(
            module: 'Vendor',
            action: $vendor ? 'edit_vendor_form' : 'create_vendor_form',
            targetId: $vendor?->id_vendor,
            details: ['user_id' => Auth::id()]
        );

        return view('supply-chain.vendor.form', compact('vendor'))
            ->with('hideNavbar', true);
    }

    /**
     * Update Vendor
     */
    public function updateVendor(Request $request, $id)
    {
        try {
            $vendor = Vendor::findOrFail($id);

            $validated = $request->validate([
                'vendor_code' => [
                    'required',
                    'string',
                    'max:10',
                    'regex:/^(AS|AD|AL)(-[0-9]{3})?$/'
                ],
                'name_vendor' => 'required|string|max:255',
                'specialization' => 'required|in:jasa,material_lokal,material_impor',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'address' => 'nullable|string|max:500',
                'is_importer' => 'nullable|boolean',
            ]);

            $vendor->update([
                'vendor_code' => strtoupper($validated['vendor_code']),
                'name_vendor' => $validated['name_vendor'],
                'specialization' => $validated['specialization'],
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
                'address' => $validated['address'] ?? null,
                'is_importer' => $request->has('is_importer') ? 1 : 0,
            ]);

            ActivityLogger::log(
                module: 'Vendor',
                action: 'update_vendor',
                targetId: $vendor->id_vendor,
                details: ['user_id' => Auth::id()]
            );

            $redirect = $request->input('redirect', 'kelola');
            $routeName = $redirect === 'pilih' ? 'supply-chain.vendor.pilih' : 'supply-chain.vendor.kelola';

            return redirect()->route($routeName)
                ->with('success', 'Vendor "' . $vendor->name_vendor . '" berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Error updating vendor: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal memperbarui vendor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Detail Vendor
     */
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

        ActivityLogger::log(
            module: 'Vendor',
            action: 'view_vendor_detail',
            targetId: $vendor->id_vendor,
            details: ['user_id' => Auth::id()]
        );

        return view('supply-chain.vendor.detail', compact('vendor'))
            ->with('hideNavbar', true);
    }

    /**
     * Store Vendor
     */
    public function storeVendor(Request $request)
    {
        $validated = $request->validate([
            'vendor_code' => [
                'required',
                'string',
                'max:10',
                'regex:/^(AS|AD|AL)(-[0-9]{3})?$/'
            ],
            'name_vendor' => 'required|string|max:255|unique:vendors,name_vendor',
            'specialization' => 'required|in:jasa,material_lokal,material_impor',
            'address' => 'nullable|string|max:500',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|email|max:255|unique:vendors,email',
            'is_importer' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $vendor = Vendor::create([
                'vendor_code' => strtoupper($validated['vendor_code']),
                'name_vendor' => $validated['name_vendor'],
                'specialization' => $validated['specialization'],
                'address' => $validated['address'] ?? null,
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
                'is_importer' => $request->has('is_importer') ? 1 : 0,
            ]);

            ActivityLogger::log(
                module: 'Vendor',
                action: 'store_vendor',
                targetId: $vendor->id_vendor,
                details: ['user_id' => Auth::id()]
            );

            DB::commit();

            return redirect()
                ->route('supply-chain.vendor.kelola')
                ->with('success', 'Vendor berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return back()->with('error', 'Gagal menambahkan vendor.')
                ->withInput();
        }
    }

    public function updateMaterialArrival(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 2: Load PROCUREMENT ===
        $procurement = Procurement::findOrFail($procurementId);

        // === STEP 3: Get CURRENT CHECKPOINT ===
        $currentCheckpoint = $procurement->procurementProgress()
            ->with('checkpoint')
            ->where('status', 'in_progress')
            ->first();

        if (!$currentCheckpoint) {
            abort(400, 'Procurement tidak sedang di tahap apapun.');
        }

        // === STEP 4: Check CHECKPOINT DIVISION ===
        if ($currentCheckpoint->checkpoint->responsible_division !== $user->division_id) {
            abort(403, 'Procurement sedang ditangani divisi lain.');
        }

        // === STEP 5: Check CHECKPOINT NAME ===
        if ($currentCheckpoint->checkpoint->point_name !== 'Kedatangan Material') {
            abort(403, 'Procurement tidak sedang di tahap Kedatangan Material.');
        }

        $validated = $request->validate([
            'arrival_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $procurement = Procurement::findOrFail($procurementId);
        $procurement->update(['status_procurement' => 'inspeksi_barang']);

        $qaUsers = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('role_code', 'qa');
        })->get();
        foreach ($qaUsers as $qaUser) {
            Notification::create([
                'user_id' => $qaUser->id,
                'sender_id' => Auth::id(),
                'type' => 'inspection_required',
                'title' => 'Inspeksi Material Diperlukan',
                'message' => 'Material telah tiba untuk pengadaan: ' . $procurement->name_procurement,
                'reference_type' => 'App\Models\Procurement',
                'reference_id' => $procurement->procurement_id,
            ]);
        }

        return redirect()->route('supply-chain.dashboard')
            ->with('success', 'Status kedatangan material berhasil diupdate');
    }

    /**
     * Store Pengadaan
     */
    public function storePengadaan(Request $request)
    {
        $validated = $request->validate([
            'pengadaan' => 'required|array',
            'pengadaan.*.name' => 'required|string|max:255',
            'pengadaan.*.department' => 'required|exists:departments,department_id',
            'pengadaan.*.start_date' => 'required|date',
            'pengadaan.*.end_date' => 'required|date|after:start_date',
            'pengadaan.*.priority' => 'required|in:rendah,sedang,tinggi',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['pengadaan'] as $pengadaanData) {
                $lastCode = Procurement::where('code_procurement', 'LIKE', 'PRC-' . date('Y') . '-%')
                    ->orderBy('code_procurement', 'desc')
                    ->first();

                if ($lastCode) {
                    $lastNumber = intval(substr($lastCode->code_procurement, -3));
                    $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $newNumber = '001';
                }

                $code = 'PRC-' . date('Y') . '-' . $newNumber;

                Procurement::create([
                    'code_procurement' => $code,
                    'name_procurement' => $pengadaanData['name'],
                    'department_procurement' => $pengadaanData['department'],
                    'start_date' => $pengadaanData['start_date'],
                    'end_date' => $pengadaanData['end_date'],
                    'priority' => $pengadaanData['priority'],
                    'status_procurement' => 'draft',
                ]);
            }

            ActivityLogger::log(
                module: 'Procurement',
                action: 'store_multiple_procurements',
                targetId: null,
                details: [
                    'user_id' => Auth::id(),
                    'total_created' => count($validated['pengadaan']),
                ]
            );

            DB::commit();
            return redirect()->route('supply-chain.dashboard')
                ->with('success', 'Pengadaan berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating pengadaan: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menambahkan pengadaan')
                ->withInput();
        }
    }

    /**
     * Store new contract review
     */
    public function storeContractReview(Request $request, $procurementId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->loadAuthContext();

        // === STEP 1: Check ROLE ===
        if (!$user->hasRole('contract')) {
            abort(403, 'Anda tidak punya role contract.');
        }

        // === STEP 2: Load PROCUREMENT ===
        $procurement = Procurement::findOrFail($procurementId);

        // === STEP 3: Get CURRENT CHECKPOINT ===
        $currentCheckpoint = $procurement->procurementProgress()
            ->with('checkpoint')
            ->where('status', 'in_progress')
            ->first();

        if (!$currentCheckpoint) {
            abort(400, 'Procurement tidak sedang di tahap apapun.');
        }

        // === STEP 4: Check CHECKPOINT DIVISION ===
        if ($currentCheckpoint->checkpoint->responsible_division !== $user->division_id) {
            abort(403, 'Procurement sedang ditangani divisi lain.');
        }

        // === STEP 5: Check CHECKPOINT NAME ===
        if ($currentCheckpoint->checkpoint->point_name !== 'Pengesahan Kontrak') {
            abort(403, 'Procurement tidak sedang di tahap Pengesahan Kontrak.');
        }

        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id_vendor',
            'start_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $procurement = Procurement::with('project')->findOrFail($procurementId);

            $contractReview = ContractReview::create([
                'procurement_id' => $procurementId,
                'vendor_id' => $validated['vendor_id'],
                'project_id' => $procurement->project_id,
                'start_date' => $validated['start_date'],
                'current_revision' => 'R0',
                'remarks' => $validated['remarks'],
                'status' => 'on_progress',
            ]);

            // Create initial revision
            ContractReviewRevision::create([
                'contract_review_id' => $contractReview->contract_review_id,
                'revision_code' => 'R0',
                'date_sent_to_vendor' => $validated['start_date'],
                'created_by' => Auth::id(),
            ]);

            ActivityLogger::log(
                module: 'Contract Review',
                action: 'create_contract_review',
                targetId: $contractReview->contract_review_id,
                details: [
                    'procurement_id' => $procurementId,
                    'vendor_id' => $validated['vendor_id'],
                    'user_id' => Auth::id(),
                ]
            );
            $scmUsers = \App\Models\User::whereHas('division', function ($q) {
                $q->where('division_name', 'LIKE', '%Supply Chain%');
            })->get();

            foreach ($scmUsers as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'info',
                    'title' => 'Lengkapi Dokumen Kontrak',
                    'message' => "Silakan lengkapi dokumen kontrak awal (R0) untuk {$contractReview->procurement->project->project_name} sebelum dikirim ke vendor.",
                    'action_url' => route('supply-chain.contract-review.show', $contractReview->contract_review_id),
                    'reference_type' => 'App\Models\ContractReview',
                    'reference_id' => $contractReview->contract_review_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('procurements.show', $procurementId)
                ->with('success', 'Contract review berhasil dibuat')
                ->withFragment('contract-review');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating contract review: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal membuat contract review')
                ->withInput();
        }
    }

    /**
     * Show contract review detail
     */
    public function showContractReview($contractReviewId)
    {
        $contractReview = ContractReview::with([
            'procurement.project',
            'vendor',
            'project',
            'revisions.creator'
        ])->findOrFail($contractReviewId);

        $procurement = $contractReview->procurement;
        $revisions = $contractReview->revisions;

        ActivityLogger::log(
            module: 'Contract Review',
            action: 'view_contract_review_detail',
            targetId: $contractReviewId,
            details: ['user_id' => Auth::id()]
        );

        return view('supply-chain.contract_review.show', compact('contractReview', 'procurement', 'revisions'));
    }



    /**
     * Save link for contract review revision (AJAX)
     */
    public function saveLink(Request $request)
    {
        try {
            $validated = $request->validate([
                'revision_id' => 'required|integer',
                'vendor_link' => 'nullable|string',
                'sc_link' => 'nullable|string',
            ]);

            $revision = ContractReviewRevision::findOrFail($validated['revision_id']);

            $updateData = [];
            if ($request->has('vendor_link')) {
                $updateData['vendor_link'] = $validated['vendor_link'];
            }
            if ($request->has('sc_link')) {
                $updateData['sc_link'] = $validated['sc_link'];
            }

            if (!empty($updateData)) {
                $revision->update($updateData);

                // ✅ Handle Notification Logic for SCM
                if (isset($updateData['sc_link'])) {
                    // Load related data
                    $contractReview = $revision->contractReview;
                    $contractReview->load('vendor', 'procurement.project');

                    // 1. Delete "Lengkapi Dokumen Kontrak" (Done)
                    \App\Models\Notification::whereIn('title', ['Lengkapi Dokumen Kontrak', 'Perlu Isi Link'])
                        ->where('reference_type', 'App\Models\ContractReview')
                        ->where('reference_id', $contractReview->contract_review_id)
                        ->delete();

                    // 2. Check State
                    $isVendorFilled = !empty($revision->vendor_link);
                    $scmUsers = \App\Models\User::whereHas('division', function ($q) {
                        $q->where('division_name', 'LIKE', '%Supply Chain%');
                    })->get();

                    foreach ($scmUsers as $user) {
                        if ($isVendorFilled) {
                            // Both filled -> Review Needed
                            // Check existence
                            $exists = \App\Models\Notification::where('user_id', $user->user_id)
                                ->where('title', 'Review Kontrak Diperlukan')
                                ->where('message', 'LIKE', "%{$revision->revision_code}%")
                                ->exists();

                            if (!$exists) {
                                \App\Models\Notification::create([
                                    'user_id' => $user->user_id,
                                    'type' => 'info',
                                    'title' => 'Review Kontrak Diperlukan',
                                    'message' => "Dokumen kontrak {$contractReview->procurement->project->project_name} ({$revision->revision_code}) lengkap. Silakan review.",
                                    'action_url' => route('supply-chain.contract-review.show', $contractReview->contract_review_id),
                                    'reference_type' => 'App\Models\ContractReview',
                                    'reference_id' => $contractReview->contract_review_id,
                                    'is_read' => false,
                                    'created_at' => now(),
                                ]);
                            }
                        } else {
                            // Vendor Empty -> Wating Vendor
                            $exists = \App\Models\Notification::where('user_id', $user->user_id)
                                ->where('title', 'Menunggu Vendor')
                                ->where('message', 'LIKE', "%{$revision->revision_code}%")
                                ->exists();

                            if (!$exists) {
                                \App\Models\Notification::create([
                                    'user_id' => $user->user_id,
                                    'type' => 'info',
                                    'title' => 'Menunggu Vendor',
                                    'message' => "Menunggu Vendor {$contractReview->vendor->name_vendor} mengupload dokumen kontrak {$contractReview->procurement->project->project_name} ({$revision->revision_code}).",
                                    'action_url' => route('supply-chain.contract-review.show', $contractReview->contract_review_id),
                                    'reference_type' => 'App\Models\ContractReview',
                                    'reference_id' => $contractReview->contract_review_id,
                                    'is_read' => false,
                                    'created_at' => now(),
                                ]);
                            }
                        }
                    }
                }
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error saving link: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Save activity log (AJAX)
     */
    public function saveLog(Request $request)
    {
        try {
            $validated = $request->validate([
                'contract_review_id' => 'required|integer',
                'log' => 'nullable|string',
            ]);

            $contractReview = ContractReview::findOrFail($validated['contract_review_id']);

            $contractReview->update([
                'log' => $validated['log'],
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error saving log: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Approve revision (AJAX)
     */
    public function approve(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'revision_id' => 'required|integer',
            ]);

            $revision = ContractReviewRevision::findOrFail($validated['revision_id']);
            $contractReview = $revision->contractReview;

            $revision->update([
                'result' => 'approve',
                'date_result' => now()->toDateString(),
            ]);

            $contractReview->update([
                'result' => 'approve',
                'status' => 'completed',
            ]);

            // ✅ DELETE notifikasi "Menunggu Review SCM" untuk contract ini
            \App\Models\VendorNotification::where('vendor_id', $contractReview->vendor_id)
                ->where('title', 'Menunggu Review SCM')
                ->where('link', route('vendor.contract-review.review', $contractReview->contract_review_id))
                ->delete();

            // ✅ DELETE stored notification "Review Kontrak Diperlukan" untuk SCM users
            \App\Models\Notification::where('title', 'Review Kontrak Diperlukan')
                ->where('action_url', route('supply-chain.contract-review.show', $contractReview->contract_review_id))
                ->delete();

            // ✅ Notify ALL SCM Users (History)
            $contractReview->load('procurement.project');
            $scmUsers = \App\Models\User::whereHas('division', function ($q) {
                $q->where('division_name', 'LIKE', '%Supply Chain%');
            })->get();

            // ✅ Check if ALL contract reviews for this procurement are approved
            $procurement = $contractReview->procurement;
            if ($procurement && $procurement->contractReviews()->count() > 0) {
                $allContractReviews = $procurement->contractReviews()->with('revisions')->get();

                // Check if ALL contract reviews have 'approve' result
                $allApproved = $allContractReviews->every(function ($review) {
                    $latestRevision = $review->revisions()->latest('contract_review_revision_id')->first();
                    return $latestRevision && $latestRevision->result === 'approve';
                });
                
                if ($allApproved) {
                    // Notify Supply Chain Division (completion notification)
                    foreach ($scmUsers as $user) {
                        \App\Models\Notification::create([
                            'user_id' => $user->user_id,
                            'type' => 'success',
                            'title' => 'Review Kontrak Selesai',
                            'message' => "Seluruh review kontrak pada pengadaan '{$procurement->procurement_name}' telah selesai.",
                            'action_url' => route('procurements.show', $procurement->procurement_id),
                            'reference_type' => 'App\Models\Procurement',
                            'reference_id' => $procurement->procurement_id,
                            'is_read' => false,
                        ]);
                    }
                }
            }


            ActivityLogger::log(
                module: 'Contract Review',
                action: 'approve_revision',
                targetId: $contractReview->contract_review_id,
                details: ['revision' => $revision->revision_code, 'user_id' => Auth::id()]
            );

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving revision: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Reject revision (AJAX)
     */
    public function reject(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'revision_id' => 'required|integer',
            ]);

            $revision = ContractReviewRevision::findOrFail($validated['revision_id']);
            $contractReview = $revision->contractReview;

            $revision->update([
                'result' => 'not_approve',
                'date_result' => now()->toDateString(),
            ]);

            $contractReview->update([
                'result' => 'not_approve',
                'status' => 'completed',
            ]);

            // ✅ DELETE notifikasi "Menunggu Review SCM" untuk contract ini
            \App\Models\VendorNotification::where('vendor_id', $contractReview->vendor_id)
                ->where('title', 'Menunggu Review SCM')
                ->where('link', route('vendor.contract-review.review', $contractReview->contract_review_id))
                ->delete();

            // ✅ DELETE stored notification "Review Kontrak Diperlukan" untuk SCM users
            \App\Models\Notification::where('title', 'Review Kontrak Diperlukan')
                ->where('action_url', route('supply-chain.contract-review.show', $contractReview->contract_review_id))
                ->delete();

            // ✅ Notify ALL SCM Users (History)
            $contractReview->load('procurement.project');
            $scmUsers = \App\Models\User::whereHas('division', function ($q) {
                $q->where('division_name', 'LIKE', '%Supply Chain%');
            })->get();

            foreach ($scmUsers as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'danger',
                    'title' => 'Kontrak Ditolak',
                    'message' => "Kontrak pengadaan '{$contractReview->procurement->project->project_name}' ({$revision->revision_code}) DITOLAK.",
                    'action_url' => route('supply-chain.contract-review.show', $contractReview->contract_review_id),
                    'reference_type' => 'App\Models\ContractReview',
                    'reference_id' => $contractReview->contract_review_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }


            ActivityLogger::log(
                module: 'Contract Review',
                action: 'reject_revision',
                targetId: $contractReview->contract_review_id,
                details: ['revision' => $revision->revision_code, 'user_id' => Auth::id()]
            );

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting revision: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }



    /**
     * Create new revision (AJAX)
     */
    public function revisi(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'revision_id' => 'required|integer',
            ]);

            $revision = ContractReviewRevision::findOrFail($validated['revision_id']);
            $contractReview = $revision->contractReview;

            // Update old revision dengan result dan date_result
            $revision->update([
                'result' => 'revisi',
                'date_result' => now()->toDateString(),
            ]);

            // Create new revision
            $revisionNumber = intval(substr($contractReview->current_revision, 1)) + 1;
            $newRevisionCode = 'R' . $revisionNumber;

            $newRevision = ContractReviewRevision::create([
                'contract_review_id' => $contractReview->contract_review_id,
                'revision_code' => $newRevisionCode,
                'date_sent_to_vendor' => now()->toDateString(),
                'result' => 'pending',
                'created_by' => Auth::id(),
            ]);

            $contractReview->update([
                'current_revision' => $newRevisionCode,
                'status' => 'on_progress',
            ]);

            // ✅ DELETE stored notification lama untuk SCM users dan vendor
            \App\Models\Notification::whereIn('title', ['Review Kontrak Diperlukan'])
                ->where('action_url', route('supply-chain.contract-review.show', $contractReview->contract_review_id))
                ->delete();

            \App\Models\VendorNotification::where('vendor_id', $contractReview->vendor_id)
                ->whereIn('title', ['Menunggu Review SCM', 'Kontrak Disetujui', 'Kontrak Ditolak'])
                ->where('link', route('vendor.contract-review.review', $contractReview->contract_review_id))
                ->delete();

            ActivityLogger::log(
                module: 'Contract Review',
                action: 'create_revision',
                targetId: $contractReview->contract_review_id,
                details: ['new_revision' => $newRevisionCode, 'user_id' => Auth::id()]
            );

            // ✅ Notify SCM Users: Menunggu Vendor
            $contractReview->load('vendor', 'procurement.project');
            $scmUsers = \App\Models\User::whereHas('division', function ($q) {
                $q->where('division_name', 'LIKE', '%Supply Chain%');
            })->get();

            foreach ($scmUsers as $user) {
                \App\Models\Notification::create([
                    'user_id' => $user->user_id,
                    'type' => 'info',
                    'title' => 'Lengkapi Dokumen Kontrak',
                    'message' => "Silakan lengkapi dokumen revisi kontrak {$contractReview->procurement->project->project_name} ({$newRevisionCode}).",
                    'action_url' => route('supply-chain.contract-review.show', $contractReview->contract_review_id),
                    'reference_type' => 'App\Models\ContractReview',
                    'reference_id' => $contractReview->contract_review_id,
                    'is_read' => false,
                    'created_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'new_revision' => [
                    'contract_review_revision_id' => $newRevision->contract_review_revision_id,
                    'revision_code' => $newRevision->revision_code,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating revision: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }
}
