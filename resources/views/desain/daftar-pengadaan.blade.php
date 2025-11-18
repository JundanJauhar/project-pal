    @extends('layouts.app')

    @section('title', 'Daftar Pengadaan - PT PAL Indonesia')

    @push('styles')
    <style>

        /* Card abu-abu besar */
        .big-card {
            background: #ebebeb;
            border-radius: 18px;
            padding: 40px 50px;
            min-height: 550px;
            box-shadow: 0 6px 12px rgba(0,0,0,0.12);
        }

        /* Search bar */
        .search-wrapper {
            width: 40%;
            /* margin: 0 auto 20px auto; */
            position: relative;
            justify-content: space-between;
            display: flex;
            margin-bottom: 25px;
        }

        .search-input {
            width: 100%;
            height: 38px;
            border-radius: 20px;
            border: none;
            padding: 0 45px 0 20px;
            background: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .search-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.6;
            cursor: pointer;
        }

        /* Table style */
        .request-table {
            width: 100%;
            margin-top: 25px;
            font-size: 15px;
        }

        .request-table th {
            font-weight: 600;
            color: #222;
            padding-bottom: 15px;
            border-bottom: 1px solid #858585;
        }

        .request-table td {
            padding: 12px 0;
            border-bottom: 1px solid #cfcfcf;
        }

        /* Dropdown */
        .filter-select {
            border-radius: 6px;
            padding: 4px 10px;
            border: 1px solid #bbb;
            background: white;
            font-size: 14px;
            width: 120px;
        }
        .tambah .btn{
        background: #003d82;
        border-color: #003d82;
    }

    </style>
    @endpush


    @section('content')

    <h2 class="fw-bold mb-4">Daftar Pengadaan</h2>
    
    
    <div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <form id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Cari Equipment..." value="">
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
                    <div class="tambah col-md-2 text-end">
                        
                        <a href="{{ route('procurements.create') }}" class="btn btn-primary w-100 btn-custom">
                            <i class="bi bi-plus-circle"></i> Tambah
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <div class="card big-card">
        {{-- TABLE HEADER --}}
        <table class="request-table">
            <thead>
                <tr>
                    <th style = "padding: 12px 8px; text-align: left;">Equipment</th>
                    <th style = "padding: 12px 8px; text-align: left;">Vendor</th>
                    <th style = "padding: 12px 8px; text-align: center;">Status</th>
                    <th style = "padding: 12px 8px; text-align: center;">Information</th>
                    <th style = "padding: 12px 8px; text-align: center;">Tanggal Pengadaan</th>
                    <th style = "padding: 12px 8px; text-align: center;">Tanggal Tenggat</th>
                </tr>
            </thead>

            <tbody>

                {{-- Loop semua procurement dari project --}}
                @forelse($project->procurements as $procurement)
                    {{-- Loop request procurement dari setiap procurement --}}
                    @foreach($procurement->requestProcurements as $req)
                        {{-- Loop items dari setiap request --}}
                        @forelse($req->items as $item)
                        <tr>
                            <td style = "padding: 12px 8px; text-align: left;">
                                <a href="{{ route('desain.review-evatek', $req->request_id) }}"
                                style="text-decoration: none; color: #000; font-weight: 600;">
                                    {{ $item->item_name }}
                                </a>
                                <div style="font-size: 12px; color: #666;">
                                    {{ $item->amount }} {{ $item->unit }}
                                </div>
                            </td>

                            <td style = "padding: 12px 8px; text-align: left;">{{ $req->vendor->name_vendor ?? '-' }}</td>
                            <td style = "padding: 12px 8px; text-align: center;">
                                @php
                                    $statusMap = [
                                        'on_progress' => ['On Progress', '#ECAD02'],
                                        'cancelled' => ['Rejected', '#F10303'],
                                        'completed' => ['Completed', '#28AC00'],
                                    ];
                                    [$statusText, $color] = $statusMap[$req->request_status] ?? [ucfirst($req->request_status), '#6c757d'];
                                @endphp
                                <span style="background: {{ $color }}; color: white; padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td style = "padding: 12px 8px; text-align: center;">
                                <div style="font-size: 13px;">{{ $procurement->code_procurement }}</div>
                                <div style="font-size: 11px; color: #666;">{{ $req->request_name }}</div>
                            </td>
                            <td style = "padding: 12px 8px; text-align: center;">{{ \Carbon\Carbon::parse($req->created_date)->format('d/m/Y') }}</td>
                            <td style = "padding: 12px 8px; text-align: center;">
                                @php
                                    $deadline = \Carbon\Carbon::parse($req->deadline_date);
                                    $now = \Carbon\Carbon::now();
                                    $isLate = $deadline->isPast() && $req->request_status !== 'completed';
                                @endphp
                                <span style="color: {{ $isLate ? '#dc3545' : '#000' }}; font-weight: {{ $isLate ? '600' : '400' }};">
                                    {{ $deadline->format('d/m/Y') }}
                                    @if($isLate)
                                        <small style="display: block; font-size: 10px;">⚠️ Terlambat</small>
                                    @endif
                                </span>
                            </td>
                        </tr>
                        @empty
                        @endforelse
                    @endforeach
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5">Belum ada permintaan atau item untuk project ini.</td>
                </tr>
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

    const $statusMap = [
    'on_progress' => ['On Progress', '#ECAD02'],
    'cancelled' => ['Rejected', '#F10303'],
    'completed' => ['Completed', '#28AC00'],
    ];

    function renderRows(items) {
        if (!Array.isArray(items) || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-2">Tidak ada data item</p>
                    </td>
                </tr>`;
            paginationWrap.innerHTML = "";
            return;
        }

        tbody.innerHTML = items.map(p => {
            const [statusText, badgeColor] =
                statusMap[p.request_status] ?? [p.request_status ?? '-', "#ECAD02"];

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