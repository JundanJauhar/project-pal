@extends('layouts.app')

@section('title', 'Evatek - Vendor')

@push('styles')

<style>
    .big-card {
        border-radius: 18px;
        padding: 40px 50px;
        min-height: 450px;
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.12);
        border: none;
        background: #ffffff;
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

    .status-desain {
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .status-pending {
        color: #ffffffff;
        background-color: #fd8b00ff;
    }

    .status-approved {
        color: #ffffffff;
        background-color: #28AC00;
    }

    .status-not-approved {
        color: #ffffffff;
        background-color: #BD0000;
    }

    .status-revisi {
        color: #ffffffff;
        background-color: #0066CC;
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
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .filter-select {
        border-radius: 6px;
        padding: 4px 10px;
        border: 1px solid #bbb;
        background: white;
        font-size: 14px;
        width: 150px;
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .blink-animation {
        animation: blink 1.5s infinite;
    }
</style>
@endpush


@section('content')

<h1 class="fw-bold mb-5 text-center" >{{ $vendor->name_vendor }}</h1>

<h2 class="fw-bold mb-2">Evatek Vendor</h2>
<div class="row">
    <div class="col-md-3">
        <div class="stat-card stat-total">
            <div class="stat-content">
                <div class="stat-title">Total Evatek</div>
                <div class="stat-value">{{ $stats['total_evatek'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-list-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-progress">
            <div class="stat-content">
                <div class="stat-title">Sedang Proses</div>
                <div class="stat-value">{{ $stats['pending'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-content">
                <div class="stat-title">Diterima </div>
                <div class="stat-value">{{ $stats['approved'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-content">
                <div class="stat-title">Revisi </div>
                <div class="stat-value">{{ $stats['revisi'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-rejected">
            <div class="stat-content">
                <div class="stat-title">Ditolak</div>
                <div class="stat-value">{{ $stats['rejected'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- Filter bar sederhana --}}
<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <input type="text" id="search-input" class="form-control" placeholder="Cari nama item...">
    </div>
    <div class="col-md-3 mb-2">
        <select id="status-filter" class="form-select">
            <option value="">Semua Status</option>
            @foreach(['pending' => 'Pending', 'approve' => 'Approved', 'not approve' => 'Rejected', 'revisi' => 'Revisi'] as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="card big-card">
    <table class="request-table">
        <thead>
            <tr>
                <th style="padding: 12px 8px; text-align: left;">Item</th>
                <th style="padding: 12px 8px; text-align: left;">Project / Procurement</th>
                <th style="padding: 12px 8px; text-align: center;">Status Evatek</th>
                <th style="padding: 12px 8px; text-align: center;">Posisi</th>
                <th style="padding: 12px 8px; text-align: left;">Catatan</th>
                <th style="padding: 12px 8px; text-align: center;">Dibuat</th>
                <th style="padding: 12px 8px; text-align: center;">Aksi</th>
            </tr>
        </thead>

        <tbody id="evatek-tbody">
            @forelse($evatekItems as $ev)
            @php
            $item = $ev->item;
            $proc = $ev->procurement ?? null;
            $proj = $proc ? $proc->project : null;
            $latestRevision = $ev->latestRevision;
            $status = $latestRevision ? $latestRevision->status : 'pending';
            $catatan = $latestRevision ? ($latestRevision->catatan_approval ?? $latestRevision->alasan_reject ?? '-') : '-';
            @endphp

            <tr data-status="{{ $status }}">
                {{-- Item --}}
                <td style="padding: 12px 8px; text-align: left;">
                    <div style="font-weight: 600;">
                        {{ $item->item_name ?? '-' }}
                    </div>
                    <div style="font-size: 12px; color: #666;">
                        {{ $item->amount ?? '-' }} {{ $item->unit ?? '' }}
                    </div>
                </td>

                {{-- Project / Procurement --}}
                <td style="padding: 12px 8px; text-align: left;">
                    <div style="font-size: 13px;">
                        {{ $proj->project_name ?? '-' }}
                    </div>
                    <div style="font-size: 11px; color:#666;">
                        {{ $proc->code_procurement ?? '-' }}
                    </div>
                </td>

                {{-- Status Evatek --}}
                <td style="padding: 12px 8px; text-align: center;">
                    @if($status === 'approve')
                    <span class="status-desain status-approved">Approved</span>
                    @elseif($status === 'not approve')
                    <span class="status-desain status-not-approved">Rejected</span>
                    @elseif($status === 'revisi')
                    <span class="status-desain status-revisi">Revisi</span>
                    @else
                    <span class="status-desain status-pending">Pending</span>
                    @endif
                </td>

                {{-- Posisi --}}
                <td style="padding: 12px 8px; text-align: center;">
                    @if(in_array($status, ['approve', 'not approve']))
                    <span class="text-muted">-</span>
                    @elseif(empty(trim($latestRevision->vendor_link ?? '')))
                    <span class="badge bg-warning text-dark">Evatek Vendor</span>
                    @else
                    <span class="badge bg-info text-dark">Evatek Divisi</span>
                    @endif
                </td>

                {{-- Catatan Evaluasi --}}
                <td style="padding: 12px 8px; text-align: left; font-size: 13px;">
                    {{ $ev->evaluation_note ?? '-' }}
                </td>

                {{-- Tanggal dibuat --}}
                <td style="padding: 12px 8px; text-align: center; font-size: 13px;">
                    {{ $ev->created_at ? $ev->created_at->format('d/m/Y') : '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    <a href="{{ route('vendor.evatek.review', $ev->evatek_id) }}"
                        class="btn btn-sm btn-primary"
                        style="padding: 6px 14px; border-radius: 6px; text-decoration: none;">
                        Review Evatek
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    Belum ada item yang sedang dievaluasi (Evatek) untuk saat ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Contract Review Section --}}
<h2 class="fw-bold mb-3 mt-5">Review Kontrak dengan Supply Chain</h2>

{{-- Contract Review Stats Cards --}}
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-total">
            <div class="stat-content">
                <div class="stat-title">Total Review Kontrak</div>
                <div class="stat-value">{{ $contractStats['total_review'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-progress">
            <div class="stat-content">
                <div class="stat-title">Sedang Proses</div>
                <div class="stat-value">{{ $contractStats['on_progress'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-content">
                <div class="stat-title">Approved</div>
                <div class="stat-value">{{ $contractStats['approved'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-content">
                <div class="stat-title">Revisi</div>
                <div class="stat-value">{{ $contractStats['revisi'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-rejected">
            <div class="stat-content">
                <div class="stat-title">Not Approved</div>
                <div class="stat-value">{{ $contractStats['rejected'] }}</div>
            </div>
            <div class="stat-icon">
                <div class="stat-icon-inner">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="big-card mt-4">

    <table class="request-table">
        <thead>
            <tr>
                <th style="text-align: left;">Item</th>
                <th style="text-align: left;">Project / Procurement</th>
                <th style="text-align: center;">Status Evatek</th>
                <th style="text-align: center;">Catatan</th>
                <th style="text-align: center;">Dibuat</th>
                <th style="text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($contractReviews as $review)
            @php
            $proc = $review->procurement ?? null;
            $proj = $proc ? $proc->project : ($review->project ?? null);
            $requestProc = $proc ? $proc->requestProcurements->first() : null;
            $items = $requestProc ? $requestProc->items : collect();
            $item = $items->first();
            @endphp
            <tr>
                {{-- Item --}}
                <td style="padding: 12px 8px; text-align: left;">
                    <strong>{{ $item->item_name ?? 'N/A' }}</strong><br>
                    <small class="text-muted">{{ $item->amount ?? '' }} {{ $item->unit ?? '' }}</small>
                </td>

                {{-- Project / Procurement --}}
                <td style="padding: 12px 8px; text-align: left; font-size: 13px;">
                    <strong>{{ $proj->project_name ?? '-' }}</strong><br>
                    <small class="text-muted">{{ $proc->code_procurement ?? '-' }}</small>
                </td>

                {{-- Status --}}
                <td style="padding: 12px 8px; text-align: center;">
                    @php
                    $latestRevision = $review->revisions->first();
                    $statusClass = 'status-pending';
                    $statusText = 'Pending';

                    if($latestRevision) {
                    if($latestRevision->result == 'approve') {
                    $statusClass = 'status-approved';
                    $statusText = 'Approved';
                    } elseif($latestRevision->result == 'not_approve') {
                    $statusClass = 'status-not-approved';
                    $statusText = 'Not Approved';
                    } elseif($latestRevision->result == 'revisi') {
                    $statusClass = 'status-revisi';
                    $statusText = 'Revisi';
                    }
                    }
                    @endphp
                    <span class="status-desain {{ $statusClass }}">{{ $statusText }}</span>
                </td>

                {{-- Catatan --}}
                <td style="padding: 12px 8px; text-align: left; font-size: 13px;">
                    {{ $review->remarks ?? '-' }}
                </td>

                {{-- Tanggal dibuat --}}
                <td style="padding: 12px 8px; text-align: center; font-size: 13px;">
                    {{ $review->start_date ? \Carbon\Carbon::parse($review->start_date)->format('d/m/Y') : '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    <a href="{{ route('vendor.contract-review.review', $review->contract_review_id) }}"
                        class="btn btn-sm btn-primary"
                        style="padding: 6px 14px; border-radius: 6px; text-decoration: none;">
                        Review Kontrak
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    Belum ada review kontrak untuk saat ini.
                </td>
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
        const tbody = document.getElementById('evatek-tbody');
        const allRows = tbody.querySelectorAll('tr[data-status]');

        function filterTable() {
            const searchTerm = (searchInput.value || '').toLowerCase();
            const selectedStatus = statusFilter.value;

            allRows.forEach(row => {
                const status = row.getAttribute('data-status');
                const itemName = row.querySelector('td:first-child div').textContent.toLowerCase();

                const matchesSearch = itemName.includes(searchTerm);
                const matchesStatus = !selectedStatus || status === selectedStatus;

                row.style.display = (matchesSearch && matchesStatus) ? '' : 'none';
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterTable);
        if (statusFilter) statusFilter.addEventListener('change', filterTable);
    });
</script>
@endpush