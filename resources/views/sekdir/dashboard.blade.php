@extends('layouts.app')

@section('title', 'Dashboard - PT PAL Indonesia')

@push('styles')
<style>
    /* Override stat card border to be more visible */
    .stat-total::before {
        width: 6px !important;
        background: #4F9DFD !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        border-radius: 18px 0 0 18px !important;
    }

    .stat-progress::before {
        width: 6px !important;
        background: #ECAD02 !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        border-radius: 18px 0 0 18px !important;
    }

    .stat-success::before {
        width: 6px !important;
        background: #28AC00 !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        border-radius: 18px 0 0 18px !important;
    }

    .stat-rejected::before {
        width: 6px !important;
        background: #F10303 !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        border-radius: 18px 0 0 18px !important;
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
        font-size: 16px;
    }

    .badge-priority.badge-sedang {
        color: #FFBB00;
        font-size: 16px;
    }

    .badge-priority.badge-rendah {
        color: #6f6f6f;
        font-size: 16px;
    }

    .dashboard-table-wrapper {
        padding: 25px;
        border-radius: 14px;
        margin-top: 20px;
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.12);
        background: #FFFFFF;
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    /* Title (mirip Payment) */
    .dashboard-table-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Filter wrapper */
    .filters-wrap {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Search box styling */
    .dashboard-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #F0F0F0;
        border-radius: 25px;
        padding: 6px 12px;
        width: 240px;
        border: 1px solid #ddd;
        font-size: 14px;
    }

    .dashboard-search-box input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 14px;
    }

    .dashboard-search-box i {
        font-size: 14px;
        color: #777;
    }

    /* Filter selects */
    .filter-select {
        background: #fff;
        border: 1px solid #ddd;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 14px;
    }

    .card-header {
        background-color: #ffffff;
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

    .tambah .btn {
        background: #003d82;
        border-color: #003d82;
    }

    .search .card-custom {
        border: 1px solid #E0E0E0;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .tambah .btn:hover {
        background: #002e5c;
        border-color: #002e5c;
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
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-3">
            <div class="stat-card stat-total">
                <div class="stat-content">
                    <div class="stat-title">Total Pengadaan</div>
                    <div class="stat-value">{{ $stats['total_pengadaan'] }}</div>
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
                    <div class="stat-title">Sedang Proses</div>
                    <div class="stat-value">{{ $stats['sedang_proses'] }}</div>
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
                    <div class="stat-title">Selesai</div>
                    <div class="stat-value">{{ $stats['selesai'] }}</div>
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
                    <div class="stat-value">{{ $stats['ditolak'] }}</div>
                </div>
                <div class="stat-icon">
                    <div class="stat-icon-inner">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Form Section -->
    <div class="search row mb-4">
        <div class="col-12">
            <div class="card card-custom">
                <div class="card-body">
                    <form id="filter-form" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Cari Pengadaan..." value="">
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-2">
                            <select class="form-select" name="project">
                                <option value="">Semua Project</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->project_code }}">{{ $project->project_code }}</option>
                                @endforeach
                                <!-- <option value="W000301">W000301</option>
                                <option value="W000302">W000302</option>
                                <option value="W000303">W000303</option>
                                <option value="W000304">W000304</option>
                                <option value="W000305">W000305</option>
                                <option value="W000306">W000306</option>
                                <option value="W000307">W000307</option> -->
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="checkpoint">
                                <option value="">Semua Checkpoint</option>
                                @foreach($checkpoints as $checkpoint)
                                <option value="{{ $checkpoint->point_name }}">{{ $checkpoint->point_name }}</option>
                                @endforeach
                                <!-- <option value="Penawaran Permintaan">Penawaran Permintaan</option>
                                <option value="Evatek">Evatek</option>
                                <option value="Negosiasi">Negosiasi</option>
                                <option value="Usulan Pengadaan / OC">Usulan Pengadaan / OC</option>
                                <option value="Pengesahan Kontrak">Pengesahan Kontrak</option>
                                <option value="Pengiriman Material">Pengiriman Material</option>
                                <option value="Pembayaran DP">Pembayaran DP</option>
                                <option value="Proses Importasi / Produksi">Proses Importasi / Produksi</option>
                                <option value="Kedatangan Material">Kedatangan Material</option>
                                <option value="Serah Terima Dokumen">Serah Terima Dokumen</option>
                                <option value="Inspeksi Barang">Inspeksi Barang</option>
                                <option value="Berita Acara / NCR">Berita Acara / NCR</option>
                                <option value="Verifikasi Dokumen">Verifikasi Dokumen</option>
                                <option value="Pembayaran">Pembayaran</option>
                                <option value="completed">Selesai</option>
                                <option value="cancelled">Dibatalkan</option> -->
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="priority">
                                <option value="">Semua Prioritas</option>
                                @foreach($priority as $priorityOption)
                                <option value="{{ $priorityOption -> priority }}">{{$priorityOption -> priority }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div style="position: relative; height: 600px; margin-top: 15px; overflow: hidden; border-radius: 8px;">
         <iframe 
                id="grafanaChart"
                src="http://localhost:3000/d/juxnfgn/project-pal-grafik?orgId=1&from=now-1y&to=now&kiosk" 
                width="100%" 
                height="100%" 
                frameborder="0"
                style="border: none; display: block;"
                title="Grafana Dashboard">
            </iframe>
    </div>

    {{-- ===== TABLE (dengan gaya mirip Payment) ===== --}}
    <div class="dashboard-table-wrapper">

        <div class="dashboard-table-title">
            <span>Daftar Pengadaan</span>
        </div>

        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">NO</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Project</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nama Pengadaan</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Mulai</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Selesai</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="procurements-tbody">
                    @forelse($procurements as $procurement)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center;">
                            <strong>{{ ($procurements->currentPage() - 1) * $procurements->perPage() + $loop->iteration }}</strong>
                        </td>
                        <td style="padding: 12px 8px; text-align: center;"><strong>{{ $procurement->project->project_code ?? '-' }}</strong></td>
                        <td style="padding: 12px 8px; text-align: center;"><strong>{{ $procurement->code_procurement }}</strong></td>
                        <td style="padding: 12px 8px;">{{ Str::limit($procurement->name_procurement, 40) }}</td>
                        <td style="padding: 12px 8px; text-align: center;">{{ $procurement->department->department_name ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center;">{{ $procurement->start_date->format('d/m/Y') }}</td>
                        <td style="padding: 12px 8px; text-align: center;">{{ $procurement->end_date->format('d/m/Y') }}</td>
                        <td style="padding: 12px 8px; text-align: center;">{{ $procurement->requestProcurements->first()?->vendor->name_vendor ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <span class="badge-priority badge-{{ strtolower($procurement->priority) }}">
                                {{ strtoupper($procurement->priority) }}
                            </span>
                        </td>
                        <td style="padding: 12px 8px; text-align: center;">
                            @php
                            $status = $procurement->status_procurement;
                            $currentCheckpoint = $procurement->current_checkpoint;

                            // Determine badge color and text based on status
                            if ($status === 'completed') {
                            $badgeColor = '#28AC00';
                            $text = 'Selesai';
                            } elseif ($status === 'cancelled') {
                            $badgeColor = '#BD0000';
                            $text = 'Dibatalkan';
                            } elseif ($status === 'in_progress') {
                            $badgeColor = '#ECAD02';
                            // Show current checkpoint name, fallback to 'Sedang Proses'
                            $text = $currentCheckpoint ?: 'Sedang Proses';
                            } else {
                            $badgeColor = '#555';
                            $text = $status ?: 'N/A';
                            }
                            @endphp

                            <span class="badge"
                                style="background-color: {{ $badgeColor }};
                                    color:white;
                                    padding:6px 12px;
                                    font-weight:600;
                                    border-radius:6px;">
                                {{ $text }}
                            </span>
                        </td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <a href="{{ route('procurements.show', $procurement->procurement_id) }}" class="btn btn-sm btn-primary btn-custom" wire:navigate>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size:40px; color:#bbb;"></i>
                            <p class="text-muted mt-2">Tidak ada data pengadaan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <div id="procurements-pagination">
                {{ $procurements->links() }}
            </div>
        </div>

    </div>
    <!-- Chart Section Project Statistics -->
    <!-- Chart Section Project Statistics -->
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

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                presetRange.value = 'last-6-months';
                startDate.value = '';
                endDate.value = '';
                updateGrafanaTime('now-6M', 'now');
            });
        }
    });


    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        const checkpointSelect = document.querySelector('select[name="checkpoint"]');
        const prioritySelect = document.querySelector('select[name="priority"]');
        const projectSelect = document.querySelector('select[name="project"]');
        const tbody = document.getElementById('procurements-tbody');
        const paginationWrap = document.getElementById('procurements-pagination');

        if (!searchInput || !checkpointSelect || !prioritySelect || !tbody || !paginationWrap) {
            console.error('Missing required elements');
            return;
        }

        let currentPage = 1;
        let lastPagination = null;

        function getStatusBadge(status, checkpoint) {
            let badgeColor, text;

            if (status === 'completed') {
                badgeColor = '#28AC00';
                text = 'Selesai';
            } else if (status === 'cancelled') {
                badgeColor = '#BD0000';
                text = 'Dibatalkan';
            } else if (status === 'in_progress') {
                badgeColor = '#ECAD02';
                text = checkpoint || 'Sedang Proses';
            } else {
                badgeColor = '#555';
                text = status || 'N/A';
            }

            return `<span class="badge" style="background-color: ${badgeColor}; color:white; padding:6px 12px; font-weight:600; border-radius:6px;">
            ${text}
        </span>`;
        }

        function renderRows(items) {
            if (!Array.isArray(items) || items.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 40px; color: #bbb;"></i>
                        <p class="text-muted mt-2">Tidak ada data pengadaan</p>
                    </td>
                </tr>`;
                paginationWrap.innerHTML = "";
                return;
            }

            tbody.innerHTML = items.map(p => {
                const priorityClass = p.priority?.toLowerCase() || '';
                const priorityText = p.priority?.toUpperCase() || '-';

                return `
            <tr>
                <td style="padding: 12px 8px; text-align:center;"><strong>${p.project_code}</strong></td>
                <td style="padding: 12px 8px; text-align:center"><strong>${p.code_procurement}</strong></td>
                <td style="padding: 12px 8px;">${p.name_procurement?.substring(0, 40) || '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">${p.department_name || '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">${p.start_date || '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">${p.end_date || '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">${p.vendor_name || '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">
                    <span class="badge-priority badge-${priorityClass}">
                        ${priorityText}
                    </span>
                </td>
                <td style="padding: 12px 8px; text-align: center;">
                    ${getStatusBadge(p.status_procurement, p.current_checkpoint)}
                </td>
                <td style="padding: 12px 8px; text-align: center;">
                    <a href="/procurements/${p.procurement_id}" class="btn btn-sm btn-primary btn-custom">
                        Detail
                    </a>
                </td>
            </tr>`;
            }).join("");

            renderPagination();
        }

        function renderPagination() {
            if (!lastPagination) {
                paginationWrap.innerHTML = '';
                return;
            }

            const p = lastPagination;
            let html = `<nav><ul class="pagination">`;

            html += p.current_page > 1 ?
                `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${p.current_page - 1})">← Sebelumnya</a></li>` :
                `<li class="page-item disabled"><span class="page-link">← Sebelumnya</span></li>`;

            for (let i = 1; i <= p.last_page; i++) {
                html += i === p.current_page ?
                    `<li class="page-item active"><span class="page-link">${i}</span></li>` :
                    `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${i})">${i}</a></li>`;
            }

            html += p.has_more ?
                `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${p.current_page + 1})">Berikutnya →</a></li>` :
                `<li class="page-item disabled"><span class="page-link">Berikutnya →</span></li>`;

            html += `</ul></nav>`;
            paginationWrap.innerHTML = html;
        }

        window.goToPage = function(page) {
            currentPage = page;
            fetchProcurements();
        };

        function fetchProcurements() {
            const q = encodeURIComponent(searchInput.value.trim());
            const checkpoint = encodeURIComponent(checkpointSelect.value);
            const priority = encodeURIComponent(prioritySelect.value);
            const project = encodeURIComponent(projectSelect.value);

            const url = `{{ route('dashboard.search') }}?q=${q}&checkpoint=${checkpoint}&priority=${priority}&project=${project}&page=${currentPage}`;
            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(res => {
                    lastPagination = res.pagination;
                    renderRows(res.data);
                })
                .catch(err => {
                    console.error("Search error:", err);
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="text-center py-5">
                            <i class="bi bi-exclamation-circle" style="font-size: 48px; color: #f00;"></i>
                            <p class="text-danger mt-2">Terjadi kesalahan: ${err.message}</p>
                        </td>
                    </tr>`;
                });
        }

        const debouncedFetch = debounce(() => {
            currentPage = 1;
            fetchProcurements();
        }, 300);

        searchInput.addEventListener('input', debouncedFetch);
        checkpointSelect.addEventListener('change', debouncedFetch);
        prioritySelect.addEventListener('change', debouncedFetch);
        projectSelect.addEventListener('change', debouncedFetch);
    });
</script>
@endpush