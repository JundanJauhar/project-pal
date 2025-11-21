@extends('layouts.app')

@section('title', 'Approval Pengadaan - Sekretaris Direksi')

@push('styles')
<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        color: white;
    }
    .stat-total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .stat-progress {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .stat-rejected {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    .stat-content {
        position: relative;
        z-index: 1;
    }
    .stat-title {
        font-size: 14px;
        margin-bottom: 10px;
        opacity: 0.9;
    }
    .stat-value {
        font-size: 32px;
        font-weight: bold;
    }
    .stat-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 50px;
        opacity: 0.2;
    }
    .badge-priority {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-priority.badge-tinggi {
        color: #BD0000;
        font-size: 14px;
    }
    .badge-priority.badge-sedang {
        color: #FFBB00;
        font-size: 14px;
    }
    .badge-priority.badge-rendah {
        color: #6f6f6f;
        font-size: 14px;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <h2 class="mb-4">Approval Pengadaan</h2>

    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card stat-total">
                <div class="stat-content">
                    <div class="stat-title">Total Project</div>
                    <div class="stat-value">{{ $stats['total'] }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-progress">
                <div class="stat-content">
                    <div class="stat-title">Menunggu Approval</div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-success">
                <div class="stat-content">
                    <div class="stat-title">Disetujui</div>
                    <div class="stat-value">{{ $stats['approved'] }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-rejected">
                <div class="stat-content">
                    <div class="stat-title">Ditolak</div>
                    <div class="stat-value">{{ $stats['rejected'] }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Daftar Project yang Perlu Approval</h5>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode Project</th>
                            <th>Nama Project</th>
                            <th>Division</th>
                            <th>Vendor</th>
                            <th>Tanggal Dibuat</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($procurements as $procurement)
                        <tr>
                            <td><strong>{{ $procurement->project_code ?? '-' }}</strong></td>
                            <td>{{ Str::limit($procurement->project_name, 40) }}</td>
                            <td>{{ $procurement->ownerDivision->division_name ?? '-' }}</td>
                            <td>{{ $procurement->contracts->first()?->vendor?->name_vendor ?? '-' }}</td>
                            <td>{{ $procurement->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('sekdir.approval-detail', $procurement->project_id) }}">

                                    @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'persetujuan_sekretaris' => 'warning',
                                        'Persetujuan_dokumen' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                    ];
                                    $badgeColor = $statusColors[$procurement->status_project] ?? 'secondary';
                                    $statusText = [
                                        'persetujuan_sekretaris' => 'Verifikasi Dokumen',
                                        'Persetujuan_dokumen' => 'Dokumen Disetujui',
                                        'rejected' => 'Dokumen Ditolak',
                                    ];
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ $statusText[$procurement->status_project] ?? ucfirst($procurement->status_project) }}
                                    </span>
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('sekdir.approval-detail', $procurement->project_id) }}"
                                   class="btn btn-sm btn-primary"
                                   wire:navigate>
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-3 mb-0 text-muted">Tidak ada project yang perlu approval</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    console.log('Sekdir Approval Page Loaded');
</script>
@endpush
