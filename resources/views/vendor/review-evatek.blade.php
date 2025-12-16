@extends('layouts.app')

@section('title', 'Review Evatek - Vendor')

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
.link-status { font-size: 12px; color: #777; margin-top: 5px; }

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
.rev-check.status-approve { background: #28a745; border-color:#28a745; }
.rev-check.status-revisi { background:#ffcc00; border-color:#ffcc00; }
.rev-check.status-reject { background:#d62828; border-color:#d62828; }

/* log */
.log-card { background:#f8f8f8; border-radius:14px; padding:25px; border:1px solid #ddd; }
.log-textarea { width:100%; height:450px; border:none; background:transparent; font-family: monospace; font-size: 12px; line-height: 1.6; resize: none; }

.content-wrapper { display: grid; grid-template-columns: 3fr 1fr; gap: 35px; }

.vendor-note {
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    font-size: 14px;
}
</style>
@endpush

@section('content')

<div id="evatekData" data-evatek-id="{{ $evatek->evatek_id }}" data-item-id="{{ $item->item_id }}"></div>

<button class="back-btn" onclick="goBackWithRefresh()">← Back</button>

<p class="eq-name">{{ $item->item_name }}</p>

<h2 class="vendor-name">
    {{ $evatek->vendor->name_vendor ?? '-' }}
</h2>

<div class="vendor-note">
    <strong>📌 Petunjuk untuk Vendor:</strong><br>
    - Anda dapat mengisi link file evatek pada kolom "Vendor File Link"<br>
    - Gunakan "Activity Log" untuk berkomunikasi dengan tim desain<br>
    - Status evaluasi akan diperbarui oleh tim desain internal
</div>

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
                        <th>Status</th>
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
                                @endif" disabled>
                            </td>
                            <td><strong>{{ $rev->revision_code }}</strong></td>
                            <td>
                                {{-- Vendor HANYA bisa edit link mereka sendiri --}}
                                <input type="text" class="link-input vendor-link" value="{{ $rev->vendor_link }}" 
                                    @if($rev->status == 'approve' || $rev->status == 'not approve') readonly @endif>
                                @if($rev->status != 'approve' && $rev->status != 'not approve')
                                <button class="action-btn btn-upload save-vendor-link">Save</button>
                                @endif
                                <div class="link-status"></div>
                            </td>
                            <td>
                                {{-- Design link READONLY untuk vendor --}}
                                <input type="text" class="link-input design-link" value="{{ $rev->design_link }}" readonly style="background: #f5f5f5;">
                            </td>
                            <td>
                                @if($rev->status == 'approve')
                                    <span style="color: #28a745; font-weight: 600;">✓ Approved</span>
                                @elseif($rev->status == 'revisi')
                                    <span style="color: #ffc107; font-weight: 600;">⟳ Revisi</span>
                                @elseif($rev->status == 'not approve')
                                    <span style="color: #d62828; font-weight: 600;">✗ Rejected</span>
                                @else
                                    <span style="color: #666; font-weight: 600;">⏳ Pending</span>
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
        <textarea id="logInput" class="log-textarea" placeholder="Tulis pesan untuk tim desain..."></textarea>
        <button class="action-btn btn-upload" style="width: 100%; margin-top: 10px;" onclick="addVendorLog()">Send Message</button>
        
        <div style="margin-top: 20px; border-top: 1px solid #ccc; padding-top: 15px;">
            <div style="font-weight: bold; margin-bottom: 10px;">History:</div>
            <textarea id="logHistory" class="log-textarea" readonly style="height: 300px;"></textarea>
        </div>
    </div>
</div>

{{-- ========================= JAVASCRIPT ========================= --}}
<script>
const CSRF = "{{ csrf_token() }}";
const EVATEK_ID = "{{ $evatek->evatek_id }}";

// ✅ Load existing log saat page load
document.addEventListener('DOMContentLoaded', function() {
    const existingLog = {!! json_encode($evatek->log ?? '') !!};
    if (existingLog) {
        document.getElementById('logHistory').value = existingLog;
    }
});

// ✅ Function untuk vendor menambah log
function addVendorLog() {
    const logInput = document.getElementById('logInput');
    const logHistory = document.getElementById('logHistory');
    const message = logInput.value.trim();
    
    if (!message) {
        alert('Pesan tidak boleh kosong!');
        return;
    }
    
    const timestamp = new Date().toLocaleString('id-ID');
    const vendorName = "{{ $vendor->name_vendor }}";
    const entry = `[${timestamp}] ${vendorName}: ${message}\n`;
    
    // Tambahkan ke history
    logHistory.value = entry + logHistory.value;
    
    // Simpan ke database
    saveLogToDatabase(logHistory.value);
    
    // Clear input
    logInput.value = '';
}

// ✅ Function untuk simpan log ke database
function saveLogToDatabase(logContent) {
    fetch("{{ route('vendor.evatek.save-log') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({
            evatek_id: EVATEK_ID,
            log: logContent
        })
    })
    .then(r => r.json())
    .then(r => {
        if (r.success) {
            console.log('Log saved successfully');
        } else {
            alert('Gagal menyimpan log');
        }
    })
    .catch(err => {
        console.error('Error saving log:', err);
        alert('Terjadi kesalahan saat menyimpan log');
    });
}

/* SAVE VENDOR LINK ONLY */
document.addEventListener("click", function(e) {
    if (e.target.classList.contains("save-vendor-link")) {
        let row = e.target.closest("tr");
        saveVendorLink(row);
    }
});

function saveVendorLink(row) {
    const vendorLink = row.querySelector(".vendor-link").value;
    const statusDiv = row.querySelector(".link-status");
    
    fetch("{{ route('vendor.evatek.save-link') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": CSRF
        },
        body: JSON.stringify({
            revision_id: row.dataset.revisionId,
            vendor_link: vendorLink
        })
    })
    .then(r => r.json())
    .then(r => {
        if (r.success) {
            statusDiv.innerText = "✓ Saved";
            statusDiv.style.color = "#28a745";
            
            // Log activity
            const logHistory = document.getElementById('logHistory');
            const timestamp = new Date().toLocaleString('id-ID');
            const vendorName = "{{ $vendor->name_vendor }}";
            const entry = `[${timestamp}] ${vendorName}: Link uploaded for ${row.dataset.rev}\n`;
            logHistory.value = entry + logHistory.value;
            saveLogToDatabase(logHistory.value);
        } else {
            statusDiv.innerText = "✗ Failed";
            statusDiv.style.color = "#d62828";
        }
    })
    .catch(err => {
        console.error(err);
        statusDiv.innerText = "✗ Error";
        statusDiv.style.color = "#d62828";
    });
}

// ✅ Function untuk back dengan refresh halaman sebelumnya
function goBackWithRefresh() {
    window.location.href = "{{ route('vendor.index') }}";
}
</script>

@endsection
