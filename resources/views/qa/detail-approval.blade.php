@extends('layouts.app')

@section('title', 'Detail Approval — Inspeksi Barang')

@push('styles')
<style>
    /* ======= HEADER SECTION ======= */
    .top-action-wrapper {
        width: 100%;
        display: flex;
        justify-content: flex-end;
        padding-right: 50px;
        margin-top: 25px;
    }

    .btn-back {
        font-size: 26px;
        color: #DA3B3B;
        cursor: pointer;
    }

    /* ======= INFO CARD ======= */
    .procurement-header {
        padding: 25px;
        background: white;
        border-radius: 14px;
        box-shadow: 0px 2px 6px rgba(0,0,0,0.08);
        margin-top: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-top: 20px;
    }

    .info-label {
        font-weight: 600;
        color: #555;
        margin-bottom: 5px;
    }

    .info-value {
        color: #333;
        font-size: 15px;
    }

    /* ======= TABLE SECTION ======= */
    .dashboard-table-wrapper {
        padding: 25px;
        border-radius: 14px;
        margin-top: 25px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    table.dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-table thead th {
        padding: 14px;
        background: #f5f5f5;
        font-size: 14px;
        font-weight: 600;
        color: #444;
        border-bottom: 2px solid #dcdcdc;
        text-align: center;
    }

    .dashboard-table tbody td {
        padding: 14px;
        border-bottom: 1px solid #e0e0e0;
        text-align: center;
        vertical-align: middle;
        font-size: 15px;
    }

    .dashboard-table tbody tr:hover {
        background: #fafafa;
    }

    /* ======= INPUT ATA ======= */
    .ata-input {
        width: 150px;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }

    /* ======= TOGGLE ======= */
    .toggle-box {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #f0f0f0;
        border: 1px solid #dcdcdc;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: .15s;
    }

    .toggle-box.pass {
        background: #dff7dd;
        color: #137d2c;
        border-color: #bde7bb;
    }

    .toggle-box.fail {
        background: #ffe5e5;
        color: #c62828;
        border-color: #f3b9b9;
    }

    textarea.notes-input {
        width: 100%;
        min-height: 55px;
        border-radius: 8px;
        padding: 8px;
        resize: vertical;
        border: 1px solid #cfcfcf;
        font-size: 14px;
    }

    /* ======= ACTION BUTTONS ======= */
    .detail-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 25px;
        margin-bottom: 40px;
    }

    .btn-edit {
        background: #e4e4e4;
        padding: 10px 18px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-weight: 600;
    }

    .btn-save {
        background: #138a33;
        padding: 10px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        border: none;
    }
</style>
@endpush


@section('content')

{{-- BACK BUTTON --}}
<div class="top-action-wrapper">
    <a href="{{ route('inspections.index') }}" class="btn-back">
        <i class="bi bi-arrow-left-circle"></i>
    </a>
</div>

{{-- HEADER PROCUREMENT --}}
<div class="procurement-header">
    <h3 class="mb-3">Detail Pengadaan</h3>

    <div class="info-grid">
        <div>
            <div class="info-label">Kode Procurement</div>
            <div class="info-value"><strong>{{ $procurement->code_procurement }}</strong></div>

            <div class="info-label mt-3">Nama Pengadaan</div>
            <div class="info-value">{{ $procurement->name_procurement }}</div>

            <div class="info-label mt-3">Vendor</div>
            <div class="info-value">
                {{ $procurement->requestProcurements->first()?->vendor?->name_vendor ?? '-' }}
            </div>
        </div>

        <div>
            <div class="info-label">Prioritas</div>
            <span class="badge bg-danger px-3 py-1">
                {{ strtoupper($procurement->priority) }}
            </span>

            <div class="info-label mt-3">Tanggal Dibuat</div>
            <div class="info-value">{{ $procurement->start_date?->format('d/m/Y') }}</div>

            <div class="info-label mt-3">Tanggal Tenggat</div>
            <div class="info-value">{{ $procurement->end_date?->format('d/m/Y') }}</div>
        </div>
    </div>
</div>

