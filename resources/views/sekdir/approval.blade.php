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
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
    }

    .stat-title {
        font-size: 15px;
        font-weight: 600;
        color: #fff;
    }

    .stat-value {
        font-size: 34px;
        font-weight: 700;
        margin-top: 8px;
        color: #fff;
    }

    .stat-icon i {
        font-size: 46px;
        opacity: 0.2;
        color: #fff;
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
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card stat-total">
                <div class="stat-content">
                    <div class="stat-title">Total Pengadaan</div>
                    <div class="stat-value">{{ $totalProcurements }}</div>
                </div>
                <div class="stat-icon">
                    <div class="stat-icon-inner">
                        <i class="bi bi-list-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-progress">
                <div class="stat-content">
                    <div class="stat-title">Menunggu Approval</div>
                    <div class="stat-value">{{ $stats['pending'] }}</div>
                </div>
                <div class="stat-icon">
                    <div class="stat-icon-inner">
                        <i class="bi bi-hourglass-split"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-success">
                <div class="stat-content">
                    <div class="stat-title">Disetujui</div>
                    <div class="stat-value">{{ $stats['approved'] }}</div>
                </div>
                <div class="stat-icon">
                    <div class="stat-icon-inner">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-rejected">
                <div class="stat-content">
                    <div class="stat-title">Ditolak</div>
                    <div class="stat-value">{{ $stats['rejected'] }}</div>
                </div>
                <div class="stat-icon">
                    <div class="stat-icon-inner">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== TABLE 1: Menunggu Approval (Pending) ===== --}}
    <div class="dashboard-table-wrapper">

        <div class="dashboard-table-title">
            <span>📋 Pengadaan Menunggu Approval</span>
            <span class="badge bg-warning text-dark" style="font-size: 14px;">{{ $stats['pending'] }} Menunggu</span>
        </div>

        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Division</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Dibuat</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingProcurements as $procurement)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <strong>{{ ($pendingProcurements->currentPage() - 1) * $pendingProcurements->perPage() + $loop->iteration }}</strong>
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>{{ $procurement->code_procurement ?? '-' }}</strong></td>
                        <td style="padding: 12px 8px; text-align: left; color: #000;">{{ Str::limit($procurement->name_procurement, 40) }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $procurement->project->ownerDivision->division_name ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $procurement->requestProcurements->first()?->vendor?->name_vendor ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">{{ $procurement->created_at->format('d/m/Y') }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <span class="badge" style="background-color: #ECAD02; color:white; padding:6px 12px; font-weight:600; border-radius:6px;">
                                Menunggu Pengesahan
                            </span>
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
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size:40px; color:#bbb;"></i>
                            <p class="text-muted mt-2">Tidak ada pengadaan yang menunggu approval</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $pendingProcurements->links() }}
        </div>

    </div>

    {{-- ===== TABLE 2: Sudah Disetujui (Approved) ===== --}}
    <div class="dashboard-table-wrapper mt-4">

        <div class="dashboard-table-title">
            <span>✅ Pengadaan yang Sudah Disetujui</span>
            <span class="badge bg-success" style="font-size: 14px;">{{ $stats['approved'] }} Disetujui</span>
        </div>

        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Division</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Disetujui</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($approvedProcurements as $procurement)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <strong>{{ ($approvedProcurements->currentPage() - 1) * $approvedProcurements->perPage() + $loop->iteration }}</strong>
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>{{ $procurement->code_procurement ?? '-' }}</strong></td>
                        <td style="padding: 12px 8px; text-align: left; color: #000;">{{ Str::limit($procurement->name_procurement, 40) }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $procurement->project->ownerDivision->division_name ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $procurement->requestProcurements->first()?->vendor?->name_vendor ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            @php
                                $cp4 = $procurement->procurementProgress->firstWhere('checkpoint_id', 4);
                            @endphp
                            {{ $cp4 && $cp4->end_date ? $cp4->end_date->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <span class="badge" style="background-color: #28AC00; color:white; padding:6px 12px; font-weight:600; border-radius:6px;">
                                <i class="bi bi-check-circle"></i> Kontrak Disahkan
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size:40px; color:#bbb;"></i>
                            <p class="text-muted mt-2">Belum ada pengadaan yang disetujui</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $approvedProcurements->links() }}
        </div>

    </div>

    {{-- ===== TABLE 3: Ditolak (Rejected) - Optional ===== --}}
    @if($stats['rejected'] > 0)
    <div class="dashboard-table-wrapper mt-4">

        <div class="dashboard-table-title">
            <span>❌ Pengadaan yang Ditolak</span>
            <span class="badge bg-danger" style="font-size: 14px;">{{ $stats['rejected'] }} Ditolak</span>
        </div>

        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Division</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Ditolak</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rejectedProcurements as $procurement)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <strong>{{ ($rejectedProcurements->currentPage() - 1) * $rejectedProcurements->perPage() + $loop->iteration }}</strong>
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>{{ $procurement->code_procurement ?? '-' }}</strong></td>
                        <td style="padding: 12px 8px; text-align: left; color: #000;">{{ Str::limit($procurement->name_procurement, 40) }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $procurement->project->ownerDivision->division_name ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $procurement->requestProcurements->first()?->vendor?->name_vendor ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            @php
                                $cp4 = $procurement->procurementProgress->firstWhere('checkpoint_id', 4);
                            @endphp
                            {{ $cp4 && $cp4->end_date ? $cp4->end_date->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <span class="badge" style="background-color: #dc3545; color:white; padding:6px 12px; font-weight:600; border-radius:6px;">
                                <i class="bi bi-x-circle"></i> Kontrak Ditolak
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size:40px; color:#bbb;"></i>
                            <p class="text-muted mt-2">Tidak ada pengadaan yang ditolak</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $rejectedProcurements->links() }}
        </div>

    </div>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>

// Function to update Grafana time range
function updateGrafanaTime(from, to) {
    const iframe = document.getElementById('grafanaFrame');
    const baseUrl = 'https://jundan87.grafana.net/d-solo/jubx2zx/project-pal';
    iframe.src = `${baseUrl}?orgId=1&from=${from}&to=${to}&panelId=2&theme=light&refresh=5s`;
}

// Event listeners for date inputs
document.addEventListener('DOMContentLoaded', function() {
    const presetRange = document.getElementById('presetRange');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const filterBtn = document.getElementById('filterBtn');
    const resetBtn = document.getElementById('resetBtn');

    if (presetRange) {
        presetRange.addEventListener('change', function() {
            const preset = this.value;
            let from, to;

            switch(preset) {
                case 'last-30-days':
                    from = 'now-30d';
                    to = 'now';
                    break;
                case 'last-60-days':
                    from = 'now-60d';
                    to = 'now';
                    break;
                case 'last-90-days':
                    from = 'now-90d';
                    to = 'now';
                    break;
                case 'last-6-months':
                    from = 'now-6M';
                    to = 'now';
                    break;
                case 'this-month':
                    from = 'now/M';
                    to = 'now/M';
                    break;
                case 'last-month':
                    from = 'now-1M/M';
                    to = 'now-1M/M';
                    break;
                case 'this-year':
                    from = 'now/y';
                    to = 'now';
                    break;
                default:
                    return;
            }

            updateGrafanaTime(from, to);
        });
    }

    if (filterBtn) {
        filterBtn.addEventListener('click', function() {
            const start = startDate.value;
            const end = endDate.value;

            if (start && end) {
                const fromTimestamp = new Date(start).getTime();
                const toTimestamp = new Date(end).getTime();
                
                updateGrafanaTime(fromTimestamp, toTimestamp);
            } else {
                alert('Pilih tanggal mulai dan akhir');
            }
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            presetRange.value = 'last-6-months';
            startDate.value = '';
            endDate.value = '';
            updateGrafanaTime('now-6M', 'now');
        });
    }
});

// ============ PROCUREMENT CHART ============
document.addEventListener('DOMContentLoaded', function() {
    const canvasProcurement = document.getElementById('procurementChart');
    const startDateInputProcurement = document.getElementById('startDateProcurement');
    const endDateInputProcurement = document.getElementById('endDateProcurement');
    const presetRangeProcurement = document.getElementById('presetRangeProcurement');
    const filterBtnProcurement = document.getElementById('filterBtnProcurement');
    const resetBtnProcurement = document.getElementById('resetBtnProcurement');
    const chartInfoProcurement = document.getElementById('chartInfoProcurement');

    let chartInstanceProcurement = null;

    function formatDateProcurement(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function getPresetDatesProcurement(preset) {
        const today = new Date();
        let startDate, endDate = formatDateProcurement(today);

        switch (preset) {
            case 'last-30-days':
                const d30 = new Date();
                d30.setDate(d30.getDate() - 30);
                startDate = formatDateProcurement(d30);
                break;
            case 'last-60-days':
                const d60 = new Date();
                d60.setDate(d60.getDate() - 60);
                startDate = formatDateProcurement(d60);
                break;
            case 'last-90-days':
                const d90 = new Date();
                d90.setDate(d90.getDate() - 90);
                startDate = formatDateProcurement(d90);
                break;
            case 'last-6-months':
                const d6m = new Date();
                d6m.setMonth(d6m.getMonth() - 6);
                startDate = formatDateProcurement(d6m);
                break;
            case 'this-month':
                startDate = formatDateProcurement(new Date(today.getFullYear(), today.getMonth(), 1));
                endDate = formatDateProcurement(new Date(today.getFullYear(), today.getMonth() + 1, 0));
                break;
            case 'last-month':
                startDate = formatDateProcurement(new Date(today.getFullYear(), today.getMonth() - 1, 1));
                endDate = formatDateProcurement(new Date(today.getFullYear(), today.getMonth(), 0));
                break;
            case 'this-year':
                startDate = formatDateProcurement(new Date(today.getFullYear(), 0, 1));
                endDate = formatDateProcurement(new Date(today.getFullYear(), 11, 31));
                break;
            default:
                return null;
        }

        return { startDate, endDate };
    }

    function formatDisplayDateProcurement(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }

    function loadProcurementChartData(startDate = null, endDate = null) {
        let url = '/api/procurement-stats';
        const params = new URLSearchParams();

        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);

        if (params.toString()) url += '?' + params.toString();

        if (startDate && endDate) {
            chartInfoProcurement.textContent = `Menampilkan data dari ${formatDisplayDateProcurement(startDate)} - ${formatDisplayDateProcurement(endDate)}`;
        } else if (startDate) {
            chartInfoProcurement.textContent = `Menampilkan data dari ${formatDisplayDateProcurement(startDate)}`;
        } else if (endDate) {
            chartInfoProcurement.textContent = `Menampilkan data sampai ${formatDisplayDateProcurement(endDate)}`;
        } else {
            chartInfoProcurement.textContent = 'Menampilkan data 6 bulan terakhir';
        }

        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                if (data.length === 0) {
                    chartInfoProcurement.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-circle"></i> Tidak ada data untuk rentang tanggal yang dipilih</span>';
                    if (chartInstanceProcurement) {
                        chartInstanceProcurement.destroy();
                        chartInstanceProcurement = null;
                    }
                    return;
                }

                const months = data.map(item => item.month);
                const totalProcurements = data.map(item => item.total_procurements);
                const completed = data.map(item => item.completed);
                const inProgress = data.map(item => item.in_progress);
                const cancelled = data.map(item => item.cancelled);

                if (chartInstanceProcurement) chartInstanceProcurement.destroy();

                chartInstanceProcurement = new Chart(canvasProcurement, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [
                            {
                                label: 'Total Pengadaan',
                                data: totalProcurements,
                                backgroundColor: 'rgba(79, 157, 253, 0.2)',
                                borderColor: 'rgba(79, 157, 253, 1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgba(79, 157, 253, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5
                            },
                            {
                                label: 'Selesai',
                                data: completed,
                                backgroundColor: 'rgba(40, 172, 0, 0.2)',
                                borderColor: 'rgba(40, 172, 0, 1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgba(40, 172, 0, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5
                            },
                            {
                                label: 'Sedang Proses',
                                data: inProgress,
                                backgroundColor: 'rgba(236, 173, 2, 0.2)',
                                borderColor: 'rgba(236, 173, 2, 1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgba(236, 173, 2, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5
                            },
                            {
                                label: 'Dibatalkan',
                                data: cancelled,
                                backgroundColor: 'rgba(241, 3, 3, 0.2)',
                                borderColor: 'rgba(241, 3, 3, 1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgba(241, 3, 3, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    padding: 15,
                                    font: { size: 13, weight: '500' },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0, 0, 0, 0.05)' },
                                ticks: { stepSize: 1 }
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error fetching procurement chart data:', error);
                chartInfoProcurement.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Gagal memuat data chart</span>';
                if (chartInstanceProcurement) {
                    chartInstanceProcurement.destroy();
                    chartInstanceProcurement = null;
                }
            });
    }

    if (presetRangeProcurement) {
        presetRangeProcurement.addEventListener('change', function() {
            const preset = this.value;
            if (!preset) {
                startDateInputProcurement.value = '';
                endDateInputProcurement.value = '';
                return;
            }
            const dates = getPresetDatesProcurement(preset);
            if (dates) {
                startDateInputProcurement.value = dates.startDate;
                endDateInputProcurement.value = dates.endDate;
                loadProcurementChartData(dates.startDate, dates.endDate);
            }
        });
    }

    [startDateInputProcurement, endDateInputProcurement].forEach(input => {
        if (input) {
            input.addEventListener('change', function() {
                presetRangeProcurement.value = '';
            });
        }
    });

    if (filterBtnProcurement) {
        filterBtnProcurement.addEventListener('click', function() {
            const startDate = startDateInputProcurement.value;
            const endDate = endDateInputProcurement.value;

            if (!startDate && !endDate) {
                alert('Pilih minimal satu tanggal atau pilih preset range');
                return;
            }
            if (startDate && endDate && startDate > endDate) {
                alert('Tanggal mulai tidak boleh lebih besar dari tanggal akhir');
                return;
            }
            loadProcurementChartData(startDate, endDate);
        });
    }

    if (resetBtnProcurement) {
        resetBtnProcurement.addEventListener('click', function() {
            startDateInputProcurement.value = '';
            endDateInputProcurement.value = '';
            presetRangeProcurement.value = 'last-6-months';
            const defaultPreset = getPresetDatesProcurement('last-6-months');
            if (defaultPreset) {
                startDateInputProcurement.value = defaultPreset.startDate;
                endDateInputProcurement.value = defaultPreset.endDate;
                loadProcurementChartData(defaultPreset.startDate, defaultPreset.endDate);
            }
        });
    }

    if (canvasProcurement) {
        const defaultPreset = getPresetDatesProcurement('last-6-months');
        if (defaultPreset) {
            startDateInputProcurement.value = defaultPreset.startDate;
            endDateInputProcurement.value = defaultPreset.endDate;
            loadProcurementChartData(defaultPreset.startDate, defaultPreset.endDate);
        }
    }
});

</script>
@endpush