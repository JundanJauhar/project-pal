@extends('layouts.app')

@section('title', 'Projects - PT PAL Indonesia')

@section('content')

<style>
    .page-shell {
        min-height: 100%;
        padding: 40px 0 60px;
        background: linear-gradient(180deg, #f4f4f4 0%, #f8f8f8 40%, #ebebeb 100%);
    }
    .projects-wrap {
        background: #EBEBEB;
        padding: 32px 44px;
        border-radius: 20px;
        box-shadow:
            0 22px 45px rgba(0, 0, 0, 0.12),
            0 12px 20px rgba(0, 0, 0, 0.08);
    }
    .projects-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 32px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .projects-title {
        font-weight: 800;
        font-size: 32px;
        margin: 0;
        color: #202020;
    }
    .projects-search {
        max-width: 520px;
        margin: 0;
        position: relative;
        width: 100%;
    }
    .projects-search input {
        border-radius: 24px;
        padding: 10px 42px 10px 18px;
        border: 1px solid #d3d3d3;
        width: 100%;
        outline: none;
        background: #f2f2f2;
        box-shadow: inset 0 3px 6px rgba(0,0,0,0.12);
    }
    .projects-search .bi-search {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c6c6c;
    }

    /* Table styles */
    .table-clean { width: 100%; border-collapse: collapse; }
    .table-clean thead th {
        font-weight: 700;
        color: #1f1f1f;
        padding: 18px 12px;
        text-align: center;
        font-size: 17px;
        border-bottom: 2.2px solid #1f1f1f;
    }
    .table-clean thead th:nth-child(1),
    .table-clean tbody td:nth-child(1) { width: 19%; text-align: left; }
    .table-clean thead th:nth-child(2),
    .table-clean tbody td:nth-child(2) { width: 32%; text-align: left; }
    .table-clean thead th:nth-child(3),
    .table-clean tbody td:nth-child(3) { width: 18%; }
    .table-clean thead th:nth-child(4),
    .table-clean tbody td:nth-child(4),
    .table-clean thead th:nth-child(5),
    .table-clean tbody td:nth-child(5),
    .table-clean thead th:nth-child(6),
    .table-clean tbody td:nth-child(6) { width: 10%; }
    .table-clean tbody tr { border-bottom: 1.6px solid #cecece; }
    .table-clean tbody td {
        padding: 24px 12px;
        vertical-align: middle;
        font-size: 16px;
        text-align: center;
        color: #2f2f2f;
    }
    .table-clean tbody td:first-child,
    .table-clean tbody td:nth-child(2) {
        text-align: left;
    }
    .row-divider {
        height: 1px;
        background: #d8d8d8;
    }
</style>

<div class="page-shell">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="projects-wrap">
                <div class="projects-header">
                    <h2 class="projects-title">Daftar Project</h2>
                    <div class="projects-search">
                        <input type="text" id="search-projects" placeholder="Cari project...">
                        <i class="bi bi-search"></i>
                    </div>
                </div>
                <div class="row-divider mb-3"></div>

                <div class="table-responsive">
                    <table class="table-clean" id="projects-table">
                        <thead>
                            <tr>
                                <th>Nama Project</th>
                                <th>Deskripsi Pengadaan</th>
                                <th>Department</th>
                                <th>Tanggal Pengadaan</th>
                                <th>Tanggal Target</th>
                                <th>Tanggal Keluar</th>
                            </tr>
                        </thead>
                        <tbody id="projects-tbody">
                            @forelse($projects as $project)
                            <tr>
                                <td><strong>{{ $project->name_project }}</strong></td>
                                <td>{{ Str::limit($project->description ?? '-', 90) }}</td>
                                <td>{{ $project->ownerDivision->nama_divisi ?? '-' }}</td>
                                <td>{{ optional($project->start_date)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ optional($project->end_date)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ optional($project->exit_date ?? null)->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-2">Tidak ada data project</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($projects, 'links'))
                <div class="mt-3">
                    <div id="projects-pagination">
                        {{ $projects->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('input[name="search"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const prioritySelect = document.querySelector('select[name="priority"]');
        const tbody = document.getElementById('projects-tbody');
        const paginationWrap = document.getElementById('projects-pagination');

        let currentPage = 1;
        let lastPagination = null;
    }
        function renderRows(items) {
    const statusMap = {
        draft: ['Draft', '#555555'],
        completed: ['Completed', '#28AC00'],
        selesai: ['Selesai', '#28AC00'],
        decline: ['Declined', '#BD0000'],
        ditolak: ['Ditolak', '#BD0000'],
        rejected: ['Rejected', '#BD0000'],
        review_sc: ['Review SC', '#ECAD02'],
        persetujuan_sekretaris: ['Persetujuan Sekdir', '#ECAD02'],
        pemilihan_vendor: ['Pemilihan Vendor', '#ECAD02'],
        pengecekan_legalitas: ['Pengecekan Legalitas', '#ECAD02'],
        pemesanan: ['Pemesanan', '#ECAD02'],
        pembayaran: ['Pembayaran', '#ECAD02'],
        in_progress: ['Sedang Diproses', '#ECAD02'],
        ongoing: ['Sedang Berjalan', '#ECAD02'],
        proses: ['Dalam Proses', '#ECAD02'],
    };

    if (!Array.isArray(items) || items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-4">
                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                    <p class="text-muted mt-2">Tidak ada data project</p>
                </td>
            </tr>`;
        paginationWrap.innerHTML = "";
        return;
    }

    tbody.innerHTML = items.map(p => {
        const [statusText, badgeColor] = statusMap[p.status_project] ?? [
            p.status_project?.replace(/_/g, " ").toUpperCase(),
            "#ECAD02"
        ];

        return `
        <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 12px 8px;"><strong>${p.code_project}</strong></td>
            <td style="padding: 12px 8px;">${p.name_project.substring(0, 40)}</td>
            <td style="padding: 12px 8px; text-align: center;">${p.owner_division}</td>
            <td style="padding: 12px 8px; text-align: center;">${p.start_date ?? "-"}</td>
            <td style="padding: 12px 8px; text-align: center;">${p.end_date ?? "-"}</td>
            <td style="padding: 12px 8px;">${p.vendor ?? "-"}</td>
            <td style="padding: 12px 8px; text-align: center;">
                <span class="badge-priority badge-${(p.priority || "").toLowerCase()}">
                    ${(p.priority || "").toUpperCase()}
                </span>
            </td>
            <td style="padding: 12px 8px; text-align: center;">
                <span class="status-badge" style="background-color: ${badgeColor} !important; color: white !important; padding: 6px 12px !important; font-weight: 600 !important; border-radius: 6px !important;">
                    ${statusText}
                </span>
            </td>
        </tr>`;
    }).join("");

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
        });
    });
</script>
@endpush
