<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Kontrak;
use App\Models\Pembayaran;
use App\Models\Department;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplyChainDashboardController extends Controller
{
    /**
     * Display Supply Chain Dashboard main page
     * Render Blade dengan layout dasar + JavaScript
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'procurement');

        // Load required data untuk filter dropdowns
        $departments = Department::all();

        ActivityLogger::log(
            module: 'Supply Chain',
            action: 'view_supply_chain_dashboard',
            targetId: null,
            details: [
                'user_id' => Auth::id(),
                'tab' => $tab,
            ]
        );

        return view('supply-chain.dashboard', [
            'tab' => $tab,
            'departments' => $departments,
        ]);
    }

    /**
     * AJAX: Get procurement data dengan filter
     * Returns: JSON dengan data dan pagination
     */
    public function ajaxProcurement(Request $request)
    {
        $search = $request->input('search', '');
        $priorityFilter = $request->input('priority', '');
        $statusFilter = $request->input('status', '');
        $departmentFilter = $request->input('department', '');
        $page = $request->input('page', 1);
        $perPage = 15;

        // Base query
        $query = Procurement::with([
            'project:project_id,project_code,project_name',
            'department:department_id,department_name',
            'requestProcurements' => function ($q) {
                $q->with('vendor:id_vendor,name_vendor')
                    ->latest()
                    ->limit(1);
            }
        ]);

        // Apply filters
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code_procurement', 'LIKE', "%{$search}%")
                    ->orWhere('name_procurement', 'LIKE', "%{$search}%")
                    ->orWhereHas('project', function ($pq) use ($search) {
                        $pq->where('project_code', 'LIKE', "%{$search}%");
                    });
            });
        }

        if (!empty($priorityFilter)) {
            $query->where('priority', $priorityFilter);
        }

        if (!empty($statusFilter)) {
            if ($statusFilter === 'belum_ada_vendor') {
                $query->whereDoesntHave('requestProcurements.vendor');
            } else {
                $query->where('status_procurement', $statusFilter);
            }
        }

        if (!empty($departmentFilter)) {
            $query->where('department_procurement', $departmentFilter);
        }

        // Get total untuk pagination
        $total = $query->count();
        $lastPage = $total > 0 ? ceil($total / $perPage) : 1;

        // Apply pagination
        $procurements = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Format response
        $data = $procurements->map(function ($p) {
            $vendor = $p->requestProcurements->first()?->vendor;

            return [
                'procurement_id' => $p->procurement_id,
                'project_code' => $p->project?->project_code ?? '-',
                'code_procurement' => $p->code_procurement,
                'name_procurement' => substr($p->name_procurement, 0, 40),
                'department_name' => $p->department?->department_name ?? '-',
                'vendor_name' => $vendor?->name_vendor ?? null,
                'start_date' => optional($p->start_date)->format('d/m/Y') ?? '-',
                'end_date' => optional($p->end_date)->format('d/m/Y') ?? '-',
                'priority' => $p->priority,
                'status_procurement' => $p->status_procurement,
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => $page < $lastPage,
            ]
        ]);
    }

    /**
     * AJAX: Get contract data dengan filter
     * Returns: JSON dengan data dan pagination
     */
    public function ajaxContract(Request $request)
    {
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $page = $request->input('page', 1);
        $perPage = 15;

        // Base query
        $query = Procurement::with([
            'project:project_id,project_code,project_name',
            'department:department_id,department_name',
            'requestProcurements' => function ($q) {
                $q->with('vendor:id_vendor,name_vendor')
                    ->latest()
                    ->limit(1);
            },
            'kontraks' => function ($q) {
                $q->select('kontrak_id', 'procurement_id', 'vendor_id', 'nilai', 'currency', 'tgl_kontrak')
                    ->latest('created_at')
                    ->limit(1);
            },
            'kontraks.vendor:id_vendor,name_vendor'
        ])
            ->whereHas('kontraks');

        // Apply filters
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code_procurement', 'LIKE', "%{$search}%")
                    ->orWhere('name_procurement', 'LIKE', "%{$search}%")
                    ->orWhereHas('kontraks.vendor', function ($vq) use ($search) {
                        $vq->where('name_vendor', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Get total untuk pagination
        $total = $query->count();
        $lastPage = $total > 0 ? ceil($total / $perPage) : 1;

        // Apply pagination
        $contracts = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Format response
        $data = $contracts->map(function ($p) {
            $kontrak = $p->kontraks->first();
            $vendor = $kontrak?->vendor ?? $p->requestProcurements->first()?->vendor;

            return [
                'procurement_id' => $p->procurement_id,
                'code_procurement' => $p->code_procurement,
                'name_procurement' => substr($p->name_procurement, 0, 40),
                'vendor_name' => $vendor?->name_vendor ?? '-',
                'nilai_kontrak' => $kontrak?->nilai ?? 0,
                'mata_uang' => $kontrak?->currency ?? 'IDR',
                'tanggal_kontrak' => optional($kontrak?->tgl_kontrak)->format('d/m/Y') ?? '-',
            ];
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => $page < $lastPage,
            ]
        ]);
    }

    /**
     * AJAX: Get payment data dengan filter
     * Returns: JSON dengan data dan pagination
     */
    public function ajaxPayment(Request $request)
    {
        $search = $request->input('search', '');
        $typeFilter = $request->input('type', '');
        $page = $request->input('page', 1);
        $perPage = 15;

        // Base query
        $query = Procurement::with([
            'project:project_id,project_code,project_name',
            'requestProcurements' => function ($q) {
                $q->with('vendor:id_vendor,name_vendor')
                    ->latest()
                    ->limit(1);
            },
            'pembayarans' => function ($q) use ($typeFilter) {
                $q->select('id', 'procurement_id', 'vendor_id', 'payment_type', 'payment_value', 
                           'percentage', 'realization_date', 'no_memo')
                    ->when(!empty($typeFilter), function ($sq) use ($typeFilter) {
                        return $sq->where('payment_type', $typeFilter);
                    })
                    ->orderBy('created_at', 'desc');
            },
            'pembayarans.vendor:id_vendor,name_vendor'
        ])
            ->whereHas('pembayarans', function ($q) use ($typeFilter) {
                if (!empty($typeFilter)) {
                    $q->where('payment_type', $typeFilter);
                }
            });

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('code_procurement', 'LIKE', "%{$search}%")
                    ->orWhereHas('requestProcurements.vendor', function ($vq) use ($search) {
                        $vq->where('name_vendor', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Get total untuk pagination
        $total = $query->count();
        $lastPage = $total > 0 ? ceil($total / $perPage) : 1;

        // Apply pagination
        $payments = $query->orderBy('created_at', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Flatten pembayaran untuk setiap row
        $data = [];
        foreach ($payments as $procurement) {
            $vendor = $procurement->requestProcurements->first()?->vendor;
            
            if ($procurement->pembayarans->isEmpty()) {
                $data[] = [
                    'type' => 'empty',
                    'code_procurement' => $procurement->code_procurement,
                    'procurement_id' => $procurement->procurement_id,
                ];
            } else {
                foreach ($procurement->pembayarans as $payment) {
                    $data[] = [
                        'type' => 'payment',
                        'code_procurement' => $procurement->code_procurement,
                        'procurement_id' => $procurement->procurement_id,
                        'vendor_name' => $payment->vendor?->name_vendor ?? $vendor?->name_vendor ?? '-',
                        'payment_type' => 'SKBDN',
                        'payment_value' => $payment->payment_value ?? 0,
                        'percentage' => $payment->percentage ?? 0,
                        'realization_date' => optional($payment->realization_date)->format('d/m/Y') ?? null,
                        'no_memo' => substr($payment->no_memo ?? '', 0, 30),
                    ];
                }
            }
        }

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
                'has_more' => $page < $lastPage,
            ]
        ]);
    }
}