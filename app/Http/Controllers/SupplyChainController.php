<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RequestProcurement;
use App\Models\Procurement;
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
    public function dashboard(Request $request)
    {
        $search = $request->input('search');
        $statusFilter = $request->input('status');
        $priorityFilter = $request->input('priority');

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
            ->get();

        return view('supply_chain.dashboard', compact('procurements'));
    }

    /**
     * Show create procurement form
     */
    public function createPengadaan()
    {
        return view('supply_chain.create');
    }

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
            ->orderBy('name_vendor')
            ->get();

        $procurements = procurement::whereIn('status_procurement', ['pemilihan_vendor'])
            ->with(['ownerDivision'])
            ->get();

        return view('supply_chain.vendor.kelola', compact('vendors', 'procurements'));
    }
    public function pilihVendor($procurementId, Request $request)
    {
        // Ambil data procurement berdasarkan ID
        $procurement = Procurement::with(['project', 'department'])
            ->findOrFail($procurementId);

        // Query vendors dengan search (jika ada)
        $search = $request->input('search');

        $vendors = Vendor::query()
            ->when($search, function ($query, $search) {
                return $query->where('name_vendor', 'like', "%{$search}%")
                    ->orWhere('id_vendor', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            })
            ->where('legal_status', 'verified') // Hanya vendor yang sudah diapprove
            ->orderBy('name_vendor', 'asc')
            ->get();

        // Hitung statistik
        $stats = [
            'total' => Vendor::count(),
            'active' => Vendor::where('legal_status', 'approved')->count(),
            'pending' => Vendor::where('legal_status', 'pending')->count(),
            'importer' => Vendor::where('is_importer', true)->count(),
        ];

        return view('supply_chain.vendor.pilih', compact('vendors', 'stats', 'procurement'))
            ->with('hideNavbar', true);
    }

    public function simpanVendor($procurementId, Request $request)
    {
        // Validasi input vendor_id
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id_vendor'
        ]);

        // Ambil data procurement
        $procurement = Procurement::findOrFail($procurementId);

        // Ambil data vendor yang dipilih
        $vendor = Vendor::findOrFail($validated['vendor_id']);

        // Cek apakah sudah ada request procurement untuk pengadaan ini
        $existingRequest = RequestProcurement::where('procurement_id', $procurementId)->first();

        if ($existingRequest) {
            // Jika sudah ada, update vendor_id nya
            $existingRequest->update([
                'vendor_id' => $validated['vendor_id'],
                'request_status' => 'submitted' // Ubah status jadi submitted
            ]);

            $message = "Vendor berhasil diubah menjadi {$vendor->name_vendor}";
        } else {
            // Jika belum ada, buat request procurement baru
            RequestProcurement::create([
                'procurement_id' => $procurementId,
                'vendor_id' => $validated['vendor_id'],
                'request_name' => "Request untuk {$procurement->name_procurement}",
                'department_id' => auth()->user()->department_id,
                'request_status' => 'submitted',
                'created_date' => now(),
                'deadline_date' => $procurement->end_date,
            ]);

            $message = "Vendor {$vendor->name_vendor} berhasil dipilih";
        }

        // Redirect ke dashboard dengan pesan sukses
        return redirect()
            ->route('supply-chain.dashboard')
            ->with('success', $message);
    }

    public function formVendor(Request $request)
    {
        $vendorId = $request->query('id');
        $vendor   = $vendorId ? Vendor::find($vendorId) : null;

        if ($vendorId && ! $vendor) {
            return redirect()->route('supply-chain.vendor.kelola')
                ->with('error', 'Vendor tidak ditemukan.');
        }

        return view('supply_chain.vendor.form', compact('vendor'))
            ->with('hideNavbar', true)
            ->with('success', "Vendor berhasil ditambahkan.");
    }

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
                'email' => 'required|email|max:255|unique:vendors,email',
                'is_importer' => 'nullable|boolean',
            ]);

            // Generate ID Vendor dengan pengecekan duplikasi
            DB::beginTransaction();

            try {
                // Ambil vendor terakhir berdasarkan ID (terurut descending)
                $lastVendor = Vendor::orderByRaw('CAST(SUBSTRING(id_vendor, 3) AS UNSIGNED) DESC')->first();

                // Hitung nomor baru
                if ($lastVendor && preg_match('/^V-(\d+)$/', $lastVendor->id_vendor, $matches)) {
                    $lastNumber = intval($matches[1]);
                } else {
                    $lastNumber = 0;
                }

                // Loop untuk memastikan ID unik
                do {
                    $lastNumber++;
                    $idVendor = 'V-' . str_pad($lastNumber, 3, '0', STR_PAD_LEFT);
                } while (Vendor::where('id_vendor', $idVendor)->exists());

                // Create vendor dengan ID yang sudah dipastikan unik
                $vendor = Vendor::create([
                    'id_vendor' => $idVendor,
                    'name_vendor' => $validated['name_vendor'],
                    'address' => $validated['address'] ?? null,
                    'phone_number' => $validated['phone_number'],
                    'email' => $validated['email'],
                    'is_importer' => $request->has('is_importer') ? 1 : 0,
                    'legal_status' => 'pending',
                ]);

                DB::commit();

                $redirect = $request->input('redirect', 'kelola');
                $routeName = $redirect === 'pilih' ? 'supply-chain.vendor.pilih' : 'supply-chain.vendor.kelola';

                return redirect()->route($routeName)
                    ->with('success', 'Vendor "' . $vendor->name_vendor . '" berhasil ditambahkan dengan ID: ' . $idVendor);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
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
     * Reviewprocurement for SC
     */
    public function reviewProject($procurementId)
    {

        return view('supply_chain.review_project', compact('procurement'))
            ->with('hideNavbar', true);
    }

    /**
     * Approveprocurement review
     */
    public function approveReview(Request $request, $procurementId)
    {
        $procurement = procurement::findOrFail($procurementId);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($procurement, $validated) {
            $procurement->update([
                'status_procurement' => 'persetujuan_sekretaris'
            ]);

            // Notify Sekretaris Direksi
            $sekretaris = \App\Models\User::where('roles', 'sekretaris_direksi')->get();
            foreach ($sekretaris as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'sender_id' => Auth::id(),
                    'type' => 'approval_required',
                    'title' => 'Persetujuan Diperlukan',
                    'message' => 'Proyek menunggu persetujuan Sekretaris: ' . $procurement->name_project,
                    'reference_type' => 'App\Models\Project',
                    'reference_id' => $procurement->project_id,
                ]);
            }
        });

        return redirect()->route('supply-chain.dashboard')
            ->with('success', 'Reviewprocurement berhasil disetujui');
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
     * Select vendor forprocurement
     */
    public function selectVendor(Request $request, $procurementId)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,vendor_id',
        ]);

        $procurement = procurement::findOrFail($procurementId);

        // Create or update contract with vendor
        Contract::create([
            'project_id' => $procurement->project_id,
            'vendor_id' => $validated['vendor_id'],
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        $procurement->update([
            'status_procurement' => 'pengecekan_legalitas'
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
        $procurements = procurement::whereIn('status_procurement', ['pemesanan', 'pengiriman_material'])
            ->with(['contracts.vendor'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('supply_chain.material_shipping', compact('projects'));
    }

    /**
     * Update material arrival
     */
    public function updateMaterialArrival(Request $request, $procurementId)
    {
        $validated = $request->validate([
            'arrival_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $procurement = procurement::findOrFail($procurementId);
        $procurement->update(['status_procurement' => 'inspeksi_barang']);

        // Notify QA team
        $qaUsers = \App\Models\User::where('roles', 'qa')->get();
        foreach ($qaUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'sender_id' => Auth::id(),
                'type' => 'inspection_required',
                'title' => 'Inspeksi Material Diperlukan',
                'message' => 'Material telah tiba untuk proyek: ' . $procurement->name_project,
                'reference_type' => 'App\Models\Project',
                'reference_id' => $procurement->project_id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status kedatangan material berhasil diupdate'
        ]);
    }

    /**
     * Store multiple pengadaan
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
                // Generate code
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
