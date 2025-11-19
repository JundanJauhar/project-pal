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

    .modal-pengadaan {
        max-width: 900px;
    }

    .pengadaan-section {
        border: 2px solid #003d82;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        background: #f8f9fa;
    }

    .section-title {
        color: #003d82;
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 15px;
        text-decoration: underline;
    }

    .btn-tambah-pengadaan {
        position: absolute;
        bottom: 20px;
        right: 20px;
        width: 160px;
        height: 50px;
        font-weight: 600;
    }
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
            <p class="text-muted">{{ Auth::user()->department->department_name ?? 'Department' }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari pengadaan...">
        </div>
        <div class="col-md-3">
            <select id="statusFilter" class="form-select">
                <option value="">Semua Status</option>
                <option value="draft">Draft</option>
                <option value="submitted">Submitted</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <div class="col-md-3">
            <select id="priorityFilter" class="form-select">
                <option value="">Semua Prioritas</option>
                <option value="tinggi">Tinggi</option>
                <option value="sedang">Sedang</option>
                <option value="rendah">Rendah</option>
            </select>
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalTambahPengadaan">
                <i class="bi bi-plus-circle"></i> Tambah
            </button>
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
                            <td>{{ $procurement->requestProcurements->first()?->vendor->name_vendor ?? '-' }}</td>
                            <td>{{ $procurement->start_date->format('d/m/Y') }}</td>
                            <td>{{ $procurement->end_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="priority-badge priority-{{ strtolower($procurement->priority) }}">
                                    {{ strtoupper($procurement->priority) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('procurements.show', $procurement->procurement_id) }}" class="btn btn-sm btn-primary">
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

<!-- Modal Tambah Pengadaan -->
<div class="modal fade" id="modalTambahPengadaan" tabindex="-1">
    <div class="modal-dialog modal-pengadaan modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengadaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formTambahPengadaan" action="{{ route('supply-chain.dashboard.store') }}" method="POST">
                    @csrf
                    <div id="pengadaanContainer">
                        <!-- Pengadaan 1 (Default) -->
                        <div class="pengadaan-section" data-index="1">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="section-title mb-0">Pengadaan 1</h6>
                                <button type="button" class="btn btn-sm btn-danger btn-remove-pengadaan" style="display: none;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Pengadaan <span class="text-danger">*</span></label>
                                    <input type="text" name="pengadaan[1][name]" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Department <span class="text-danger">*</span></label>
                                    <select name="pengadaan[1][department]" class="form-select" required>
                                        <option value="">Pilih Department</option>
                                        @foreach(\App\Models\Department::all() as $dept)
                                        <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" name="pengadaan[1][start_date]" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Waktu <span class="text-danger">*</span></label>
                                    <input type="date" name="pengadaan[1][end_date]" class="form-control" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                                    <select name="pengadaan[1][priority]" class="form-select" required>
                                        <option value="">Pilih Prioritas</option>
                                        <option value="rendah">RENDAH</option>
                                        <option value="sedang">SEDANG</option>
                                        <option value="tinggi">TINGGI</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Button Tambah -->
                    <div class="text-center mb-3">
                        <button type="button" class="btn btn-outline-primary" id="btnTambahSection">
                            <i class="bi bi-plus-circle"></i> Tambah
                        </button>
                    </div>

                    <!-- Button Kirim -->
                    <div class="position-relative" style="height: 80px;">
                        <button type="submit" class="btn btn-primary btn-tambah-pengadaan">
                            Kirim Pengadaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let pengadaanIndex = 1;

    // Tambah Section Pengadaan
    document.getElementById('btnTambahSection').addEventListener('click', function() {
        pengadaanIndex++;
        const container = document.getElementById('pengadaanContainer');

        const newSection = document.createElement('div');
        newSection.className = 'pengadaan-section';
        newSection.setAttribute('data-index', pengadaanIndex);
        newSection.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="section-title mb-0">Pengadaan ${pengadaanIndex}</h6>
                <button type="button" class="btn btn-sm btn-danger btn-remove-pengadaan">
                    <i class="bi bi-trash"></i>
                </button>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Pengadaan <span class="text-danger">*</span></label>
                    <input type="text" name="pengadaan[${pengadaanIndex}][name]" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Department <span class="text-danger">*</span></label>
                    <select name="pengadaan[${pengadaanIndex}][department]" class="form-select" required>
                        <option value="">Pilih Department</option>
                        @foreach(\App\Models\Department::all() as $dept)
                        <option value="{{ $dept->department_id }}">{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="date" name="pengadaan[${pengadaanIndex}][start_date]" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Waktu <span class="text-danger">*</span></label>
                    <input type="date" name="pengadaan[${pengadaanIndex}][end_date]" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                    <select name="pengadaan[${pengadaanIndex}][priority]" class="form-select" required>
                        <option value="">Pilih Prioritas</option>
                        <option value="rendah">RENDAH</option>
                        <option value="sedang">SEDANG</option>
                        <option value="tinggi">TINGGI</option>
                    </select>
                </div>
            </div>
        `;

        container.appendChild(newSection);
        updateRemoveButtons();
    });

    // Remove Section Pengadaan
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-pengadaan')) {
            e.target.closest('.pengadaan-section').remove();
            updateRemoveButtons();
        }
    });

    function updateRemoveButtons() {
        const sections = document.querySelectorAll('.pengadaan-section');
        sections.forEach((section, index) => {
            const removeBtn = section.querySelector('.btn-remove-pengadaan');
            if (removeBtn) {
                removeBtn.style.display = sections.length > 1 ? 'block' : 'none';
            }
        });
    }

    // Search and Filter
    let searchTimeout;
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

