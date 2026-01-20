@extends('layouts.app')

@section('title', 'Review Kontrak')

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
.procurement-name { font-size: 16px; font-weight: 400; color: #444; margin-bottom: 3px; }
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
/* buttons */
.action-btn { border: none; font-size: 12px; font-weight: 700; border-radius: 14px; padding: 6px 14px; cursor: pointer; width: 100px; margin: 3px auto; color: white; transition: all 0.3s ease; }
.btn-save { background: #000; }
.btn-upload { background: #000; }

/* Default state: Gray */
.btn-approve, .btn-reject, .btn-revisi { background: #e0e0e0; color: #555; }

/* Active states */
.btn-approve.active { background: #28AC00; color: white; }
.btn-reject.active { background: #F10303; color: white; }
.btn-revisi.active { background: #ECAD02; color: white; }

/* ✅ Disabled state */
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
.rev-check.status-approve { background: #28AC00; border-color:#28AC00; }
.rev-check.status-revisi { background:#ECAD02; border-color:#ECAD02; }
.rev-check.status-reject { background:#F10303; border-color:#F10303; }

/* log */
.log-card { background:#f8f8f8; border-radius:14px; padding:25px; border:1px solid #ddd; }
.log-textarea { width:100%; height:350px; border:none; background:transparent; font-family: monospace; font-size: 12px; line-height: 1.6; resize: none; }

.content-wrapper { display: grid; grid-template-columns: 3fr 1fr; gap: 35px; }
</style>
@endpush

@section('content')

<div id="contractReviewData" data-review-id="{{ $contractReview->contract_review_id }}" data-procurement-id="{{ $procurement->procurement_id }}"></div>

<button class="back-btn" onclick="goBackWithRefresh()">← Back</button>

<p class="procurement-name">{{ $procurement->code_procurement }} -> {{ $procurement->project->project_name ?? '-' }} -> {{ $procurement->name_procurement ?? '-' }}</p>

<h2 class="vendor-name">
    {{ $contractReview->vendor->name_vendor ?? '-' }}
</h2>

{{-- STATUS CARD --}}
<div class="status-card">
    <div class="status-card-header">Current Status</div>
    <div class="status-grid">
        <div>
            <p class="status-item-label">Revision</p>
            <p id="statusRevision" class="status-item-value">{{ $contractReview->current_revision }}</p>
        </div>
        <div>
            <p class="status-item-label">Status</p>
            <p id="statusDivision" class="status-small-value">{{ ucfirst(str_replace('_', ' ', $contractReview->status)) }}</p>
        </div>
        <div>
            <p class="status-item-label">Last Update</p>
            <p id="statusDate" class="status-small-value">
                {{ $contractReview->date_vendor_feedback ? \Carbon\Carbon::parse($contractReview->date_vendor_feedback)->format('d/m/Y') : 
                   ($contractReview->date_sent_to_vendor ? \Carbon\Carbon::parse($contractReview->date_sent_to_vendor)->format('d/m/Y') : 
                   \Carbon\Carbon::parse($contractReview->start_date)->format('d/m/Y')) }}
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
                        <th>SC File Link</th>
                        <th>Decision</th>
                    </tr>
                </thead>
                <tbody id="revisionBody">
                    @foreach($revisions as $rev)
                        <tr data-revision-id="{{ $rev->contract_review_revision_id }}" data-rev="{{ $rev->revision_code }}">
                            <td>
                                <input type="checkbox" class="rev-check
                                @if($rev->result == 'approve') status-approve
                                @elseif($rev->result == 'revisi') status-revisi
                                @elseif($rev->result == 'not_approve') status-reject
                                @endif">
                            </td>
                            <td><strong>{{ $rev->revision_code }}</strong></td>
                            <td>
                                <input type="link " class="link-input vendor-link" value="{{ $rev->vendor_link }}"  readonly>
                                <div class="link-status"></div>
                            </td>
                            <td>
                                <input type="text" class="link-input sc-link" value="{{ $rev->sc_link }}" >
                                <button class="action-btn btn-upload save-link">Save</button>
                                <div class="link-status"></div>
                            </td>
                            <td>
                                @if(in_array($rev->result, ['approve', 'not_approve', 'revisi']))
                                    <div class="d-flex flex-column align-items-center">
                                        @if($rev->result == 'approve')
                                            <span class="fw-bold text-success">Approved</span>
                                        @elseif($rev->result == 'not_approve')
                                            <span class="fw-bold text-danger">Not Approved</span>
                                        @elseif($rev->result == 'revisi')
                                            <span class="fw-bold text-warning" style="color:#ECAD02;">Revisi</span>
                                        @endif
                                        <span class="text-muted" style="font-size: 11px;">
                                            {{ \Carbon\Carbon::parse($rev->updated_at)->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                @else
                                    <button class="action-btn btn-approve approve-btn">Approve</button>
                                    <button class="action-btn btn-revisi revisi-btn">Revisi</button>
                                    <button class="action-btn btn-reject reject-btn">Not Approve</button>
                                    <button class="action-btn btn-save save-status" style="display:none; background:#007bff;">Save Status</button>
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
        
        {{-- ✅ SC Message Input --}}
        <div style="margin-top: 15px;">
            <textarea id="scMessageInput" 
                      placeholder="Tulis pesan untuk tim desain..." 
                      style="width: 100%; height: 80px; padding: 10px; border: 1px solid #ccc; border-radius: 8px; font-size: 13px; resize: none;"></textarea>
            <button onclick="sendSCMessage()" 
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
const CONTRACT_REVIEW_ID = "{{ $contractReview->contract_review_id }}";

// ... (existing code) ...

// ✅ Function untuk save status dengan konfirmasi popup
function saveSelectedStatus(row) {
    const selectedStatus = row.dataset.selectedStatus;

    // VALIDATION: Cek apakah kedua link sudah diisi
    const vendorLink = row.querySelector('.vendor-link').value.trim();
    const scLink = row.querySelector('.sc-link').value.trim();

    if (!vendorLink || !scLink) {
        Swal.fire({
            icon: 'warning',
            title: 'Data Belum Lengkap',
            text: 'Mohon isi "Vendor File Link" dan "SC File Link" terlebih dahulu sebelum menyimpan status.',
            confirmButtonColor: '#3085d6',
        });
        return;
    }
    
    if (!selectedStatus) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Pilih status terlebih dahulu',
            confirmButtonColor: '#3085d6',
        });
        return;
    }
    
    let statusText = "";
    let confirmColor = "#3085d6";

    if (selectedStatus === "approve") {
        statusText = "Approve / Setujui";
        confirmColor = "#28AC00";
    } else if (selectedStatus === "reject") {
        statusText = "Not Approve / Tolak";
        confirmColor = "#F10303";
    } else if (selectedStatus === "revisi") {
        statusText = "Revisi";
        confirmColor = "#ECAD02";
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
            // Show loading
            Swal.fire({
                title: 'Menyimpan...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            if (selectedStatus === "approve") {
                submitApprove(row);
            } else if (selectedStatus === "reject") {
                submitReject(row);
            } else if (selectedStatus === "revisi") {
                submitRevisi(row);
            }
        }
    });
}

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
        
        // Cek apakah revisi ini sudah punya status (approve, reject, atau revisi)
        const hasStatusApprove = checkbox.classList.contains('status-approve');
        const hasStatusReject = checkbox.classList.contains('status-reject');
        const hasStatusRevisi = checkbox.classList.contains('status-revisi');
        
        // Jika sudah ada status, disable input dan hapus tombol
        if (hasStatusApprove || hasStatusReject || hasStatusRevisi) {
            // Disable input link
            const vendorLinkInput = row.querySelector(".vendor-link");
            const scLinkInput = row.querySelector(".sc-link");
            if (vendorLinkInput) vendorLinkInput.disabled = true;
            if (scLinkInput) scLinkInput.disabled = true;

            // Hapus tombol Save di kolom link
            const saveLinkButtons = row.querySelectorAll(".save-link");
            saveLinkButtons.forEach(btn => btn.remove());

            // Note: Action buttons are handled by server-side rendering for history
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
    fetch("{{ route('supply-chain.contract-review.save-link') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({
            revision_id: row.dataset.revisionId,
            vendor_link: row.querySelector(".vendor-link").value,
            sc_link: row.querySelector(".sc-link").value
        })
    })
    .then(r => r.json())
    .then(r => {
        if (r.success) {
            row.querySelectorAll(".link-status").forEach(el => el.innerText = "Saved");
            
            const logMessage = `✓ Links saved for ${row.dataset.rev}`;
            addLog(logMessage);
            saveLogToDatabase(logMessage);
        }
    });
}

// ✅ Function untuk simpan log ke database
function saveLogToDatabase(message) {
    const logText = document.getElementById('logText').value;
    
    fetch("{{ route('supply-chain.contract-review.save-log') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({
            contract_review_id: CONTRACT_REVIEW_ID,
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
        selectApprove(e.target.closest("tr"), e.target);
    }
});

function selectApprove(row, btn) {
    // Reset all buttons in this row
    row.querySelectorAll('.action-btn').forEach(b => b.classList.remove('active'));
    
    // Set this button active
    if (btn) btn.classList.add('active');

    const checkbox = row.querySelector(".rev-check");
    checkbox.classList.remove("status-reject", "status-revisi");
    checkbox.classList.add("status-approve");
    
    row.dataset.selectedStatus = "approve";
    
    const saveBtn = row.querySelector(".save-status");
    saveBtn.style.display = "block";
}

function submitApprove(row) {
    fetch("{{ route('supply-chain.contract-review.approve') }}", {
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
            updateStatus(row.dataset.rev, "Approved");
            
            const logMessage = `✓ ${row.dataset.rev} APPROVED`;
            addLog(logMessage);
            saveLogToDatabase(logMessage);
            
            // Disable input link
            const vendorLinkInput = row.querySelector(".vendor-link");
            const scLinkInput = row.querySelector(".sc-link");
            if (vendorLinkInput) vendorLinkInput.disabled = true;
            if (scLinkInput) scLinkInput.disabled = true;

            // Hapus tombol Save di kolom link
            const saveLinkButtons = row.querySelectorAll(".save-link");
            saveLinkButtons.forEach(btn => btn.remove());
            
            // Update Action Cell with History
            const actionCell = row.querySelector("td:last-child");
            const dateStr = new Date().toLocaleString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).replace(/\./g, ':');
            actionCell.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <span class="fw-bold text-success">Approved</span>
                    <span class="text-muted" style="font-size: 11px;">${dateStr}</span>
                </div>
            `;
            
            disableRevisionPermanent(row.dataset.rev);

            // ✅ Close loading & Show Success
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Status Approved berhasil disimpan.',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

/* NOT APPROVE */
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("reject-btn")) {
        selectReject(e.target.closest("tr"), e.target);
    }
});

function selectReject(row, btn) {
    // Reset all buttons in this row
    row.querySelectorAll('.action-btn').forEach(b => b.classList.remove('active'));
    
    // Set this button active
    if (btn) btn.classList.add('active');

    const checkbox = row.querySelector(".rev-check");
    checkbox.classList.remove("status-approve", "status-revisi");
    checkbox.classList.add("status-reject");
    
    row.dataset.selectedStatus = "reject";
    
    const saveBtn = row.querySelector(".save-status");
    saveBtn.style.display = "block";
}

function submitReject(row) {
    fetch("{{ route('supply-chain.contract-review.reject') }}", {
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
            updateStatus(row.dataset.rev, "Not Approved");
            
            const logMessage = `✗ ${row.dataset.rev} NOT APPROVED`;
            addLog(logMessage);
            saveLogToDatabase(logMessage);
            
            // Disable input link
            const vendorLinkInput = row.querySelector(".vendor-link");
            const scLinkInput = row.querySelector(".sc-link");
            if (vendorLinkInput) vendorLinkInput.disabled = true;
            if (scLinkInput) scLinkInput.disabled = true;

            // Hapus tombol Save di kolom link
            const saveLinkButtons = row.querySelectorAll(".save-link");
            saveLinkButtons.forEach(btn => btn.remove());
            
            // Update Action Cell with History
            const actionCell = row.querySelector("td:last-child");
            const dateStr = new Date().toLocaleString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).replace(/\./g, ':');
            actionCell.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <span class="fw-bold text-danger">Not Approved</span>
                    <span class="text-muted" style="font-size: 11px;">${dateStr}</span>
                </div>
            `;

            disableRevisionPermanent(row.dataset.rev);

            // ✅ Close loading & Show Success
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Status Not Approved berhasil disimpan.',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

/* REVISI */
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("revisi-btn")) {
        selectRevisi(e.target.closest("tr"), e.target);
    }
});

function selectRevisi(row, btn) {
    // Reset all buttons in this row
    row.querySelectorAll('.action-btn').forEach(b => b.classList.remove('active'));
    
    // Set this button active
    if (btn) btn.classList.add('active');

    const checkbox = row.querySelector(".rev-check");
    checkbox.classList.remove("status-approve", "status-reject");
    checkbox.classList.add("status-revisi");
    
    row.dataset.selectedStatus = "revisi";
    
    const saveBtn = row.querySelector(".save-status");
    saveBtn.style.display = "block";
}

function submitRevisi(row) {
    fetch("{{ route('supply-chain.contract-review.revisi') }}", {
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

        // Ubah checkbox menjadi kuning untuk revisi
        const checkbox = row.querySelector(".rev-check");
        checkbox.classList.remove("status-approve", "status-reject");
        checkbox.classList.add("status-revisi");

        // Disable semua input link di row ini
        const vendorLinkInput = row.querySelector(".vendor-link");
        const scLinkInput = row.querySelector(".sc-link");
        if (vendorLinkInput) vendorLinkInput.disabled = true;
        if (scLinkInput) scLinkInput.disabled = true;

        // Hapus semua tombol di row ini and replace with history
        const actionCell = row.querySelector("td:last-child");
        const dateStr = new Date().toLocaleString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }).replace(/\./g, ':');
        actionCell.innerHTML = `
            <div class="d-flex flex-column align-items-center">
                <span class="fw-bold text-warning" style="color:#ECAD02;">Revisi</span>
                <span class="text-muted" style="font-size: 11px;">${dateStr}</span>
            </div>
        `;

        // Hapus tombol Save di kolom link
        const saveLinkButtons = row.querySelectorAll(".save-link");
        saveLinkButtons.forEach(btn => btn.remove());

        disableRevisionPermanent(row.dataset.rev);

        let html = `
        <tr data-revision-id="${next.contract_review_revision_id}" data-rev="${next.revision_code}">
            <td><input type="checkbox" class="rev-check"></td>
            <td><strong>${next.revision_code}</strong></td>
            <td>
                <input type="text" class="link-input vendor-link" readonly>
                <div class="link-status"></div>
            </td>
            <td>
                <input type="text" class="link-input sc-link">
                <button class="action-btn btn-upload save-link">Save</button>
                <div class="link-status"></div>
            </td>
            <td>
                <button class="action-btn btn-approve approve-btn">Approve</button>
                <button class="action-btn btn-revisi revisi-btn">Revisi</button>
                <button class="action-btn btn-reject reject-btn">Not Approve</button>
                <button class="action-btn btn-save save-status" style="display:none; background:#007bff;">Save Status</button>
            </td>
        </tr>`;

        document.getElementById("revisionBody").insertAdjacentHTML("afterbegin", html);

        updateStatus(next.revision_code, "On Progress");
        
        const logMessage = `⟳ ${row.dataset.rev} REVISI → ${next.revision_code} created`;
        addLog(logMessage);
        saveLogToDatabase(logMessage);

        // ✅ Close loading & Show Success
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Revisi baru berhasil dibuat.',
            timer: 2000,
            showConfirmButton: false
        });
    });
}

