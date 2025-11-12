@extends('layouts.app')

@section('title', 'Projects - PT PAL Indonesia')

@section('content')

<style>
    .tambah .btn{
        background: #003d82;
        border-color: #003d82;
    }
    .search-container {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 20px;
    }
    .search-input {
        position: relative;
        width: 300px;
    }
    .search-input input {
        padding-right: 40px;
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    .search-input i {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }
</style>

@if(Auth::user()->roles === 'desain')
    <!-- View for Desain User -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 style="font-weight: 600; margin: 0;">Daftar Pengadaan</h2>
        </div>
        <div class="col-md-6">
            <div class="search-container">
                <div class="search-input">
                    <input type="text" class="form-control" id="search-procurement" placeholder="Cari...">
                    <i class="bi bi-search"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Procurement Table -->
    <div class="row">
        <div class="col-12">
            <div style="background: #EBEBEB; padding: 20px; border-radius: 8px;">
                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse; background: white;">
                        <thead>
                            <tr style="border-bottom: 2px solid #000;">
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000; border-bottom: 2px solid #000;">Nama Project</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000; border-bottom: 2px solid #000;">Deskripsi Pengadaan</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000; border-bottom: 2px solid #000;">Department</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000; border-bottom: 2px solid #000;">Tanggal Pengadaan</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000; border-bottom: 2px solid #000;">Tanggal Target</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000; border-bottom: 2px solid #000;">Tanggal Keluar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($procurements ?? [] as $procurement)
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px 8px;">{{ $procurement->project->name_project ?? '-' }}</td>
                                <td style="padding: 12px 8px;">{{ $procurement->request_name ?? '-' }}</td>
                                <td style="padding: 12px 8px;">
                                    @if($procurement->applicantDivision)
                                        {{ $procurement->applicantDivision->nama_divisi }}
                                    @elseif($procurement->project && $procurement->project->ownerDivision)
                                        {{ $procurement->project->ownerDivision->nama_divisi }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="padding: 12px 8px;">{{ $procurement->created_date ? $procurement->created_date->format('d/m/Y') : '-' }}</td>
                                <td style="padding: 12px 8px;">{{ $procurement->deadline_date ? $procurement->deadline_date->format('d/m/Y') : '-' }}</td>
                                <td style="padding: 12px 8px;">{{ $procurement->deadline_date ? $procurement->deadline_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-2">Tidak ada data pengadaan</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(isset($procurements) && $procurements->hasPages())
                <div class="mt-3">
                    {{ $procurements->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-procurement');
            const tableRows = document.querySelectorAll('tbody tr');
            
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    
                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
@else
    <!-- View for Other Roles -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="bi bi-folder-fill"></i> Daftar Projects</h2>
        </div>
    </div>

    <!-- Filter and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-custom">
                <div class="card-body">
                    <form method="GET" action="{{ route('projects.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Cari project..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="review_sc" {{ request('status') === 'review_sc' ? 'selected' : '' }}>Review SC</option>
                                <option value="persetujuan_sekretaris" {{ request('status') === 'persetujuan_sekretaris' ? 'selected' : '' }}>Persetujuan Sekretaris</option>
                                <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="priority">
                                <option value="">Semua Prioritas</option>
                                <option value="rendah" {{ request('priority') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                                <option value="sedang" {{ request('priority') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                                <option value="tinggi" {{ request('priority') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                            </select>
                        </div>
                        <div class="tambah col-md-2 text-end">
                            @if(in_array(Auth::user()->roles, ['user', 'supply_chain']))
                            <a href="{{ route('projects.create') }}" class="btn btn-primary w-100 btn-custom">
                                <i class="bi bi-plus-circle"></i> Tambah
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="row">
        <div class="col-12">
            <div style="background: #EBEBEB; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.08);">
                <h3 style="margin-bottom: 15px; font-weight: 600; border-bottom: 2px solid #0000; padding-bottom: 15px;">
                    <i class=""></i> Daftar Pengadaan
                </h3>

                <div class="table-responsive">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #000;">
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Kode Project</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Project</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Mulai</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Selesai</th>
                                <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Vendor</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="projects-tbody">
                            @forelse($projects ?? [] as $project)
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px 8px;"><strong>{{ $project->code_project }}</strong></td>
                            <td style="padding: 12px 8px;">{{ Str::limit($project->name_project, 40) }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $project->ownerDivision->nama_divisi ?? '-' }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $project->start_date->format('d/m/Y') }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $project->end_date->format('d/m/Y') }}</td>
                            <td style="padding: 12px 8px; text-align: left;">
                                @php
                                    $contract = $project->contracts->first();
                                    $vendorName = $contract->vendor->name_vendor ?? '-';
                                    $vendorStatus = match($project->status_project) {
                                        'pemilihan_vendor', 'in_progress', 'ongoing', 'proses' => 'process',
                                        'selesai', 'completed' => 'completed',
                                        'rejected', 'ditolak' => 'rejected',
                                        default => 'neutral'
                                    };
                                @endphp
                                @if($contract)
                                    <span class="vendor-pill vendor-status-{{ $vendorStatus }}">{{ Str::limit($vendorName, 20) }}</span>
                                @else
                                    <span class="vendor-pill vendor-status-neutral">-</span>
                                @endif
                            </td>
                            <td style="padding: 12px 8px; text-align: center;">
                                <span class="badge-priority badge-{{ strtolower($project->priority) }}"
                                      style="padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                    {{ strtoupper($project->priority) }}
                                </span>
                            </td>
                            <td style="padding: 12px 8px; text-align: center;">
                                @php
                                    $statusClass = match($project->status_project) {
                                        'completed', 'selesai' => 'success',
                                        'rejected' => 'danger',
                                        'review_sc', 'persetujuan_sekretaris' => 'warning',
                                        'draft' => 'secondary',
                                        default => 'info'
                                    };
                                    $statusText = match($project->status_project) {
                                        'review_sc' => 'Review SC',
                                        'persetujuan_sekretaris' => 'Review Sekretaris',
                                        'pemilihan_vendor' => 'Pemilihan Vendor',
                                        'selesai', 'completed' => 'Success',
                                        'rejected' => 'Denied',
                                        default => ucfirst($project->status_project)
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusClass }}"
                                      style="padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                    {{ $statusText }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-2">Tidak ada data project</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                <div id="projects-pagination">
                    {{ $projects->links() }}
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
@if(Auth::user()->roles !== 'desain')
<script>
    // Debounce helper
    function debounce(fn, delay) {
        let t;
        return function () {
            const args = arguments;
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('input[name="search"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const prioritySelect = document.querySelector('select[name="priority"]');
        const tbody = document.getElementById('projects-tbody');
        const paginationWrap = document.getElementById('projects-pagination');

        let currentPage = 1;
        let lastPagination = null;

        // Render rows helper
        function renderRows(items) {
            if (!Array.isArray(items) || items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                            <p class="text-muted mt-2">Tidak ada data project</p>
                        </td>
                    </tr>`;
                paginationWrap.innerHTML = '';
                return;
            }

            tbody.innerHTML = items.map(p => `
                <tr style="border-bottom: 1px solid #ddd;">
                    <td style="padding: 12px 8px;"><strong>${p.code_project}</strong></td>
                    <td style="padding: 12px 8px;">${p.name_project.length > 40 ? p.name_project.substring(0,40) + '...' : p.name_project}</td>
                    <td style="padding: 12px 8px; text-align: center;">${p.owner_division}</td>
                    <td style="padding: 12px 8px; text-align: center;">${p.start_date ?? '-'}</td>
                    <td style="padding: 12px 8px; text-align: center;">${p.end_date ?? '-'}</td>
                    <td style="padding: 12px 8px;">${p.vendor ?? '-'}</td>
                    <td style="padding: 12px 8px; text-align: center;"><span class="badge-priority badge-${(p.priority || '').toLowerCase()}" style="padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">${(p.priority || '').toUpperCase()}</span></td>
                    <td style="padding: 12px 8px; text-align: center;"><span class="badge bg-info" style="padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600;">${(p.status_project || '').replace(/_/g,' ')}</span></td>
                </tr>
            `).join('');

            // Render pagination
            renderPagination();
        }

        // Render pagination controls
        function renderPagination() {
            if (!lastPagination) {
                paginationWrap.innerHTML = '';
                return;
            }

            const p = lastPagination;
            let html = '<nav><ul class="pagination">';

            // Previous button
            if (p.current_page > 1) {
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToPage(${p.current_page - 1})">← Sebelumnya</a></li>`;
            } else {
                html += '<li class="page-item disabled"><span class="page-link">← Sebelumnya</span></li>';
            }

            // Page numbers
            for (let i = 1; i <= p.last_page; i++) {
                if (i === p.current_page) {
                    html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToPage(${i})">${i}</a></li>`;
                }
            }

            // Next button
            if (p.has_more) {
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="goToPage(${p.current_page + 1})">Berikutnya →</a></li>`;
            } else {
                html += '<li class="page-item disabled"><span class="page-link">Berikutnya →</span></li>';
            }

            html += '</ul></nav>';
            paginationWrap.innerHTML = html;
        }

        // Go to page
        window.goToPage = function(page) {
            currentPage = page;
            fetchProjects();
        };

        // Fetch helper
        function fetchProjects() {
            const q = encodeURIComponent(searchInput.value.trim());
            const status = encodeURIComponent(statusSelect.value);
            const priority = encodeURIComponent(prioritySelect.value);
            const url = `{{ route('projects.search') }}?q=${q}&status=${status}&priority=${priority}&page=${currentPage}`;

            console.log('Fetching:', url);

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => {
                    if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
                    return r.json();
                })
                .then(response => {
                    console.log('Response:', response);
                    lastPagination = response.pagination;
                    renderRows(response.data);
                })
                .catch(err => console.error('Search error:', err));
        }

        const debouncedFetch = debounce(function() {
            currentPage = 1;
            fetchProjects();
        }, 300);

        // Load initial data
        console.log('Initializing projects table...');
        fetchProjects();

        // Live on typing
        if (searchInput) searchInput.addEventListener('input', debouncedFetch);

        // Also trigger when filters change
        if (statusSelect) statusSelect.addEventListener('change', function() {
            currentPage = 1;
            fetchProjects();
        });
        if (prioritySelect) prioritySelect.addEventListener('change', function() {
            currentPage = 1;
            fetchProjects();
        });

        // Optional: keep behaviour when user submits the form (fallback)
        const filterForm = document.querySelector('form[action="{{ route('projects.index') }}"]');
        if (filterForm) {
            filterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                currentPage = 1;
                fetchProjects();
            });
        }
    });
</script>
@endif
@endpush
