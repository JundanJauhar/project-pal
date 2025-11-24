@extends('layouts.app')

@section('title', 'Approval Pengadaan - Sekretaris Direksi')

@push('styles')
<style>
    .stat-card {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fff;
        padding: 22px 20px;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
        transition: 0.3s;
        height: 130px;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .stat-title {
        font-size: 15px;
        font-weight: 600;
        color: #555;
    }

    .stat-value {
        font-size: 34px;
        font-weight: 700;
        margin-top: 8px;
        color: #222;
    }

    .stat-icon i {
        font-size: 46px;
        opacity: 0.2;
    }

    .stat-total i {
        color: #0d6efd;
    }

    .stat-progress i {
        color: #ffc107;
    }

    .stat-success i {
        color: #198754;
    }

    .stat-rejected i {
        color: #dc3545;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stat-card {
            height: auto;
            padding: 18px;
            margin-bottom: 14px;
        }

        .stat-value {
            font-size: 28px;
        }

        .stat-icon i {
            font-size: 38px;
        }
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
    <div class="row g-3">
        <div class="col-md-3 col-6">
            <div class="stat-card stat-total">
                <div>
                    <div class="stat-title">Total Project</div>
                    <div class="stat-value">{{ $total ?? $totalProcurements ?? 0 }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-file-earmark-text"></i></div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="stat-card stat-progress">
                <div>
                    <div class="stat-title">Menunggu Approval</div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="stat-card stat-success">
                <div>
                    <div class="stat-title">Disetujui</div>
                    <div class="stat-value">{{ $stats['approved'] }}</div>
                </div>
                <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
            </div>
        </div>

        <div class="col-md-3 col-6">
            <div class="stat-card stat-rejected">
                <div>
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
                            <td><strong>{{ $procurement->code_procurement ?? '-' }}</strong></td>
                            <td>{{ Str::limit($procurement->name_procurement, 40) }}</td>
                            <td>{{ $procurement->project->ownerDivision->division_name ?? '-' }}</td>
                            <td>{{$procurement->requestProcurements->first()?->vendor?->name_vendor ?? '-' }}</td>
                            <td>{{ $procurement->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('sekdir.approval-detail', $procurement->project_id) }}">

                                    @php
                                    // Ambil auto status dan checkpoint dari procurement
                                    $status = $procurement->auto_status;

                                    // Warna badge mengikuti rule auto_status
                                    $badgeColor = match($status) {
                                    'completed' => 'success', // hijau
                                    'in_progress' => 'warning', // kuning
                                    default => 'danger'
                                    };

                                    // Teks badge mengikuti rule: in_progress -> Proses Pengesahan Kontrak
                                    $statusText = match($status) {
                                    'completed' => 'Selesai',
                                    'in_progress' => 'Proses Pengesahan Kontrak',
                                    default => ucfirst($status)
                                    };
                                    @endphp


                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ $statusText }}
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