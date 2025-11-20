@extends('layouts.app')

@section('title', 'List Approval — Inspeksi Barang')

@section('content')

@push('styles')
<style>
/* ===== list-approval styles ===== */

/* Layout & card */
.la-wrapper { padding: 18px 0; }
.la-topbar { display:flex; align-items:center; gap:10px; margin-bottom:14px; }
.btn-back {
    display:inline-flex; align-items:center; gap:8px;
    padding:8px 12px; border-radius:8px;
    background:#fff; border:1px solid #e6e6e6;
    cursor:pointer; text-decoration:none; color:#111;
}
.la-card { background:#f6f6f6; border-radius:12px; padding:14px; border:1px solid #e6e6e6; }

/* Header row */
.la-row {
    background:#fff; border-radius:10px; padding:16px 18px;
    border:1px solid #e8e8e8;
    display:flex; align-items:center;
    justify-content:space-between; gap:12px;
    margin-bottom:10px;
}
.la-row .left { display:flex; align-items:center; gap:18px; flex:1; }
.la-code { font-weight:600; font-size:16px; min-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

.la-progress { width: 220px; max-width:35%; }
.progress-track { width:100%; height:8px; background:#ebecec; border-radius:8px; overflow:hidden; }
.progress-fill { height:100%; background:#9ce01a; transition:width .4s ease; }

/* meta */
.la-date { min-width:120px; text-align:center; color:#222; }
.la-priority { min-width:90px; text-align:center; font-weight:700; }

/* status badge */
.la-status {
    min-width:150px; text-align:right;
    padding:8px 12px; border-radius:12px;
    font-weight:700;
    background:#ffd966; color:#8a6d1a;
}

/* chevron */
.la-chevron i { font-size:20px; }

/* detail block */
.la-detail {
    background:#fdfdfd; border-radius:8px; padding:18px;
    border:1px solid #e9e9e9; display:none;
    margin-top:6px;
}
.la-table { width:100%; border-collapse:collapse; }
.la-table thead th {
    text-align:left; padding:12px 8px; color:#6b6b6b;
    border-bottom:2px solid #e0e0e0; font-weight:600;
}
.la-table tbody td {
    padding:18px 8px; border-bottom:1px solid #f0f0f0;
}

/* inspect toggles */
.inspect-toggle { display:flex; gap:8px; align-items:center; }
.inspect-box {
    width:34px; height:34px;
    border-radius:8px; border:1px solid #e0e0e0;
    display:flex; align-items:center; justify-content:center;
    background:#f0f0f0; cursor:pointer;
}
.inspect-box.active.passed { background:#dff0d8; border-color:#b6e2b3; }
.inspect-box.active.failed { background:#f8d7da; border-color:#e6b5b8; }

/* Notes textarea */
.inspect-notes { display:none; margin-top:8px; width:100%; }
.inspect-notes textarea { width:100%; min-height:60px; padding:8px; border-radius:8px; }

/* Buttons */
.item-actions { display:flex; gap:8px; align-items:center; }
.btn-save {
    background:#1f8b3b; color:#fff;
    padding:6px 12px; border-radius:8px;
    border:none; cursor:pointer;
}
.btn-edit {
    background:#f0f0f0; padding:6px 10px;
    border:1px solid #ddd; border-radius:8px; cursor:pointer;
}
.item-saved { color:#1f8b3b; font-weight:700; }
</style>
@endpush


<div class="la-wrapper container-fluid">

    {{-- TOPBAR LAYOUT B --}}
    <div class="la-topbar">
        <a href="{{ route('inspections.index') }}" class="btn-back">
            <i class="bi bi-arrow-left"></i> <span>Kembali</span>
        </a>
    </div>

    {{-- SEARCH BAR --}}
    <div class="mb-3">
        <form method="GET" class="d-flex" action="{{ route('qa.list-approval') }}">
            <input type="search" name="q" class="form-control me-2"
                   placeholder="Cari kode, nama pengadaan, nama barang, spesifikasi..."
                   value="{{ request('q') }}">
            <button class="btn btn-light"><i class="bi bi-search"></i></button>
        </form>
    </div>

    {{-- CARD CONTAINER --}}
    <div class="la-card">

        @forelse($procurements as $proc)
            @php
                $items = $proc->items ?? collect([]);
                $completedCount = $proc->procurementProgress->where('status', 'completed')->count();
                $progressPerc = max(min(round(($completedCount / max($totalCheckpoints,1))*100), 100), 8);

                $totalItems = $items->count();
                $inspectedCount = $items->filter(fn($it)=>$it->inspectionReports->isNotEmpty())->count();
                $headerStatus = $inspectedCount >= $totalItems && $totalItems>0 ? 'Ter-update' : 'Butuh Update';
            @endphp

            {{-- HEADER ROW --}}
            <div class="la-row"
                 data-target="detail-{{ $proc->procurement_id }}"
                 data-procurement-id="{{ $proc->procurement_id }}">

                <div class="left">
                    <div class="la-code" title="{{ $proc->name_procurement }}">
                        {{ $proc->code_procurement }}
                    </div>

                    <div class="la-progress">
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $progressPerc }}%;"></div>
                        </div>
                    </div>
                </div>

                <div class="la-date">{{ optional($proc->start_date)->format('d/m/Y') ?? '-' }}</div>
                <div class="la-priority">{{ ucfirst($proc->priority ?? '-') }}</div>

                <div class="la-status" id="status-{{ $proc->procurement_id }}">
                    {{ $headerStatus }}
                </div>

                <div class="la-chevron">
                    <i class="bi bi-chevron-down"></i>
                </div>
            </div>

            {{-- DETAIL ROW --}}
            <div id="detail-{{ $proc->procurement_id }}" class="la-detail">
                <table class="la-table">
                    <thead>
                    <tr>
                        <th>Nama Barang</th>
                        <th>Spesifikasi</th>
                        <th>Jumlah</th>
                        <th>Tgl Kedatangan</th>
                        <th>Vendor</th>
                        <th>Hasil Inspeksi</th>
                    </tr>
                    </thead>
                    <tbody>

                    @forelse($items as $item)
                        @php
                            $vendor = $item->requestProcurement?->vendor?->name_vendor ?? '-';
                            $latest = $item->inspectionReports->sortByDesc('inspection_date')->first();
                            $existing = $latest?->result;
                        @endphp

                        <tr data-item-id="{{ $item->item_id }}"
                            data-procurement-id="{{ $proc->procurement_id }}">

                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->specification ?? '-' }}</td>
                            <td>{{ $item->amount }} {{ $item->unit }}</td>
                            <td>{{ $item->arrival_date ? \Carbon\Carbon::parse($item->arrival_date)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $vendor }}</td>

                            <td>
                                <div class="inspect-toggle" data-item-id="{{ $item->item_id }}">

                                    <div class="inspect-box passed {{ $existing === 'passed' ? 'active' : '' }}"
                                         data-value="passed">
                                        <i class="bi bi-check-lg"></i>
                                    </div>

                                    <div class="inspect-box failed {{ $existing === 'failed' ? 'active' : '' }}"
                                         data-value="failed">
                                        <i class="bi bi-x-lg"></i>
                                    </div>

                                    <div class="inspect-notes"
                                         style="{{ $existing === 'failed' ? 'display:block;' : '' }}">
                                        <textarea class="notes-input form-control"
                                                  placeholder="Keterangan wajib jika gagal">
                                            {{ $latest->notes ?? '' }}
                                        </textarea>
                                    </div>

                                    <div class="item-actions ms-2">
                                        <button class="btn-save" data-item-id="{{ $item->item_id }}">Simpan</button>
                                        <button class="btn-edit" data-item-id="{{ $item->item_id }}" style="display:none;">
                                            Edit
                                        </button>

                                        <div class="item-saved"
                                             style="display:{{ $existing ? 'inline-block' : 'none' }};">
                                            Tersimpan
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">
                                Tidak ada item.
                            </td>
                        </tr>
                    @endforelse

                    </tbody>
                </table>
            </div>

        @empty

            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox" style="font-size:40px;"></i>
                <div>Belum ada pengadaan.</div>
            </div>

        @endforelse

        <div class="mt-3">
            {{ $procurements->links() }}
        </div>
    </div>

</div>

@endsection


@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    /* ===== Expand / Collapse ===== */
    document.querySelectorAll('.la-row').forEach(row => {
        row.addEventListener('click', function (e) {

            if (e.target.closest('button') || e.target.closest('textarea')) return;

            const id = row.dataset.target;
            const detail = document.getElementById(id);
            const icon = row.querySelector('.la-chevron i');

            const isOpen = detail.style.display === 'block';

            document.querySelectorAll('.la-detail').forEach(d => d.style.display = 'none');
            document.querySelectorAll('.la-chevron i').forEach(i => {
                i.classList.remove('bi-chevron-up');
                i.classList.add('bi-chevron-down');
            });

            if (!isOpen) {
                detail.style.display = 'block';
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            }
        });
    });

    /* ===== Passed / Failed toggle ===== */
    document.querySelectorAll('.inspect-toggle').forEach(t => {

        const pass = t.querySelector('.inspect-box.passed');
        const fail = t.querySelector('.inspect-box.failed');
        const notes = t.querySelector('.inspect-notes');
        const input = t.querySelector('.notes-input');

        pass.addEventListener('click', () => {
            pass.classList.add('active'); fail.classList.remove('active');
            notes.style.display = 'none';
            input.value = '';
        });

        fail.addEventListener('click', () => {
            fail.classList.add('active'); pass.classList.remove('active');
            notes.style.display = 'block';
        });

    });

    /* ===== Save AJAX ===== */
    document.querySelectorAll('.btn-save').forEach(btn => {
        btn.addEventListener('click', async function () {

            const itemId = this.dataset.itemId;
            const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
            const procurementId = row.dataset.procurementId;

            const toggle = row.querySelector('.inspect-toggle');
            const pass = toggle.querySelector('.inspect-box.passed').classList.contains('active');
            const fail = toggle.querySelector('.inspect-box.failed').classList.contains('active');
            const notes = toggle.querySelector('.notes-input').value.trim();

            let result = pass ? 'passed' : (fail ? 'failed' : null);
            if (!result) return alert("Pilih hasil inspeksi.");
            if (result === 'failed' && notes.length === 0)
                return alert("Keterangan wajib untuk item gagal.");

            const payload = {
                item_id: parseInt(itemId),
                procurement_id: parseInt(procurementId),
                result,
                notes
            };

            const res = await fetch("{{ route('qa.inspection.save-item') }}", {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify(payload)
            });

            const json = await res.json();
            if (!json.success) return alert("Gagal menyimpan.");

            toggle.querySelector('.item-saved').style.display = 'inline-block';
            this.style.display = 'none';
            toggle.querySelector('.btn-edit').style.display = 'inline-block';

            if (json.all_inspected) {
                const header = document.getElementById(`status-${procurementId}`);
                header.textContent = "Ter-update";
                header.style.background = "#dff0d8";
                header.style.color = "#1f8b3b";
            }
        });
    });

    /* ===== Edit ===== */
    document.querySelectorAll('.btn-edit').forEach(btn => {
        btn.addEventListener('click', function () {
            const row = document.querySelector(`tr[data-item-id="${this.dataset.itemId}"]`);
            row.querySelector('.item-saved').style.display = 'none';
            row.querySelector('.btn-save').style.display = 'inline-block';
            this.style.display = 'none';
        });
    });

});
</script>
@endpush
