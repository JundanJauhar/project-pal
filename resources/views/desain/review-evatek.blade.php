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
.action-btn { border: none; font-size: 12px; font-weight: 700; border-radius: 14px; padding: 6px 14px; cursor: pointer; width: 85px; margin: 3px auto; color: white; }
.btn-upload { background: #000; }
.btn-approve { background: #28a745; }
.btn-reject { background: #d62828; }
.btn-revisi { background: #ffcc00; color:#000; }

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
.log-textarea { width:100%; height:350px; border:none; background:transparent; }

.content-wrapper { display: grid; grid-template-columns: 3fr 1fr; gap: 35px; }
</style>
@endpush


@section('content')

{{-- GLOBAL DATA for JavaScript --}}
<div id="evatekData"
     data-evatek-id="{{ $evatek->evatek_id }}"
     data-item-id="{{ $item->item_id }}">
</div>

<button class="back-btn" onclick="history.back()">← Back</button>

<p class="eq-name">{{ $item->item_name }}</p>

<h2 class="vendor-name">
    {{ $item->requestProcurement->vendor->name_vendor ?? '-' }}
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
                            <button class="action-btn btn-reject reject-btn">Reject</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- REVISION HISTORY --}}
    <div class="card-default">
        <div class="card-title">Review History</div>
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th>Revision</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($evatek->revisions()->orderBy('created_at', 'desc')->get() as $revision)
                    <tr>
                        <td>{{ $revision->revision_code }}</td>
                        <td>
                            <span class="badge 
                                @if($revision->status === 'approve') badge-success
                                @elseif($revision->status === 'not approve') badge-danger
                                @elseif($revision->status === 'revisi') badge-warning
                                @else badge-secondary
                                @endif
                            ">
                                {{ ucfirst($revision->status) }}
                            </span>
                        </td>
                        <td>
                            @if($revision->approved_at)
                                {{ \Carbon\Carbon::parse($revision->approved_at)->format('d/m/Y H:i') }}
                            @elseif($revision->not_approved_at)
                                {{ \Carbon\Carbon::parse($revision->not_approved_at)->format('d/m/Y H:i') }}
                            @else
                                {{ \Carbon\Carbon::parse($revision->created_at)->format('d/m/Y H:i') }}
                            @endif
                        </td>
                        <td>{{ $revision->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">No revision history</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- LOG ACTIVITY --}}
    <div class="log-card">
        <div class="log-title">Log Activity</div>
        <textarea id="logText" class="log-textarea">{{ $evatek->log }}</textarea>
    </div>
</div>


{{-- ========================= JAVASCRIPT ========================= --}}
<script>
const CSRF = "{{ csrf_token() }}";

/* ---------------- SAVE LINK ---------------- */
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
        }
    });
}


/* ---------------- APPROVE ---------------- */
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
            updateStatus(row.dataset.rev, "Completed");
        }
    });
}


/* ---------------- REJECT ---------------- */
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
        }
    });
}


/* ---------------- REVISI ---------------- */
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

        let html = `
        <tr data-revision-id="${next.revision_id}" data-rev="${next.revision_code}">
            <td><input type="checkbox" class="rev-check status-revisi"></td>
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
                <button class="action-btn btn-reject reject-btn">Reject</button>
            </td>
        </tr>`;

        document
            .getElementById("revisionBody")
            .insertAdjacentHTML("beforeend", html);

        updateStatus(next.revision_code, "Revision Needed");
    });
}


/* ---------------- UPDATE STATUS CARD ---------------- */
function updateStatus(revCode, division) {
    document.getElementById("statusRevision").innerText = revCode;
    document.getElementById("statusDivision").innerText = division;
    document.getElementById("statusDate").innerText = new Date().toLocaleDateString();
}
</script>

@endsection
