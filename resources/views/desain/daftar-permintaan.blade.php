@extends('layouts.app')

@section('title', 'Daftar Item - PT PAL Indonesia')

@push('styles')
<style>
    .big-card {
        border-radius: 18px;
        padding: 40px 50px;
        min-height: 550px;
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.12);
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
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
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

    .tambah .btn:hover {
        background: #002e5c;
        border-color: #002e5c;
    }

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
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="search-input" name="search" placeholder="Cari Item..." value="">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="status-filter" name="status">
                            <option value="">Semua Status</option>
                            <option value="on_progress">On Progress</option>
                            <option value="approve">Approved</option>
                            <option value="not_approve">Not Approved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="deadline-filter" name="deadline">
                            <option value="">Semua Target</option>
                            <option value="hari_ini">Hari Ini</option>
                            <option value="satu_minggu">1 Minggu</option>
                            <option value="satu_bulan">1 Bulan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="pic-filter" name="PIC">
                            <option value="">Semua PIC</option>
                            <option value="EO">EO</option>
                            <option value="HC">HC</option>
                            <option value="MO">MO</option>
                            <option value="HO">HO</option>
                            <option value="SEWACO">SEWACO</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card big-card">
    <table class="request-table">
        <thead>
            <tr>
                <th style="padding: 12px 8px; text-align: left;">Item</th>
                <th style="padding: 12px 8px; text-align: left;">Vendor</th>
                <th style="padding: 12px 8px; text-align: left;">PIC</th>
                <th style="padding: 12px 8px; text-align: center;">Start Date</th>
                <th style="padding: 12px 8px; text-align: center;">Target Date</th>
                <th style="padding: 12px 8px; text-align: center;">Revision</th>
                <th style="padding: 12px 8px; text-align: center;">Posisi</th>
                <th style="padding: 12px 8px; text-align: center;">Status</th>
                <th style="padding: 12px 8px; text-align: center;">Last Update</th>
            </tr>
        </thead>

        <tbody id="items-tbody">
            @forelse($evatekItems as $evatek)
            @php
            $item = $evatek->item;
            $proc = $evatek->procurement ?? null;
            $proj = $proc ? $proc->project : null;
            $latestRevision = $evatek->latestRevision;
            $status = $latestRevision ? $latestRevision->status : 'pending';
            $catatan = $latestRevision ? ($latestRevision->catatan_approval ?? $latestRevision->alasan_reject ?? '-') : '-';
            @endphp
                
            @endphp
            <tr data-status="{{ $evatek->status }}" data-pic="{{ $evatek->pic_evatek }}" data-target="{{ $evatek->target_date }}" class="evatek-row">
                <td style="padding: 12px 8px; text-align: left;">
                    <a href="{{ route('desain.review-evatek', $evatek->evatek_id) }}"
                        data-evatek-id="{{ $evatek->evatek_id }}"
                        class="evatek-link"
                        style="text-decoration: none; color: #000; font-weight: 600;">
                        {{ $evatek->item->item_name ?? 'N/A' }}
                        @if(isset($unreadEvatekIds) && in_array($evatek->evatek_id, $unreadEvatekIds))
                        <span class="badge bg-danger ms-2" style="font-size: 10px;">Baru</span>
                        @endif
                    </a>
                </td>

                <td style="padding: 12px 8px; text-align: left;">
                    {{ $evatek->vendor->name_vendor ?? '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: left; color: #1976D2; ">
                    {{ $evatek->pic_evatek ?? '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    {{ $evatek->start_date ? \Carbon\Carbon::parse($evatek->start_date)->format('d/m/Y') : '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    {{ $evatek->target_date ? \Carbon\Carbon::parse($evatek->target_date)->format('d/m/Y') : '-' }}
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    {{ $evatek->current_revision }}
                </td>

                {{-- Posisi --}}
                <td style="padding: 12px 8px; text-align: center;">
                    @if(in_array($status, ['approve', 'not approve']))
                    <span class="text-muted">-</span>
                    @elseif(empty(trim($latestRevision->vendor_link ?? '')))
                    <span class="badge bg-warning text-dark">Evatek Vendor</span>
                    @else
                    <span class="badge bg-info text-dark">Evatek Desain</span>
                    @endif
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    <span class="status-badge 
                        @if($evatek->status === 'approve') status-approved
                        @elseif($evatek->status === 'not_approve') status-not-approved
                        @else
                        @endif
                    " style="
                        @if($evatek->status === 'approve')
                            color: #28AC00 !important;
                        @elseif($evatek->status === 'not_approve')
                            color: #BD0000 !important;
                        @else
                            color: #FF9500 !important;
                        @endif
                    ">
                        {{ ucfirst($evatek->status) }}
                    </span>
                </td>

                <td style="padding: 12px 8px; text-align: center;">
                    {{ $evatek->current_date ? \Carbon\Carbon::parse($evatek->current_date)->format('d/m/Y') : '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-5">Belum ada item evatek untuk project ini.</td>
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
        const picFilter = document.getElementById('pic-filter');
        const tbody = document.getElementById('items-tbody');
        const allRows = tbody.querySelectorAll('tr.evatek-row');
        const CLICKED_KEY = 'clicked_evatek_items_v3'; // Versioned to auto-reset after DB changes

        // Ambil data dari localStorage
        let clickedItems = JSON.parse(localStorage.getItem(CLICKED_KEY)) || [];

        document.querySelectorAll('.evatek-link').forEach(link => {
            const evatekId = link.dataset.evatekId;
            const badge = link.querySelector('.badge');

            // Sembunyikan badge kalau sudah pernah diklik
            if (clickedItems.includes(evatekId) && badge) {
                badge.style.display = 'none';
            }

            // Saat diklik
            link.addEventListener('click', function() {
                if (badge) badge.style.display = 'none';

                if (!clickedItems.includes(evatekId)) {
                    clickedItems.push(evatekId);
                    localStorage.setItem(CLICKED_KEY, JSON.stringify(clickedItems));
                }
            });
        });

        function filterTable() {
            const searchTerm = searchInput.value.toLowerCase();
            const selectedStatus = statusFilter.value;
            const selectedDeadline = deadlineFilter.value;
            const selectedPic = picFilter.value;
            const now = new Date();

            allRows.forEach(row => {
                const itemLink = row.querySelector('a');
                const itemName = itemLink ? itemLink.textContent.toLowerCase() : '';
                const status = row.getAttribute('data-status');
                const pic = row.getAttribute('data-pic');
                const target = row.getAttribute('data-target');

                //Filter by Target
                let matchesTarget = true;

                if (selectedDeadline && target) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    const targetDate = new Date(target);
                    targetDate.setHours(0, 0, 0, 0);

                    const diffTime = targetDate - today;
                    const diffDays = diffTime / (1000 * 60 * 60 * 24);

                    if (selectedDeadline === 'hari_ini') {
                        matchesTarget = diffDays === 0;
                    } else if (selectedDeadline === 'satu_minggu') {
                        matchesTarget = diffDays >= 0 && diffDays <= 7;
                    } else if (selectedDeadline === 'satu_bulan') {
                        matchesTarget = diffDays >= 0 && diffDays <= 30;
                    }
                }

                // Filter by PIC
                const matchesPic = !selectedPic || pic === selectedPic;

                // Filter by search
                const matchesSearch = itemName.includes(searchTerm);

                // Filter by status
                const matchesStatus = !selectedStatus || status === selectedStatus;

                // Show/hide row
                if (matchesSearch && matchesStatus && matchesPic && matchesTarget) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);
        deadlineFilter.addEventListener('change', filterTable);
        picFilter.addEventListener('change', filterTable);
    });
</script>
@endpush