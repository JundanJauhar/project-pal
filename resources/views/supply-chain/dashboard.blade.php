@extends('layouts.app')

@section('title', 'Supply Chain Dashboard')

@push('styles')
<style>
    /* Tab Navigation */
    .sc-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 24px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 0;
    }

    .sc-tab-btn {
        padding: 12px 24px;
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 15px;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        text-decoration: none;
        position: relative;
        top: 2px;
    }

    .sc-tab-btn:hover {
        color: #003d82;
    }

    .sc-tab-btn.active {
        color: #003d82;
        border-bottom-color: #003d82;
    }

    /* Filter Section */
    .sc-filters {
        background: #ffffff;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 1px solid #e5e7eb;
    }

    .sc-filter-group {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    .sc-filter-input,
    .sc-filter-select {
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        min-width: 220px;
        transition: all 0.2s;
    }

    .sc-filter-input:focus,
    .sc-filter-select:focus {
        outline: none;
        border-color: #003d82;
        box-shadow: 0 0 0 3px rgba(0, 61, 130, 0.1);
    }

    .sc-btn-reset {
        background: #6b7280;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .sc-btn-reset:hover {
        background: #4b5563;
    }

    .sc-btn-add {
        background: #003d82;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }

    .sc-btn-add:hover {
        background: #002e5c;
    }

    /* Table Wrapper */
    .dashboard-table-wrapper {
        padding: 25px;
        border-radius: 14px;
        margin-top: 20px;
        margin-bottom: 30px;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-table thead th {
        padding: 14px 8px;
        border-bottom: 2px solid #C9C9C9;
        font-size: 14px;
        text-transform: uppercase;
        color: #555;
        text-align: center;
        vertical-align: middle;
        font-weight: 600;
    }

    .dashboard-table tbody tr:hover {
        background: #EFEFEF;
    }

    .dashboard-table tbody td {
        padding: 14px 8px;
        border-bottom: 1px solid #DFDFDF;
        font-size: 15px;
        color: #333;
        text-align: center;
    }

    /* Badges */
    .sc-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
    }

    .sc-badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .sc-badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .sc-badge-danger {
        background: #fee2e2;
        color: #991b1b;
    }

    .sc-badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Pagination */
    .sc-pagination {
        display: flex;
        justify-content: center;
        padding: 20px;
        gap: 4px;
    }

    .sc-pagination .pagination {
        display: flex;
        gap: 4px;
        margin: 0;
    }

    .sc-pagination .page-link {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        color: #374151;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }

    .sc-pagination .page-link:hover:not(.disabled) {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .sc-pagination .page-item.active .page-link {
        background: #003d82;
        color: white;
        border-color: #003d82;
    }

    .sc-pagination .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Empty State */
    .sc-empty {
        text-align: center;
        padding: 60px 20px;
        color: #9ca3af;
    }

    .sc-empty-icon {
        font-size: 56px;
        margin-bottom: 16px;
        opacity: 0.4;
    }

    .sc-empty p {
        font-size: 16px;
        margin: 0;
    }

    /* Loading State */
    .sc-loading {
        text-align: center;
        padding: 40px;
        color: #6b7280;
    }

    .sc-loading-spinner {
        display: inline-block;
        width: 32px;
        height: 32px;
        border: 3px solid #e5e7eb;
        border-top-color: #003d82;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 12px;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* Header */
    .sc-header {
        margin-bottom: 24px;
    }

    .sc-header h2 {
        font-size: 28px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .sc-header p {
        color: #6b7280;
        font-size: 15px;
        margin: 0;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="sc-header">
        <h2>Supply Chain Dashboard</h2>
        <p>Monitoring pengadaan, kontrak, dan pembayaran secara real-time</p>
    </div>

    <!-- Tab Navigation -->
    <div class="sc-tabs">
        <button type="button"
            class="sc-tab-btn active"
            data-tab="procurement"
            onclick="switchTab('procurement')">
            <i class="bi bi-box-seam"></i> Daftar Pengadaan
        </button>
        <button type="button"
            class="sc-tab-btn"
            data-tab="contract"
            onclick="switchTab('contract')">
            <i class="bi bi-file-earmark-text"></i> Daftar Kontrak
        </button>
        <button type="button"
            class="sc-tab-btn"
            data-tab="payment"
            onclick="switchTab('payment')">
            <i class="bi bi-credit-card"></i> Daftar Pembayaran
        </button>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter Section (akan di-render per tab) -->
    <div id="filter-container"></div>

    <!-- Table Container (akan di-render per tab) -->
    <div id="content-container"></div>
</div>
@endsection

@push('scripts')
<script>
    // ===== DATA =====
    const departments = {
        !!json_encode($departments) !!
    };
    let currentTab = 'procurement';
    let currentPage = 1;
    let isLoading = false;

    // ===== UTILITY: Debounce =====
    function debounce(fn, delay) {
        let timeoutId;
        return function(...args) {
            clearTimeout(timeoutId);
            timeoutId = setTimeout(() => fn(...args), delay);
        };
    }

    // ===== TAB SWITCHING =====
    function switchTab(tab) {
        currentTab = tab;
        currentPage = 1;

        // Update active tab button
        document.querySelectorAll('.sc-tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tab);
        });

        // Render filter dan fetch data
        renderFilter();
        fetchTabData();
    }

    // ===== RENDER FILTER SECTION =====
    function renderFilter() {
        const filterContainer = document.getElementById('filter-container');
        let html = '';

        if (currentTab === 'procurement') {
            html = `
                <form class="sc-filters">
                    <div class="sc-filter-group">
                        <input type="text" 
                            id="search-input" 
                            class="sc-filter-input"
                            placeholder="Cari kode, nama, atau project..."
                            onkeyup="handleSearch()">
                        
                        <select id="priority-filter" 
                            class="sc-filter-select"
                            onchange="handleFilter()">
                            <option value="">Semua Prioritas</option>
                            <option value="tinggi">Tinggi</option>
                            <option value="sedang">Sedang</option>
                            <option value="rendah">Rendah</option>
                        </select>

                        <select id="status-filter" 
                            class="sc-filter-select"
                            onchange="handleFilter()">
                            <option value="">Semua Status</option>
                            <option value="belum_ada_vendor">Belum ada vendor</option>
                            <option value="in_progress">Sedang Proses</option>
                            <option value="completed">Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>

                        <select id="department-filter" 
                            class="sc-filter-select"
                            onchange="handleFilter()">
                            <option value="">Semua Department</option>
                            ${departments.map(d => `<option value="${d.department_id}">${d.department_name}</option>`).join('')}
                        </select>

                        <button type="button" class="sc-btn-reset" onclick="resetProcurementFilter()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>

                        <a href="{{ route('procurements.create') }}" class="sc-btn-add" style="text-decoration: none;">
                            <i class="bi bi-plus-circle"></i> Tambah
                        </a>
                    </div>
                </form>
            `;
        } else if (currentTab === 'contract') {
            html = `
                <form class="sc-filters">
                    <div class="sc-filter-group">
                        <input type="text" 
                            id="search-input" 
                            class="sc-filter-input"
                            placeholder="Cari kode, nama, atau vendor..."
                            onkeyup="handleSearch()">
                        
                        <button type="button" class="sc-btn-reset" onclick="resetContractFilter()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </form>
            `;
        } else if (currentTab === 'payment') {
            html = `
                <form class="sc-filters">
                    <div class="sc-filter-group">
                        <input type="text" 
                            id="search-input" 
                            class="sc-filter-input"
                            placeholder="Cari kode atau vendor..."
                            onkeyup="handleSearch()">

                        <button type="button" class="sc-btn-reset" onclick="resetPaymentFilter()">
                            <i class="bi bi-arrow-clockwise"></i> Reset
                        </button>
                    </div>
                </form>
            `;
        }

        filterContainer.innerHTML = html;
    }

    // ===== FETCH DATA FROM AJAX =====
    async function fetchTabData(page = 1) {
        if (isLoading) return;
        isLoading = true;
        currentPage = page;

        const params = getFilterParams();
        params.append('page', page);

        const endpoint = getEndpoint();
        const url = new URL(endpoint, window.location.origin);
        url.search = params.toString();

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            renderTable(data);
            renderPagination(data.pagination);
        } catch (error) {
            console.error('Fetch error:', error);
            showError('Terjadi kesalahan saat memuat data');
        } finally {
            isLoading = false;
        }
    }

    // ===== GET FILTER PARAMS =====
    function getFilterParams() {
        const params = new URLSearchParams();

        const searchInput = document.getElementById('search-input');
        if (searchInput?.value) {
            params.append('search', searchInput.value);
        }

        if (currentTab === 'procurement') {
            const priorityFilter = document.getElementById('priority-filter');
            const statusFilter = document.getElementById('status-filter');
            const departmentFilter = document.getElementById('department-filter');

            if (priorityFilter?.value) params.append('priority', priorityFilter.value);
            if (statusFilter?.value) params.append('status', statusFilter.value);
            if (departmentFilter?.value) params.append('department', departmentFilter.value);
        } else if (currentTab === 'payment') {
            const typeFilter = document.getElementById('type-filter');
            if (typeFilter?.value) params.append('type', typeFilter.value);
        }

        return params;
    }

    // ===== GET ENDPOINT =====
    function getEndpoint() {
        const endpoints = {
            'procurement': '{{ route("supply-chain.ajax.procurement") }}',
            'contract': '{{ route("supply-chain.ajax.contract") }}',
            'payment': '{{ route("supply-chain.ajax.payment") }}'
        };
        return endpoints[currentTab];
    }

    // ===== RENDER TABLE =====
    function renderTable(data) {
        const container = document.getElementById('content-container');

        if (!data.data || data.data.length === 0) {
            container.innerHTML = getEmptyState();
            return;
        }

        let html = `
            <h4 style="margin-bottom: 16px; color: #000;">
                ${getTableTitle()}
            </h4>
            <div class="dashboard-table-wrapper">
                <table class="dashboard-table">
                    <thead>
                        ${getTableHeader()}
                    </thead>
                    <tbody id="table-body">
                        ${getTableRows(data.data)}
                    </tbody>
                </table>
            </div>
            <div id="pagination-container" class="sc-pagination"></div>
        `;

        container.innerHTML = html;
    }

    // ===== GET TABLE TITLE =====
    function getTableTitle() {
        const titles = {
            'procurement': 'Daftar Pengadaan',
            'contract': 'Daftar Kontrak',
            'payment': 'Daftar Pembayaran'
        };
        return titles[currentTab];
    }

    // ===== GET TABLE HEADER =====
    function getTableHeader() {
        if (currentTab === 'procurement') {
            return `
                <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Project</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nama Pengadaan</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Mulai</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Selesai</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                </tr>
            `;
        } else if (currentTab === 'contract') {
            return `
                <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No PO</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Item</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Kontrak</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Maker</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nilai</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Payment Term</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Incoterms</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">COO</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Warranty</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Delivery Time</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Link</th>
                </tr>
            `;
        } else if (currentTab === 'payment') {
            return `
                <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Jenis Pembayaran</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Persen</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nilai Pembayaran</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No Memo</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Link Memo</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">LSD</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Link Evidence</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Target</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Realisasi</th>
                </tr>
            `;
        }
    }

    // ===== GET TABLE ROWS =====
    function getTableRows(rows) {
        if (currentTab === 'procurement') {
            return rows.map((row, i) => {
                const rowNumber = (currentPage - 1) * 15 + i + 1;
                const statusClass = getStatusClass(row.status_procurement);
                const priorityClass = getPriorityClass(row.priority);

                return `
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>${rowNumber}</strong></td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>${row.project_code}</strong></td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <a href="/procurements/${row.procurement_id}" 
                                style="color: #000; font-weight: 600; text-decoration: none;">
                                ${row.code_procurement}
                            </a>
                        </td>
                        <td style="padding: 12px 8px; color: #000;">${row.name_procurement}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.department_name}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.start_date}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.end_date}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.vendor_name ? 
                                `<span class="sc-badge sc-badge-success">${row.vendor_name}</span>` : 
                                `<span class="sc-badge sc-badge-warning">Belum dipilih</span>`
                            }
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <span class="sc-badge ${priorityClass}">
                                ${row.priority.charAt(0).toUpperCase() + row.priority.slice(1)}
                            </span>
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <span class="sc-badge ${statusClass}">
                                ${getStatusLabel(row.status_procurement)}
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        } else if (currentTab === 'contract') {
            return rows.map((row, i) => {
                const rowNumber = (currentPage - 1) * 15 + i + 1;

                return `
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>${rowNumber}</strong></td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <a href="/procurements/${row.procurement_id}" 
                                style="color: #000; font-weight: 600; text-decoration: none;">
                                ${row.code_procurement}
                            </a>
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.no_po}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.vendor_name || '<span style="color: #999;">-</span>'}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.item_name || '<span style="color: #999;">-</span>'}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.tanggal_kontrak}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.maker}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.nilai > 0 ? 
                                row.currency + ' ' + new Intl.NumberFormat('id-ID').format(row.nilai) : 
                                '<span style="color: #999;">-</span>'
                            }
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.payment_term}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.incoterms}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.coo}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.warranty}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.delivery_time}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.link ? 
                                '<a href="' + row.link + '" target="_blank" style="color: #0066cc; text-decoration: underline; font-weight: 600;">Link</a>' : 
                                '<span style="color: #999;">-</span>'
                            }
                        </td>
                    </tr>
                `;
            }).join('');
        } else if (currentTab === 'payment') {
            return rows.map((row, i) => {
                const rowNumber = (currentPage - 1) * 15 + i + 1;

                if (row.type === 'empty') {
                    return `
                        <tr>
                            <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>${rowNumber}</strong></td>
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                <a href="/procurements/${row.procurement_id}" 
                                    style="color: #000; font-weight: 600; text-decoration: none;">
                                    ${row.code_procurement}
                                </a>
                            </td>
                            <td colspan="10" style="padding: 12px 8px; text-align: center; color: #999; font-style: italic;">
                                Tidak ada data pembayaran untuk pengadaan ini
                            </td>
                        </tr>
                    `;
                }

                return `
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;"><strong>${rowNumber}</strong></td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <a href="/procurements/${row.procurement_id}" 
                                style="color: #000; font-weight: 600; text-decoration: none;">
                                ${row.code_procurement}
                            </a>
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.vendor_name || '<span style="color: #999;">-</span>'}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">${row.payment_type}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.percentage ? row.percentage + '%' : '<span style="color: #999;">-</span>'}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.payment_value > 0 ? 
                                row.currency + ' ' + new Intl.NumberFormat('id-ID').format(row.payment_value) : 
                                '<span style="color: #999;">-</span>'
                            }
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.no_memo}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.link ? 
                                '<a href="' + row.link + '" target="_blank" style="color: #0066cc; text-decoration: underline; font-weight: 600;">Link</a>' : 
                                '<span style="color: #999;">-</span>'
                            }
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.lsd}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.evidence_link ? 
                                '<a href="' + row.evidence_link + '" target="_blank" style="color: #0066cc; text-decoration: underline; font-weight: 600;">Link</a>' : 
                                '<span style="color: #999;">-</span>'
                            }
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.target_date}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            ${row.realization_date ? 
                                row.realization_date : 
                                '<span class="sc-badge sc-badge-warning">Belum Direalisasi</span>'
                            }
                        </td>
                    </tr>
                `;
            }).join('');
        }
    }

    // ===== RENDER PAGINATION =====
    function renderPagination(pagination) {
        const container = document.getElementById('pagination-container');

        if (!pagination || pagination.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '<nav><ul class="pagination">';

        // Previous button
        if (pagination.current_page > 1) {
            html += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="fetchTabData(${pagination.current_page - 1}); return false;">
                        ← Sebelumnya
                    </a>
                </li>
            `;
        } else {
            html += `
                <li class="page-item disabled">
                    <span class="page-link">← Sebelumnya</span>
                </li>
            `;
        }

        // Page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === pagination.current_page) {
                html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
            } else {
                html += `
                    <li class="page-item">
                        <a class="page-link" href="#" onclick="fetchTabData(${i}); return false;">${i}</a>
                    </li>
                `;
            }
        }

        // Next button
        if (pagination.current_page < pagination.last_page) {
            html += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="fetchTabData(${pagination.current_page + 1}); return false;">
                        Berikutnya →
                    </a>
                </li>
            `;
        } else {
            html += `
                <li class="page-item disabled">
                    <span class="page-link">Berikutnya →</span>
                </li>
            `;
        }

        html += '</ul></nav>';
        container.innerHTML = html;
    }

    // ===== EMPTY STATE =====
    function getEmptyState() {
        const icons = {
            'procurement': '📦',
            'contract': '📄',
            'payment': '💳'
        };

        const messages = {
            'procurement': 'Tidak ada data pengadaan',
            'contract': 'Tidak ada data kontrak',
            'payment': 'Tidak ada data pembayaran'
        };

        return `
            <h4 style="margin-bottom: 16px; color: #000;">
                ${getTableTitle()}
            </h4>
            <div class="dashboard-table-wrapper">
                <div class="sc-empty">
                    <div class="sc-empty-icon">${icons[currentTab]}</div>
                    <p>${messages[currentTab]}</p>
                </div>
            </div>
        `;
    }

    // ===== SHOW ERROR =====
    function showError(message) {
        const container = document.getElementById('content-container');
        container.innerHTML = `
            <h4 style="margin-bottom: 16px; color: #000;">
                ${getTableTitle()}
            </h4>
            <div class="dashboard-table-wrapper">
                <div class="sc-empty">
                    <div class="sc-empty-icon">⚠️</div>
                    <p style="color: #991b1b;">${message}</p>
                </div>
            </div>
        `;
    }

    // ===== EVENT HANDLERS =====
    const handleSearch = debounce(() => {
        currentPage = 1;
        fetchTabData();
    }, 500);

    const handleFilter = debounce(() => {
        currentPage = 1;
        fetchTabData();
    }, 300);

    function resetProcurementFilter() {
        document.getElementById('search-input').value = '';
        document.getElementById('priority-filter').value = '';
        document.getElementById('status-filter').value = '';
        document.getElementById('department-filter').value = '';
        currentPage = 1;
        fetchTabData();
    }

    function resetContractFilter() {
        document.getElementById('search-input').value = '';
        currentPage = 1;
        fetchTabData();
    }

    function resetPaymentFilter() {
        document.getElementById('search-input').value = '';
        document.getElementById('type-filter').value = '';
        currentPage = 1;
        fetchTabData();
    }

    // ===== HELPER FUNCTIONS =====
    function getStatusClass(status) {
        const classes = {
            'in_progress': 'sc-badge-warning',
            'completed': 'sc-badge-success',
            'cancelled': 'sc-badge-danger',
        };
        return classes[status] || 'sc-badge-info';
    }

    function getStatusLabel(status) {
        const labels = {
            'in_progress': 'Dalam Proses',
            'completed': 'Selesai',
            'cancelled': 'Dibatalkan',
        };
        return labels[status] || status;
    }

    function getPriorityClass(priority) {
        const classes = {
            'tinggi': 'sc-badge-danger',
            'sedang': 'sc-badge-warning',
            'rendah': 'sc-badge-info',
        };
        return classes[priority] || 'sc-badge-info';
    }

    // ===== INIT =====
    document.addEventListener('DOMContentLoaded', () => {
        renderFilter();
        fetchTabData();
    });
</script>
@endpush