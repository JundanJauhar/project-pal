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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                            <h2 class="stat-number">{{ $vendors->where('status', 'approved')->count() }}</h2>
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
                            <h2 class="stat-number">{{ $vendors->where('status', 'pending')->count() }}</h2>
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

        <!-- Tambah Vendor -->
        <div class="tambah col-md-2 text-end ">
            @if(in_array(Auth::user()->roles, ['user', 'supply_chain']))
                <a href="{{ route('supply-chain.vendor.create') }}" class="btn btn-primary w-100 btn-custom">
                    <i class="bi bi-plus-circle"></i> Tambah Vendor
                </a>
            @endif
        </div>

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
                                        <th>ID Vendor</th>
                                        <th>Nama Vendor</th>
                                        <th>Alamat</th>
                                        <th>Kontak</th>
                                        <th>Email</th>
                                        <th>Status Legal</th>
                                        <th>Importer</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($vendors as $vendor)
                                        <tr>
                                            <td><strong>{{ $vendor->id_vendor }}</strong></td>
                                            <td>{{ $vendor->name_vendor }}</td>
                                            <td>{{ Str::limit($vendor->address ?? '-', 30) }}</td>
                                            <td>{{ $vendor->phone_number ?? '-' }}</td>
                                            <td>{{ $vendor->email ?? '-' }}</td>
                                            <td>{{ $vendor->legal_status ?? '-' }}</td>
                                            <td>
                                                @if($vendor->is_importer)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-globe"></i> Ya
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Tidak</span>
                                                @endif
                                            </td>
                                            <td>
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
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        onclick="selectVendor({{ $vendor->id_vendor }}, '{{ $vendor->name_vendor }}')">
                                                        <i class="bi bi-check-circle"></i> Pilih
                                                    </button>
                                                    <a href="#" class="btn btn-sm btn-info text-white">
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
        function selectVendor(vendorId, vendorName) {
            if (confirm('Pilih vendor "' + vendorName + '" untuk project ini?')) {
                alert('Vendor ID: ' + vendorId + ' dipilih');
                // Implementasi logic pilih vendor
            }
        }
    </script>
@endpush
