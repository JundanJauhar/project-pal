@extends('layouts.app')

@section('title', 'Review Evatek')

@push('styles')
<style>
/* --- layout --- */
.back-btn {
    display: inline-block;
    background: #e0e0e0;
    padding: 8px 18px;
    border-radius: 8px;
    font-weight: 600;
    color: #333;
    border: 1px solid #ccc;
    margin-bottom: 15px;
    cursor: pointer;
}
.eq-name { font-size: 16px; font-weight: 400; color: #444; margin-bottom: 3px; }
.vendor-name { font-size: 32px; font-weight: 800; margin-top: -4px; margin-bottom: 35px; }

/* status card */
.status-card {
    background: #f8f8f8;
    border-radius: 14px;
    padding: 25px 28px;
    margin-bottom: 20px;
    border: 1px solid #dddddd;
}
.status-card-header { font-size: 20px; font-weight: 700; }

.status-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 45px; }
.status-item-label { font-size: 13px; color: #666; }
.status-item-value { font-size: 22px; font-weight: 800; }
.status-small-value { font-size: 17px; font-weight: 700; }

/* tracking */
.tracking-card { background: #ffffff; border-radius: 14px; padding: 25px 30px; border: 1px solid #dcdcdc; min-height: 260px; }
.tracking-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
.tracking-table th { font-size: 14px; font-weight: 700; border-bottom: 1px solid #ccc; text-align: center; }
.tracking-table td { padding: 18px 0; border-bottom: 1px solid #e4e4e4; text-align: center; }

/* link input */
.link-input { width: 90%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 10px; }
.link-status { font-size: 12px; color: #777; }

/* buttons */
.action-btn { border: none; font-size: 12px; font-weight: 700; border-radius: 14px; padding: 6px 14px; cursor: pointer; width: 100px; margin: 3px auto; color: white; }
.btn-upload { background: #000; }
.btn-approve { background: #28a745; }
.btn-reject { background: #d62828; }
.btn-revisi { background: #ffcc00; }

/* ✅ Disabled state */
.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* checkbox */
.rev-check {
    -webkit-appearance: none;
    appearance: none;
    width: 18px;
    height: 18px;
    border: 2px solid #cfcfcf;
    border-radius: 4px;
    cursor: pointer;
}
.rev-check.status-approve { background: #28a745; border-color:#28a745; }
.rev-check.status-revisi { background:#ffcc00; border-color:#ffcc00; }
.rev-check.status-reject { background:#d62828; border-color:#d62828; }

/* log */
.log-card { background:#f8f8f8; border-radius:14px; padding:25px; border:1px solid #ddd; }
.log-textarea { width:100%; height:450px; border:none; background:transparent; font-family: monospace; font-size: 12px; line-height: 1.6; resize: none; }

.content-wrapper { display: grid; grid-template-columns: 3fr 1fr; gap: 35px; }
</style>
@endpush

@section('content')

<div id="evatekData" data-evatek-id="{{ $evatek->evatek_id }}" data-item-id="{{ $item->item_id }}"></div>

<button class="back-btn" onclick="goBackWithRefresh()">← Back</button>

<p class="eq-name">{{ $item->item_name }}</p>

<h2 class="vendor-name">
    {{ $evatek->vendor->name_vendor ?? '-' }}
</h2>

{{-- STATUS CARD --}}
<div class="status-card">
    <div class="status-card-header">Current Status</div>
    <div class="status-grid">
        <div>
            <p class="status-item-label">Revision</p>
            <p id="statusRevision" class="status-item-value">{{ $evatek->current_revision }}</p>
        </div>
        <div>
            <p class="status-item-label">Status</p>
            <p id="statusDivision" class="status-small-value">{{ ucfirst($evatek->status) }}</p>
        </div>
        <div>
            <p class="status-item-label">Last Update</p>
            <p id="statusDate" class="status-small-value">
                {{ $evatek->current_date ? \Carbon\Carbon::parse($evatek->current_date)->format('d/m/Y') : '-' }}
            </p>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div>
        {{-- TRACKING TABLE --}}
        <div class="tracking-card">
            <table class="tracking-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Revision</th>
                        <th>Vendor File Link</th>
                        <th>Divisi Desain File Link</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody id="revisionBody">
                    @foreach($revisions as $rev)
                        <tr data-revision-id="{{ $rev->revision_id }}" data-rev="{{ $rev->revision_code }}">
                            <td>
                                <input type="checkbox" class="rev-check
                                @if($rev->status=='approve') status-approve
                                @elseif($rev->status=='revisi') status-revisi
                                @elseif($rev->status=='not approve') status-reject
                                @endif">
                            </td>
                            <td><strong>{{ $rev->revision_code }}</strong></td>
                            <td>
                                <input type="text" class="link-input vendor-link" value="{{ $rev->vendor_link }}">
                                <button class="action-btn btn-upload save-link">Save</button>
                                <div class="link-status"></div>
                            </td>
                            <td>
                                <input type="text" class="link-input design-link" value="{{ $rev->design_link }}">
                                <button class="action-btn btn-upload save-link">Save</button>
                                <div class="link-status"></div>
                            </td>
                            <td>
                                <button class="action-btn btn-approve approve-btn">Approve</button>
                                <button class="action-btn btn-revisi revisi-btn">Revisi</button>
                                <button class="action-btn btn-reject reject-btn">Not Approve</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- LOG ACTIVITY --}}
    <div class="log-card">
        <div style="font-weight: bold; margin-bottom: 15px;">Activity Log</div>
        <textarea id="logText" class="log-textarea" readonly></textarea>
    </div>
</div>

{{-- ========================= JAVASCRIPT ========================= --}}
<script>
const CSRF = "{{ csrf_token() }}";
const EVATEK_ID = "{{ $evatek->evatek_id }}";

// ✅ Simpan disabled revisions di Set
const disabledRevisions = new Set();

// ✅ Function untuk update log activity
function addLog(message) {
    const logText = document.getElementById('logText');
    const timestamp = new Date().toLocaleString('id-ID');
    const entry = `[${timestamp}] ${message}\n`;
    logText.value = entry + logText.value;
}

// ✅ Function untuk disable buttons secara permanent
function disableRevisionPermanent(revisionCode) {
    disabledRevisions.add(revisionCode);
    // Tombol sudah dihapus, tidak perlu disable lagi
}

// ✅ Check disabled revisions saat page load
function checkDisabledRevisions() {
    const rows = document.querySelectorAll('#revisionBody tr');
    rows.forEach(row => {
        const rev = row.dataset.rev;
        const checkbox = row.querySelector('.rev-check');
        
        // Cek apakah revisi ini sudah punya status (approve, reject, atau revisi)
        const hasStatusApprove = checkbox.classList.contains('status-approve');
        const hasStatusReject = checkbox.classList.contains('status-reject');
        const hasStatusRevisi = checkbox.classList.contains('status-revisi');
        
        // Jika sudah ada status, hapus tombol decision
        if (hasStatusApprove || hasStatusReject || hasStatusRevisi) {
            const decisionCell = row.querySelector("td:last-child");
            if (decisionCell) {
                decisionCell.innerHTML = "";
            }
            disableRevisionPermanent(rev);
        }
    });
}

/* SAVE LINK */
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("save-link")) {
        let row = e.target.closest("tr");
        saveLink(row);
    }
});

function saveLink(row) {
    fetch("{{ route('desain.evatek.save-link') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({
            revision_id: row.dataset.revisionId,
            vendor_link: row.querySelector(".vendor-link").value,
            design_link: row.querySelector(".design-link").value
        })
    })
    .then(r => r.json())
    .then(r => {
        if (r.success) {
            row.querySelectorAll(".link-status").forEach(el => el.innerText = "Saved");
            
            const logMessage = `✓ Link saved for ${row.dataset.rev}`;
            addLog(logMessage);
            saveLogToDatabase(logMessage);
        }
    });
}

// ✅ Function untuk simpan log ke database
function saveLogToDatabase(message) {
    const logText = document.getElementById('logText').value;
    
    fetch("{{ route('desain.evatek.save-log') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({
            evatek_id: EVATEK_ID,
            log: logText
        })
    })
    .then(r => r.json())
    .then(r => {
        if (!r.success) {
            console.error('Failed to save log');
        }
    });
}

/* APPROVE */
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("approve-btn")) {
        approve(e.target.closest("tr"));
    }
});

