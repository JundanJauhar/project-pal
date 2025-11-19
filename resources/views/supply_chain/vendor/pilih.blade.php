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

    .card-header {
        background: #003d82;
        color: white;
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

    .tambah .btn {
        background: #003d82;
        border-color: #003d82;
    }

    .cari .btn {
        background: #003d82;
        border-color: #003d82;
    }
</style>

@endpush

@section('content')



<div class="mb-4 px-4">
    <a href="{{ route('supply-chain.dashboard') }}" class="text-decoration-none text-primary">

        <h4><i class="bi bi-arrow-left"></i> </h4>
    </a>
</div>
<div class="container-fluid px-4">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Pilih Vendor</h2>
            <p class="text-muted mb-0">Kelola dan pilih vendor untuk pengadaan material</p>
        </div>
    </div>

    <!-- Info Procurement -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Pilih Vendor untuk Pengadaan:</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Kode:</strong> {{ $procurement->code_procurement }}</p>
                    <p class="mb-1"><strong>Nama:</strong> {{ $procurement->name_procurement }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Department:</strong> {{ $procurement->department->department_name ?? '-' }}</p>
                    <p class="mb-1"><strong>Prioritas:</strong>
                        <span class="badge bg-{{ $procurement->priority == 'tinggi' ? 'danger' : ($procurement->priority == 'sedang' ? 'warning' : 'secondary') }}">
                            {{ strtoupper($procurement->priority) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->

    <!-- Tambah Vendor -->
    <div class="tambah col-md-2 text-end ">
        @if(in_array(Auth::user()->roles, ['user', 'supply_chain']))
        <a href="{{ route('supply-chain.vendor.form', ['redirect' => 'pilih']) }}" class="btn btn-primary w-100 btn-custom">
            <i class="bi bi-plus-circle"></i> Tambah Vendor Baru
        </a>
        @endif
    </div>

    <form class="row g-3 align-items-end mb-3" id="searchForm">
        <div class="col-md-4">
            <div class="position-relative">
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
        </div>
        <div class="col-md-3">
        </div>
    </form>

    <!-- Vendors Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Daftar Vendor
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">ID Vendor</th>
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
                                    <td style="padding: 12px 8px;"><strong>{{ $vendor->id_vendor }}</strong></td>
                                    <td style="padding: 12px 8px;">{{ $vendor->name_vendor }}</td>
                                    <td style="padding: 12px 8px;">{{ Str::limit($vendor->address ?? '-', 30) }}</td>
                                    <td style="padding: 12px 8px;">{{ $vendor->phone_number ?? '-' }}</td>
                                    <td style="padding: 12px 8px;">{{ $vendor->email ?? '-' }}</td>
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
                                            <form action="{{ route('supply-chain.vendor.simpan', $procurement->procurement_id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Pilih vendor {{ $vendor->name_vendor }} untuk pengadaan ini?')">
                                                @csrf
                                                <input type="hidden" name="vendor_id" value="{{ $vendor->id_vendor }}">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-check-circle"></i> Pilih
                                                </button>
                                            </form>
                                            <a href="{{ route('supply-chain.vendor.detail', ['id' => $vendor->id_vendor]) }}" class="btn btn-sm btn-info text-white">
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
        fetch('{{ route("supply-chain.vendor.pilih", $procurement->procurement_id) }}?search=' + encodeURIComponent(searchValue), {
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
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        performSearch();
    });

    // Clear dengan ESC
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && this.value) {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            performSearch();
        }
    });
});

// Fungsi untuk memilih vendor
function selectVendor(vendorId, vendorName) {
    if (confirm('Pilih vendor "' + vendorName + '" untuk project ini?')) {
        alert('Vendor ID: ' + vendorId + ' dipilih');
        // Implementasi logic pilih vendor
    }
}
</script>
@endpush
