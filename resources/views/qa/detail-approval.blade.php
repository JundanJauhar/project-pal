@extends('layouts.app')

@section('title', 'Detail Approval — Inspeksi Barang')

@push('styles')
<style>
/* basic wrapper */
.detail-approval {
    padding: 18px 0;
}

/* header */
.da-header {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-bottom:18px;
}
.da-title { font-size:20px; font-weight:700; }
.da-close {
    background:transparent;
    border:0;
    font-size:20px;
    cursor:pointer;
}

/* grid items */
.items-grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap:16px;
}

/* card */
.item-card {
    background:#fff;
    border-radius:10px;
    padding:14px;
    border:1px solid #ececec;
    display:flex;
    flex-direction:column;
    gap:10px;
}

/* header area in card */
.item-head { display:flex; justify-content:space-between; align-items:center; gap:8px; }
.item-name { font-weight:700; font-size:16px; }
.item-meta { color:#666; font-size:13px; text-align:right; }

/* fields */
.item-field { font-size:14px; color:#333; }

/* toggle boxes */
.toggle-row { display:flex; gap:8px; align-items:center; margin-top:6px; }
.toggle-box {
    flex:0 0 48%;
    padding:10px;
    border-radius:8px;
    border:1px solid #ddd;
    text-align:center;
    cursor:pointer;
    user-select:none;
    font-weight:700;
}
.toggle-box.pass { background:#f0fff3; border-color:#c6eccf; color:#1f8b3b; }
.toggle-box.fail { background:#fff5f5; border-color:#f0c2c2; color:#c82727; }
.toggle-box.inactive { background:#f7f7f7; color:#666; border-color:#e6e6e6; }

/* notes */
.notes { margin-top:8px; display:none; }
.notes textarea { width:100%; min-height:72px; padding:8px; border-radius:6px; border:1px solid #e6e6e6; resize:vertical; }

/* saved badge */
.saved-badge { color:#1f8b3b; font-weight:700; display:none; }

/* bottom actions */
.detail-actions {
    margin-top:18px;
    display:flex;
    gap:12px;
    justify-content:flex-end;
    align-items:center;
}
.btn-save {
    background:#1f8b3b; color:#fff; padding:8px 16px; border-radius:8px; border:0; cursor:pointer;
}
.btn-edit {
    background:#f0f0f0; padding:8px 12px; border-radius:8px; border:1px solid #ddd; cursor:pointer;
}

/* small responsive tweaks */
@media (max-width:600px) {
    .da-header { flex-direction:column; align-items:flex-start; gap:8px; }
    .toggle-row { flex-direction:column; }
    .toggle-box { width:100%; }
}
</style>
@endpush

@section('content')
<div class="container-fluid detail-approval">

    <div class="da-header">
        <div>
            <div class="da-title">Detail Inspeksi — {{ $procurement->code_procurement }} </div>
            <div class="text-muted" style="font-size:13px;">{{ $procurement->name_procurement }}</div>
        </div>

        <div style="display:flex; align-items:center; gap:12px;">
            <button class="btn btn-outline-secondary" onclick="window.location='{{ route('inspections.index') }}'">
                <i class="bi bi-arrow-left"></i> Kembali
            </button>

            <button class="da-close btn btn-light" title="Close" onclick="window.location='{{ route('inspections.index') }}'">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    {{-- items --}}
    <div class="items-grid" id="itemsGrid">
        @foreach($items as $item)
            @php
                $latest = $item->inspectionReports->sortByDesc('inspection_date')->first();
                $current = $latest?->result;
                $notes = $latest?->notes ?? '';
            @endphp

            <div class="item-card" data-item-id="{{ $item->item_id }}">
                <div class="item-head">
                    <div>
                        <div class="item-name">{{ $item->item_name }}</div>
                        <div class="text-muted" style="font-size:13px;">{{ $item->item_description ?? '' }}</div>
                    </div>
                    <div class="item-meta">
                        <div>Jumlah: {{ $item->amount }} {{ $item->unit }}</div>
                        <div>Vendor: {{ $item->vendor?->name_vendor ?? '-' }}</div>
                        <div>Tgl Kedatangan: {{ $item->arrival_date ? \Carbon\Carbon::parse($item->arrival_date)->format('d/m/Y') : '-' }}</div>
                    </div>
                </div>

                <div class="item-field">Spesifikasi: <span class="text-muted">{{ $item->specification ?? '-' }}</span></div>

                <div class="toggle-row" data-current="{{ $current ?? '' }}">
                    <div class="toggle-box toggle-pass {{ $current === 'passed' ? 'pass' : 'inactive' }}" data-value="passed">
                        Lolos
                    </div>
                    <div class="toggle-box toggle-fail {{ $current === 'failed' ? 'fail' : 'inactive' }}" data-value="failed">
                        Tidak Lolos
                    </div>
                </div>

                <div class="notes" style="{{ $current === 'failed' ? 'display:block;' : 'display:none;' }}">
                    <textarea class="notes-input" placeholder="Keterangan (wajib jika Tidak Lolos)">{{ $notes }}</textarea>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
                    <div class="saved-badge">Tersimpan ✓</div>
                    <div style="font-size:13px; color:#666;">ID Item: {{ $item->item_id }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- actions --}}
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

    // init toggles
    document.querySelectorAll('.item-card').forEach(card => {
        const passBox = card.querySelector('.toggle-pass');
        const failBox = card.querySelector('.toggle-fail');
        const notesWrap = card.querySelector('.notes');
        const notesInput = card.querySelector('.notes-input');

        const setActive = (which) => {
            if (which === 'passed') {
                passBox.classList.remove('inactive'); passBox.classList.add('pass');
                failBox.classList.remove('fail'); failBox.classList.add('inactive');
                notesWrap.style.display = 'none';
            } else if (which === 'failed') {
                failBox.classList.remove('inactive'); failBox.classList.add('fail');
                passBox.classList.remove('pass'); passBox.classList.add('inactive');
                notesWrap.style.display = 'block';
            } else {
                passBox.classList.remove('pass'); passBox.classList.add('inactive');
                failBox.classList.remove('fail'); failBox.classList.add('inactive');
                notesWrap.style.display = 'none';
            }
        };

        // wire clicks
        passBox.addEventListener('click', () => setActive('passed'));
        failBox.addEventListener('click', () => setActive('failed'));

        // make fields editable only after clicking Edit (default: editable)
        // We'll handle Edit button below.
    });

    let editMode = false;
    const btnEdit = document.getElementById('btnEdit');
    const btnSave = document.getElementById('btnSave');

    btnEdit.addEventListener('click', () => {
        editMode = !editMode;
        btnEdit.textContent = editMode ? 'Batal' : 'Edit';
        // enable/disable toggles and notes
        document.querySelectorAll('.item-card').forEach(card => {
            const passBox = card.querySelector('.toggle-pass');
            const failBox = card.querySelector('.toggle-fail');
            const notesInput = card.querySelector('.notes-input');

            if (editMode) {
                passBox.style.pointerEvents = 'auto';
                failBox.style.pointerEvents = 'auto';
                notesInput.removeAttribute('readonly');
                card.querySelector('.saved-badge').style.display = 'none';
            } else {
                passBox.style.pointerEvents = 'none';
                failBox.style.pointerEvents = 'none';
                notesInput.setAttribute('readonly', true);
            }
        });
    });

    // default: allow editing (as requested). Set editMode true so toggles work.
    editMode = true;
    btnEdit.textContent = 'Batal';
    document.querySelectorAll('.item-card .toggle-pass, .item-card .toggle-fail').forEach(el => {
        el.style.pointerEvents = 'auto';
    });

    // Save all
    btnSave.addEventListener('click', async () => {
        // collect items
        const payloadItems = [];
        let valid = true;

        document.querySelectorAll('.item-card').forEach(card => {
            const itemId = parseInt(card.dataset.itemId);
            const passActive = card.querySelector('.toggle-pass').classList.contains('pass');
            const failActive = card.querySelector('.toggle-fail').classList.contains('fail');
            const notesInput = card.querySelector('.notes-input');

            let result = null;
            if (passActive) result = 'passed';
            if (failActive) result = 'failed';

            if (!result) {
                // not selected -> skip this item (or you can force selection; here we skip)
                return;
            }

            const notes = notesInput ? notesInput.value.trim() : null;
            if (result === 'failed' && (!notes || notes.length === 0)) {
                alert('Keterangan wajib diisi untuk item yang tidak lolos.');
                valid = false;
            }

            payloadItems.push({
                item_id: itemId,
                result: result,
                notes: notes
            });
        });

        if (!valid) return;

        if (payloadItems.length === 0) {
            return alert('Pilih hasil inspeksi untuk minimal 1 item sebelum menyimpan.');
        }

        // disable button
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

            // mark saved badge per item
            payloadItems.forEach(it => {
                const card = document.querySelector(`.item-card[data-item-id="${it.item_id}"]`);
                if (card) {
                    card.querySelector('.saved-badge').style.display = 'inline-block';
                }
            });

            // update top-cards in the page (if exist)
            const topLolos = document.querySelector('.qa-card.green h3');
            const topGagal = document.querySelector('.qa-card.red h3');

            if (topLolos && typeof json.lolos_count !== 'undefined') {
                topLolos.textContent = json.lolos_count;
            }
            if (topGagal && typeof json.gagal_count !== 'undefined') {
                topGagal.textContent = json.gagal_count;
            }

            // if all inspected, optionally change "Butuh Inspeksi" card
            const butuhCard = document.querySelector('.qa-card.yellow h3');
            if (butuhCard && typeof json.all_inspected !== 'boolean') {
                // nothing
            } else if (butuhCard && json.all_inspected === true) {
                butuhCard.textContent = 0;
            } else if (butuhCard && json.all_inspected === false) {
                // leave as-is, or decrement — we don't compute delta here
            }

            // feedback
            alert('Hasil inspeksi berhasil disimpan.');

            // after save, lock editing
            editMode = false;
            btnEdit.textContent = 'Edit';
            document.querySelectorAll('.item-card').forEach(card => {
                card.querySelector('.toggle-pass').style.pointerEvents = 'none';
                card.querySelector('.toggle-fail').style.pointerEvents = 'none';
                const ni = card.querySelector('.notes-input');
                if (ni) ni.setAttribute('readonly', true);
            });

            btnSave.disabled = false;
            btnSave.textContent = 'Simpan';
        } catch (err) {
            console.error(err);
            alert('Terjadi kesalahan saat menyimpan.');
            btnSave.disabled = false;
            btnSave.textContent = 'Simpan';
        }
    });
});
</script>
@endpush
