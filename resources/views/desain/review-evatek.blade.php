@extends('layouts.app')

@section('title', 'Review Evatek')

@push('styles')
<style>

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
.eq-name {
    font-size: 16px;
    font-weight: 400;
    color: #444;
    margin-bottom: 3px;
}

.vendor-name {
    font-size: 32px;
    font-weight: 800;
    margin-top: -4px;
    margin-bottom: 35px;
}

.status-card {
    background: #f8f8f8;
    border-radius: 14px;
    padding: 25px 28px;
    margin-bottom: 20px;
    border: 1px solid #dddddd;
    transition: all 0.3s ease;
}

.status-card.collapsed {
    padding: 15px 28px;
}

.status-header {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.status-card-header {
    font-size: 20px;
    font-weight: 700;
    flex-grow: 1;
}

.dropdown-icon {
    font-size: 22px;
    transition: 0.3s;
}

.dropdown-icon.rotate {
    transform: rotate(-90deg);
}

.status-content {
    margin-top: 20px;
}

.status-content.hidden {
    display: none;
}

.status-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 45px;
}

.status-item-label {
    font-size: 13px;
    color: #666;
    margin-bottom: 4px;
}

.status-item-value {
    font-size: 22px;
    font-weight: 800;
    color: #222;
}

.status-small-value {
    font-size: 17px;
    font-weight: 700;
    color: #333;
}

.tracking-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 25px 30px;
    border: 1px solid #dcdcdc;
    margin-bottom: 20px;
    height: 420px;
    overflow-y: auto;
}

.tracking-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 18px;
}

.tracking-table {
    width: 100%;
}

.tracking-table th {
    font-size: 14px;
    font-weight: 700;
    padding-bottom: 12px;
    border-bottom: 1px solid #ccc;
    text-align: center;
}

.tracking-table td {
    padding: 25px 0;
    border-bottom: 1px solid #e4e4e4;
    text-align: center;
    vertical-align: middle;
}

.check-col {
    width: 40px;
}

/* ==== GOOGLE DRIVE LINK INPUT ==== */
.link-input {
    width: 90%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 10px;
    font-size: 14px;
    margin-bottom: 10px;
}

.link-input:focus {
    outline: none;
    border-color: #007bff;
}

.action-btn {
    border: none;
    font-size: 12px;
    font-weight: 700;
    border-radius: 14px;
    padding: 6px 14px;
    cursor: pointer;
    margin: 3px;
    width: 85px;
    color: white;
    display: block;
    margin-left: auto;
    margin-right: auto;
}

