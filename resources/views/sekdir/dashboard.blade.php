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

    .dashboard-table-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .filters-wrap {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

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

    /* =========================
   GRAFANA SLIDER (REFINED)
========================= */

    .grafana-slider-card {
        position: relative;
        background: #ffffff;
        border-radius: 18px;
        padding: 26px 26px 30px;
        box-shadow: 0 12px 26px rgba(0,0,0,.18);
        margin: 60px 0; 
    }

    .grafana-slider-title {
        font-size: 24px;
        font-weight: 700;
        text-align: center;        
        margin-bottom: 18px;
    }

    .grafana-slider-wrapper {
        overflow: hidden;
        position: relative;
    }

    .grafana-slider-track {
        display: flex;
        transition: transform 0.55s ease-in-out;
    }

    .grafana-slide {
        min-width: 100%;
    }

    /* CARD LEBIH TINGGI */
    .grafana-slide iframe {
        width: 100%;
        height: 760px;           
        border: none;
        background: #0b0f14;
        border-radius: 12px;
    }

    /* Navigation arrows */
    .grafana-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        background: rgba(0,0,0,.8);
        color: #fff;
        border: none;
        border-radius: 50%;
        font-size: 22px;
        cursor: pointer;
        z-index: 20;
    }

    .grafana-nav:hover {
        background: rgba(0,0,0,.95);
    }

    .grafana-nav.left {
        left: 14px;
    }

    .grafana-nav.right {
        right: 14px;
    }
</style>
@endpush

@section('content')

    <div class="grafana-slider-card">
    <div class="grafana-slider-title" id="grafanaTitle">
     Executive Procurement Overview
    </div>

    <button class="grafana-nav left" onclick="slideGrafana(-1)">
        <i class="bi bi-chevron-left"></i>
    </button>
    <button class="grafana-nav right" onclick="slideGrafana(1)">
        <i class="bi bi-chevron-right"></i>
    </button>

    <div class="grafana-slider-wrapper">
        <div class="grafana-slider-track" id="grafanaSlider">

            <div class="grafana-slide" data-title="Executive Procurement Overview">
                <iframe src="http://localhost:3000/d/ad56wzw/executive-procurement-overview?orgId=1&theme=dark&kiosk"></iframe>
            </div>

            <div class="grafana-slide" data-title="Procurement Lifecycle & Bottleneck">
                <iframe src="http://localhost:3000/d/adqkrmk/procurement-lifecycle-and-bottleneck?orgId=1&theme=dark&kiosk"></iframe>
            </div>

            <div class="grafana-slide" data-title="Project & Budget Performance">
                <iframe src="http://localhost:3000/d/adr4zqx/project-and-budget-performance?orgId=1&theme=dark&kiosk"></iframe>
            </div>

            <div class="grafana-slide" data-title="Risk, Compliance & Delay Monitoring">
                <iframe src="http://localhost:3000/d/adsf9dg/risk-compliance-and-delay-monitoring?orgId=1&theme=dark&kiosk"></iframe>
            </div>

            <div class="grafana-slide" data-title="Vendor & Contract Performance">
                <iframe src="http://localhost:3000/d/adkzcch/vendor-and-contract-performance?orgId=1&theme=dark&kiosk"></iframe>
            </div>

        </div>
    </div>
