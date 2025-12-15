<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Procurement;
use App\Models\Item;
use App\Models\Vendor;
use App\Models\Hps;
use App\Models\Notification;
use App\Models\ProcurementProgress;
use App\Models\EvatekItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\CheckpointTransitionService;
use App\Helpers\ActivityLogger;

class SupplyChainController extends Controller
{
    /**
     * Display Supply Chain dashboard
     */
    public function dashboard(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $priorityFilter = $request->input('priority');
        $checkpoints = Checkpoint::all();

        $procurements = Procurement::with([
            'project',
            'department',
            'requestProcurements',
            'requestProcurements.vendor'
        ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('code_procurement', 'LIKE', "%{$search}%")
                        ->orWhere('name_procurement', 'LIKE', "%{$search}%")
                        ->orWhereHas('department', function ($dept) use ($search) {
                            $dept->where('department_name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($priorityFilter, function ($query, $priority) {
                return $query->where('priority', $priority);
            })
            ->when($statusFilter, function ($query, $status) {
                if ($status === 'belum_ada_vendor') {
                    return $query->doesntHave('requestProcurements');
                } else {
                    return $query->whereHas('requestProcurements', function ($q) use ($status) {
                        $q->where('request_status', $status);
                    });
                }
            })
            ->orderBy('start_date', 'desc')
            ->paginate(10)
            ->withQueryString();

        ActivityLogger::log(
            module: 'Supply Chain',
            action: 'view_dashboard',
            targetId: null,
            details: ['filters' => $request->all(), 'user_id' => Auth::id()]
        );

        return view('supply_chain.dashboard', compact('procurements', 'checkpoints'));
    }

    /**
     * Show form untuk input item baru
     */
    public function inputItem($projectId)
    {
        if (Auth::user()->roles !== 'supply_chain') {
            abort(403, 'Unauthorized action.');
        }

        $project = Project::findOrFail($projectId);
        $procurements = Procurement::where('project_id', $projectId)
            ->with('requestProcurements.items')
            ->orderBy('code_procurement', 'asc')
            ->get();
        

        return view('supply_chain.input-item', compact('project', 'procurements', 'vendors'));
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

    /**
     * Simpan item dengan multiple vendors
     */
    public function storeItem(Request $request, $projectId)
    {
        $validated = $request->validate([
            'procurement_id' => 'required',
            'item_id' => 'required|exists:items,item_id',
            'vendor_ids' => 'required|array|min:1',
            'vendor_ids.*' => 'required|exists:vendors,id_vendor',
        ], [
            'item_id.required' => 'Pilih satu item terlebih dahulu',
            'vendor_ids.required' => 'Pilih minimal satu vendor',
            'vendor_ids.min' => 'Harus memilih minimal satu vendor',
        ]);

        $project = Project::findOrFail($projectId);
        if (Auth::user()->department_id != $project->department_id && Auth::user()->roles !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke project ini.');
        }

        try {
            DB::beginTransaction();

            $item = Item::findOrFail($validated['item_id']);
            $procurement = Procurement::findOrFail($validated['procurement_id']);

            if ($procurement->project_id != $projectId) {
                throw new \Exception('Procurement tidak sesuai dengan project.');
            }

            $vendorNames = Vendor::whereIn('id_vendor', $validated['vendor_ids'])
                ->pluck('name_vendor')
                ->implode(', ');

            $vendorIds = $validated['vendor_ids'];

            foreach ($vendorIds as $vendorId) {
                $existingRequest = RequestProcurement::where('procurement_id', $validated['procurement_id'])
                    ->where('vendor_id', $vendorId)
                    ->where('project_id', $projectId)
                    ->first();

                if (!$existingRequest) {
                    $requestProcurement = RequestProcurement::create([
                        'procurement_id' => $validated['procurement_id'],
                        'vendor_id' => $vendorId,
                        'project_id' => $projectId,
                        'request_name' => 'Request untuk ' . $item->item_name . ' - Vendor: ' . Vendor::find($vendorId)->name_vendor,
                        'created_date' => now()->toDateString(),
                        'deadline_date' => $procurement->end_date,
                        'request_status' => 'pending',
                        'department_id' => Auth::user()->department_id,
                    ]);

                    if ($item->request_procurement_id === null) {
                        $item->update([
                            'request_procurement_id' => $requestProcurement->request_id,
                        ]);
                    }
                }
            }

            if ($item->status === 'not_approved') {
                $item->update(['status' => 'pending']);
            }

            EvatekItem::firstOrCreate(
                ['item_id' => $item->item_id],
                [
                    'project_id' => $projectId,
                    'current_revision' => 'R0',
                    'current_status' => 'on_progress',
                    'current_date' => now()->toDateString(),
                ]
            );

            DB::commit();

            return redirect()->route('supply-chain.dashboard')
                ->with('success', "Item '{$item->item_name}' berhasil ditambahkan untuk {$vendorNames}!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing item: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Store Evatek item(s) directly
     */
    public function storeEvatekItem(Request $request, $projectId)
    {
        if (Auth::user()->roles !== 'supply_chain' && Auth::user()->roles !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'procurement_id' => 'required|exists:procurement,procurement_id',
            'item_id' => 'required|exists:items,item_id',
            'vendor_ids' => 'required|array|min:1',
            'vendor_ids.*' => 'required|exists:vendors,id_vendor',
            'start_date' => 'nullable|date',
            'target_date' => 'nullable|date|after:today',
        ]);

        try {
            DB::beginTransaction();

            $project = Project::findOrFail($projectId);
            $item = Item::findOrFail($validated['item_id']);
            $procurement = Procurement::findOrFail($validated['procurement_id']);

            if ($procurement->project_id != $projectId) {
                throw new \Exception('Procurement tidak sesuai dengan project.');
            }

            $created = [];
            foreach ($validated['vendor_ids'] as $vendorId) {
                $targetDate = $validated['target_date']
                    ? \Carbon\Carbon::parse($validated['target_date'])->toDateString()
                    : $procurement->end_date->toDateString();

                $evatek = EvatekItem::firstOrCreate(
                    [
                        'item_id' => $item->item_id,
                        'procurement_id' => $procurement->procurement_id,
                        'vendor_id' => $vendorId,
                    ],
                    [
                        'project_id' => $projectId,
                        'start_date' => now()->toDateString(),
                        'target_date' => $targetDate,
                        'current_revision' => 'R0',
                        'status' => 'on_progress',
                        'current_date' => null,
                    ]
                );

                $created[] = $evatek;
            }

            ActivityLogger::log(
                module: 'Vendor',
                action: 'store_evatek_item',
                targetId: $item->item_id,
                details: ['user_id' => Auth::id()]
            );

            DB::commit();

            $vendorNames = Vendor::whereIn('id_vendor', $validated['vendor_ids'])
                ->pluck('name_vendor')
                ->implode(', ');

            return redirect()->route('procurements.show', $procurement->procurement_id)
                ->with('success', "Evatek item '{$item->item_name}' berhasil dibuat untuk: {$vendorNames}");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing evatek item: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Gagal menyimpan Evatek item: ' . $e->getMessage())
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
            ->orderByRaw('CAST(SUBSTRING(id_vendor, 3) AS UNSIGNED) ASC')
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

        return view('supply_chain.vendor.kelola', compact('vendors', 'procurements'));
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
                    ->orWhere('id_vendor', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->orderBy('id_vendor', 'asc')
            ->get();

        $stats = [
            'total' => Vendor::count(),
            'importer' => Vendor::where('is_importer', true)->count(),
        ];

        ActivityLogger::log(
            module: 'Vendor',
            action: 'view_select_vendor_form',
            targetId: $procurement->procurement_id,
            details: ['user_id' => Auth::id()]
        );

        return view('supply_chain.vendor.pilih', compact('vendors', 'stats', 'procurement'))
            ->with('hideNavbar', true);
    }

    /**
     * Simpan Vendor
     */
    public function simpanVendor($procurementId, Request $request)
    {
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

        return view('supply_chain.vendor.form', compact('vendor'))
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
                'name_vendor' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'address' => 'nullable|string|max:500',
                'is_importer' => 'nullable|boolean',
            ]);

            $vendor->update([
                'name_vendor' => $validated['name_vendor'],
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

        return view('supply_chain.vendor.detail', compact('vendor'))
            ->with('hideNavbar', true);
    }

    /**
     * Store Vendor
     */
    public function storeVendor(Request $request)
    {
        try {
            $validated = $request->validate([
                'name_vendor' => 'required|string|max:255|unique:vendors,name_vendor',
                'address' => 'nullable|string|max:500',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|max:255|unique:vendors,email',
                'user_vendor' => 'nullable|string|max:100|unique:vendors,user_vendor',
                'is_importer' => 'nullable|boolean',
            ]);

            DB::beginTransaction();

            $lastVendor = Vendor::orderByRaw('CAST(SUBSTRING(id_vendor, 3) AS UNSIGNED) DESC')->first();
            if ($lastVendor && preg_match('/^(\d+)$/', $lastVendor->id_vendor, $matches)) {
                $lastNumber = intval($matches[1]);
            } else {
                $lastNumber = 0;
            }

            do {
                $lastNumber++;
                $idVendor = str_pad($lastNumber, 3, '0', STR_PAD_LEFT); // 001, 002, 003
            } while (Vendor::where('id_vendor', $idVendor)->exists());

            $vendor = Vendor::create([
                'id_vendor' => $idVendor,
                'name_vendor' => $validated['name_vendor'],
                'address' => $validated['address'] ?? null,
                'phone_number' => $validated['phone_number'],
                'email' => $validated['email'],
                'user_vendor' => $validated['user_vendor'] ?? null, // auto-generate di model jika null
                'is_importer' => $request->has('is_importer') ? 1 : 0,
            ]);

            ActivityLogger::log(
                module: 'Vendor',
                action: 'store_vendor',
                targetId: $vendor->id_vendor,
                details: ['user_id' => Auth::id()]
            );

            DB::commit();

            $redirect = $request->input('redirect', 'kelola');
            $routeName = $redirect === 'pilih' ? 'supply-chain.vendor.pilih' : 'supply-chain.vendor.kelola';

            return redirect()->route($routeName)
                ->with('success', 'Vendor "' . $vendor->name_vendor . '" berhasil ditambahkan dengan ID: ' . $idVendor . ', Email Login: ' . $vendor->user_vendor);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating vendor: ' . $e->getMessage());

            return back()
                ->with('error', 'Gagal menambahkan vendor: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Review Project
     */
    public function reviewProject($procurementId)
    {
        $procurement = Procurement::findOrFail($procurementId);

        ActivityLogger::log(
            module: 'Procurement',
            action: 'review_project',
            targetId: $procurementId,
            details: ['user_id' => Auth::id()]
        );

        return view('supply_chain.review_project', compact('procurement'))
            ->with('hideNavbar', true);
    }

    /**
     * Approve Review
     */
    public function approveReview(Request $request, $procurementId)
    {
        $procurement = Procurement::findOrFail($procurementId);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($procurement, $validated) {
            $procurement->update([
                'status_procurement' => 'persetujuan_sekretaris'
            ]);

            $sekretaris = \App\Models\User::where('roles', 'sekretaris_direksi')->get();
            foreach ($sekretaris as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'sender_id' => Auth::id(),
                    'type' => 'approval_required',
                    'title' => 'Persetujuan Diperlukan',
                    'message' => 'Proyek menunggu persetujuan Sekretaris: ' . $procurement->name_procurement,
                    'reference_type' => 'App\Models\Procurement',
                    'reference_id' => $procurement->procurement_id,
                ]);
            }
        });

        ActivityLogger::log(
            module: 'Procurement',
            action: 'approve_review',
            targetId: $procurement->procurement_id,
            details: ['user_id' => Auth::id()]
        );

        return redirect()->route('supply-chain.dashboard')
            ->with('success', 'Review pengadaan berhasil disetujui');
    }

    /**
     * Vendors List
     */
    public function vendors()
    {
        $vendors = Vendor::orderBy('name_vendor')->paginate(20);

        ActivityLogger::log(
            module: 'Vendor',
            action: 'view_vendors_admin',
            targetId: null,
            details: ['user_id' => Auth::id()]
        );

        return view('supply_chain.vendors', compact('vendors'));
    }

    /**
     * Update Material Arrival
     */
    public function updateMaterialArrival(Request $request, $procurementId)
    {
        $validated = $request->validate([
            'arrival_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $procurement = Procurement::findOrFail($procurementId);
        $procurement->update(['status_procurement' => 'inspeksi_barang']);

        $qaUsers = \App\Models\User::where('roles', 'qa')->get();
        foreach ($qaUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
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
}
