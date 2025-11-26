@extends('layouts.app')

@section('title', 'Pilih Vendor - PT PAL Indonesia')

@push('styles')
<style>
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .stat-card.border-blue {
        border-left: 4px solid #667eea;
    }

    .stat-card.border-green {
        border-left: 4px solid #4facfe;
    }

    .stat-card.border-yellow {
        border-left: 4px solid #f093fb;
    }

    .stat-card.border-red {
        border-left: 4px solid #fa709a;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: #2d3748;
        margin: 0;
    }

    .stat-label {
        color: #718096;
        font-size: 0.875rem;
        margin: 0;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .stat-icon.bg-blue {
        background-color: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .stat-icon.bg-green {
        background-color: rgba(79, 172, 254, 0.1);
        color: #4facfe;
    }

    .stat-icon.bg-yellow {
        background-color: rgba(240, 147, 251, 0.1);
        color: #f093fb;
    }

    .stat-icon.bg-red {
        background-color: rgba(250, 112, 154, 0.1);
        color: #fa709a;
    }

    .tambah {
        margin-bottom: 20px;
    }

    .tambah .btn {
        background-color: #003d82;
    }

    .vendor-status {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
    }

    .status-active {
        background-color: #28AC00;
        color: white;
    }

    .status-inactive {
        background-color: #BD0000;
        color: white;
    }

    .status-pending {
        background-color: #FFBB00;
        color: black;
    }

        .vendor-table thead th {
        padding: 14px 6px;
        border-bottom: 2px solid #C9C9C9;
        font-size: 14px;
        text-transform: uppercase;
        color: #555;
        text-align: center;
        vertical-align: middle;
    }

    .vendor-table tbody tr:hover {
        background: #EFEFEF;
    }

    .vendor-table tbody td {
        padding: 14px 6px;
        border-bottom: 1px solid #DFDFDF;
        font-size: 15px;
        color: #333;
        text-align: center;
    }

    .vendor-table-wrapper {
        /* background: #F6F6F6; */
        padding: 25px;
        border-radius: 14px;
        border: 1px solid #E0E0E0;
        margin-top: 20px;
    }

    .vendor-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    /* Title (mirip Payment) */
    .vendor-table-title {
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
    .vendor-search-box {
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
    .vendor-search-box input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 14px;
    }
    .vendor-search-box i {
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

    .tambah .btn {
        background: #003d82;
        border-color: #003d82;
    }

    .tambah .btn:hover{
        background: #002e5c;
        border-color: #002e5c;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Kelola Vendor</h2>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card border-blue">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Total Vendor</p>
                        <h2 class="stat-number">{{ $vendors->count() }}</h2>
                    </div>
                    <div class="stat-icon bg-blue">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-green">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Vendor Aktif</p>
                        <h2 class="stat-number">{{ $vendors->where('legal_status', 'approved')->count() }}</h2>
                    </div>
                    <div class="stat-icon bg-green">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-yellow">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Menunggu Verifikasi</p>
                        <h2 class="stat-number">{{ $vendors->where('legal_status', 'pending')->count() }}</h2>
                    </div>
                    <div class="stat-icon bg-yellow">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-red">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Vendor Importer</p>
                        <h2 class="stat-number">{{ $vendors->where('is_importer', true)->count() }}</h2>
                    </div>
                    <div class="stat-icon bg-red">
                        <i class="bi bi-globe"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Tambah Vendor -->
    <div class="d-flex justify-content-between align-items-end mb-3">
        <!-- Search Form -->
        <form class="d-flex gap-2" id="searchForm" style="flex: 0 0 auto;">
            <div class="position-relative" style="width: 300px;">
                <input type="text"
                    class="form-control pe-5"
                    name="search"
                    id="searchInput"
                    placeholder="Cari Vendor..."
                    autocomplete="off">
                <button type="button"
                    class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-danger pe-2"
                    id="clearSearch"
                    style="z-index: 10; display: none;">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
        </form>

        <!-- Tambah Vendor -->
        <div class="tambah col-md-2">
            @if(in_array(Auth::user()->roles, ['user', 'supply_chain']))
            <a href="{{ route('supply-chain.vendor.form', ['redirect' => 'kelola']) }}" class="btn btn-primary w-100 btn-custom" wire:navigate>
                <i class="bi bi-plus-circle"></i> Tambah
            </a>
            @endif
        </div>
    </div>

    <!-- Vendors Table -->
    <div class="vendor-table-wrapper">
        <div class="card-header">
            <div class="vendor-table-title">
                <span>Daftar Vendor</span>
            </div>
                    <div class="table-responsive">
                        <table class="vendor-table">
                            <thead>
                                <tr>
                                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">ID Vendor</th>
                                    <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Vendor</th>
                                    <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Alamat</th>
                                    <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Kontak</th>
                                    <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Email</th>
                                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status Legal</th>
                                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Importer</th>
                                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="vendorTableBody">
                                @forelse($vendors as $vendor)
                                <tr>
                                    <td style="padding: 12px 8px; text-align: center;"><strong>{{ $vendor->id_vendor }}</strong></td>
                                    <td style="padding: 12px 8px; text-align: left;">{{ $vendor->name_vendor }}</td>
                                    <td style="padding: 12px 8px; text-align: left;">{{ Str::limit($vendor->address ?? '-', 30) }}</td>
                                    <td style="padding: 12px 8px; text-align: left;">{{ $vendor->phone_number ?? '-' }}</td>
                                    <td style="padding: 12px 8px; text-align: left;">{{ $vendor->email ?? '-' }}</td>
                                    <td style="padding: 12px 8px; text-align: center;">{{ $vendor->legal_status ?? '-' }}</td>
                                    <td style="padding: 12px 8px; text-align: center;">
                                        @if($vendor->is_importer)
                                        <span class="badge bg-success">
                                            <i class="bi bi-globe"></i> Ya
                                        </span>
                                        @else
                                        <span class="badge bg-secondary">Tidak</span>
                                        @endif
                                    </td>
                                    <td style="padding: 12px 8px; text-align: center;">
                                        @php
                                        $statusClass = match ($vendor->status ?? 'pending') {
                                        'approved' => 'status-active',
                                        'pending' => 'status-pending',
                                        'rejected' => 'status-inactive',
                                        default => 'status-pending'
                                        };
                                        $statusText = match ($vendor->status ?? 'pending') {
                                        'approved' => 'Aktif',
                                        'pending' => 'Pending',
                                        'rejected' => 'Ditolak',
                                        default => 'Pending'
                                        };
                                        @endphp
                                        <span class="vendor-status {{ $statusClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>
                                    <td style="padding: 12px 8px; text-align: center;">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('supply-chain.vendor.form', ['id' => $vendor->id_vendor]) }}" class="btn btn-sm btn-primary text-white" wire:navigate>
                                                <i class="bi bi-pencil"></i> Edit
                                            </a>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('supply-chain.vendor.detail', ['id' => $vendor->id_vendor]) }}" class="btn btn-sm btn-info text-white" wire:navigate>
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                        <p class="mt-3 mb-0">Tidak ada data vendor</p>
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
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const searchBtn = document.getElementById('searchBtn');
    const tableBody = document.getElementById('vendorTableBody');
    let searchTimeout;

    // Function untuk melakukan search
    function performSearch() {
        const searchValue = searchInput.value.trim();

        // Tampilkan/sembunyikan tombol X
        clearBtn.style.display = searchValue ? 'block' : 'none';

        // Tampilkan loading
        tableBody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Mencari vendor...</p>
                </td>
            </tr>
        `;

        // Fetch data dengan AJAX
        fetch('{{ route("supply-chain.vendor.kelola") }}?search=' + encodeURIComponent(searchValue), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse HTML response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTableBody = doc.querySelector('#vendorTableBody');

            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5 text-danger">
                        <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-0">Terjadi kesalahan saat mencari data</p>
                    </td>
                </tr>
            `;
        });
    }

    // Search saat mengetik (debounced)
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(performSearch, 500);
    });


    // Search saat tekan Enter
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchTimeout);
            performSearch();
        }
    });

    // Clear search
    clearBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        searchInput.value = '';
        searchInput.focus();
        clearBtn.style.display = 'none';
        performSearch();
    });

    // Clear dengan ESC
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && this.value) {
            e.preventDefault();
            searchInput.value = '';
            clearBtn.style.display = 'none';
            performSearch();
        }
    });
});

function selectVendor(vendorId, vendorName) {
    if (confirm('Pilih vendor "' + vendorName + '" untuk project ini?')) {
        alert('Vendor ID: ' + vendorId + ' dipilih');
        // Implementasi logic pilih vendor
    }
}
</script>
@endpush
