@extends('layouts.app')

@section('title', 'Dashboard - PT PAL Indonesia')

@push('styles')
<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        color: white;
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
    .card-header {
        background-color: #ffffff;
    }
    table thead th {
        vertical-align: middle;
    }
    table tbody td {
        vertical-align: middle;
    }
    .tambah .btn{
        background: #003d82;
        border-color: #003d82;
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
                <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-check-lg"></i></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-progress">
                <div class="stat-content">
                    <div class="stat-title">Sedang Proses</div>
                    <div class="stat-value">{{ $stats['sedang_proses'] }}</div>
                </div>
                <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-box"></i></div></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card stat-success">
                <div class="stat-content">
                    <div class="stat-title">Selesai</div>
                    <div class="stat-value">{{ $stats['selesai'] }}</div>
                </div>
                <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-check-lg"></i></div></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-rejected">
            <div class="stat-content">
                <div class="stat-title">Ditolak</div>
                <div class="stat-value">{{ $stats['ditolak'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-x"></i></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header text-black d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Pengadaan</h5>
                <div class="position-relative" style="width: 300px;">
                    <input type="text"
                           id="searchInput"
                           class="form-control"
                           placeholder="Cari pengadaan...">
                    <button id="clearSearch"
                            class="btn btn-sm position-absolute"
                            style="right: 5px; top: 50%; transform: translateY(-50%); display: none;">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Kode Pengadaan</th>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Pengadaan</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Mulai</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Selesai</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="procurementTableBody">
                        @forelse($procurements as $procurement)
                        <tr data-name="{{ strtolower($procurement->name_procurement) }} {{ strtolower($procurement->code_procurement) }}">
                            <td style="padding: 12px 8px;"><strong>{{ $procurement->code_procurement }}</strong></td>
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
                                    $status = $procurement->auto_status;
                                    $current = $procurement->current_checkpoint; // ← ini yang kita tambahkan

                                    $badgeColor = match($status) {
                                        'completed' => '#28AC00',
                                        'in_progress' => '#ECAD02',
                                        'not_started' => '#555',
                                        default => '#BD0000'
                                    };

                                    $text = match($status) {
                                        'completed' => 'Selesai',
                                        'not_started' => 'Belum Dimulai',
                                        'in_progress' => $current ?? 'Sedang Proses', // ← tampilkan checkpoint!
                                        default => $status
                                    };
                                @endphp

                                <span class="badge"
                                    style="background-color: {{ $badgeColor }};
                                        color:white;
                                        padding:6px 12px;
                                        font-weight:600;">
                                    {{ $text }}
                                </span>

                            </td>


                            <td style="padding: 12px 8px; text-align: center;">
                                <a href="{{ route('procurements.show', $procurement->procurement_id) }}" class="btn btn-sm btn-primary">
                                     Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center">Tidak ada data pengadaan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

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
                                <option value="not_started">Belum Dimulai</option>
                                <option value="PDM">PDM</option>
                                <option value="Pengecekan">Pengecekan</option>
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
                        <!-- <div class="tambah col-md-2 text-end">
                            <a href="{{ route('procurements.create') }}" class="btn btn-primary w-100 btn-custom">
                                <i class="bi bi-plus-circle"></i> Tambah
                            </a>
                        </div> -->
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header text-black">
                    <h5 class="mb-0">Daftar Pengadaan</h5>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Kode Pengadaan</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Pengadaan</th>
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
                                <td style="padding: 12px 8px;"><strong>{{ $procurement->code_procurement }}</strong></td>
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
                                        $status = $procurement->auto_status;
                                        $current = $procurement->current_checkpoint;

                                        $badgeColor = match($status) {
                                            'completed' => '#28AC00',
                                            'in_progress' => '#ECAD02',
                                            'not_started' => '#555',
                                            default => '#BD0000'
                                        };

                                        $text = match($status) {
                                            'completed' => 'Selesai',
                                            'not_started' => 'Belum Dimulai',
                                            'in_progress' => $current ?? 'Sedang Proses',
                                            default => $status
                                        };
                                    @endphp

                                    <span class="badge"
                                        style="background-color: {{ $badgeColor }};
                                            color:white;
                                            padding:6px 12px;
                                            font-weight:600;">
                                        {{ $text }}
                                    </span>
                                </td>
                                <td style="padding: 12px 8px; text-align: center;">
                                    <a href="{{ route('procurements.show', $procurement->procurement_id) }}" class="btn btn-sm btn-primary">
                                         Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="text-center">Tidak ada data pengadaan</td></tr>
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
    </div>
</div>

@endsection


@push('scripts')
<script>
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const clearBtn = document.getElementById('clearSearch');
    const tableBody = document.getElementById('procurementTableBody');
    const rows = tableBody.querySelectorAll('tr[data-name]'); // ✅ Ambil semua row dengan data-name

    // Search dengan debouncing
    searchInput.addEventListener('input', function() {
        const value = this.value.trim().toLowerCase();

        // Show/hide clear button
        clearBtn.style.display = value ? 'block' : 'none';

        // Debounce search
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch(value);
        }, 300);
    });

    // Clear search button
    clearBtn.addEventListener('click', function() {
        searchInput.value = '';
        this.style.display = 'none';
        performSearch('');
    });

    // Function untuk filter table rows
    function performSearch(searchValue) {
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.getAttribute('data-name');

            if (name.includes(searchValue)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Tampilkan pesan jika tidak ada hasil
        const emptyRow = document.getElementById('emptyRow');
        if (emptyRow) {
            emptyRow.remove();
        }

        if (visibleCount === 0 && searchValue !== '') {
            const newEmptyRow = document.createElement('tr');
            newEmptyRow.id = 'emptyRow';
            newEmptyRow.innerHTML = '<td colspan="9" class="text-center text-muted">Tidak ada pengadaan yang ditemukan untuk "' + searchValue + '"</td>';
            tableBody.appendChild(newEmptyRow);
        }
    }
</script>
@endpush
