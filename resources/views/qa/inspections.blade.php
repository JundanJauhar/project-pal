@extends('layouts.app')

@section('title', 'Quality Assurance - Inspeksi')

@push('styles')
<style>
/* ===== DASHBOARD CARDS ===== */
.qa-topcards {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
    margin-top: 10px;
}
.qa-card {
    flex: 1;
    padding: 18px 20px;
    border-radius: 12px;
    background: #F4F4F4;
    border: 1px solid #E0E0E0;
}
.qa-card-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.qa-card h6 { color: #676767; font-size: 14px; margin-bottom: 6px; }
.qa-card h3 { font-weight: 700; font-size: 30px; margin: 0; }

.qa-card.blue   { border-left: 5px solid #1E90FF; }
.qa-card.yellow { border-left: 5px solid #F2C94C; }
.qa-card.green  { border-left: 5px solid #27AE60; }
.qa-card.red    { border-left: 5px solid #EB5757; }
.qa-card.gray   { border-left: 5px solid #9E9E9E; }

.qa-card-icon {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
}

/* ===== TABLE WRAPPER ===== */
.qa-table-wrapper {
    padding: 25px;
    border-radius: 14px;
    background: #fff;
    box-shadow: 0 8px 12px rgba(0,0,0,0.12);
    margin-top: 30px;
}

/* ===== HEADER BAR ===== */
.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
}

.qa-table-title {
    font-size: 26px;
    font-weight: 700;
    margin: 0;
}

/* ===== FILTER BAR RIGHT ===== */
.filter-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.search-input-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #F4F4F4;
    border: 1px solid #E0E0E0;
    padding: 10px 16px;
    border-radius: 10px;
    width: 260px;
}

.search-input-box input {
    border: none;
    background: transparent;
    width: 100%;
    outline: none;
    font-size: 14px;
}

.filter-pill {
    padding: 10px 16px;
    background: #F4F4F4;
    border: 1px solid #E0E0E0;
    border-radius: 10px;
}

.filter-pill select {
    border: none;
    background: transparent;
    font-size: 14px;
    outline: none;
}

/* ===== TABLE ===== */
.qa-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.qa-table thead th {
    padding: 14px;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
    color: #333;
    border-bottom: 2px solid #DADADA;
}

.qa-table tbody td {
    padding: 16px 10px;
    font-size: 15px;
    text-align: center;
    border-bottom: 1px solid #EFEFEF;
}

.qa-table tbody tr:hover {
    background: #F6F6F6;
}

/* PRIORITY COLORS */
.priority-high { color: #D60000; font-weight: bold; }
.priority-medium { color: #FF8C00; font-weight: bold; }
.priority-low { color: #A9A9A9; font-weight: bold; }

/* STATUS COLORS */
.status-pass { color: #27AE60; font-weight: bold; }
.status-fail { color: #D60000; font-weight: bold; }
.status-wait { color: #888; font-weight: bold; }
.status-inprogress { color: #1E90FF; font-weight: bold; }

.status-link { text-decoration: none; }

/* Responsive */
@media(max-width:1100px) {
    .table-header { flex-wrap: wrap; gap: 12px; }
    .filter-right { flex-wrap: wrap; justify-content: flex-end; }
}
</style>
@endpush



@section('content')

{{-- ===== CARDS ===== --}}
<div class="qa-topcards">
    <div class="qa-card gray">
        <div class="qa-card-inner"><div><h6>Total Pengadaan</h6><h3>{{ $total }}</h3></div><div class="qa-card-icon"><i class="bi bi-clipboard2-data"></i></div></div>
    </div>
    <div class="qa-card yellow">
        <div class="qa-card-inner"><div><h6>Butuh Inspeksi</h6><h3>{{ $butuh }}</h3></div><div class="qa-card-icon"><i class="bi bi-hourglass-split"></i></div></div>
    </div>
    <div class="qa-card green">
        <div class="qa-card-inner"><div><h6>Lolos Inspeksi</h6><h3>{{ $lolos }}</h3></div><div class="qa-card-icon"><i class="bi bi-check-circle-fill"></i></div></div>
    </div>
    <div class="qa-card blue">
        <div class="qa-card-inner"><div><h6>Sedang Proses</h6><h3>{{ $sedang }}</h3></div><div class="qa-card-icon"><i class="bi bi-arrow-repeat"></i></div></div>
    </div>
    <div class="qa-card red">
        <div class="qa-card-inner"><div><h6>Tidak Lolos</h6><h3>{{ $gagal }}</h3></div><div class="qa-card-icon"><i class="bi bi-x-circle-fill"></i></div></div>
    </div>
</div>


{{-- ===== TABLE WRAPPER ===== --}}
<div class="qa-table-wrapper">

    <div class="table-header">
        <div class="qa-table-title">Daftar Pengadaan</div>

        <form method="GET" id="filterForm">
            <div class="filter-right">

                {{-- SEARCH (multi-column) --}}
                <div class="search-input-box">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari Pengadaan / Vendor / Dept / Project / Kode..." />
                </div>

                {{-- PRIORITAS --}}
                <div class="filter-pill">
                    <select name="priority" onchange="submitFilters()">
                        <option value="">Semua Prioritas</option>
                        <option value="tinggi" {{ request('priority')=='tinggi'?'selected':'' }}>Tinggi</option>
                        <option value="sedang" {{ request('priority')=='sedang'?'selected':'' }}>Sedang</option>
                        <option value="rendah" {{ request('priority')=='rendah'?'selected':'' }}>Rendah</option>
                    </select>
                </div>

                {{-- STATUS --}}
                <div class="filter-pill">
                    <select name="result" onchange="submitFilters()">
                        <option value="">Semua Status</option>
                        <option value="not_inspected" {{ request('result')=='not_inspected'?'selected':'' }}>Belum Diinspeksi</option>
                        <option value="in_progress" {{ request('result')=='in_progress'?'selected':'' }}>Sedang Proses</option>
                        <option value="passed" {{ request('result')=='passed'?'selected':'' }}>Lolos</option>
                        <option value="failed" {{ request('result')=='failed'?'selected':'' }}>Tidak Lolos</option>
                    </select>
                </div>

            </div>
        </form>
    </div>


    <div class="table-responsive">
        <table class="qa-table" id="qaTable">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Kode Pengadaan</th>
                    <th>Nama Pengadaan</th>
                    <th>Department</th>
                    <th>Vendor</th>
                    <th>Tgl Mulai</th>
                    <th>Tgl Selesai</th>
                    <th>Prioritas</th>
                    <th>Status Inspeksi</th>
                </tr>
            </thead>

            <tbody>
            @foreach($procurements as $proc)

                @php
                    $items = $proc->requestProcurements->flatMap->items ?? collect();
                    $totalItems = $items->count();

                    if ($totalItems === 0) {
                        $statusText = "BELUM DIINSPEKSI";
                        $statusClass = "status-wait";
                    } else {
                        $latestResults = $items->map(fn($it)=>$it->inspectionReports->sortByDesc('inspection_date')->first()?->result);
                        $inspectedCount = $latestResults->filter()->count();

                        if ($inspectedCount === 0) {
                            $statusText = "BELUM DIINSPEKSI";
                            $statusClass = "status-wait";
                        } elseif ($inspectedCount < $totalItems) {
                            $statusText = "SEDANG PROSES";
                            $statusClass = "status-inprogress";
                        } else {
                            if ($latestResults->every(fn($r)=>$r=="passed")) {
                                $statusText = "LOLOS";
                                $statusClass = "status-pass";
                            } elseif ($latestResults->every(fn($r)=>$r=="failed")) {
                                $statusText = "TIDAK LOLOS";
                                $statusClass = "status-fail";
                            } else {
                                $statusText = "SEDANG PROSES";
                                $statusClass = "status-inprogress";
                            }
                        }
                    }
                @endphp

                <tr class="table-row">
                    <td data-col="project"><strong>{{ $proc->project->project_code ?? '-' }}</strong></td>
                    <td data-col="kode">{{ $proc->code_procurement }}</td>
                    <td data-col="nama">{{ $proc->name_procurement }}</td>
                    <td data-col="department">{{ $proc->department->department_name }}</td>
                    <td data-col="vendor">{{ $proc->requestProcurements->first()?->vendor->name_vendor ?? '-' }}</td>
                    <td data-col="mulai">{{ $proc->start_date?->format('d/m/Y') }}</td>
                    <td data-col="selesai">{{ $proc->end_date?->format('d/m/Y') }}</td>

                    <td data-col="prioritas">
                        @php
                            $p = strtolower($proc->priority);
                            $cls = $p=='tinggi'?'priority-high':($p=='sedang'?'priority-medium':'priority-low');
                        @endphp
                        <span class="{{ $cls }}">{{ strtoupper($proc->priority) }}</span>
                    </td>

                    <td data-col="status">
                        <a href="{{ route('qa.detail-approval', ['procurement_id'=>$proc->procurement_id]) }}"
                           class="status-link {{ $statusClass }}">
                           {{ $statusText }}
                        </a>
                    </td>
                </tr>

            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $procurements->links() }}
    </div>

</div>



@push('scripts')
<script>
function submitFilters() {
    document.getElementById("filterForm").submit();
}

/* ===== MULTI-COLUMN CLIENT-SIDE SEARCH ===== */
document.getElementById("searchInput").addEventListener("input", function () {
    const term = this.value.toLowerCase();

    document.querySelectorAll(".table-row").forEach(row => {
        const rowText =
            (row.querySelector('[data-col="project"]')?.innerText.toLowerCase() ?? '') +
            (row.querySelector('[data-col="kode"]')?.innerText.toLowerCase() ?? '') +
            (row.querySelector('[data-col="nama"]')?.innerText.toLowerCase() ?? '') +
            (row.querySelector('[data-col="department"]')?.innerText.toLowerCase() ?? '') +
            (row.querySelector('[data-col="vendor"]')?.innerText.toLowerCase() ?? '') +
            (row.querySelector('[data-col="prioritas"]')?.innerText.toLowerCase() ?? '') +
            (row.querySelector('[data-col="status"]')?.innerText.toLowerCase() ?? '');

        row.style.display = rowText.includes(term) ? "" : "none";
    });
});
</script>
@endpush

@endsection