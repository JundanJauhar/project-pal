@extends('layouts.app')

@section('title', 'Daftar Pengadaan - Supply Chain')

@push('styles')
<style>
    .priority-badge {
        padding: 5px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }
    .priority-tinggi { color: #BD0000; }
    .priority-sedang { color: #FFBB00; }
    .priority-rendah { color: #6f6f6f; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Success/Error Messages -->
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

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-box-seam"></i> Daftar Pengadaan</h2> 
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-custom">
                <div class="card-body">
                    <form id="filter-form" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Cari Pengadaan..." value="">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="checkpoint">
                                <option value="">Semua Checkpoint</option>
                                <option value="Penawaran Permintaan">Penawaran Permintaan</option>
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
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="priority">
                                <option value="">Semua Prioritas</option>
                                <option value="rendah">Rendah</option>
                                <option value="sedang">Sedang</option>
                                <option value="tinggi">Tinggi</option>
                            </select>
                        </div>
                        <div class="tambah col-md-2 text-end">
                            <a href="{{ route('procurements.create') }}" class="btn btn-primary w-100 btn-custom" wire:navigate>
                                <i class="bi bi-plus-circle"></i> Tambah
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kode Pengadaan</th>
                            <th>Nama Pengadaan</th>
                            <th>Department</th>
                            <th>Vendor</th>
                            <th>Tanggal Mulai</th>
                            <th>Tempat Selesai</th>
                            <th>Prioritas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($procurements as $procurement)
                        <tr data-name="{{ strtolower($procurement->name_procurement) }} {{ strtolower($procurement->code_procurement) }}">
                            <td><strong>{{ $procurement->code_procurement }}</strong></td>
                            <td>{{ Str::limit($procurement->name_procurement, 40) }}</td>
                            <td>{{ $procurement->department->department_name ?? '-' }}</td>
                            <td>
                                @php
                                    $requestProcurement = $procurement->requestProcurements->first();
                                    $vendor = $requestProcurement?->vendor;
                                @endphp

                                @if($vendor)
                                    <div class="d-flex flex-column">
                                        <div>
                                            <strong>{{ $vendor->name_vendor }}</strong>
                                            @if($requestProcurement->request_status)
                                                @php
                                                    $statusMap = [
                                                        'submitted' => ['Submitted', 'info'],
                                                        'approved' => ['Approved', 'success'],
                                                        'rejected' => ['Rejected', 'danger'],
                                                    ];
                                                    [$statusText, $badgeColor] = $statusMap[$requestProcurement->request_status] ?? ['Pending', 'warning'];
                                                @endphp
                                                <span class="badge bg-{{ $badgeColor }} ms-2">{{ $statusText }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <a href="{{ route('supply-chain.vendor.pilih', $procurement->procurement_id) }}"
                                       class="btn btn-sm btn-primary" wire:navigate>
                                        <i class="bi bi-plus-circle"></i> Kelola Vendor
                                    </a>
                                @endif
                            </td>
                            <td>{{ $procurement->start_date->format('d/m/Y') }}</td>
                            <td>{{ $procurement->end_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="priority-badge priority-{{ strtolower($procurement->priority) }}">
                                    {{ strtoupper($procurement->priority) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('procurements.show', $procurement->procurement_id) }}" class="btn btn-sm btn-primary" wire:navigate>
                                    Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data pengadaan</td>
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
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const tableBody = document.getElementById('tableBody');

    function filterTable() {
        const searchValue = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value.toLowerCase();
        const priorityValue = priorityFilter.value.toLowerCase();
        const rows = tableBody.querySelectorAll('tr[data-name]');

        rows.forEach(row => {
            const name = row.getAttribute('data-name');
            const status = row.querySelector('td:nth-child(8)')?.textContent.toLowerCase() || '';
            const priority = row.querySelector('.priority-badge')?.textContent.toLowerCase() || '';

            const matchSearch = name.includes(searchValue);
            const matchStatus = !statusValue || status.includes(statusValue);
            const matchPriority = !priorityValue || priority.includes(priorityValue);

            row.style.display = (matchSearch && matchStatus && matchPriority) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(filterTable, 300);
    });

    statusFilter.addEventListener('change', filterTable);
    priorityFilter.addEventListener('change', filterTable);
</script>
@endpush

