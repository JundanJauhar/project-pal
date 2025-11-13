@extends('layouts.app')

@section('title', 'Supply Chain Dashboard')

@section('content')


<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-truck"></i> Supply Chain Dashboard</h2>
        <p class="text-muted">Kelola pengadaan material dan vendor</p>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-custom" style="border-left: 4px solid #667eea;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pending Review</h6>
                        <h3 class="mb-0">{{ $stats['pending_review'] }}</h3>
                    </div>
                    <i class="bi bi-clock-history" style="font-size: 36px; color: #667eea;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom" style="border-left: 4px solid #f093fb;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Active Negotiations</h6>
                        <h3 class="mb-0">{{ $stats['active_negotiations'] }}</h3>
                    </div>
                    <i class="bi bi-chat-dots" style="font-size: 36px; color: #f093fb;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom" style="border-left: 4px solid #4facfe;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Pending Contracts</h6>
                        <h3 class="mb-0">{{ $stats['pending_contracts'] }}</h3>
                    </div>
                    <i class="bi bi-file-earmark-text" style="font-size: 36px; color: #4facfe;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card card-custom" style="border-left: 4px solid #fa709a;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Material Requests</h6>
                        <h3 class="mb-0">{{ $stats['material_requests'] }}</h3>
                    </div>
                    <i class="bi bi-box-seam" style="font-size: 36px; color: #fa709a;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('supply-chain.material-requests') }}" class="btn btn-outline-primary w-100 btn-custom">
                            <i class="bi bi-box-seam"></i> Material Requests
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('supply-chain.vendors') }}" class="btn btn-outline-primary w-100 btn-custom">
                            <i class="bi bi-people"></i> Vendor Management
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('supply-chain.negotiations') }}" class="btn btn-outline-primary w-100 btn-custom">
                            <i class="bi bi-chat-dots"></i> Negotiations
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('supply-chain.material-shipping') }}" class="btn btn-outline-primary w-100 btn-custom">
                            <i class="bi bi-truck"></i> Material Shipping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Projects Needing Attention -->
<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-exclamation-circle"></i> Projects Needing Attention</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Kode Project</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Project</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($projects as $project)
                            <tr>
                                <td><strong>{{ $project->code_project }}</strong></td>
                                <td>{{ Str::limit($project->name_project, 50) }}</td>
                                <td style="padding: 12px 8px; text-align: center;">{{ $project->ownerDivision->nama_divisi ?? '-' }}</td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <span class="badge-priority badge-{{ strtolower($project->priority) }}">
                                        {{ strtoupper($project->priority) }}
                                    </span>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;" >
                                    @php
                                        $statusText = match($project->status_project) {
                                            'review_sc' => 'Review SC',
                                            'pemilihan_vendor' => 'Pemilihan Vendor',
                                            'pengecekan_legalitas' => 'Cek Legalitas',
                                            default => ucfirst($project->status_project)
                                        };
                                    @endphp
                                    <span class="badge bg-warning">{{ $statusText }}</span>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    @if($project->status_project === 'review_sc')
                                    <a href="{{ route('supply-chain.review-project', $project->project_id) }}"
                                       class="btn btn-sm btn-primary btn-custom">
                                        <i class="bi bi-eye"></i> Review
                                    </a>
                                    @elseif($project->status_project === 'pemilihan_vendor')
                                    <a href="{{ route('supply-chain.vendor.pilih') }}"
                                       class="btn btn-sm btn-success btn-custom">
                                        <i class="bi bi-people"></i> Pilih Vendor
                                    </a>
                                    @else
                                    <a href="{{ route('projects.show', $project->project_id) }}"
                                       class="btn btn-sm btn-info btn-custom">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-check-circle" style="font-size: 48px; color: #28a745;"></i>
                                    <p class="text-muted mt-2">Tidak ada project yang memerlukan perhatian</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
