<?php

namespace App\Http\Controllers;

use App\Models\Procurement;
use App\Models\Kontrak;
use App\Models\Pembayaran;
use App\Helpers\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplyChainDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'procurement');

        // Data untuk tab yang aktif
        $data = match ($tab) {
            'contract' => $this->getContractData($request),
            'payment'  => $this->getPaymentData($request),
            default    => $this->getProcurementData($request),
        };

        ActivityLogger::log(
            module: 'Supply Chain',
            action: 'view_supply_chain_dashboard',
            targetId: null,
            details: [
                'user_id' => Auth::id(),
                'tab' => $tab,
                'filters' => $request->all(),
            ]
        );

        return view('supply-chain.dashboard', array_merge($data, ['tab' => $tab]));
    }

    protected function getProcurementData(Request $request)
    {
        $search = $request->input('search', '');
        $priorityFilter = $request->input('priority', '');
        $statusFilter = $request->input('status', '');

        $procurements = Procurement::with([
            'project:project_id,project_code,project_name',
            'department:department_id,department_name',
            'requestProcurements' => function ($query) {
                $query->with('vendor:id_vendor,name_vendor')
                    ->latest()
                    ->limit(1);
            }
        ])
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('code_procurement', 'LIKE', "%{$search}%")
                        ->orWhere('name_procurement', 'LIKE', "%{$search}%")
                        ->orWhereHas('project', function ($pq) use ($search) {
                            $pq->where('project_code', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($priorityFilter, function ($query, $priority) {
                return $query->where('priority', $priority);
            })
            ->when($statusFilter, function ($query, $status) {
                if ($status === 'belum_ada_vendor') {
                    return $query->whereDoesntHave('requestProcurements.vendor');
                } else {
                    return $query->where('status_procurement', $status);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return ['procurements' => $procurements];
    }

    protected function getContractData(Request $request)
    {
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');

        $contracts = Procurement::with([
            'project:project_id,project_code,project_name',
            'department:department_id,department_name',
            'requestProcurements' => function ($query) {
                $query->with('vendor:id_vendor,name_vendor')
                    ->latest()
                    ->limit(1);
            },
            'kontraks' => function ($query) {
                $query->select('kontrak_id', 'procurement_id', 'vendor_id', 'nilai', 'currency', 'tgl_kontrak')
                    ->latest('created_at')
                    ->limit(1);
            },
            'kontraks.vendor:id_vendor,name_vendor'
        ])
            ->whereHas('kontraks')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('code_procurement', 'LIKE', "%{$search}%")
                        ->orWhere('name_procurement', 'LIKE', "%{$search}%")
                        ->orWhereHas('kontraks.vendor', function ($vq) use ($search) {
                            $vq->where('name_vendor', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($statusFilter, function ($query, $status) {
                // Kontrak tidak punya status, skip filter
                return $query;
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return ['contracts' => $contracts];
    }

    protected function getPaymentData(Request $request)
    {
        $search = $request->input('search', '');
        $typeFilter = $request->input('type', '');

        $payments = Procurement::with([
            'project:project_id,project_code,project_name',
            'requestProcurements' => function ($query) {
                $query->with('vendor:id_vendor,name_vendor')
                    ->latest()
                    ->limit(1);
            },
            'pembayarans' => function ($query) use ($typeFilter) {
                $query->select('id', 'procurement_id', 'vendor_id', 'payment_type', 'payment_value', 'percentage', 'realization_date', 'no_memo')
                    ->when($typeFilter, function ($q, $type) {
                        return $q->where('payment_type', $type);
                    })
                    ->orderBy('created_at', 'desc');
            },
            'pembayarans.vendor:id_vendor,name_vendor'
        ])
            ->whereHas('pembayarans', function ($query) use ($typeFilter) {
                if ($typeFilter) {
                    $query->where('payment_type', $typeFilter);
                }
            })
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('code_procurement', 'LIKE', "%{$search}%")
                        ->orWhereHas('requestProcurements.vendor', function ($vq) use ($search) {
                            $vq->where('name_vendor', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return ['payments' => $payments];
    }
}
