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
    padding: 35px;
    border-radius: 14px;
    border: 1px solid #E0E0E0;
}

/* Title */
.qa-table-title {
    font-size: 26px;
    font-weight: 700;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* Search */
.qa-search-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #F0F0F0;
    border-radius: 25px;
    padding: 4px 14px;
    width: 350px;
    border: 1px solid #ddd;
}
.qa-search-box input {
    border: none;
    background: transparent;
    width: 100%;
    outline: none;
}
.qa-search-box i { font-size: 18px; color: #777; }

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

.status-link { text-decoration: none; font-weight: bold; }
</style>
@endpush

@section('content')

{{-- ===== TOP CARDS ===== --}}
<div class="qa-topcards">

    {{-- Card total --}}
    <div class="qa-card blue">
        <h6>Total Pengadaan</h6>
        <h3>{{ $totalProcurements ?? 0 }}</h3>
    </div>

    {{-- Card butuh inspeksi (TIDAK CLICKABLE LAGI) --}}
    <div class="qa-card yellow">
        <h6>Butuh Inspeksi</h6>
        <h3>{{ $butuhInspeksiCount ?? 0 }}</h3>
    </div>

    {{-- Lolos --}}
    <div class="qa-card green">
        <h6>Lolos Inspeksi</h6>
        <h3>{{ $lolosCount ?? 0 }}</h3>
    </div>

    {{-- Tidak Lolos --}}
    <div class="qa-card red">
        <h6>Tidak Lolos Inspeksi</h6>
        <h3>{{ $gagalCount ?? 0 }}</h3>
    </div>

</div>

{{-- ===== TABLE ===== --}}
<div class="qa-table-wrapper">

    <div class="qa-table-title">
        <span>Daftar Pengadaan</span>

        <form method="GET">
            <div class="qa-search-box">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari...">
                <i class="bi bi-search"></i>
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
                    $items = $proc->items ?? collect();
                    $reports = $items->flatMap->inspectionReports;

                    if ($reports->count() === 0) {
                        $statusText = "BELUM DIINSPEKSI";
                        $statusClass = "status-wait";
                    } elseif ($reports->contains('result', 'failed')) {
                        $statusText = "TIDAK LOLOS";
                        $statusClass = "status-fail";
                    } else {
                        $statusText = "LOLOS";
                        $statusClass = "status-pass";
                    }
                @endphp

                <tr>
                    <td>{{ $proc->code_procurement }}</td>
                    <td>{{ $proc->name_procurement }}</td>
                    <td>{{ $proc->department->department_name ?? '-' }}</td>
                    <td>{{ $proc->requestProcurements->first()?->vendor->name_vendor ?? '-' }}</td>
                    <td>{{ $proc->start_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $proc->end_date?->format('d/m/Y') ?? '-' }}</td>

                    <td>
                        @php
                            $p = strtolower($proc->priority);
                            $class = match($p) {
                                'tinggi' => 'priority-high',
                                'sedang' => 'priority-medium',
                                'rendah' => 'priority-low',
                                default  => '',
                            };
                        @endphp
                        <span class="{{ $class }}">{{ strtoupper($proc->priority) }}</span>
                    </td>

                    {{-- ===== STATUS LINK BARU ===== --}}
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
                        <p class="text-muted mt-2">Tidak ada pengadaan yang butuh inspeksi</p>
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
    const update = localStorage.getItem('inspectionUpdate');
    if (update) {
        const data = JSON.parse(update);

        // update card hijau (LOLOS)
        const cardLolos = document.querySelector('.qa-card.green h3');
        if (cardLolos) cardLolos.textContent = data.lolos;

        // update card merah (TIDAK LOLOS)
        const cardGagal = document.querySelector('.qa-card.red h3');
        if (cardGagal) cardGagal.textContent = data.gagal;

        // update card kuning (BUTUH INSPEKSI)
        const cardButuh = document.querySelector('.qa-card.yellow h3');
        if (cardButuh && data.butuh !== null) {
            cardButuh.textContent = data.butuh;
        }

        // hapus agar tidak update berkali-kali
        localStorage.removeItem('inspectionUpdate');
    }
});
</script>
@endpush


@endsection
