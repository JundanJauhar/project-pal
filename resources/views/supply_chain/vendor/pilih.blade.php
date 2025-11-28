@extends('layouts.app')

@section('title', 'Pilih Vendor - PT PAL Indonesia')

@push('styles')
<style>
    /* ===== DASHBOARD CARDS ===== */
    .vendor-topcards {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        margin-top: 10px;
    }

    .vendor-card {
        flex: 1;
        padding: 18px 20px;
        border-radius: 12px;
        background: #F4F4F4;
        border: 1px solid #E0E0E0;
    }

    .vendor-card-inner {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .vendor-card h6 {
        color: #676767;
        font-size: 14px;
        margin-bottom: 6px;
    }

    .vendor-card h3 {
        font-weight: 700;
        font-size: 30px;
        margin: 0;
    }

    .vendor-card.blue {
        border-left: 5px solid #667eea;
    }

    .vendor-card.green {
        border-left: 5px solid #4facfe;
    }

    .vendor-card.yellow {
        border-left: 5px solid #f093fb;
    }

    .vendor-card.red {
        border-left: 5px solid #fa709a;
    }

    .vendor-card-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        font-size: 20px;
        color: #555;
    }

    /* ===== TABLE WRAPPER ===== */
    .vendor-table-wrapper {
        padding: 25px;
        border-radius: 14px;
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.12);
        background: #FFFFFF;
        margin-top: 60px;
    }

    /* Title + Search + Tambah */
    .vendor-table-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Search + Filters container */
    .filters-wrap {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Search */
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
        position: relative;
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

    .vendor-search-box .clear-btn {
        cursor: pointer;
        background: none;
        border: none;
        color: #d60000;
        font-size: 14px;
        padding: 0;
        display: none;
    }

    /* Tambah Vendor Button */
    .btn-tambah-vendor {
        background: #003d82;
        border-color: #003d82;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        width: 100%;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }

    .btn-tambah-vendor:hover {
        background: #002e5c;
        border-color: #002e5c;
        color: white;
    }

    /* ===== TABLE STYLE ===== */
    .vendor-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .vendor-table thead th {
        padding: 14px 6px;
        border-bottom: 2px solid #C9C9C9;
        font-size: 14px;
        text-transform: uppercase;
        color: #555;
        text-align: center;
        font-weight: 600;
    }

    .vendor-table tbody td {
        padding: 14px 6px;
        border-bottom: 1px solid #DFDFDF;
        font-size: 15px;
        color: #333;
        text-align: center;
    }

    .vendor-table tbody tr:hover {
        background: #EFEFEF;
    }

    /* Status Badges */
    .vendor-status {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
        display: inline-block;
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

    /* Action Buttons */
    .btn-group {
        display: inline-flex;
        gap: 6px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: #ECAD02;
        color: white;
        border: 1px solid #ECAD02;
    }

    .btn-primary:hover {
        background: #A77A00;
        border-color: #A77A00;
    }

    .btn-info {
        background: #003d82;
        color: white;
        border: 1px solid #003d82;
    }

    .btn-info:hover {
        background: #002e5c;
        border-color: #002e5c;
    }

    /* Info Box */
    .procurement-info-box {
        background: #F9F9F9;
        border-left: 4px solid #003d82;
        padding: 18px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
    }

    .procurement-info-box h5 {
        font-weight: 700;
        font-size: 16px;
        margin-bottom: 12px;
        color: #2d3748;
    }

    .procurement-info-box p {
        margin-bottom: 8px;
        color: #555;
        font-size: 14px;
    }

    .procurement-info-box strong {
        color: #2d3748;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }

    .empty-state i {
        font-size: 3rem;
        opacity: 0.3;
    }

    .empty-state p {
        margin-top: 12px;
        color: #999;
    }

    /* Back Link */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #003d82;
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 20px;
        transition: all 0.2s;
    }

    .back-link:hover {
        color: #002e5c;
        transform: translateX(-4px);
    }

    /* Responsive */
    @media (max-width: 1100px) {
        .vendor-topcards {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 900px) {
        .vendor-topcards {
            flex-direction: column;
        }

        .vendor-table-title {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }

        .filters-wrap {
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
            width: 100%;
        }

        .vendor-search-box {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

{{-- Back Link --}}
<div class="px-4 mb-3">
    <a href="{{ route('supply-chain.dashboard') }}" class="back-link" wire:navigate>
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

{{-- Page Header --}}
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-2">Pilih Vendor</h2>
            <p class="text-muted mb-0">Kelola dan pilih vendor untuk pengadaan material</p>
        </div>
    </div>

    {{-- Info Procurement --}}
    <div class="procurement-info-box">
        <h5>Informasi Pengadaan</h5>
        <div class="row">
            <div class="col-md-6">
                <p class="mb-1"><strong>Kode:</strong> {{ $procurement->code_procurement }}</p>
                <p class="mb-1"><strong>Nama:</strong> {{ $procurement->name_procurement }}</p>
            </div>
            <div class="col-md-6">
                <p class="mb-1"><strong>Department:</strong> {{ $procurement->department->department_name ?? '-' }}</p>
                <p class="mb-0">
                    <strong>Prioritas:</strong>
                    <span class="badge bg-{{ $procurement->priority == 'tinggi' ? 'danger' : ($procurement->priority == 'sedang' ? 'warning' : 'secondary') }}">
                        {{ strtoupper($procurement->priority) }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    {{-- TABLE ===== --}}
    <div class="vendor-table-wrapper">

        <div class="vendor-table-title">
            <span>Daftar Vendor</span>

            <!-- Search + Tambah di kanan -->
            <div class="d-flex gap-2 align-items-center" style="flex: 0 0 auto;">
                {{-- Search --}}
                <div class="vendor-search-box">
                    <input type="text" id="searchInput" placeholder="Cari Vendor..." autocomplete="off" />
                    <button type="button" class="clear-btn" id="clearSearch" style="display: none;">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                    <i class="bi bi-search"></i>
                </div>

                {{-- Tambah Vendor --}}
                @if(in_array(Auth::user()->roles, ['user', 'supply_chain']))
                <div class="tambah" style="min-width: 150px;">
                    <a href="{{ route('supply-chain.vendor.form', ['redirect' => 'pilih']) }}"
                       class="btn-tambah-vendor"
                       wire:navigate>
                        <i class="bi bi-plus-circle"></i> Tambah Vendor
                    </a>
                </div>
                @endif
            </div>
        </div>

        <div class="table-responsive">
            <table class="vendor-table">
                <thead>
                    <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">ID Vendor</th>
                    <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Vendor</th>
                    <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Alamat</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kontak</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Email</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status Legal</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Importer</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="vendorTableBody">
                    @forelse($vendors as $vendor)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            <strong>{{ $vendor->id_vendor }}</strong>
                        </td>
                        <td style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">
                            {{ $vendor->name_vendor }}
                        </td>
                        <td style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">
                            {{ Str::limit($vendor->address ?? '-', 30) }}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            {{ $vendor->phone_number ?? '-' }}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            {{ $vendor->email ?? '-' }}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            {{ $vendor->legal_status ?? '-' }}
                        </td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            @if($vendor->is_importer)
                            <span class="badge bg-success">
                                <i class="bi bi-globe"></i> Ya
                            </span>
                            @else
                            <span class="badge bg-secondary">Tidak</span>
                            @endif
                        </td>
                        <td style="padding: 12px 8px; text-align: center;">
                            <div class="btn-group">
                                <form action="{{ route('supply-chain.vendor.simpan', $procurement->procurement_id) }}"
                                    method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Pilih vendor {{ $vendor->name_vendor }} untuk pengadaan ini?')">
                                    @csrf
                                    <input type="hidden" name="vendor_id" value="{{ $vendor->id_vendor }}">
                                    <button type="submit" class="btn-sm btn-primary">
                                        <i class="bi bi-check-circle"></i> Pilih
                                    </button>
                                </form>
                                <a href="{{ route('supply-chain.vendor.detail', ['id' => $vendor->id_vendor]) }}"
                                   class="btn-sm btn-info"
                                   wire:navigate>
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Tidak ada data vendor</p>
                            </div>
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
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const tableBody = document.getElementById('vendorTableBody');
    let searchTimeout;

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
        fetch('{{ route("supply-chain.vendor.pilih", $procurement->procurement_id) }}?search=' + encodeURIComponent(searchValue), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.text())
        .then(html => {
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
</script>
@endpush