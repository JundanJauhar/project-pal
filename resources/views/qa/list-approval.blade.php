@extends('layouts.app')

@section('title', 'List Approval — Inspeksi Barang')

@section('content')

@push('styles')
<style>
    /* Container & search */
    .la-wrapper { padding: 18px 0; }
    .la-search {
        width: 520px;
        max-width: 90%;
        margin: 12px auto 26px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .la-search input {
        flex: 1;
        height: 40px;
        border-radius: 24px;
        border: 1px solid #d6d6d6;
        padding: 6px 16px;
        font-size: 15px;
        background: #fff;
    }
    .la-search button {
        border-radius: 24px;
        height: 40px;
        width: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #d6d6d6;
        background: #fff;
    }

    /* Card */
    .la-card {
        background: #f6f6f6;
        border-radius: 14px;
        padding: 18px;
        border: 1px solid #e2e2e2;
        margin-bottom: 20px;
    }

    /* Row header (procurement) */
    .la-row {
        background: #fff;
        border-radius: 12px;
        padding: 18px 22px;
        border: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
    }
    .la-row .left {
        display:flex;
        align-items:center;
        gap:18px;
        flex:1;
    }
    .la-code { font-weight:700; font-size:16px; color:#1b1b1b; min-width:160px; }
    .la-progress {
        width: 200px;
        max-width: 40%;
    }
    .progress-track {
        width:100%;
        height:6px;
        background:#ebecec;
        border-radius:6px;
        overflow:hidden;
    }
    .progress-fill { height:100%; background:#c0392b; width:16%; } /* small red indicator like mockup */

    .la-date { min-width:120px; text-align:center; color:#333; }
    .la-priority { min-width:90px; text-align:center; font-weight:700; text-transform:capitalize; }
    .la-status { min-width:150px; text-align:right; color:#666; }

    .la-chevron { font-size:20px; color:#222; margin-left:12px; }

    /* Detail box (table) */
    .la-detail {
        background:#f3f3f3;
        border-radius:10px;
        padding:18px;
        margin-top:-2px;
        border:1px solid #e0e0e0;
        display:none;
    }
    .la-table {
        width:100%;
        border-collapse:collapse;
    }
    .la-table thead th {
        text-align:left;
        padding:14px 8px;
        color:#6b6b6b;
        border-bottom:2px solid #d7d7d7;
        font-weight:600;
    }
    .la-table tbody td {
        padding:18px 8px;
        vertical-align:top;
        border-bottom:1px solid #e8e8e8;
        color:#111;
    }

    /* small square checkboxes like mockup */
    .la-checkbox {
        display:inline-block;
        width:28px;
        height:28px;
        background:#e7e7e7;
        border-radius:6px;
        border:1px solid #dfdfdf;
        margin-left:8px;
    }

    /* footer row style (progress small, date, priority, status text hardcoded 'Butuh Update') */
    .la-footer {
        background:#fff;
        border-radius:12px;
        padding:14px 20px;
        border:1px solid #e0e0e0;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        margin-top:8px;
    }
    .footer-left { display:flex; align-items:center; gap:18px; flex:1; }
    .footer-date { min-width:120px; text-align:center; color:#333; }
    .footer-priority { min-width:90px; text-align:center; font-weight:700; text-transform:capitalize; }
    .footer-status { min-width:160px; text-align:right; color:#000; font-weight:700; background:#ffd966; padding:6px 12px; border-radius:18px; }

    /* responsive */
    @media (max-width: 900px){
        .la-row { flex-direction:column; align-items:flex-start; gap:10px; }
        .la-row .left { flex-direction:column; align-items:flex-start; gap:8px; }
        .la-progress { width:100%; max-width:100%; }
    }
</style>
@endpush

<div class="la-wrapper container-fluid">
    {{-- SEARCH BAR --}}
    <div class="la-search">
        <input type="search" id="la-search-input" placeholder="Cari kode, nama atau barang...">
        <button id="la-search-btn"><i class="bi bi-search"></i></button>
    </div>

    {{-- CARD (main container) --}}
    <div class="la-card">

        {{-- Loop procurements --}}
        @forelse($procurements as $proc)
            @php
                // items are already filtered server-side to only include items needing inspection
                $items = $proc->items;
                $totalItems = $items->count();
                // Represent progress as small indicator: (items done / total) but here show small red like mock
                $doneItems = 0;
                $progressPerc = $totalItems > 0 ? round(($doneItems / $totalItems) * 100) : 0;
            @endphp

            {{-- Row header --}}
            <div class="la-row" data-target="detail-{{ $proc->procurement_id }}">
                <div class="left">
                    <div class="la-code">{{ $proc->code_procurement }}</div>

                    <div class="la-progress">
                        <div class="progress-track" aria-hidden="true">
                            <div class="progress-fill" style="width: {{ max($progressPerc, 8) }}%;"></div>
                        </div>
                    </div>
                </div>

                <div class="la-date">{{ $proc->start_date?->format('d/m/Y') ?? '-' }}</div>

                <div class="la-priority">{{ $proc->priority }}</div>

                <div class="la-status">Inspeksi Barang</div>

                <div class="la-chevron"><i class="bi bi-chevron-down"></i></div>
            </div>

            {{-- Detail box (table) --}}
            <div id="detail-{{ $proc->procurement_id }}" class="la-detail">
                <table class="la-table">
                    <thead>
                        <tr>
                            <th style="width:22%;">Nama Barang</th>
                            <th style="width:36%;">Spesifikasi</th>
                            <th style="width:10%;">Jumlah</th>
                            <th style="width:14%;">Tanggal Kedatangan</th>
                            <th style="width:18%;">Hasil Inspeksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->specification ?? ($item->item_description ?? '-') }}</td>
                                <td>{{ $item->amount }} {{ $item->unit ?? '' }}</td>
                                <td>{{ optional($item->arrival_date ?? $item->created_at)->format('d/m/Y') }}</td>
                                <td>
                                    {{-- two small square placeholders (UI only) --}}
                                    <span class="la-checkbox" title="Sudah Inspeksi"></span>
                                    <span class="la-checkbox" title="Belum Inspeksi"></span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer row (hardcoded status "Butuh Update") --}}
            <div class="la-footer">
                <div class="footer-left">
                    <div style="width:90px;">
                        <div class="progress-track" style="height:6px;">
                            <div class="progress-fill" style="width: {{ max($progressPerc, 6) }}%; background:#c0392b;"></div>
                        </div>
                    </div>
                    <div style="flex:1"></div>
                </div>

                <div class="footer-date">{{ $proc->start_date?->format('d/m/Y') ?? '-' }}</div>

                <div class="footer-priority">{{ $proc->priority }}</div>

                <div class="footer-status">Butuh Update</div>

                <div style="margin-left:10px;"><i class="bi bi-chevron-down"></i></div>
            </div>

        @empty
            <div class="text-center py-4 text-muted">
                <i class="bi bi-inbox" style="font-size:40px;"></i>
                <div class="mt-2">Tidak ada pengadaan yang membutuhkan inspeksi.</div>
            </div>
        @endforelse

    </div>
</div>

@push('scripts')
<script>
    // toggle detail on click (row or chevron)
    document.querySelectorAll('.la-row').forEach(function(row){
        row.addEventListener('click', function(e){
            // prevent double toggles when clicking chevron
            const targetId = row.getAttribute('data-target');
            const detail = document.getElementById(targetId);
            const chevron = row.querySelector('.la-chevron i');

            if (!detail) return;

            const isOpen = getComputedStyle(detail).display === 'block';

            // close all details first (optional: keep only one open)
            document.querySelectorAll('.la-detail').forEach(d => { d.style.display = 'none'; });
            document.querySelectorAll('.la-row .la-chevron i').forEach(c => c.classList.remove('bi-chevron-up') );

            if (!isOpen) {
                detail.style.display = 'block';
                chevron.classList.remove('bi-chevron-down');
                chevron.classList.add('bi-chevron-up');
            } else {
                detail.style.display = 'none';
                chevron.classList.remove('bi-chevron-up');
                chevron.classList.add('bi-chevron-down');
            }
        });
    });

    // basic search (client-side)
    (function(){
        const input = document.getElementById('la-search-input');
        const btn = document.getElementById('la-search-btn');

        function performSearch() {
            const q = input.value.trim().toLowerCase();
            document.querySelectorAll('.la-row').forEach(row => {
                const code = row.querySelector('.la-code')?.textContent?.toLowerCase() || '';
                // check also procurement priority & date text
                const priority = row.querySelector('.la-priority')?.textContent?.toLowerCase() || '';
                const visible = q === '' || code.includes(q) || priority.includes(q);
                row.style.display = visible ? '' : 'none';

                // toggle corresponding detail + footer visibility
                const target = row.getAttribute('data-target');
                const detail = document.getElementById(target);
                if (detail) detail.style.display = visible && getComputedStyle(detail).display === 'block' ? 'block' : 'none';

                // footer element is the next sibling after detail (in markup), so toggle it too
                const footer = row.nextElementSibling && row.nextElementSibling.nextElementSibling ? row.nextElementSibling.nextElementSibling : null;
                // In our structure footer is after detail; but safe-guard:
                if (footer && footer.classList && footer.classList.contains('la-footer')) {
                    footer.style.display = visible ? '' : 'none';
                }
            });
        }

        btn.addEventListener('click', performSearch);
        input.addEventListener('keydown', function(e){ if (e.key === 'Enter') performSearch(); });
    })();
</script>
@endpush

@endsection
