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
.qa-card-inner{
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.qa-card h6 { color: #676767; font-size: 14px; margin-bottom: 6px; }
.qa-card h3 { font-weight: 700; font-size: 30px; margin:0; }

.qa-card.blue   { border-left: 5px solid #1E90FF; }
.qa-card.yellow { border-left: 5px solid #F2C94C; }
.qa-card.green  { border-left: 5px solid #27AE60; }
.qa-card.red    { border-left: 5px solid #EB5757; }
.qa-card.gray   { border-left: 5px solid #9E9E9E; }

.qa-card-icon{
    width:42px;
    height:42px;
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#ffffff;
    box-shadow:0 2px 4px rgba(0,0,0,0.08);
    font-size:20px;
    color:#555;
}

/* ===== TABLE WRAPPER ===== */
.qa-table-wrapper {
    padding: 25px;
    border-radius: 14px;
    /* border: 1px solid #E0E0E0; */
    box-shadow: 0 8px 12px rgba(0,0,0,0.12);
    background: #FFFFFF;
    margin-top: 60px;
}

/* Title */
.qa-table-title {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Search + Filters container */
.filters-wrap {
    display:flex;
    gap:12px;
    align-items:center;
}

/* Search */
.qa-search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #F0F0F0;
    border-radius: 25px;
    padding: 6px 12px;
    width: 240px;
    border: 1px solid #ddd;
    font-size:14px;
}
.qa-search-box input {
    border: none;
    background: transparent;
    width: 100%;
    outline: none;
    font-size:14px;
}
.qa-search-box i { font-size: 14px; color: #777; }

/* Filter selects */
.filter-select {
    background: #fff;
    border: 1px solid #ddd;
    padding:6px 10px;
    border-radius:8px;
    font-size:14px;
}

/* ===== TABLE STYLE ===== */
.qa-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.qa-table thead th {
    padding: 14px 6px;
    border-bottom: 2px solid #C9C9C9;
    font-size: 14px;
    text-transform: uppercase;
    color: #555;
    text-align: center;
}

.qa-table tbody td {
    padding: 14px 6px;
    border-bottom: 1px solid #DFDFDF;
    font-size: 15px;
    color: #333;
    text-align: center;
}

.qa-table tbody tr:hover {
    background: #EFEFEF;
}

/* PRIORITY COLORS */
.priority-high   { color: #D60000; font-weight: bold; }
.priority-medium { color: #FF8C00; font-weight: bold; }
.priority-low    { color: #A9A9A9; font-weight: bold; }

/* INSPECTION STATUS */
.status-pass       { color: #27AE60; font-weight: bold; }
.status-fail       { color: #D60000; font-weight: bold; }
.status-wait       { color: #888;    font-weight: bold; }
.status-inprogress { color: #1E90FF; font-weight: bold; }

.status-link { text-decoration: none; font-weight: bold; }

/* responsive tweaks */
@media (max-width: 1100px) {
    .qa-topcards { flex-wrap: wrap; }
}
@media (max-width: 900px) {
    .qa-topcards { flex-direction: column; }
    .filters-wrap { flex-direction: column; align-items:flex-end; gap:8px; }
}
</style>
@endpush

@section('content')

{{-- ===== TOP CARDS ===== --}}
<div class="qa-topcards">

    {{-- Total Pengadaan --}}
    <div class="qa-card gray">
        <div class="qa-card-inner">
            <div>
                <h6>Total Pengadaan</h6>
                <h3>{{ $total ?? $totalProcurements ?? 0 }}</h3>
            </div>
            <div class="qa-card-icon">
                <i class="bi bi-clipboard2-data"></i>
            </div>
        </div>
    </div>

    {{-- Butuh Inspeksi --}}
    <div class="qa-card yellow">
        <div class="qa-card-inner">
            <div>
                <h6>Butuh Inspeksi</h6>
                <h3>{{ $butuh ?? $butuhInspeksiCount ?? 0 }}</h3>
            </div>
            <div class="qa-card-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
    </div>

    {{-- Lolos Inspeksi --}}
    <div class="qa-card green">
        <div class="qa-card-inner">
            <div>
                <h6>Lolos Inspeksi</h6>
                <h3>{{ $lolos ?? $lolosCount ?? 0 }}</h3>
            </div>
            <div class="qa-card-icon">
                <i class="bi bi-check-circle-fill"></i>
            </div>
        </div>
    </div>

    {{-- Sedang Proses Inspeksi --}}
    <div class="qa-card blue">
        <div class="qa-card-inner">
            <div>
                <h6>Sedang Proses Inspeksi</h6>
                <h3>{{ $sedang ?? $sedangProsesCount ?? 0 }}</h3>
            </div>
            <div class="qa-card-icon">
                <i class="bi bi-arrow-repeat"></i>
            </div>
        </div>
    </div>

    {{-- Tidak Lolos Inspeksi --}}
    <div class="qa-card red">
        <div class="qa-card-inner">
            <div>
                <h6>Tidak Lolos Inspeksi</h6>
                <h3>{{ $gagal ?? $gagalCount ?? 0 }}</h3>
            </div>
            <div class="qa-card-icon">
                <i class="bi bi-x-circle-fill"></i>
            </div>
        </div>
    </div>

</div>

{{-- ===== TABLE ===== --}}
<div class="qa-table-wrapper">

    <div class="qa-table-title">
        <span>Daftar Pengadaan</span>

        <!-- <form method="GET" style="margin:0;">
            <div class="filters-wrap">
                {{-- Search live (client-side) --}}
                <div class="qa-search-box" title="Cari project atau nama pengadaan">
                    <input type="text" id="searchInput" name="q" value="{{ request('q') }}" placeholder="Cari..." />
                    <i class="bi bi-search"></i>
                </div>

                {{-- Prioritas --}}
                <select name="priority" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Prioritas</option>
                    <option value="tinggi" {{ request('priority') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                    <option value="sedang" {{ request('priority') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="rendah" {{ request('priority') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                </select>

                {{-- Status Inspeksi --}}
                <select name="result" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Hasil Inspeksi</option>
                    <option value="not_inspected" {{ request('result') === 'not_inspected' ? 'selected' : '' }}>Butuh Inspeksi</option>
                    <option value="in_progress"   {{ request('result') === 'in_progress'   ? 'selected' : '' }}>Sedang Proses</option>
                    <option value="passed"        {{ request('result') === 'passed'        ? 'selected' : '' }}>Lolos</option>
                    <option value="failed"        {{ request('result') === 'failed'        ? 'selected' : '' }}>Tidak Lolos</option>
                </select>
            </div>
        </form> -->
    </div>

    <div class="table-responsive">
        <table class="qa-table">
            <thead>
                <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Project</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nama Pengadaan</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tgl Mulai</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tgl Selesai</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status Inspeksi</th>
                </tr>
            </thead>

            <tbody>
            @forelse($procurements as $proc)

                @php
                    $items = $proc->requestProcurements->flatMap->items ?? collect();
                    $totalItems = $items->count();

                    if ($totalItems === 0) {
                        $statusText = "BELUM DIINSPEKSI";
                        $statusClass = "status-wait";
                    } else {
                        $latestResults = $items->map(function($it){
                            $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
                            return $latest?->result ?? null;
                        });

                        $inspectedCount = $latestResults->filter(fn($r)=>!is_null($r))->count();

                        if ($inspectedCount === 0) {
                            $statusText = "BELUM DIINSPEKSI";
                            $statusClass = "status-wait";
                        } elseif ($inspectedCount < $totalItems) {
                            $statusText = "SEDANG PROSES";
                            $statusClass = "status-inprogress";
                        } else {
                            if ($latestResults->every(fn($r)=>$r === 'passed')) {
                                $statusText = "LOLOS";
                                $statusClass = "status-pass";
                            } elseif ($latestResults->every(fn($r)=>$r === 'failed')) {
                                $statusText = "TIDAK LOLOS";
                                $statusClass = "status-fail";
                            } else {
                                $statusText = "SEDANG PROSES";
                                $statusClass = "status-inprogress";
                            }
                        }
                    }
                @endphp

                <tr>
                    {{-- Project --}}
                    <td style="padding: 12px 8px; text-align: center;">
                        <strong>{{ optional($proc->project)->project_code ?? '-' }}</strong>
                    </td>
                    <td style="padding: 12px 8px; text-align: center;">
                        <strong>{{ $proc->code_procurement ?? '-' }}</strong>
                    </td>
                    {{-- Nama Pengadaan --}}
                    <td style="padding: 12px 8px; text-align: center;">
                        {{ $proc->name_procurement }}
                    </td>

                    <td style="padding: 12px 8px; text-align: center;">{{ $proc->department->department_name ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center;">{{ $proc->requestProcurements->first()?->vendor->name_vendor ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center;">{{ $proc->start_date?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center;">{{ $proc->end_date?->format('d/m/Y') ?? '-' }}</td>

                    <td style="padding: 12px 8px; text-align: center;">
                        @php
                            $p = strtolower($proc->priority ?? '');
                            $class = match($p) {
                                'tinggi' => 'priority-high',
                                'sedang' => 'priority-medium',
                                'rendah' => 'priority-low',
                                default  => '',
                            };
                        @endphp
                        <span class="{{ $class }}">{{ strtoupper($proc->priority ?? '-') }}</span>
                    </td>

                    <td style="padding: 12px 8px; text-align: center;">
                        <a href="{{ route('qa.detail-approval', ['procurement_id' => $proc->procurement_id]) }}"
                           class="status-link {{ $statusClass }}">
                            {{ $statusText }}
                        </a>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size:40px; color:#bbb;"></i>
                        <p class="text-muted mt-2">Tidak ada pengadaan yang sesuai filter</p>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $procurements->links() }}
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ==== LIVE SEARCH (client-side) ====
    const searchInput = document.getElementById('searchInput');
    const rows = Array.from(document.querySelectorAll('.qa-table tbody tr'));

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();

            rows.forEach(row => {
                const projectCell = row.querySelector('[data-col="project"]');
                const nameCell    = row.querySelector('[data-col="name"]');

                const projectText = projectCell ? projectCell.textContent.toLowerCase() : '';
                const nameText    = nameCell ? nameCell.textContent.toLowerCase() : '';

                if (projectText.includes(term) || nameText.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // === Hapus mekanisme localStorage lama (kalau masih ada data sisa) ===
    localStorage.removeItem('inspectionUpdate');
    });
</script>
@endpush

@endsection
