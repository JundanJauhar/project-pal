@extends('layouts.app')

@section('title', 'Review Kontrak - Vendor')

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
.action-btn { border: none; font-size: 12px; font-weight: 700; border-radius: 14px; padding: 6px 14px; cursor: pointer; width: 100px; margin: 3px auto; color: white; }
.btn-upload { background: #000; }

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
.log-textarea { width:100%; height:320px; border:none; background:transparent; font-family: monospace; font-size: 12px; line-height: 1.6; resize: none; }
.message-input { width:100%; height:80px; border:1px solid #ccc; border-radius:10px; padding:10px; font-family: sans-serif; font-size: 14px; resize: none; margin-top: 15px; }
.btn-send { background: #000; color: white; border: none; padding: 10px 30px; border-radius: 10px; font-weight: 600; margin-top: 10px; cursor: pointer; }

.content-wrapper { display: grid; grid-template-columns: 3fr 1fr; gap: 35px; }

.history-card { background:#fff; border-radius:14px; padding:25px; border:1px solid #ddd; margin-top:20px; }
</style>
@endpush

@section('content')

<div id="contractReviewData" data-review-id="{{ $contractReview->contract_review_id }}"></div>

<button class="back-btn" onclick="window.location.href='{{ route('vendor.index') }}'">← Back</button>

@php
    $proc = $contractReview->procurement ?? null;
    $proj = $proc ? $proc->project : ($contractReview->project ?? null);
    $requestProc = $proc ? $proc->requestProcurements->first() : null;
    $items = $requestProc ? $requestProc->items : collect();
    $item = $items->first();
@endphp

<p class="procurement-name">{{ $proc->code_procurement ?? '-' }} - {{ $proj->project_name ?? '-' }}</p>

<h2 class="vendor-name">
    {{ $item->item_name ?? 'N/A' }}
    <small class="text-muted" style="font-size:20px; font-weight:400;">{{ $contractReview->current_revision }}</small>
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
                {{ $contractReview->updated_at ? $contractReview->updated_at->format('d/m/Y') : 
                   \Carbon\Carbon::parse($contractReview->start_date)->format('d/m/Y') }}
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
                        <th>Status</th>
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
                                @endif" disabled>
                            </td>
                            <td><strong>{{ $rev->revision_code }}</strong></td>
                            <td>
                                <input type="text" class="link-input vendor-link" value="{{ $rev->vendor_link }}">
                                <button class="action-btn btn-upload save-link">Save</button>
                                <div class="link-status"></div>
                            </td>
                            <td>
                                <input type="text" class="link-input sc-link" value="{{ $rev->sc_link }}" disabled>
                            </td>
                            <td>
                                @if($rev->result == 'approve')
                                    <span style="color: #28AC00; font-weight: 700;">✔ Approved</span>
                                @elseif($rev->result == 'not_approve')
                                    <span style="color: #F10303; font-weight: 700;">✘ Not Approved</span>
                                @elseif($rev->result == 'revisi')
                                    <span style="color: #ECAD02; font-weight: 700;">⟳ Revisi</span>
                                @else
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- LOG ACTIVITY --}}
    <div>
        <div class="log-card">
            <div style="font-weight: bold; margin-bottom: 15px;">Activity Log</div>
            <textarea id="logText" class="log-textarea" readonly>{{ $contractReview->log ?? '' }}</textarea>
            
            <textarea id="messageInput" class="message-input" placeholder="Tulis pesan untuk tim desain..."></textarea>
            <button class="btn-send" onclick="sendMessage()">Send Message</button>
        </div>

        <div class="history-card">
            <div style="font-weight: bold; margin-bottom: 15px;">History</div>
            <div id="historyContent" style="font-size: 12px; color: #666;">
                @foreach($revisions as $index => $rev)
                    <div style="margin-bottom: 10px; padding: 8px; background: #f9f9f9; border-radius: 6px;">
                        <strong>{{ $rev->revision_code }}</strong>
                        <span style="font-size: 11px; color: #999;">
                            {{ $rev->created_at->format('d/m/Y H:i') }}
                        </span>
                        <br>
                        @if($rev->result == 'approve')
                            <span style="color: #28AC00;">Approved</span>
                        @elseif($rev->result == 'not_approve')
                            <span style="color: #F10303;">Not Approved</span>
                        @elseif($rev->result == 'revisi')
                            <span style="color: #ECAD02;">Revisi</span>
                        @else
                            <span style="color: #999;">Pending</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ========================= JAVASCRIPT ========================= --}}
<script>
const CSRF = "{{ csrf_token() }}";
const CONTRACT_REVIEW_ID = "{{ $contractReview->contract_review_id }}";

// ✅ Simpan disabled revisions di Set
const disabledRevisions = new Set();

// ✅ Function untuk update log activity
function addLog(message) {
    const logText = document.getElementById('logText');
    const timestamp = new Date().toLocaleString('id-ID');
    const entry = `[${timestamp}] ${message}\n`;
    logText.value = entry + logText.value;
}

// ✅ Function untuk disable revisions
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
            const vendorLinkInput = row.querySelector(".vendor-link");
            if (vendorLinkInput) vendorLinkInput.disabled = true;

            const saveLinkButtons = row.querySelectorAll(".save-link");
            saveLinkButtons.forEach(btn => btn.remove());

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
    fetch("{{ route('vendor.contract-review.save-link') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({
            revision_id: row.dataset.revisionId,
            vendor_link: row.querySelector(".vendor-link").value
        })
    })
    .then(r => r.json())
    .then(r => {
        if (r.success) {
            row.querySelector(".link-status").innerText = "Saved";
            
            const logMessage = `✓ Vendor link saved for ${row.dataset.rev}`;
            addLog(logMessage);
            saveLogToDatabase(logMessage);
        }
    });
}

// ✅ Function untuk simpan log ke database
function saveLogToDatabase(message) {
    const logText = document.getElementById('logText').value;
    
    fetch("{{ route('vendor.contract-review.save-log') }}", {
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

// Send Message Function
function sendMessage() {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) {
        alert('Tulis pesan terlebih dahulu');
        return;
    }
    
    const vendorName = "{{ $vendor->name_vendor ?? 'Vendor' }}";
    const logMessage = `💬 [${vendorName}]: ${message}`;
    addLog(logMessage);
    saveLogToDatabase(logMessage);
    
    messageInput.value = '';
}

// Load existing log dan check disabled revisions saat page load
document.addEventListener('DOMContentLoaded', function() {
    checkDisabledRevisions();
});
</script>

@endsection
