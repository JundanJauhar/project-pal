@extends('layouts.app')

@section('title', 'Procurements - PT PAL Indonesia')

@section('content')

<style>
    .tambah .btn{
        background: #003d82;
        border-color: #003d82;
    }

</style>


<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-folder-fill"></i> Daftar Procurements</h2>
    </div>
</div>

<!-- Filter and Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <form id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Cari procurement..." value="">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="reviewed">Reviewed</option>
                            <option value="approved">Approved</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
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

                        <a href="{{ route('procurements.create') }}" class="btn btn-primary w-100 btn-custom" wire:navigate>
                            <i class="bi bi-plus-circle"></i> Tambah
                        </a>
                    </div> -->
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
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Kode Procurement</th>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Procurement</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Mulai</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Selesai</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                        </tr>
                    </thead>
                    <tbody id="procurements-tbody">
                        @forelse($procurements as $procurement)
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 12px 8px;"><strong>{{ $procurement->code_procurement }}</strong></td>
                            <td style="padding: 12px 8px;">{{ Str::limit($procurement->name_procurement, 40) }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $procurement->department ? $procurement->department->department_name : '-' }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $procurement->start_date ? $procurement->start_date->format('d/m/Y') : '-' }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $procurement->end_date ? $procurement->end_date->format('d/m/Y') : '-' }}</td>
                            <td style="padding: 12px 8px; text-align: center;">
                                @php
                                    $firstRequest = $procurement->requestProcurements ? $procurement->requestProcurements->first() : null;
                                    $vendor = ($firstRequest && $firstRequest->vendor) ? $firstRequest->vendor->name_vendor : '-';
                                @endphp
                                <span class="vendor-pill vendor-status-neutral">{{ Str::limit($vendor, 20) }}</span>
                            </td>
                            <td style="padding: 12px 8px; text-align: center;">
                                <span class="badge-priority badge-{{ strtolower($procurement->priority) }}">
                                    {{ strtoupper($procurement->priority) }}
                                </span>
                            </td>
                            <td style="padding: 12px 8px; text-align: center;">
                            @php
                                $statusMap = [
                                    'draft'     => ['Draft', '#555555'],
                                    'submitted' => ['Submitted', '#ECAD02'],
                                    'reviewed'  => ['Reviewed', '#ECAD02'],
                                    'approved'  => ['Approved', '#ECAD02'],
                                    'rejected'  => ['Rejected', '#BD0000'],
                                    'in_progress' => ['Sedang Diproses', '#ECAD02'],
                                    'completed' => ['Completed', '#28AC00'],
                                    'cancelled' => ['Cancelled', '#555555'],
                                ];

                                [$statusText, $badgeColor] = $statusMap[$procurement->status_procurement] ?? [ucfirst($procurement->status_procurement), '#ECAD02'];
                            @endphp

                            <span class="status-badge"
                                style="background-color: {{ $badgeColor }} !important; color:white; padding:6px 12px; font-weight:600; border-radius:6px;">
                                {{ $statusText }}
                            </span>


                        </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                <p class="text-muted mt-2">Tidak ada data procurement</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                <div id="procurements-pagination">
                    {{ $procurements->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function debounce(fn, delay) {
    let t;
    return function () {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, arguments), delay);
    };
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('input[name="search"]');
    const statusSelect = document.querySelector('select[name="status"]');
    const prioritySelect = document.querySelector('select[name="priority"]');
    const tbody = document.getElementById('procurements-tbody');
    const paginationWrap = document.getElementById('procurements-pagination');

    console.log('DOMContentLoaded - Elements loaded:', { searchInput, statusSelect, prioritySelect, tbody, paginationWrap });

    if (!searchInput || !statusSelect || !prioritySelect || !tbody || !paginationWrap) {
        console.error('Missing required elements');
        return;
    }

    let currentPage = 1;
    let lastPagination = null;

    const statusMap = {
        draft: ['Draft', '#555555'],
        submitted: ['Submitted', '#ECAD02'],
        reviewed: ['Reviewed', '#ECAD02'],
        approved: ['Approved', '#ECAD02'],
        rejected: ['Rejected', '#BD0000'],
        in_progress: ['Sedang Diproses', '#ECAD02'],
        completed: ['Completed', '#28AC00'],
        cancelled: ['Cancelled', '#555555'],
    };

    function renderRows(items) {
        if (!Array.isArray(items) || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-2">Tidak ada data procurement</p>
                    </td>
                </tr>`;
            paginationWrap.innerHTML = "";
            return;
        }

        tbody.innerHTML = items.map(p => {
            const [statusText, badgeColor] =
                statusMap[p.status_procurement] ?? [p.status_procurement ?? '-', "#ECAD02"];

            return `
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 12px 8px;"><strong>${p.code_procurement}</strong></td>
                <td style="padding: 12px 8px;">${p.name_procurement?.substring(0, 40) ?? '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">${p.department_name ?? '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">${p.start_date ?? '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">${p.end_date ?? '-'}</td>
                <td style="padding: 12px 8px; text-align: center;">-</td>
                <td style="padding: 12px 8px; text-align: center;">
                    <span class="badge-priority badge-${p.priority?.toLowerCase() ?? ''}">
                        ${p.priority?.toUpperCase() ?? '-'}
                    </span>
                </td>
                <td style="padding: 12px 8px; text-align: center;">
                    <span class="status-badge"
                        style="background-color:${badgeColor} !important; color:white; padding:6px 12px; font-weight:600; border-radius:6px;">
                        ${statusText}
                    </span>
                </td>
            </tr>`;
        }).join("");

        renderPagination();
    }

    function renderPagination() {
        if (!lastPagination) {
            paginationWrap.innerHTML = '';
            return;
        }

        const p = lastPagination;
        let html = `<nav><ul class="pagination">`;

        html += p.current_page > 1
            ? `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${p.current_page - 1})">← Sebelumnya</a></li>`
            : `<li class="page-item disabled"><span class="page-link">← Sebelumnya</span></li>`;

        for (let i = 1; i <= p.last_page; i++) {
            html += i === p.current_page
                ? `<li class="page-item active"><span class="page-link">${i}</span></li>`
                : `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${i})">${i}</a></li>`;
        }

        html += p.has_more
            ? `<li class="page-item"><a class="page-link" href="#" onclick="goToPage(${p.current_page + 1})">Berikutnya →</a></li>`
            : `<li class="page-item disabled"><span class="page-link">Berikutnya →</span></li>`;

        html += `</ul></nav>`;
        paginationWrap.innerHTML = html;
    }

    window.goToPage = function (page) {
        currentPage = page;
        fetchProjects();
    };

    function fetchProjects() {
        const q = encodeURIComponent(searchInput.value.trim());
        const status = encodeURIComponent(statusSelect.value);
        const priority = encodeURIComponent(prioritySelect.value);

        const url = `{{ route('procurements.search') }}?q=${q}&status=${status}&priority=${priority}&page=${currentPage}`;
        console.log("Fetching from URL:", url, { q: searchInput.value, status: statusSelect.value, priority: prioritySelect.value });

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => {
                console.log("Response status:", res.status);
                return res.json();
            })
            .then(res => {
                console.log("Response data:", res);
                lastPagination = res.pagination;
                renderRows(res.data);
            })
            .catch(err => {
                console.error("Search error:", err);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-exclamation-circle" style="font-size: 48px; color: #f00;"></i>
                            <p class="text-danger mt-2">Terjadi kesalahan: ${err.message}</p>
                        </td>
                    </tr>`;
            });
    }

    const debouncedFetch = debounce(() => {
        currentPage = 1;
        fetchProjects();
    }, 300);

    searchInput.addEventListener('input', debouncedFetch);
    statusSelect.addEventListener('change', debouncedFetch);
    prioritySelect.addEventListener('change', debouncedFetch);
});
</script>
@endpush
