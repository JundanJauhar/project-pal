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
    padding: 22px;
    border-radius: 12px;
    background: #F4F4F4;
    border: 1px solid #E0E0E0;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.qa-card h6 { color: #676767; font-size: 14px; margin-bottom: 5px; }
.qa-card h3 { font-weight: 700; font-size: 32px; }

.qa-card.blue   { border-left: 5px solid #1E90FF; }
.qa-card.yellow { border-left: 5px solid #F2C94C; }
.qa-card.green  { border-left: 5px solid #27AE60; }
.qa-card.red    { border-left: 5px solid #EB5757; }

/* ===== TABLE WRAPPER ===== */
.qa-table-wrapper {
    background: #F6F6F6;
    padding: 25px;
    border-radius: 14px;
    border: 1px solid #E0E0E0;
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
.status-pass  { color: #27AE60; font-weight: bold; }
.status-fail  { color: #D60000; font-weight: bold; }
.status-wait  { color: #888; font-weight: bold; }
.status-inprogress { color: #1E90FF; font-weight: bold; }

.status-link { text-decoration: none; font-weight: bold; }

/* responsive tweaks */
@media (max-width: 900px) {
    .qa-topcards { flex-direction: column; }
    .filters-wrap { flex-direction: column; align-items:flex-end; gap:8px; }
}
</style>
@endpush

@section('content')

{{-- ===== TOP CARDS (modified order) ===== --}}
<div class="qa-topcards">

    {{-- Butuh Inspeksi --}}
    <div class="qa-card yellow">
        <h6>Butuh Inspeksi</h6>
        <h3>{{ $butuh ?? $butuhInspeksiCount ?? 0 }}</h3>
    </div>

    {{-- Lolos Inspeksi --}}
    <div class="qa-card green">
        <h6>Lolos Inspeksi</h6>
        <h3>{{ $lolos ?? $lolosCount ?? 0 }}</h3>
    </div>

    {{-- Sedang Proses Inspeksi --}}
    <div class="qa-card blue">
        <h6>Sedang Proses Inspeksi</h6>
        <h3>{{ $sedang ?? ($sedang ?? 0) }}</h3>
    </div>

    {{-- Tidak Lolos --}}
    <div class="qa-card red">
        <h6>Tidak Lolos Inspeksi</h6>
        <h3>{{ $gagal ?? $gagalCount ?? 0 }}</h3>
    </div>

</div>

{{-- ===== TABLE ===== --}}
<div class="qa-table-wrapper">

    <div class="qa-table-title">
        <span>Daftar Pengadaan</span>

        <form method="GET" style="margin:0;">
            <div class="filters-wrap">
                <div class="qa-search-box" title="Cari kode atau nama pengadaan">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari..." />
                    <i class="bi bi-search"></i>
                </div>

                <select name="priority" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Prioritas</option>
                    <option value="tinggi" {{ request('priority') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                    <option value="sedang" {{ request('priority') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="rendah" {{ request('priority') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                </select>

                <select name="result" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Hasil Inspeksi</option>
                    <option value="passed" {{ request('result') === 'passed' ? 'selected' : '' }}>Lolos</option>
                    <option value="in_progress" {{ request('result') === 'in_progress' ? 'selected' : '' }}>Sedang Proses</option>
                    <option value="failed" {{ request('result') === 'failed' ? 'selected' : '' }}>Tidak Lolos</option>
                    <option value="not_inspected" {{ request('result') === 'not_inspected' ? 'selected' : '' }}>Belum Diinspeksi</option>
                </select>

                <button type="submit" class="filter-select" style="cursor:pointer;">Terapkan</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="qa-table">
            <thead>
                <tr>
                    <th>Kode</th>
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
            @forelse($procurements as $proc)

                @php
                    $items = $proc->requestProcurements->flatMap->items ?? collect();
                    $reports = $items->flatMap->inspectionReports;
                    if ($items->count() === 0) {
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
                        } elseif ($inspectedCount < $items->count()) {
                            $statusText = "SEDANG PROSES";
                            $statusClass = "status-inprogress";
                        } else {
                            if ($latestResults->every(fn($r) => $r === 'passed')) {
                                $statusText = "LOLOS";
                                $statusClass = "status-pass";
                            } elseif ($latestResults->every(fn($r) => $r === 'failed')) {
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
                    <td>{{ $proc->code_procurement }}</td>
                    <td style="text-align:left;">{{ $proc->name_procurement }}</td>
                    <td>{{ $proc->department->department_name ?? '-' }}</td>
                    <td>{{ $proc->requestProcurements->first()?->vendor->name_vendor ?? '-' }}</td>
                    <td>{{ $proc->start_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $proc->end_date?->format('d/m/Y') ?? '-' }}</td>

                    <td>
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

                    <td>
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
document.addEventListener('DOMContentLoaded', function() {
    // If detail-approval set a localStorage flag after save, update cards on this page
    const update = localStorage.getItem('inspectionUpdate');
    if (update) {
        try {
            const data = JSON.parse(update);

            const cardButuh = document.querySelector('.qa-card.yellow h3');
            if (cardButuh && typeof data.butuh !== 'undefined' && data.butuh !== null) cardButuh.textContent = data.butuh;

            const cardLolos = document.querySelector('.qa-card.green h3');
            if (cardLolos && typeof data.lolos !== 'undefined' && data.lolos !== null) cardLolos.textContent = data.lolos;

            const cardSedang = document.querySelector('.qa-card.blue h3');
            if (cardSedang && typeof data.sedang_proses !== 'undefined' && data.sedang_proses !== null) cardSedang.textContent = data.sedang_proses;

            const cardGagal = document.querySelector('.qa-card.red h3');
            if (cardGagal && typeof data.gagal !== 'undefined' && data.gagal !== null) cardGagal.textContent = data.gagal;
        } catch (e) {
            console.warn('inspectionUpdate parse error', e);
        } finally {
            localStorage.removeItem('inspectionUpdate');
        }
    }
});
</script>
@endpush

@endsection