.btn-upload { background: #000; }
.btn-approve { background: #28a745; }
.btn-reject { background: #d62828; }
.btn-repair { background: #ffcc00; color: #000; }

.add-revision-btn {
    margin-top: 15px;
    background: #0066ff;
    color: white;
    padding: 8px 18px;
    border-radius: 8px;
    font-weight: 700;
    border: none;
    cursor: pointer;
}

.log-card {
    background: #f8f8f8;
    border-radius: 14px;
    padding: 25px;
    border: 1px solid #ddd;
}

.log-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 12px;
}

.log-textarea {
    width: 100%;
    height: 350px;
    border: none;
    background: transparent;
    resize: none;
    font-size: 15px;
    line-height: 28px;
    padding-left: 5px;
    outline: none;
    overflow-y: auto;

    background-image: repeating-linear-gradient(
        to bottom,
        transparent 0 27px,
        #dcdcdc 27px 28px
    );
}

.content-wrapper {
    display: grid;
    grid-template-columns: 3fr 1fr;
    gap: 35px;
}

</style>
@endpush



@section('content')

<button class="back-btn" onclick="goBack()">← Back</button>

<p class="eq-name">{{ $request->request_name }}</p>
<h2 class="vendor-name">{{ $request->vendor->name_vendor ?? '-' }}</h2>



<div id="statusCard" class="status-card">

    <div class="status-header" onclick="toggleFinalStatus()">
        <div class="status-card-header">Final Status</div>
        <div id="dropdownIcon" class="dropdown-icon">⌄</div>
    </div>

    <div id="statusContent" class="status-content">

        <div class="status-grid">

            <div>
                <p class="status-item-label">Revision</p>
                <p class="status-item-value">{{ $request->revision ?? 'R1' }}</p>
            </div>

            <div>
                <p class="status-item-label">Divisi Desain</p>
                <p class="status-small-value">{{ $request->request_status }}</p>
            </div>

            <div>
                <p class="status-item-label">Date</p>
                <p class="status-small-value">
                    {{ \Carbon\Carbon::parse($request->created_date)->format('d/m/Y') }}
                </p>
            </div>

        </div>

    </div>
</div>




<div class="content-wrapper">

    <div>
        <div class="tracking-card">
            <div class="tracking-title">Tracking</div>

            <table class="tracking-table" id="trackingTable">

                <thead>
                    <tr>
                        <th class="check-col"></th>
                        <th>Revision</th>
                        <th>Vendor File Link</th>
                        <th>Divisi Desain File Link</th>
                        <th>Decision</th>
                    </tr>
                </thead>

                <tbody id="revisionBody">

                    {{-- R0 --}}
                    <tr>
                        <td><input type="checkbox" class="rev-check"></td>
                        <td><strong>R0</strong></td>

                        <td>
                            <input type="text" class="link-input" placeholder="Paste Google Drive link">
                            <button class="action-btn btn-upload">Save</button>
                        </td>

                        <td>
                            <input type="text" class="link-input" placeholder="Paste Google Drive link">
                            <button class="action-btn btn-upload">Save</button>
                        </td>

                        <td>
                            <button class="action-btn btn-approve">Approve</button>
                            <button class="action-btn btn-reject">Reject</button>
                            <!-- <button class="action-btn btn-repair">Repair</button> -->
                        </td>
                    </tr>

                    {{-- R1 --}}
                    <tr>
                        <td><input type="checkbox" class="rev-check"></td>
                        <td><strong>R1</strong></td>

                        <td>
                            <input type="text" class="link-input" placeholder="Paste Google Drive link">
                            <button class="action-btn btn-upload">Save</button>
                        </td>

                        <td>
                            <input type="text" class="link-input" placeholder="Paste Google Drive link">
                            <button class="action-btn btn-upload">Save</button>
                        </td>

                        <td>
                            <button class="action-btn btn-approve">Approve</button>
                            <button class="action-btn btn-reject">Reject</button>
                            <!-- <button class="action-btn btn-repair">Repair</button> -->
                        </td>
                    </tr>

                </tbody>
            </table>

            <button class="add-revision-btn" onclick="addRevision()">+ Add Revision</button>

        </div>
    </div>


    <div class="log-card">
        <div class="log-title">Log Activity</div>
        <textarea id="logText" class="log-textarea" placeholder="Tulis catatan aktivitas..."></textarea>
    </div>

</div>




<script>

function toggleFinalStatus() {
    let card = document.getElementById('statusCard');
    let content = document.getElementById('statusContent');
    let icon = document.getElementById('dropdownIcon');

    content.classList.toggle('hidden');
    card.classList.toggle('collapsed');
    icon.classList.toggle('rotate');
}

function goBack() {
    saveReviewData();
    history.back();
}

function saveReviewData() {

    let checks = Array.from(document.querySelectorAll(".rev-check"))
        .map(c => c.checked);

    let logText = document.getElementById("logText").value;

    let revisions = Array.from(document.querySelectorAll("#revisionBody tr"))
        .map(row => row.children[1].innerText.trim());

    localStorage.setItem("reviewEvatekData", JSON.stringify({
        checks,
        log: logText,
        revisions
    }));
}

window.onload = function() {
    let saved = localStorage.getItem("reviewEvatekData");
    if (!saved) return;
    
    let data = JSON.parse(saved);

    let checkboxes = document.querySelectorAll(".rev-check");
    checkboxes.forEach((box, i) => {
        if (data.checks[i] !== undefined) {
            box.checked = data.checks[i];
        }
    });

    document.getElementById("logText").value = data.log || "";

    let currentRows = document.querySelectorAll("#revisionBody tr").length;

    for (let i = currentRows; i < data.revisions.length; i++) {
        addRevision();
    }
};

let revisionCount = 2;

function addRevision() {

    let tbody = document.getElementById("revisionBody");
    let newRev = "R" + revisionCount;

    let html = `
        <tr>
            <td><input type="checkbox" class="rev-check"></td>
            <td><strong>${newRev}</strong></td>

            <td>
                <input type="text" class="link-input" placeholder="Paste Google Drive link">
                <button class="action-btn btn-upload">Save</button>
            </td>

            <td>
                <input type="text" class="link-input" placeholder="Paste Google Drive link">
                <button class="action-btn btn-upload">Save</button>
            </td>

            <td>
                <button class="action-btn btn-approve">Approve</button>
                <button class="action-btn btn-reject">Reject</button>
                <button class="action-btn btn-repair">Repair</button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML("beforeend", html);

    revisionCount++;
}

</script>

@endsection
