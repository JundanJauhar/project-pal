@extends('layouts.app')

@section('title', 'Daftar Item - PT PAL Indonesia')

@push('styles')
<style>
    .big-card {
        /* background: #ebebeb; */
        border-radius: 18px;
        padding: 40px 50px;
        min-height: 550px;
        box-shadow: 0 8px 12px rgba(0,0,0,0.12);
        border: none;
    }

    .search-wrapper {
        width: 40%;
        position: relative;
        justify-content: space-between;
        display: flex;
        margin-bottom: 25px;
    }

    .search-input {
        width: 100%;
        height: 38px;
        border-radius: 20px;
        border: none;
        padding: 0 45px 0 20px;
        background: white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }

    .search-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        opacity: 0.6;
        cursor: pointer;
    }

    .request-table {
        width: 100%;
        margin-top: 15px;
        font-size: 15px;
    }

    .request-table th {
        font-weight: 600;
        color: #222;
        padding-bottom: 15px;
        border-bottom: 1px solid #858585;
    }

    .request-table td {
        padding: 12px 0;
        border-bottom: 1px solid #cfcfcf;
    }

    .filter-select {
        border-radius: 6px;
        padding: 4px 10px;
        border: 1px solid #bbb;
        background: white;
        font-size: 14px;
        width: 120px;
    }

    .tambah .btn {
        background: #003d82;
        border-color: #003d82;
    }

    .tambah .btn:hover{
        background: #002e5c;
        border-color: #002e5c;
    }

    /* Status badge styling */
    .status-badge {
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .status-approved {
        color: #28AC00 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
    }

    .status-not-approved {
        color: #BD0000 !important;
        font-weight: 600 !important;
        font-size: 13px !important;
    }
</style>
@endpush
@section('content')
    <h2 class="fw-bold mb-4">Daftar Item</h2>


    <div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <form id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="search-input" name="search" placeholder="Cari Equipment..." value="">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="status-filter" name="status">
                            <option value="">Semua Status</option>
                            <option value="not_approved">Not Approved</option>
                            <option value="approved">Approved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="deadline-filter" name="deadline">
                            <option value="">Semua Deadline</option>
                            <option value="hari_ini">Hari Ini</option>
                            <option value="satu_minggu">1 Minggu</option>
                            <option value="satu_bulan">1 Bulan</option>
                        </select>
                    </div>
                    @if(Auth::user()->roles === 'supply_chain')
                        <div class="tambah col-md-2 text-end">
                            <a href="{{ route('desain.input-item') }}" class="btn btn-primary w-100 btn-custom" wire:navigate>
                                <i class="bi bi-plus-circle"></i> Tambah
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card big-card">
    <table class="request-table">
        <thead>
            <tr>
                <th style="padding: 12px 8px; text-align: left;">Equipment</th>
                <th style="padding: 12px 8px; text-align: left;">Vendor</th>
                <th style="padding: 12px 8px; text-align: center;">Status</th>
                <th style="padding: 12px 8px; text-align: center;">Information</th>
                <th style="padding: 12px 8px; text-align: center;">Tanggal Pengadaan</th>
                <th style="padding: 12px 8px; text-align: center;">Tanggal Tenggat</th>
            </tr>
        </thead>

        <tbody id="items-tbody">
            @forelse($project->procurements as $procurement)
                @foreach($procurement->requestProcurements as $req)
                    @forelse($req->items as $item)
                    <tr data-status="{{ $item->status }}" data-deadline="{{ $req->deadline_date }}">
                        <td style="padding: 12px 8px; text-align: left;">
                            <a href="{{ route('desain.review-evatek', $req->request_id) }}"
                               style="text-decoration: none; color: #000; font-weight: 600;">
                                {{ $item->item_name }}
                            </a>
                            <div style="font-size: 12px; color: #666;">
                                {{ $item->amount }} {{ $item->unit }}
                            </div>
                        </td>

                        <td style="padding: 12px 8px; text-align: left;">
                            {{ $req->vendor->name_vendor ?? '-' }}
                        </td>

                        <td style="padding: 12px 8px; text-align: center;">
                            @if($item->status === 'approved')
                                <span class="status-badge status-approved">Approved</span>
                            @else
                                <span class="status-badge status-not-approved">Not Approved</span>
                            @endif
                        </td>

                        <td style="padding: 12px 8px; text-align: center;">
                            <div style="font-size: 13px;">{{ $procurement->code_procurement }}</div>
                            <div style="font-size: 11px; color: #666;">{{ $req->request_name }}</div>
                        </td>

                        <td style="padding: 12px 8px; text-align: center;">
                            {{ \Carbon\Carbon::parse($req->created_date)->format('d/m/Y') }}
                        </td>

                        <td style="padding: 12px 8px; text-align: center;">
                            @php
                                $deadline = \Carbon\Carbon::parse($req->deadline_date);
                                $now = \Carbon\Carbon::now();
                                $isLate = $deadline->isPast() && $item->status !== 'approved';
                            @endphp
                            <span style="color: {{ $isLate ? '#dc3545' : '#000' }}; font-weight: {{ $isLate ? '600' : '400' }};">
                                {{ $deadline->format('d/m/Y') }}
                                @if($isLate)
                                    <small style="display: block; font-size: 10px;">⚠️ Terlambat</small>
                                @endif
                            </span>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                @endforeach
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">Belum ada permintaan atau item untuk project ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-input');
    const statusFilter = document.getElementById('status-filter');
    const deadlineFilter = document.getElementById('deadline-filter');
    const tbody = document.getElementById('items-tbody');
    const allRows = tbody.querySelectorAll('tr[data-status]');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = statusFilter.value;
        const selectedDeadline = deadlineFilter.value;
        const now = new Date();

        allRows.forEach(row => {
            const itemName = row.querySelector('a').textContent.toLowerCase();
            const status = row.getAttribute('data-status');
            const deadline = new Date(row.getAttribute('data-deadline'));

            // Filter by search
            const matchesSearch = itemName.includes(searchTerm);

            // Filter by status
            const matchesStatus = !selectedStatus || status === selectedStatus;

            // Filter by deadline
            let matchesDeadline = true;
            if (selectedDeadline === 'hari_ini') {
                matchesDeadline = deadline.toDateString() === now.toDateString();
            } else if (selectedDeadline === 'satu_minggu') {
                const weekFromNow = new Date(now);
                weekFromNow.setDate(now.getDate() + 7);
                matchesDeadline = deadline >= now && deadline <= weekFromNow;
            } else if (selectedDeadline === 'satu_bulan') {
                const monthFromNow = new Date(now);
                monthFromNow.setMonth(now.getMonth() + 1);
                matchesDeadline = deadline >= now && deadline <= monthFromNow;
            }

            // Show/hide row
            if (matchesSearch && matchesStatus && matchesDeadline) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
    deadlineFilter.addEventListener('change', filterTable);
});
</script>
@endpush