/* SAVE STATUS */
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("save-status")) {
        saveSelectedStatus(e.target.closest("tr"));
    }
});



/* UPDATE STATUS CARD */
function updateStatus(revCode, status) {
    document.getElementById("statusRevision").innerText = revCode;
    document.getElementById("statusDivision").innerText = status;
    document.getElementById("statusDate").innerText = new Date().toLocaleDateString('id-ID');
}

// ✅ Function untuk SC mengirim pesan
function sendSCMessage() {
    const messageInput = document.getElementById('scMessageInput');
    const message = messageInput.value.trim();
    
    if (!message) {
        alert('Pesan tidak boleh kosong');
        return;
    }
    
    const scName = "{{ auth()->user()->name ?? 'SC' }}";
    const logMessage = `[SC - ${scName}]: ${message}`;
    addLog(logMessage);
    saveLogToDatabase(logMessage);
    
    // Clear input
    messageInput.value = '';
}

// ✅ Load existing log dan check disabled revisions saat page load
document.addEventListener('DOMContentLoaded', function() {
    const existingLog = {!! json_encode($contractReview->log ?? '') !!};
    if (existingLog) {
        document.getElementById('logText').value = existingLog;
    }
    checkDisabledRevisions();
});

// ✅ Function untuk back dengan refresh halaman sebelumnya
function goBackWithRefresh() {
    if (document.referrer) {
        window.location.href = document.referrer;
    } else {
        history.back();
    }
}
</script>

@endsection
