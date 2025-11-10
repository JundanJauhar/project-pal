@extends('layouts.app')

@section('title', 'Inspection Reports')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-clipboard-check"></i> Inspection Reports</h2>
        <p class="text-muted">Laporan inspeksi material dan barang</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('inspections.ncr.index') }}" class="btn btn-warning btn-custom me-2">
            <i class="bi bi-exclamation-triangle"></i> NCR Reports
        </a>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-custom" style="border-left: 4px solid #28a745;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Passed</h6>
                        <h3 class="mb-0">{{ $inspections->where('result', 'passed')->count() }}</h3>
                    </div>
                    <i class="bi bi-check-circle" style="font-size: 36px; color: #28a745;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom" style="border-left: 4px solid #dc3545;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Failed</h6>
                        <h3 class="mb-0">{{ $inspections->where('result', 'failed')->count() }}</h3>
                    </div>
                    <i class="bi bi-x-circle" style="font-size: 36px; color: #dc3545;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-muted">Conditional</h6>
                        <h3 class="mb-0">{{ $inspections->where('result', 'conditional')->count() }}</h3>
                    </div>
                    <i class="bi bi-exclamation-triangle" style="font-size: 36px; color: #ffc107;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inspections Table -->
<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Inspeksi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Tanggal Inspeksi</th>
                                <th>Project</th>
                                <th>Item</th>
                                <th>Inspector</th>
                                <th>Result</th>
                                <th>NCR Required</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inspections as $inspection)
                            <tr>
                                <td>{{ $inspection->inspection_date->format('d/m/Y') }}</td>
                                <td>
                                    <strong>{{ $inspection->project->code_project }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($inspection->project->name_project, 30) }}</small>
                                </td>
                                <td>{{ $inspection->item->item_name ?? 'All Items' }}</td>
                                <td>{{ $inspection->inspector->name }}</td>
                                <td>
                                    @php
                                        $resultClass = match($inspection->result) {
                                            'passed' => 'success',
                                            'failed' => 'danger',
                                            'conditional' => 'warning',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $resultClass }}">
                                        {{ ucfirst($inspection->result) }}
                                    </span>
                                </td>
                                <td>
                                    @if($inspection->ncr_required)
                                        <span class="badge bg-danger">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('inspections.show', $inspection->inspection_id) }}"
                                       class="btn btn-sm btn-info btn-custom">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-2">Tidak ada data inspeksi</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $inspections->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
