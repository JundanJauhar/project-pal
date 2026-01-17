@extends('layouts.app')

@section('title', 'Detail procurement - ' . $procurement->name_procurement)

@section('content')
<style>
    body {
        overflow-y: auto !important;
    }

    body.modal-open {
        padding-right: 0 !important;
        overflow: hidden !important;
    }

    html.modal-open {
        margin-right: 0 !important;
    }


    .procurement-header {
        padding: 25px;
        background: white;
    }

    .timeline-container {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin: 40px auto;
        padding: 0 20px;
        width: fit-content;
    }

    .timeline-container::before {
        content: "";
        position: absolute;
        top: 24px;
        left: 0;
        right: 0;
        height: 3px;
        background: #c7e5c6;
        width: calc(100% - 45px);
        margin: auto;
        z-index: 1;
    }

    .timeline-step {
        text-align: center;
        width: 90px;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
    }

    .timeline-step small {
        margin-top: 6px;
        height: 38px;
        display: flex;
        align-items: center;
        text-align: center;
        line-height: 1.2;
        font-size: 12px;
    }

    .timeline-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #e0e0e0;
        color: #999;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 20px;
        margin: auto;
        font-weight: bold;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #003d82;
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 20px;
        transition: all 0.2s;
    }

    .back-link:hover {
        color: #002e5c;
        transform: translateX(-4px);
    }

    /* Completed State - Hijau */
    .timeline-step.completed .timeline-icon {
        background: #28AC00;
        color: white;
    }

    /* Active State - Kuning */
    .timeline-step.active .timeline-icon {
        background: #ECAD02;
        color: white;
    }

    /* Not Started State - Abu-abu (default) */
    .timeline-step.not-started .timeline-icon {
        background: #e0e0e0;
        color: #999;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 8px;
    }

    .doc-card {
        background: #F7F7F7;
        border-radius: 10px;
        padding: 15px 18px;
        margin-bottom: 20px;
    }


    .logo {
        height: 100px;
    }

    .header-logo-wrapper {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: -80px;
        margin-bottom: 15px;
    }

    .logo {
        height: 220px;
        object-fit: contain;
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .info-item {
        margin-bottom: 10px;
    }

    .info-label {
        font-weight: 600;
        color: #666;
        margin-bottom: 5px;
    }

    .info-value {
        color: #333;
    }

    .badge-priority {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }


    /* Dashboard Table Styling */
    .dashboard-table-wrapper {
        padding: 25px;
        border-radius: 14px;
        margin-top: 20px;
        margin-bottom: 30px;
        background: #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .dashboard-table {
        width: 100%;
        border-collapse: collapse;
    }

    .dashboard-table thead th {
        padding: 14px 8px;
        border-bottom: 2px solid #C9C9C9;
        font-size: 14px;
        text-transform: uppercase;
        color: #555;
        text-align: center;
        vertical-align: middle;
        font-weight: 600;
    }

    .dashboard-table tbody tr:hover {
        background: #EFEFEF;
    }

    .dashboard-table tbody td {
        padding: 14px 8px;
        border-bottom: 1px solid #DFDFDF;
        font-size: 15px;
        color: #333;
        text-align: center;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 13px;
        border-radius: 6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }

    .btn-action-edit {
        background: #ECAD02;
        color: white;
        border: 1px solid #ECAD02;
    }

    .btn-action-edit:hover {
        background: #d19b02;
        color: white;
        border: 1px solid #d19b02;
    }

    .btn-action-create {
        background: #003d82;
        color: white;
        border: 1px solid #003d82;
    }

    .btn-action-create:hover {
        background: #002e5c;
        color: white;
        border: 1px solid #002e5c;
    }

    .btn-action-review {
        background: #003d82;
        color: white;
        border: 1px solid #003d82;
    }

    .btn-action-review:hover {
        background: #002e5c;
        color: white;
        border: 1px solid #002e5c;
    }

    .btn-action-abort {
        background: #BD0000;
        color: white;
        border: 1px solid #BD0000;
    }

    .btn-action-abort:hover {
        background: #930000;
        color: white;
        border: 1px solid #930000;
    }

    .btn-simpan-wrapper {
        display: flex;
        justify-content: end;
        margin-bottom: 12px;
        margin-right: 20px;

    }

    .btn-action-simpan {
        background: #003d82;
        color: white;
        border: 1px solid #003d82;
        align-items: end;
    }

    .btn-action-simpan:hover {
        background: #002e5c;
        color: white;
        border: 1px solid #002e5c;
    }

    .modal-dialog {
        margin: 0 auto;
        top: 50%;
        transform: translateY(-50%) !important;
    }

    @media (min-width: 576px) {
        .modal.show .modal-dialog {
            margin: 0 auto;
        }
    }

    .modal.fade .modal-dialog {
        transition: transform .3s ease-out;
    }
</style>

{{-- Back Link --}}
<div class="px-4 mb-3">
    <a href="{{ route('dashboard') }}" class="back-link" wire:navigate>
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<div class="procurement-header">
    {{-- Header procurement --}}
    <h3 class="mb-4">Detail Procurement</h3>

    <div class="info-grid">
        <div>
            <div class="info-item">
                <div class="info-label">Procurement</div>
                <div class="info-value"><strong>{{ $procurement->code_procurement }}</strong></div>
            </div>

            <div class="info-item">
                <div class="info-label">Nama Procurement</div>
                <div class="info-value">{{ $procurement->name_procurement }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Department</div>
                <div class="info-value">{{ $procurement->department->department_name ?? '-' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Vendor</div>
                <div class="info-value">
                    {{ $procurement->requestProcurements->first()?->vendor?->name_vendor ?? 'Belum ditentukan' }}
                </div>
            </div>

            @if($procurement->description)
            <div class="info-item mb-4">
                <div class="info-label">Deskripsi</div>
                <div class="info-value">{{ $procurement->description }}</div>
            </div>
            @endif
        </div>

        <div>
            <div class="info-item">
                <div class="info-label">Prioritas</div>
                <div class="info-value">
                    <span class="badge-priority badge-{{ $procurement->priority }}">
                        {{ strtoupper($procurement->priority) }}
                    </span>
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="badge bg-secondary">{{ strtoupper($procurement->status_procurement) }}</span>
                </div>
            </div>

            <div class="info-item">
                <div class="info-label">Tanggal Mulai</div>
                <div class="info-value">{{ $procurement->start_date ? $procurement->start_date->format('d/m/Y') : '-' }}</div>
            </div>

            <div class="info-item">
                <div class="info-label">Tanggal Target</div>
                <div class="info-value">{{ $procurement->end_date->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Timeline dengan Logic Correct dan Dynamic Icons --}}
    <div class="timeline-container">
        @forelse($checkpoints as $checkpoint)
        @php
        $status = 'not-started'; // Default

        // Check if this checkpoint is completed
        $completedCheckpoint = $procurement->procurementProgress
        ->where('checkpoint_id', $checkpoint->point_id)
        ->where('status', 'completed')
        ->first();

        if ($completedCheckpoint) {
        $status = 'completed';
        } else {
        // Check if this is the current (active) checkpoint
        $currentCheckpoint = $procurement->procurementProgress
        ->where('checkpoint_id', $checkpoint->point_id)
        ->where('status', 'in_progress')
        ->first();

        if ($currentCheckpoint) {
        $status = 'active';
        }
        }

        // Get appropriate icon for this checkpoint
        $iconClass = \App\Services\CheckpointIconService::getIconClass($checkpoint->point_sequence);
        @endphp
        <div class="timeline-step {{ $status }}" title="{{ $checkpoint->point_name }}">
            <div class="timeline-icon">
                <i class="bi {{ $iconClass }}"></i>
            </div>
            <small>{{ $checkpoint->point_name }}</small>
        </div>
        @empty
        <div class="text-center">Tidak ada checkpoint yang tersedia</div>
        @endforelse
    </div>

    {{-- Detail Items Pengadaan --}}
    <h5 class="section-title mt-4">Detail Item Pengadaan</h5>

    @forelse($procurement->requestProcurements as $request)
    @if($request->items->count() > 0)
    <div class="dashboard-table-wrapper">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Item</th>
                        <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Spesifikasi</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($request->items as $index => $item)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $index + 1 }}</td>
                        <td style="padding: 12px 8px; text-align: left; color: #000;">{{ $item->item_name }}</td>
                        <td style="padding: 12px 8px; text-align: left; color: #000;">{{ $item->item_description ?? $item->specification ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $item->amount }} {{ $item->unit }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
    @empty
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Belum ada item untuk procurement ini.
    </div>
    @endforelse

    @php
    $hasPembayaran = isset($pembayarans) && $pembayarans->count() > 0;
    $hasNonSkbdnPayment = $hasPembayaran
    && $pembayarans->contains(fn ($p) => $p->payment_type !== 'SKBDN');
    @endphp


    {{-- ================= Inquiry & Quotation ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 2,
    'procurements.partials.inquiry_quotation',
    compact('procurement','inquiryQuotations','vendors','currentStageIndex','currentCheckpointSequence'))

    {{-- ================= Evatek ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 3,
    'procurements.partials.evatek',
    compact('procurement','evatekItems','vendors','inquiryQuotations','currentCheckpointSequence'))

    {{-- ================= Negotiation ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 4,
    'procurements.partials.negotiation',
    compact('procurement','negotiations','vendors','currentCheckpointSequence'))

    {{-- ================= Pengadaan OC ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 5,
    'procurements.partials.pengadaan_oc',
    compact('procurement', 'pengadaanOcs', 'vendors', 'currentCheckpointSequence'))

    {{-- ================= Review Kontrak ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 6,
    'procurements.partials.contract_review',
    compact('procurement', 'contractReviews', 'pengadaanOcVendors', 'currentCheckpointSequence'))

    {{-- ================= Pengesahan Kontrak ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 6,
    'procurements.partials.pengesahan_kontrak',
    compact('procurement', 'pengadaanOcs', 'vendors', 'currentCheckpointSequence'))

    {{-- ================= Kontrak ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 6,
    'procurements.partials.kontrak',
    compact('procurement', 'kontraks', 'vendors', 'currentCheckpointSequence'))

    {{-- ================= Pembayaran ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 7,
    'procurements.partials.pembayaran',
    compact('procurement','pembayarans','currentCheckpointSequence')
    )

    {{-- ================= Jaminan Pembayaran ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain'
    && $currentCheckpointSequence >= 7
    && $hasNonSkbdnPayment,
    'procurements.partials.jaminanpembayaran',
    compact('procurement','jaminans','currentCheckpointSequence','pembayarans')
    )

    {{-- ================= Material Delivery ================= --}}
    @includeWhen(
    auth()->user()->roles === 'supply_chain' && $currentCheckpointSequence >= 8,
    'procurements.partials.material_delivery',
    compact('procurement', 'materialDeliveries')
    )





</div>
</div>
@endsection

@push('scripts')
<script>
    /**
     * ===============================
     * GLOBAL CURRENCY FORMATTER
     * ===============================
     * Format angka otomatis saat user mengetik
     * HARUS DIDEFINISIKAN PALING AWAL (sebelum event listeners lain)
     */
    document.addEventListener('input', function(e) {
        if (!e.target.classList.contains('currency-input')) return;

        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value ? new Intl.NumberFormat('id-ID').format(value) : '';
    });


    /**
     * ===============================
     * INQUIRY & QUOTATION
     * ===============================
     */
    function selectCurrencyEdit(cur, id) {
        document.getElementById('currencyEdit' + id).value = cur;
        document.getElementById('dropdownCurrency' + id).innerText = cur;
    }

    function selectCurrency(cur) {
        document.getElementById('currencyInput').value = cur;
        document.getElementById('dropdownCurrency').innerText = cur;
    }


    /**
     * ===============================
     * NEGOTIATION
     * ===============================
     */
    function selectCurrencyEditNegotiation(type, cur, id) {
        document.getElementById(`currencyEdit${type}${id}`).value = cur;
        document.getElementById(`dropdownCurrency${type}${id}`).innerText = cur;
    }

    function selectCurrencyCreateNegotiation(type, cur) {
        document.getElementById(`currencyCreate${type}`).value = cur;
        document.getElementById(`dropdownCurrency${type}Create`).innerText = cur;
    }


    /**
     * ===============================
     * PENGADAAN OC
     * ===============================
     */
    function selectCurrencyEditPO(cur, id) {
        document.getElementById('currencyEditPO' + id).value = cur;
        document.getElementById('dropdownCurrencyEdit' + id).innerText = cur;
    }

    function selectCurrencyCreatePO(cur) {
        document.getElementById('currencyCreatePO').value = cur;
        document.getElementById('dropdownCurrencyCreatePO').innerText = cur;
    }


    /**
     * ===============================
     * PENGESAHAN KONTRAK
     * ===============================
     */
    function selectCurrencyEditPK(cur, id) {
        document.getElementById('currencyEditPK' + id).value = cur;
        document.getElementById('dropdownCurrencyEdit' + id).innerText = cur;
    }

    function selectCurrencyCreatePK(cur) {
        document.getElementById('currencyCreatePK').value = cur;
        document.getElementById('dropdownCurrencyCreatePKDisplay').innerText = cur;
    }


    /**
     * ===============================
     * KONTRAK
     * ===============================
     */
    function selectCurrencyKontrak(cur, id) {
        document.getElementById('currencyKontrakEdit' + id).value = cur;
        document.getElementById('dropdownCurrencyKontrak' + id).innerText = cur;
    }

    function selectCurrencyCreateKontrak(cur) {
        document.getElementById('currencyCreateNilaiKontrak').value = cur;
        document.getElementById('dropdownCurrencyKontrakCreate').innerText = cur;
    }


    /**
     * ===============================
     * DOM CONTENT LOADED
     * ===============================
     * Semua event listeners yang memerlukan DOM elements
     */
    document.addEventListener('DOMContentLoaded', function() {

        /* ============================================
         * AUTO POPULATE NILAI PO (Pengadaan OC)
         * ============================================ */
        const vendorSelectPO = document.getElementById('vendorSelectPO');
        if (vendorSelectPO) {
            const nilaiDisplay = document.getElementById('nilaiPODisplay');
            const nilaiHidden = document.getElementById('nilaiPO');
            const currencyDisplay = document.getElementById('currencyCreatePODisplay');
            const currencyHidden = document.getElementById('currencyCreatePO');

            vendorSelectPO.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const harga = selected.dataset.harga;
                const currency = selected.dataset.currency || 'IDR';

                if (harga) {
                    nilaiDisplay.value = Number(harga).toLocaleString('id-ID');
                    nilaiHidden.value = harga;
                } else {
                    nilaiDisplay.value = '';
                    nilaiHidden.value = '';
                }

                currencyDisplay.innerText = currency;
                currencyHidden.value = currency;
            });
        }


        /* ============================================
         * AUTO POPULATE NILAI PK (Pengesahan Kontrak)
         * ============================================ */
        const vendorSelectPK = document.getElementById('vendorSelectPK');
        if (vendorSelectPK) {
            const nilaiDisplay = document.getElementById('nilaiPKDisplay');
            const nilaiHidden = document.getElementById('nilaiPK');
            const currencyDisplay = document.getElementById('currencyCreatePKDisplay');
            const currencyHidden = document.getElementById('currencyCreatePK');

            vendorSelectPK.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const harga = selected.dataset.harga;
                const currency = selected.dataset.currency || 'IDR';

                if (harga) {
                    nilaiDisplay.value = Number(harga).toLocaleString('id-ID');
                    nilaiHidden.value = harga;
                } else {
                    nilaiDisplay.value = '';
                    nilaiHidden.value = '';
                }

                currencyDisplay.innerText = currency;
                currencyHidden.value = currency;
            });
        }


        /* ============================================
         * MAP PEMBAYARAN → JAMINAN PEMBAYARAN
         * ============================================ */
        @if(isset($pembayarans) && $pembayarans->count() > 0)
        const paymentMap = {!! $pembayarans->mapWithKeys(fn($p) => [$p->vendor_id => $p->payment_type])->toJson() !!};

        const vendorJaminanSelect = document.getElementById('vendorJaminanSelect');
        const paymentTypeDisplay = document.getElementById('paymentTypeDisplay');

        if (vendorJaminanSelect && paymentTypeDisplay) {
            vendorJaminanSelect.addEventListener('change', function() {
                const vendorId = this.value;
                paymentTypeDisplay.value = paymentMap[vendorId] || '-';
            });
        }
        @endif

    }); // END DOMContentLoaded

</script>
@endpush