{{-- TABLE INSPEKSI --}}
<h5 class="mt-5 mb-2">Detail Item Inspeksi</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>Nama Barang</th>
                    <th>Spesifikasi</th>
                    <th>Jumlah</th>
                    <th>ATA</th>
                    <th>Hasil</th>
                    <th>Keterangan</th>
                </tr>
            </thead>

            <tbody id="tableBody">
                @foreach($items as $item)
                    @php
                        $latest = $item->inspectionReports->sortByDesc('inspection_date')->first();
                        $current = $latest?->result;
                        $notes = $latest?->notes ?? '';
                    @endphp

                    <tr class="row-item" data-item-id="{{ $item->item_id }}">
                        <td><strong>{{ $item->item_name }}</strong></td>
                        <td>{{ $item->specification ?? '-' }}</td>
                        <td>{{ $item->amount }} {{ $item->unit }}</td>

                        {{-- NEW: ATA manual input --}}
                        <td>
                            <input type="date"
                                   class="ata-input"
                                   value="{{ $item->arrival_date }}"
                                   data-field="arrival_date">
                        </td>

                        {{-- TOGGLE --}}
                        <td>
                            <div style="display:flex; gap:10px; justify-content:center;">
                                <div class="toggle-box toggle-pass {{ $current === 'passed' ? 'pass' : '' }}" data-value="passed">
                                    <i class="bi bi-check-lg"></i>
                                </div>
                                <div class="toggle-box toggle-fail {{ $current === 'failed' ? 'fail' : '' }}" data-value="failed">
                                    <i class="bi bi-x-lg"></i>
                                </div>
                            </div>
                        </td>

                        {{-- NOTES --}}
                        <td>
                            <textarea class="notes-input" placeholder="Keterangan (wajib jika Tidak Lolos)">{{ $notes }}</textarea>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ACTION BUTTON --}}
<div class="detail-actions">
    <button class="btn-edit" id="btnEdit">Edit</button>
    <button class="btn-save" id="btnSave">Simpan</button>
</div>

@endsection


{{-- ========== SCRIPT (LOGIC ASLI DIPERTAHANKAN) ========== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    /* ======== INITIALIZE TOGGLES ======== */
    document.querySelectorAll('.row-item').forEach(row => {

        const pass = row.querySelector('.toggle-pass');
        const fail = row.querySelector('.toggle-fail');
        const notes = row.querySelector('.notes-input');

        if (pass.classList.contains('pass')) notes.style.display = 'none';
        else if (fail.classList.contains('fail')) notes.style.display = 'block';
        else notes.style.display = 'none';

        pass.addEventListener('click', () => {
            pass.classList.toggle('pass');
            fail.classList.remove('fail');
            notes.style.display = pass.classList.contains('pass') ? 'none' : 'block';
        });

        fail.addEventListener('click', () => {
            fail.classList.toggle('fail');
            pass.classList.remove('pass');
            notes.style.display = fail.classList.contains('fail') ? 'block' : 'none';
        });

    });


    /* ======== EDIT MODE ======== */
    let editMode = true;
    const btnEdit = document.getElementById('btnEdit');

    function setEditMode(flag) {
        editMode = flag;
        btnEdit.textContent = editMode ? "Batal" : "Edit";

        document.querySelectorAll('.toggle-box').forEach(el => {
            el.style.pointerEvents = editMode ? "auto" : "none";
        });

        document.querySelectorAll(".notes-input, .ata-input").forEach(el => {
            el.readOnly = !editMode;
            el.style.pointerEvents = editMode ? "auto" : "none";
        });
    }

    setEditMode(true);

    btnEdit.addEventListener('click', () => {
        setEditMode(!editMode);
    });


    /* ======== SAVE DATA ======== */
    const btnSave = document.getElementById('btnSave');

    btnSave.addEventListener('click', async () => {

        const payloadItems = [];
        let valid = true;

        document.querySelectorAll('.row-item').forEach(row => {

            const itemId = row.dataset.itemId;

            const passActive = row.querySelector('.toggle-pass').classList.contains('pass');
            const failActive = row.querySelector('.toggle-fail').classList.contains('fail');
            const notesInput = row.querySelector('.notes-input');
            const ataInput = row.querySelector('.ata-input');

            let result = null;
            if (passActive) result = "passed";
            if (failActive) result = "failed";

            if (!result) return;

            const notes = notesInput.value.trim();
            if (result === "failed" && notes === "") {
                alert("Keterangan wajib diisi untuk item yang tidak lolos.");
                valid = false;
            }

            payloadItems.push({
                item_id: itemId,
                result,
                notes,
                arrival_date: ataInput.value
            });

        });

        if (!valid) return;
        if (payloadItems.length === 0)
            return alert("Pilih hasil inspeksi untuk minimal 1 item.");

        btnSave.disabled = true;
        btnSave.textContent = "Menyimpan...";

        try {
            const response = await fetch("{{ route('qa.detail-approval.save', ['procurement_id' => $procurement->procurement_id]) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify({ items: payloadItems })
            });

            const json = await response.json();

            if (!json.success) {
                alert(json.message || "Gagal menyimpan.");
            } else {
                alert("Hasil inspeksi berhasil disimpan.");
                setEditMode(false);
            }

        } catch (error) {
            console.error(error);
            alert("Terjadi kesalahan saat menyimpan.");
        }

        btnSave.disabled = false;
        btnSave.textContent = "Simpan";
    });
});
</script>
@endpush