function approve(row) {
    fetch("{{ route('desain.evatek.approve') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({ revision_id: row.dataset.revisionId })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            row.querySelector(".rev-check").classList.add("status-approve");
            updateStatus(row.dataset.rev, "Approve");
            
            const logMessage = `✓ ${row.dataset.rev} APPROVED`;
            addLog(logMessage);
            saveLogToDatabase(logMessage);
            
            disableRevisionPermanent(row.dataset.rev);
        }
    });
}

/* REJECT */
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("reject-btn")) {
        rejectRev(e.target.closest("tr"));
    }
});

function rejectRev(row) {
    fetch("{{ route('desain.evatek.reject') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({ revision_id: row.dataset.revisionId })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            row.querySelector(".rev-check").classList.add("status-reject");
            updateStatus(row.dataset.rev, "Rejected");
            
            const logMessage = `✗ ${row.dataset.rev} REJECTED`;
            addLog(logMessage);
            saveLogToDatabase(logMessage);
            
            disableRevisionPermanent(row.dataset.rev);
        }
    });
}

/* REVISI */
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("revisi-btn")) {
        revisi(e.target.closest("tr"));
    }
});

function revisi(row) {
    fetch("{{ route('desain.evatek.revisi') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({ revision_id: row.dataset.revisionId })
    })
    .then(r => r.json())
    .then(r => {
        if (!r.success) return;

        let next = r.new_revision;

        // ✅ Ubah checkbox menjadi kuning untuk revisi
        const checkbox = row.querySelector(".rev-check");
        checkbox.classList.remove("status-approve", "status-reject");
        checkbox.classList.add("status-revisi");

        // ✅ Hapus semua tombol decision dari baris ini
        const decisionCell = row.querySelector("td:last-child");
        decisionCell.innerHTML = "";

        // ✅ Simpan revisi ini ke disabled set
        disableRevisionPermanent(row.dataset.rev);

        let html = `
        <tr data-revision-id="${next.revision_id}" data-rev="${next.revision_code}">
            <td><input type="checkbox" class="rev-check"></td>
            <td><strong>${next.revision_code}</strong></td>
            <td>
                <input type="text" class="link-input vendor-link">
                <button class="action-btn btn-upload save-link">Save</button>
                <div class="link-status"></div>
            </td>
            <td>
                <input type="text" class="link-input design-link">
                <button class="action-btn btn-upload save-link">Save</button>
                <div class="link-status"></div>
            </td>
            <td>
                <button class="action-btn btn-approve approve-btn">Approve</button>
                <button class="action-btn btn-revisi revisi-btn">Revisi</button>
                <button class="action-btn btn-reject reject-btn">Not Approve</button>
            </td>
        </tr>`;

        document.getElementById("revisionBody").insertAdjacentHTML("beforeend", html);

        updateStatus(next.revision_code, "On Progress");
        
        const logMessage = `⟳ ${row.dataset.rev} REVISI → ${next.revision_code} created`;
        addLog(logMessage);
        saveLogToDatabase(logMessage);
    });
}

/* UPDATE STATUS CARD */
function updateStatus(revCode, status) {
    document.getElementById("statusRevision").innerText = revCode;
    document.getElementById("statusDivision").innerText = status;
    document.getElementById("statusDate").innerText = new Date().toLocaleDateString('id-ID');
}

// ✅ Load existing log dan check disabled revisions saat page load
document.addEventListener('DOMContentLoaded', function() {
    const existingLog = {!! json_encode($evatek->log ?? '') !!};
    if (existingLog) {
        document.getElementById('logText').value = existingLog;
    }
    checkDisabledRevisions();
});

// ✅ Function untuk back dengan refresh halaman sebelumnya
function goBackWithRefresh() {
    // Simpan referrer untuk reload
    if (document.referrer) {
        window.location.href = document.referrer;
    } else {
        history.back();
    }
}
</script>

@endsection