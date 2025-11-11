@extends('layouts.app')

@section('title', 'Projects - PT PAL Indonesia')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-folder-fill"></i> Daftar Projects</h2>
    </div>
    <div class="col-md-4 text-end">
        @if(in_array(Auth::user()->roles, ['user', 'supply_chain']))
        <a href="{{ route('projects.create') }}" class="btn btn-primary btn-custom">
            <i class="bi bi-plus-circle"></i> Tambah Project Baru
        </a>
        @endif
    </div>
</div>

<!-- Filter and Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <form method="GET" action="{{ route('projects.index') }}" class="row g-3">
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
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 btn-custom">
                            <i class="bi bi-search"></i> Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Projects Table -->
<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Pengadaan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Kode Project</th>
                                <th>Nama Project</th>
                                <th>Department</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Vendor</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="projects-tbody">
                            @forelse($projects as $project)
                            <tr>
                                <td><strong>{{ $project->code_project }}</strong></td>
                                <td>{{ Str::limit($project->name_project, 40) }}</td>
                                <td>{{ $project->ownerDivision->nama_divisi ?? '-' }}</td>
                                <td>{{ $project->start_date->format('d/m/Y') }}</td>
                                <td>{{ $project->end_date->format('d/m/Y') }}</td>
                                <td>
                                    @if($project->contracts->first())
                                        {{ Str::limit($project->contracts->first()->vendor->name_vendor ?? '-', 20) }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge-priority badge-{{ strtolower($project->priority) }}">
                                        {{ strtoupper($project->priority) }}
                                    </span>
                                </td>
                                <td>
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
                                    <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('projects.show', $project->project_id) }}"
                                           class="btn btn-sm btn-primary btn-custom"
                                           title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if(in_array(Auth::user()->roles, ['user', 'supply_chain']))
                                        <a href="{{ route('projects.edit', $project->project_id) }}"
                                           class="btn btn-sm btn-warning btn-custom"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
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
    </div>
</div>
@endsection

@push('scripts')
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
                <tr>
                    <td><strong>${p.code_project}</strong></td>
                    <td>${p.name_project.length > 40 ? p.name_project.substring(0,40) + '...' : p.name_project}</td>
                    <td>${p.owner_division}</td>
                    <td>${p.start_date ?? '-'}</td>
                    <td>${p.end_date ?? '-'}</td>
                    <td>${p.vendor ?? '-'}</td>
                    <td><span class="badge-priority badge-${(p.priority || '').toLowerCase()}">${(p.priority || '').toUpperCase()}</span></td>
                    <td><span class="badge bg-info">${(p.status_project || '').replace('_',' ')}</span></td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="/projects/${p.project_id}" class="btn btn-sm btn-primary btn-custom" title="Lihat Detail"><i class="bi bi-eye"></i></a>
                        </div>
                    </td>
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
@endpush
