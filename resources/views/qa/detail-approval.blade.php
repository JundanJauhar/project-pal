@extends('layouts.app')

@section('title', 'Detail Approval — Inspeksi Barang')

@push('styles')
<style>
/* page padding */
.detail-approval { padding: 18px 0; }

/* header */
.da-header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:28px; }
.da-title { font-size:24px; font-weight:700; }
.da-sub { color:#6c6c6c; font-size:14px; margin-top:6px; }

/* info grid (top left / right) */
.proc-info {
  display:grid;
  grid-template-columns: 1fr 320px;
  gap:20px;
  align-items:start;
  margin-bottom:26px;
}
.proc-block { background:transparent; }

/* big table-card */
.table-card {
  background:#f2f2f2;
  border-radius:10px;
  padding:0;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  overflow: hidden;
  border:1px solid #e9e9e9;
}

/* table header */
.table-head {
  display:flex;
  gap:0;
  background:transparent;
  padding:14px 18px;
  border-bottom: 1px solid rgba(0,0,0,0.08);
}
.table-head .th {
  font-weight:600;
  color:#6b6b6b;
  font-size:15px;
  padding:10px;
}
.col-name { flex: 2 1 220px; }
.col-spec { flex: 3 1 380px; }
.col-qty { flex: 0 0 110px; text-align:center;}
.col-date { flex: 0 0 140px; text-align:center;}
.col-result { flex: 0 0 180px; text-align:center;}
.col-notes { flex: 1 1 220px; text-align:left; }

/* rows */
.table-body { display:flex; flex-direction:column; }
.row-item {
  display:flex;
  align-items:center;
  gap:0;
  padding:22px 18px;
  background:transparent;
  border-bottom:1px solid rgba(0,0,0,0.06);
}
.row-item:nth-child(odd) { background: rgba(255,255,255,0.04); }

/* columns same sizes */
.row-item .col {
  padding:0 12px;
  font-size:15px;
  color:#222;
}
.row-item .col .small-muted { color:#7a7a7a; font-size:13px; margin-top:6px; }

/* spesification text wrap */
.col-spec { white-space:normal; line-height:1.4; }

/* result toggles */
.result-toggle { display:flex; gap:10px; justify-content:center; align-items:center; }
.toggle-box {
  width:40px; height:40px; border-radius:8px;
  background:#f0f0f0; border:1px solid #e6e6e6;
  display:flex; align-items:center; justify-content:center; cursor:pointer;
  transition: all .12s ease;
}
.toggle-box.pass { background:#eaf9ec; border-color:#cfead0; color:#1f8b3b; box-shadow: inset 0 -2px 0 rgba(0,0,0,0.02); }
.toggle-box.fail { background:#fff0f0; border-color:#f1cfcf; color:#c82727; }
.toggle-label { font-size:13px; color:#6b6b6b; margin-left:8px; }

/* notes inline */
.notes-inline textarea {
  width:100%;
  min-height:44px;
  max-height:84px;
  padding:8px;
  border-radius:6px;
  border:1px solid #e6e6e6;
  resize:vertical;
  font-size:14px;
}

/* saved badge */
.saved-badge { color:#138a33; font-weight:700; display:none; }

/* detail actions */
.detail-actions {
  margin-top:18px;
  display:flex; gap:12px; justify-content:flex-end; align-items:center;
}
.btn-save { background:#138a33; color:#fff; padding:8px 16px; border-radius:8px; border:0; cursor:pointer; }
.btn-edit { background:#f0f0f0; padding:8px 12px; border-radius:8px; border:1px solid #ddd; cursor:pointer; }

/* responsive */
@media (max-width:1000px) {
  .proc-info { grid-template-columns: 1fr; }
  .table-head { display:none; } 
  .table-card { padding:8px; }
  .row-item { flex-direction:column; align-items:flex-start; gap:8px; padding:12px; }
  .col { padding:6px 0; width:100%; }
  .col-qty, .col-date, .col-result { text-align:left; }
}
</style>
@endpush

@section('content')
<div class="container-fluid detail-approval">

    <div class="da-header">
        <div>
            <div class="da-title">Detail Pengadaan</div>
            <div class="text-muted da-sub">{{ $procurement->code_procurement }}</div>
        </div>

        <div style="display:flex; align-items:center; gap:12px;">
            <button class="da-close btn btn-light" title="Close" onclick="window.location='{{ route('inspections.index') }}'">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <div class="proc-info">
        <div class="proc-block">
            <div style="margin-bottom:18px;">
                <div style="font-weight:600;">Nama Pengadaan</div>
                <div class="text-muted" style="margin-top:6px;">{{ $procurement->name_procurement }}</div>
            </div>

            <div style="margin-bottom:8px;">
                <div style="font-weight:600;">Vendor</div>
                <div class="text-muted" style="margin-top:6px;">{{ $procurement->requestProcurements->first()?->vendor->name_vendor ?? '-' }}</div>
            </div>

            <div>
                <div style="font-weight:600;">Department</div>
                <div class="text-muted" style="margin-top:6px;">{{ $procurement->department->department_name ?? '-' }}</div>
            </div>
        </div>

        <div class="proc-block" style="text-align:right;">
            <div style="font-weight:600;">Prioritas</div>
            <div class="text-muted" style="margin-top:6px;">{{ strtoupper($procurement->priority ?? '-') }}</div>

            <div style="margin-top:16px;">
                <div style="font-weight:600;">Tanggal Dibuat</div>
                <div class="text-muted" style="margin-top:6px;">{{ $procurement->start_date?->format('d/m/Y') ?? '-' }}</div>
            </div>

            <div style="margin-top:12px;">
                <div style="font-weight:600;">Tanggal Tenggat</div>
                <div class="text-muted" style="margin-top:6px;">{{ $procurement->end_date?->format('d/m/Y') ?? '-' }}</div>
            </div>
        </div>
    </div>

    <h5 style="margin:18px 0 12px 0;">Detail Pengadaan</h5>

    <div class="table-card">
        <div class="table-head">
            <div class="th col-name">Nama Barang</div>
            <div class="th col-spec">Spesifikasi</div>
            <div class="th col-qty">Jumlah</div>
            <div class="th col-date">Tanggal Kedatangan</div>
            <div class="th col-result">Hasil Inspeksi</div>
            <div class="th col-notes">Keterangan</div>
        </div>

        <div class="table-body" id="tableBody">
            @foreach($items as $item)
                @php
                    $latest = $item->inspectionReports->sortByDesc('inspection_date')->first();
                    $current = $latest?->result;
                    $notes = $latest?->notes ?? '';
                @endphp

                <div class="row-item" data-item-id="{{ $item->item_id }}">
                    <div class="col col-name">
                        <div style="font-weight:600;">{{ $item->item_name }}</div>
                    </div>

                    <div class="col col-spec">
                        <div class="small-muted">{{ $item->specification ?? '-' }}</div>
                    </div>

                    <div class="col col-qty">{{ $item->amount }} {{ $item->unit }}</div>

                    <div class="col col-date">{{ $item->arrival_date ? \Carbon\Carbon::parse($item->arrival_date)->format('d/m/Y') : '-' }}</div>

                    <div class="col col-result">
                        <div class="result-toggle" data-current="{{ $current ?? '' }}">
                            <div class="toggle-box toggle-pass {{ $current === 'passed' ? 'pass' : '' }}" data-value="passed" title="Lolos">
                                <i class="bi bi-check-lg"></i>
                            </div>

                            <div class="toggle-box toggle-fail {{ $current === 'failed' ? 'fail' : '' }}" data-value="failed" title="Tidak Lolos">
                                <i class="bi bi-x-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col col-notes notes-inline">
                        <textarea class="notes-input" placeholder="Keterangan (wajib jika Tidak Lolos)">{{ $notes }}</textarea>
                        <div class="saved-badge" style="margin-top:6px;">Tersimpan ✓</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="detail-actions">
        <button class="btn-edit" id="btnEdit" type="button">Edit</button>
        <button class="btn-save" id="btnSave" type="button">Simpan</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const procurementId = "{{ $procurement->procurement_id }}";

    // Initialize rows
    document.querySelectorAll('.row-item').forEach(row => {
        const pass = row.querySelector('.toggle-pass');
        const fail = row.querySelector('.toggle-fail');
        const notes = row.querySelector('.notes-input');

        // show/hide notes depending on initial state
        if (pass.classList.contains('pass')) {
            if (notes) notes.style.display = 'none';
        } else if (fail.classList.contains('fail')) {
            if (notes) notes.style.display = 'block';
        } else {
            if (notes) notes.style.display = 'none';
        }

        // click handlers with toggle/clear-on-second-click
        pass.addEventListener('click', () => {
            if (pass.classList.contains('pass')) {
                // clear
                pass.classList.remove('pass');
                if (notes) notes.style.display = 'none';
            } else {
                pass.classList.add('pass');
                fail.classList.remove('fail');
                if (notes) notes.style.display = 'none';
            }
        });

        fail.addEventListener('click', () => {
            if (fail.classList.contains('fail')) {
                // clear
                fail.classList.remove('fail');
                if (notes) notes.style.display = 'none';
            } else {
                fail.classList.add('fail');
                pass.classList.remove('pass');
                if (notes) notes.style.display = 'block';
            }
        });
    });

    // edit mode toggling
    let editMode = true; // default editable
    const btnEdit = document.getElementById('btnEdit');
    const btnSave = document.getElementById('btnSave');

    function setEditMode(flag) {
        editMode = flag;
        btnEdit.textContent = editMode ? 'Batal' : 'Edit';
        document.querySelectorAll('.row-item').forEach(row => {
            row.querySelectorAll('.toggle-box').forEach(tb => tb.style.pointerEvents = editMode ? 'auto' : 'none');
            const ni = row.querySelector('.notes-input');
            if (ni) {
                if (editMode) ni.removeAttribute('readonly');
                else ni.setAttribute('readonly', true);
                ni.style.pointerEvents = editMode ? 'auto' : 'none';
            }
        });
    }

    setEditMode(true);

    btnEdit.addEventListener('click', () => {
        setEditMode(!editMode);
    });

    // save
    btnSave.addEventListener('click', async () => {
        const payloadItems = [];
        let valid = true;

        document.querySelectorAll('.row-item').forEach(row => {
            const itemId = parseInt(row.dataset.itemId);
            const passActive = row.querySelector('.toggle-pass').classList.contains('pass');
            const failActive = row.querySelector('.toggle-fail').classList.contains('fail');
            const notesInput = row.querySelector('.notes-input');

            let result = null;
            if (passActive) result = 'passed';
            if (failActive) result = 'failed';

            if (!result) {
                // skip unselected
                return;
            }

            const notes = notesInput ? notesInput.value.trim() : null;
            if (result === 'failed' && (!notes || notes.length === 0)) {
                alert('Keterangan wajib diisi untuk item yang tidak lolos.');
                valid = false;
            }

            payloadItems.push({ item_id: itemId, result, notes });
        });

        if (!valid) return;
        if (payloadItems.length === 0) {
            return alert('Pilih hasil inspeksi untuk minimal 1 item sebelum menyimpan.');
        }

        btnSave.disabled = true;
        btnSave.textContent = 'Menyimpan...';

        try {
            const res = await fetch("{{ route('qa.detail-approval.save', ['procurement_id' => $procurement->procurement_id]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ items: payloadItems })
            });

            const json = await res.json();
            if (!json.success) {
                alert(json.message || 'Gagal menyimpan.');
                btnSave.disabled = false;
                btnSave.textContent = 'Simpan';
                return;
            }

            // show saved per item + lock notes for passed
            payloadItems.forEach(it => {
                const row = document.querySelector(`.row-item[data-item-id="${it.item_id}"]`);
                if (!row) return;
                const badge = row.querySelector('.saved-badge');
                if (badge) badge.style.display = 'inline-block';
                const notes = row.querySelector('.notes-input');
                if (it.result === 'passed') {
                    if (notes) notes.style.display = 'none';
                } else {
                    if (notes) notes.style.display = 'block';
                }
            });

            alert('Hasil inspeksi berhasil disimpan.');
            setEditMode(false);
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan.');
        } finally {
            btnSave.disabled = false;
            btnSave.textContent = 'Simpan';
        }
    });
});
</script>
@endpush
