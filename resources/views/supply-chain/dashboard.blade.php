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

    .sc-btn-primary {
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
    }

    .sc-btn-primary:hover {
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
        transition: all 0.2s;
    }

    .sc-pagination .page-link:hover {
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
        <a href="{{ route('supply-chain.dashboard', ['tab' => 'procurement']) }}"
            class="sc-tab-btn {{ $tab === 'procurement' ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Daftar Pengadaan
        </a>
        <a href="{{ route('supply-chain.dashboard', ['tab' => 'contract']) }}"
            class="sc-tab-btn {{ $tab === 'contract' ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Daftar Kontrak
        </a>
        <a href="{{ route('supply-chain.dashboard', ['tab' => 'payment']) }}"
            class="sc-tab-btn {{ $tab === 'payment' ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Daftar Pembayaran
        </a>
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

    <!-- Render Tab Content -->
    @if ($tab === 'procurement')
    @include('supply-chain.tables.procurement')
    @elseif ($tab === 'contract')
    @include('supply-chain.tables.contract')
    @elseif ($tab === 'payment')
    @include('supply-chain.tables.payment')
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterForm = document.getElementById('filter-form');
        if (!filterForm) return;

        const inputs = filterForm.querySelectorAll('select');
        inputs.forEach(input => {
            input.addEventListener('change', () => filterForm.submit());
        });

        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => filterForm.submit(), 500);
            });
        }
    });
</script>
@endpush