</div>


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
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" name="checkpoint">
                                <option value="">Semua Checkpoint</option>
                                @foreach($checkpoints as $checkpoint)
                                <option value="{{ $checkpoint->point_name }}">{{ $checkpoint->point_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Section -->
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

                            if ($status === 'completed') {
                            $badgeColor = '#28AC00';
                            $text = 'Selesai';
                            } elseif ($status === 'cancelled') {
                            $badgeColor = '#BD0000';
                            $text = 'Dibatalkan';
                            } elseif ($status === 'in_progress') {
                            $badgeColor = '#ECAD02';
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
                        <td colspan="11" class="text-center py-5">
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
</div>

@endsection

@push('scripts')
<!-- Load Lodash FIRST - VERY IMPORTANT -->
<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // ==========================================
    // GRAFANA IFRAME ERROR HANDLING
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        const grafanaIframe = document.getElementById('grafanaChart');
        const fallbackDiv = document.getElementById('grafana-fallback');

        // Check if iframe loaded successfully
        grafanaIframe.addEventListener('load', function() {
            console.log('✅ Grafana iframe loaded successfully');
            fallbackDiv.classList.remove('show');
        });

        // Handle iframe errors
        grafanaIframe.addEventListener('error', function() {
            console.error('❌ Grafana iframe failed to load');
            fallbackDiv.classList.add('show');
            grafanaIframe.style.display = 'none';
        });

        // Timeout check - if iframe doesn't load in 10 seconds, show fallback
        setTimeout(function() {
            try {
                const iframeDoc = grafanaIframe.contentDocument || grafanaIframe.contentWindow.document;
                if (!iframeDoc || iframeDoc.body.innerHTML === '') {
                    console.warn('⚠️ Grafana iframe appears empty, showing fallback');
                    fallbackDiv.classList.add('show');
                }
            } catch (e) {
                // Cross-origin error is expected and means iframe loaded from different origin (which is fine)
                console.log('📊 Grafana loaded from different origin (this is normal)');
            }
        }, 10000);
    });

    let currentGrafanaIndex = 0;

    function slideGrafana(direction) {
        const slider = document.getElementById('grafanaSlider');
        const slides = slider.children;
        const totalSlides = slides.length;
        const titleEl = document.getElementById('grafanaTitle');

        currentGrafanaIndex += direction;

        if (currentGrafanaIndex < 0) {
            currentGrafanaIndex = totalSlides - 1;
        } else if (currentGrafanaIndex >= totalSlides) {
            currentGrafanaIndex = 0;
        }

        slider.style.transform = `translateX(-${currentGrafanaIndex * 100}%)`;

        /* UPDATE TITLE SESUAI DASHBOARD */
        const activeSlide = slides[currentGrafanaIndex];
        const newTitle = activeSlide.getAttribute('data-title');
        titleEl.textContent = newTitle;
    }

    // ==========================================
    // PROCUREMENT TABLE SEARCH & FILTER
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Check if lodash is loaded
        if (typeof _ === 'undefined') {
            console.error('❌ Lodash is not loaded! Debounce will not work properly.');
            return;
        } else {
            console.log('✅ Lodash loaded successfully');
        }

        const searchInput = document.querySelector('input[name="search"]');
        const checkpointSelect = document.querySelector('select[name="checkpoint"]');
        const prioritySelect = document.querySelector('select[name="priority"]');
        const projectSelect = document.querySelector('select[name="project"]');
        const tbody = document.getElementById('procurements-tbody');
        const paginationWrap = document.getElementById('procurements-pagination');

        if (!searchInput || !checkpointSelect || !prioritySelect || !tbody || !paginationWrap) {
            console.error('❌ Missing required form elements');
            return;
        }

        let currentPage = 1;
        let lastPagination = null;

        // ==========================================
        // STATUS BADGE HELPER
        // ==========================================
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

        // ==========================================
        // RENDER TABLE ROWS
        // ==========================================
        function renderRows(items) {
            if (!Array.isArray(items) || items.length === 0) {
                tbody.innerHTML = `
                <tr>
                    <td colspan="11" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 40px; color: #bbb;"></i>
                        <p class="text-muted mt-2">Tidak ada data pengadaan</p>
                    </td>
                </tr>`;
                paginationWrap.innerHTML = "";
                return;
            }

            tbody.innerHTML = items.map((p, index) => {
                const priorityClass = p.priority?.toLowerCase() || '';
                const priorityText = p.priority?.toUpperCase() || '-';

                return `
            <tr>
                <td style="padding: 12px 8px; text-align:center;"><strong>${index + 1}</strong></td>
                <td style="padding: 12px 8px; text-align:center"><strong>${p.project_code || '-'}</strong></td>
                <td style="padding: 12px 8px; text-align:center"><strong>${p.code_procurement || '-'}</strong></td>
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

        // ==========================================
        // RENDER PAGINATION
        // ==========================================
        function renderPagination() {
            if (!lastPagination) {
                paginationWrap.innerHTML = '';
                return;
            }

            const p = lastPagination;
            let html = `<nav><ul class="pagination">`;

            html += p.current_page > 1 ?
                `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${p.current_page - 1}); return false;">← Sebelumnya</a></li>` :
                `<li class="page-item disabled"><span class="page-link">← Sebelumnya</span></li>`;

            for (let i = 1; i <= p.last_page; i++) {
                html += i === p.current_page ?
                    `<li class="page-item active"><span class="page-link">${i}</span></li>` :
                    `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a></li>`;
            }

            html += p.has_more ?
                `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${p.current_page + 1}); return false;">Berikutnya →</a></li>` :
                `<li class="page-item disabled"><span class="page-link">Berikutnya →</span></li>`;

            html += `</ul></nav>`;
            paginationWrap.innerHTML = html;
        }

        // ==========================================
        // GO TO PAGE
        // ==========================================
        window.goToPage = function(page) {
            currentPage = page;
            fetchProcurements();
        };

        // ==========================================
        // FETCH PROCUREMENTS
        // ==========================================
        function fetchProcurements() {
            const q = encodeURIComponent(searchInput.value.trim());
            const checkpoint = encodeURIComponent(checkpointSelect.value);
            const priority = encodeURIComponent(prioritySelect.value);
            const project = encodeURIComponent(projectSelect.value);

            const url = `{{ route('dashboard.search') }}?q=${q}&checkpoint=${checkpoint}&priority=${priority}&project=${project}&page=${currentPage}`;

            console.log('🔍 Fetching:', url);

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP error! status: ${res.status}`);
                    return res.json();
                })
                .then(res => {
                    console.log('✅ Data received:', res);
                    lastPagination = res.pagination;
                    renderRows(res.data);
                })
                .catch(err => {
                    console.error("❌ Search error:", err);
                    tbody.innerHTML = `
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="bi bi-exclamation-circle" style="font-size: 48px; color: #f00;"></i>
                            <p class="text-danger mt-2">Terjadi kesalahan: ${err.message}</p>
                        </td>
                    </tr>`;
                });
        }

        // ==========================================
        // DEBOUNCED FETCH
        // ==========================================
        const debouncedFetch = _.debounce(() => {
            currentPage = 1;
            fetchProcurements();
        }, 300);

        // ==========================================
        // EVENT LISTENERS
        // ==========================================
        searchInput.addEventListener('input', debouncedFetch);
        checkpointSelect.addEventListener('change', debouncedFetch);
        prioritySelect.addEventListener('change', debouncedFetch);
        projectSelect.addEventListener('change', debouncedFetch);

        console.log('✅ Dashboard script initialized successfully');
    });
</script>
@endpush