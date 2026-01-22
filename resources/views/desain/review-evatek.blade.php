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
.action-btn { border: none; font-size: 12px; font-weight: 700; border-radius: 14px; padding: 6px 14px; cursor: pointer; width: 100px; margin: 3px auto; color: white; transition: all 0.3s ease; }
.btn-upload { background: #000; }
.btn-save { background: #007bff; } /* Blue for Save Status */

/* Default state: Gray for decision buttons */
.btn-approve, .btn-reject, .btn-revisi { background: #e0e0e0; color: #555; }

/* Active states */
.btn-approve.active { background: #28a745; color: white; }
.btn-reject.active { background: #d62828; color: white; }
.btn-revisi.active { background: #ffc107; color: white; }

/* Disabled state */
.action-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background: #e0e0e0;
    color: #999;
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
.rev-check.status-revisi { background:#ffc107; border-color:#ffc107; }
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
                                <div class="link-status"></div>
                            </td>
                            <td>
                                <input type="text" class="link-input design-link" value="{{ $rev->design_link }}">
                                <button class="action-btn btn-upload save-link">Save</button>
                                <div class="link-status"></div>
                            </td>
                            <td>
                                @if(in_array($rev->status, ['approve', 'not approve', 'revisi']))
                                    <div class="d-flex flex-column align-items-center">
                                        @if($rev->status == 'approve')
                                            <span class="fw-bold text-success">Approved</span>
                                        @elseif($rev->status == 'not approve')
                                            <span class="fw-bold text-danger">Not Approved</span>
                                        @elseif($rev->status == 'revisi')
                                            <span class="fw-bold text-warning" style="color:#ffc107;">Revisi</span>
                                        @endif
                                        <span class="text-muted" style="font-size: 11px;">
                                            {{ \Carbon\Carbon::parse($rev->updated_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @else
                                    <button class="action-btn btn-approve approve-btn">Approve</button>
                                    <button class="action-btn btn-revisi revisi-btn">Revisi</button>
                                    <button class="action-btn btn-reject reject-btn">Not Approve</button>
                                    <button class="action-btn btn-save save-status" style="display:none;">Save Status</button>
                                @endif
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
        
        {{-- ✅ Desain Message Input --}}
        <div style="margin-top: 15px;">
            <textarea id="desainMessageInput" 
                      placeholder="Tulis pesan untuk vendor..." 
                      style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 13px; resize: none;"></textarea>
            <button onclick="sendDesainMessage()" 
                    style="margin-top: 10px; background: #000; color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px;">
                Send Message
            </button>
        </div>
    </div>
</div>

{{-- ========================= JAVASCRIPT ========================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF = "{{ csrf_token() }}";
const EVATEK_ID = "{{ $evatek->evatek_id }}";

document.addEventListener('DOMContentLoaded', function() {
    const existingLog = {!! json_encode($evatek->log ?? '') !!};
    if (existingLog) {
        document.getElementById('logText').value = existingLog;
    }
    checkDisabledRevisions();
});

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
}

// ✅ Check disabled revisions saat page load
function checkDisabledRevisions() {
    const rows = document.querySelectorAll('#revisionBody tr');
    rows.forEach(row => {
        const rev = row.dataset.rev;
        const checkbox = row.querySelector('.rev-check');
        
        const hasStatusApprove = checkbox.classList.contains('status-approve');
        const hasStatusReject = checkbox.classList.contains('status-reject');
        const hasStatusRevisi = checkbox.classList.contains('status-revisi');
        
        if (hasStatusApprove || hasStatusReject || hasStatusRevisi) {
            // Disable input link
            const vendorLinkInput = row.querySelector(".vendor-link");
            const designLinkInput = row.querySelector(".design-link");
            if (vendorLinkInput) vendorLinkInput.disabled = true;
            if (designLinkInput) designLinkInput.disabled = true;

            // Hapus tombol Save di kolom link
            const saveLinkButtons = row.querySelectorAll(".save-link");
            saveLinkButtons.forEach(btn => btn.remove());

            disableRevisionPermanent(rev);
        }
    });
}

function sendDesainMessage() {
    const messageInput = document.getElementById('desainMessageInput');
    const message = messageInput.value.trim();
    
    if (!message) {
        Swal.fire({
            icon: 'warning',
            title: 'Pesan Kosong',
            text: 'Silakan tulis pesan terlebih dahulu.',
        });
        return;
    }
    
    // Explicitly identify as Desain Team
    const userName = "{{ auth()->user()->name ?? 'Desain Team' }}";
    const logMessage = `[Desain - ${userName}]: ${message}`;
    
    addLog(logMessage);
    saveLogToDatabase(logMessage);
    
    // Clear input
    messageInput.value = '';
    
    // Optional: Show brief success feedback
    const btn = document.querySelector('button[onclick="sendDesainMessage()"]');
    const originalText = btn.innerText;
    btn.innerText = 'Sent ✓';
    setTimeout(() => { btn.innerText = originalText; }, 1000);
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

/* SELECTION LOGIC */
document.addEventListener("click", function(e) {
    if(e.target.classList.contains("approve-btn")) selectStatus(e.target.closest("tr"), e.target, 'approve');
    if(e.target.classList.contains("reject-btn")) selectStatus(e.target.closest("tr"), e.target, 'reject');
    if(e.target.classList.contains("revisi-btn")) selectStatus(e.target.closest("tr"), e.target, 'revisi');
    
    if (e.target.classList.contains("save-status")) {
        saveSelectedStatus(e.target.closest("tr"));
    }
});

function selectStatus(row, btn, status) {
    row.querySelectorAll('.action-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    const checkbox = row.querySelector(".rev-check");
    checkbox.classList.remove("status-approve", "status-reject", "status-revisi");
    
    if(status === 'approve') checkbox.classList.add("status-approve");
    if(status === 'reject') checkbox.classList.add("status-reject");
    if(status === 'revisi') checkbox.classList.add("status-revisi");
    
    row.dataset.selectedStatus = status;
    row.querySelector(".save-status").style.display = "block";
}

function saveSelectedStatus(row) {
    const selectedStatus = row.dataset.selectedStatus;
    
    // Validate Links
    const vendorLink = row.querySelector('.vendor-link').value.trim();
    // Assuming design link is key? Or optional? Usually Vendor Link is required for review.
    // If we are revising, maybe we don't need design link? but let's check basic requirement.
    // For Evatek, maybe we need Design Link ONLY if we are uploading?
    // Let's assume validation is: Vendor Link must be present (uploaded by vendor).
    // Design Link is optional but recommended if we are sending comments back?
    
    if (!vendorLink) {
         Swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap',
            text: 'Vendor Link belum tersedia.',
        });
        return;
    }

    if (!selectedStatus) return;

    let statusText = "";
    let confirmColor = "#3085d6";

    if (selectedStatus === "approve") {
        statusText = "Approve / Setujui";
        confirmColor = "#28a745";
    } else if (selectedStatus === "reject") {
        statusText = "Not Approve / Tolak";
        confirmColor = "#d62828";
    } else if (selectedStatus === "revisi") {
        statusText = "Revisi";
        confirmColor = "#ffc107";
    }

    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: `Anda akan menyimpan status: ${statusText}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            if (selectedStatus === "approve") submitApprove(row);
            else if (selectedStatus === "reject") submitReject(row);
            else if (selectedStatus === "revisi") submitRevisi(row);
        }
    });

}

function submitApprove(row) {
    fetch("{{ route('desain.evatek.approve') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF },
        body: JSON.stringify({ revision_id: row.dataset.revisionId })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            updateStatus(row.dataset.rev, "Approve");
            const logMessage = `✓ ${row.dataset.rev} APPROVED`;
            addLog(logMessage);
            saveLogToDatabase(logMessage);
            disableRevisionPermanent(row.dataset.rev);
            finalizeRow(row, 'Approved', 'text-success');
             Swal.fire('Berhasil!', 'Status Approved berhasil disimpan.', 'success');
        }
    });
}

function submitReject(row) {
    fetch("{{ route('desain.evatek.reject') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF },
        body: JSON.stringify({ revision_id: row.dataset.revisionId })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
             updateStatus(row.dataset.rev, "Rejected");
            const logMessage = `✗ ${row.dataset.rev} REJECTED`;
             addLog(logMessage);
            saveLogToDatabase(logMessage);
            disableRevisionPermanent(row.dataset.rev);
            finalizeRow(row, 'Not Approved', 'text-danger');
             Swal.fire('Berhasil!', 'Status Not Approved berhasil disimpan.', 'success');
        }
    });
}

function submitRevisi(row) {
    fetch("{{ route('desain.evatek.revisi') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF },
        body: JSON.stringify({ revision_id: row.dataset.revisionId })
    })
    .then(r => r.json())
    .then(r => {
        if (!r.success) return;
        let next = r.new_revision;
        
        disableRevisionPermanent(row.dataset.rev);
        finalizeRow(row, 'Revisi', 'text-warning');
        
        // Add new Row
        let html = `
        <tr data-revision-id="${next.revision_id}" data-rev="${next.revision_code}">
            <td><input type="checkbox" class="rev-check"></td>
            <td><strong>${next.revision_code}</strong></td>
            <td>
                <input type="text" class="link-input vendor-link">
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
                <button class="action-btn btn-save save-status" style="display:none;">Save Status</button>
            </td>
        </tr>`;
        document.getElementById("revisionBody").insertAdjacentHTML("afterbegin", html);
        
        updateStatus(next.revision_code, "On Progress");
        const logMessage = `⟳ ${row.dataset.rev} REVISI → ${next.revision_code} created`;
        addLog(logMessage);
        saveLogToDatabase(logMessage);
        
         Swal.fire('Berhasil!', 'Revisi berhasil dibuat.', 'success');
    });
}

function finalizeRow(row, text, textClass) {
    const actionCell = row.querySelector("td:last-child");
    const dateStr = new Date().toLocaleString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).replace(/\./g, ':');
    // Color logic adjustment if needed
    let colorStyle = "";
    if(textClass === 'text-warning') colorStyle = "color:#ffc107;";
    
    actionCell.innerHTML = `
        <div class="d-flex flex-column align-items-center">
            <span class="fw-bold ${textClass}" style="${colorStyle}">${text}</span>
            <span class="text-muted" style="font-size: 11px;">${dateStr}</span>
        </div>
    `;
    
    // Disable inputs
     const vendorLinkInput = row.querySelector(".vendor-link");
    const designLinkInput = row.querySelector(".design-link");
    if (vendorLinkInput) vendorLinkInput.disabled = true;
    if (designLinkInput) designLinkInput.disabled = true;

    // Remove buttons
    const saveLinkButtons = row.querySelectorAll(".save-link");
    saveLinkButtons.forEach(btn => btn.remove());
}

function updateStatus(revCode, status) {
    document.getElementById("statusRevision").innerText = revCode;
    document.getElementById("statusDivision").innerText = status;
    document.getElementById("statusDate").innerText = new Date().toLocaleDateString('id-ID');
}

function goBackWithRefresh() {
    if (document.referrer) {
        window.location.href = document.referrer;
    } else {
        history.back();
    }
}
</script>

@endsection