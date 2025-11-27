@extends('layouts.app')

@section('title', 'Approval Pengadaan - Sekretaris Direksi')

@push('styles')
<style>
    /* Stats Card Styling */
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

    .stat-icon {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 50px;
        opacity: 0.2;
    }

    /* Priority Badge */
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

    /* Dashboard Table Styling (dari dashboard blade) */
    .dashboard-table-wrapper {
        padding: 25px;
        border-radius: 14px;
        border: 1px solid #E0E0E0;
        margin-top: 20px;
        background: #fff;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-table-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dashboard-table thead th {
        padding: 14px 6px;
        border-bottom: 2px solid #C9C9C9;
        font-size: 14px;
        text-transform: uppercase;
        color: #555;
        text-align: center;
        vertical-align: middle;
    }

    .dashboard-table tbody tr:hover {
        background: #EFEFEF;
    }

    .dashboard-table tbody td {
        padding: 14px 6px;
        border-bottom: 1px solid #DFDFDF;
        font-size: 15px;
        color: #333;
        text-align: center;
    }

    /* Button Styling */
    .btn-custom {
        background: #003d82;
        border-color: #003d82;
        color: #fff;
    }

    .btn-custom:hover {
        background: #002e5c;
        border-color: #002e5c;
        color: #fff;
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
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2>Approval Pengadaan</h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card stat-total">
                <div>
                    <div class="stat-title">Total Project</div>
                    <div class="stat-value">{{ $totalProcurements }}</div>
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

    <!-- Projects Table with Dashboard Style -->
    <div class="dashboard-table-wrapper">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Project</th>
                        <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Division</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Dibuat</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procurements as $procurement)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>{{ $procurement->code_procurement ?? '-' }}</strong></td>
                        <td style="padding: 12px 8px; text-align: left; color: #000;">{{ Str::limit($procurement->name_procurement, 40) }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $procurement->project->ownerDivision->division_name ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $procurement->requestProcurements->first()?->vendor?->name_vendor ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">{{ $procurement->created_at->format('d/m/Y') }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <a href="{{ route('sekdir.approval-detail', $procurement->procurement_id) }}">
                                @php
                                // 1. Ambil progress khusus checkpoint_id 5 (Pengesahan Kontrak)
                                $contractProgress = $procurement->procurementProgress
                                ->firstWhere('checkpoint_id', 5);

                                $statusText = 'Menunggu Proses Dokumen';
                                $badgeColor = '#BD0000'; // Default: Merah (Menunggu)

                                if ($contractProgress) {
                                    if ($contractProgress->status === 'completed') {
                                        $statusText = 'Dokumen Selesai (Disetujui)';
                                        $badgeColor = '#28AC00'; // Hijau (Selesai)
                                    } elseif ($contractProgress->status === 'rejected') {
                                        $statusText = 'Ditolak Sekretaris';
                                        $badgeColor = '#dc3545'; // Merah gelap (Ditolak)
                                    } else {
                                        // Status 'in_progress' atau status lain yang masih aktif
                                        $statusText = $contractProgress->checkpoint->point_name ?? 'Dalam Proses';
                                        $badgeColor = '#ECAD02'; // Kuning (Proses)
                                    }
                                }
                                @endphp

                                <span class="badge" style="background-color: {{ $badgeColor }};">
                                    {{ $statusText }}
                                </span>
                            </a>
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <a href="{{ route('sekdir.approval-detail', $procurement->procurement_id) }}"
                                class="btn btn-sm btn-primary btn-custom"
                                wire:navigate>
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5" style="padding: 40px 12px !important;">
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

@endsection

@push('scripts')
<script>
    console.log('Sekdir Approval Page Loaded');
</script>
@endpush