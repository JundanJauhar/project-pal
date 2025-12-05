@extends('layouts.app', ['hideNavbar' => true])

@section('title', 'Detail procurement - ' . $procurement->name_procurement)

@section('content')
<style>
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

    .timeline-step.completed .timeline-icon {
        background: #28AC00;
        color: white;
    }

    .timeline-step.active .timeline-icon {
        background: #ECAD02;
        color: white;
    }

    .timeline-step.not-started .timeline-icon {
        background: #e0e0e0;
        color: #999;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 8px;
    }

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
</style>


{{-- ===========================
    HEADER & LOGO
=========================== --}}
<div class="header-logo-wrapper" style="width: 100%; display: flex; justify-content: center; margin-top: -80px; margin-bottom: 15px;">
    <img src="{{ asset('images/logo-pal.png') }}" alt="Logo PAL" style="height: 220px; object-fit: contain;">
</div>

<a href="javascript:history.back()" 
   style="position: absolute; right: 90px; top: 110px; font-size: 28px; color: #DA3B3B; cursor: pointer;">
    <i class="bi bi-x-circle"></i>
</a>


{{-- ===========================
    HEADER PROCUREMENT
=========================== --}}
<div class="procurement-header">
    <h3 class="mb-4">Detail Procurement</h3>

    {{-- ===========================
        STAGE LOGIC
    =========================== --}}
    @php
        $progressByName = $procurement->procurementProgress
            ->filter(fn($pp) => $pp->checkpoint)
            ->keyBy(fn($pp) => $pp->checkpoint->point_name);

        $currentStageName = optional(
            $procurement->procurementProgress
                ->filter(fn($p) => $p->status === 'in_progress')
                ->first()?->checkpoint
        )->point_name ?? 'Inquiry & Quotation';

        $showTableInquiry = in_array($currentStageName, [
            'Inquiry & Quotation','Evatek','Negotiation','Usulan Pengadaan / OC','Pengiriman Material'
        ]);

        $showTableEvatek = in_array($currentStageName, [
            'Evatek','Negotiation','Usulan Pengadaan / OC','Pengiriman Material'
        ]);

        $showTableNegotiation = in_array($currentStageName, [
            'Negotiation','Usulan Pengadaan / OC','Pengiriman Material'
        ]);

        $showTableDelivery = in_array($currentStageName, ['Pengiriman Material']);

        $isStageInquiry     = $currentStageName === 'Inquiry & Quotation';
        $isStageEvatek      = $currentStageName === 'Evatek';
        $isStageNegotiation = $currentStageName === 'Negotiation';
        $isStageDelivery    = $currentStageName === 'Pengiriman Material';

        $isInquiryLocked     = optional($progressByName['Inquiry & Quotation'] ?? null)->status === 'completed';
        $isEvatekLocked      = optional($progressByName['Evatek'] ?? null)->status === 'completed';
        $isNegLocked         = optional($progressByName['Negotiation'] ?? null)->status === 'completed';
        $isDeliveryLocked    = optional($progressByName['Pengiriman Material'] ?? null)->status === 'completed';
    @endphp


    {{-- ===========================
        INFO GRID
    =========================== --}}
    <div class="info-grid" style="display: grid; grid-template-columns: repeat(2,1fr); gap: 20px; margin-bottom: 30px;">
        <div>
            <div class="info-item">
                <div class="info-label">Project</div>
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

            @if ($procurement->description)
            <div class="info-item">
                <div class="info-label">Deskripsi</div>
                <div class="info-value">{{ $procurement->description }}</div>
            </div>
            @endif
        </div>

        <div>
            <div class="info-item">
                <div class="info-label">Prioritas</div>
                <span class="badge-priority badge-{{ $procurement->priority }}">
                    {{ strtoupper($procurement->priority) }}
                </span>
            </div>

            <div class="info-item">
                <div class="info-label">Status</div>
                <span class="badge bg-secondary">{{ strtoupper($procurement->status_procurement) }}</span>
            </div>

            <div class="info-item">
                <div class="info-label">Tanggal Mulai</div>
                {{ $procurement->start_date?->format('d/m/Y') ?? '-' }}
            </div>

            <div class="info-item">
                <div class="info-label">Tanggal Target</div>
                {{ $procurement->end_date?->format('d/m/Y') }}
            </div>
        </div>
    </div>


    {{-- ===========================
        TIMELINE
    =========================== --}}
    <div class="timeline-container">
        @foreach ($checkpoints as $checkpoint)
            @php
                $status = 'not-started';
                $p = $procurement->procurementProgress
                        ->where('checkpoint_id', $checkpoint->point_id)
                        ->first();

                if ($p?->status === 'completed') $status = 'completed';
                elseif ($p?->status === 'in_progress') $status = 'active';

                $iconClass = \App\Services\CheckpointIconService::getIconClass($checkpoint->point_sequence);
            @endphp

            <div class="timeline-step {{ $status }}">
                <div class="timeline-icon"><i class="bi {{ $iconClass }}"></i></div>
                <small>{{ $checkpoint->point_name }}</small>
            </div>
        @endforeach
    </div>


    {{-- ===========================
        DETAIL ITEM PENGADAAN
    =========================== --}}
    <h5 class="section-title mt-4">Detail Item Pengadaan</h5>

    @foreach($procurement->requestProcurements as $request)
        @if ($request->items->count())
            <div class="dashboard-table-wrapper">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Item</th>
                            <th>Spesifikasi</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($request->items as $i => $item)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td style="text-align:left">{{ $item->item_name }}</td>
                                <td style="text-align:left">{{ $item->item_description ?? $item->specification ?? '-' }}</td>
                                <td>{{ $item->amount }} {{ $item->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach

{{-- ===========================
    INQUIRY & QUOTATION
=========================== --}}
@if ($showTableInquiry)
<h5 class="section-title mt-4">Inquiry & Quotation</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Vendor</th>
                    <th>Tanggal Inquiry</th>
                    <th>Tanggal Quotation</th>
                    <th>Target Quotation</th>
                    <th>Lead Time</th>
                    <th>Nilai Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $row = 1; @endphp

                {{-- =============================
                    DATA INQUIRY & QUOTATION
                ============================== --}}
                @foreach($inquiryQuotations as $iq)
                <tr>
                    <td>{{ $row++ }}</td>
                    <td>{{ $iq->vendor->name_vendor ?? '-' }}</td>
                    <td>{{ $iq->tanggal_inquiry?->format('d/m/Y') }}</td>
                    <td>{{ $iq->tanggal_quotation?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $iq->target_quotation?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $iq->lead_time ?? '-' }}</td>
                    <td>
                        @if($iq->nilai_harga)
                            Rp {{ number_format($iq->nilai_harga, 0, ',', '.') }} {{ $iq->currency }}
                        @else
                            -
                        @endif
                    </td>

                    {{-- =============================
                        AKSI (Hanya jika stage aktif dan belum locked)
                    ============================== --}}
                    <td>
                        @if($isStageInquiry && !$isInquiryLocked)
                        <button class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditIQ{{ $iq->inquiry_quotation_id }}">
                            Edit
                        </button>
                        @else
                        <span class="text-muted">Locked</span>
                        @endif
                    </td>
                </tr>

                {{-- =============================
                    MODAL EDIT IQ
                ============================== --}}
                @if($isStageInquiry && !$isInquiryLocked)
                <div class="modal fade" id="modalEditIQ{{ $iq->inquiry_quotation_id }}">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('inquiry-quotation.update', $iq->inquiry_quotation_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5>Edit Inquiry - {{ $iq->vendor->name_vendor }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <div class="mb-3">
                                        <label>Tanggal Inquiry *</label>
                                        <input type="date" name="tanggal_inquiry" class="form-control"
                                            value="{{ $iq->tanggal_inquiry->format('Y-m-d') }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Tanggal Quotation</label>
                                        <input type="date" name="tanggal_quotation" class="form-control"
                                            value="{{ $iq->tanggal_quotation?->format('Y-m-d') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label>Target Quotation</label>
                                        <input type="date" name="target_quotation" class="form-control"
                                            value="{{ $iq->target_quotation?->format('Y-m-d') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label>Lead Time</label>
                                        <input type="text" name="lead_time" class="form-control"
                                            value="{{ $iq->lead_time }}">
                                    </div>

                                    <div class="mb-3">
                                        <label>Nilai Harga</label>
                                        <input type="number" name="nilai_harga" class="form-control"
                                            value="{{ $iq->nilai_harga }}">
                                    </div>

                                    <div class="mb-3">
                                        <label>Mata Uang</label>
                                        <select name="currency" class="form-select">
                                            <option value="IDR" @selected($iq->currency=='IDR')>IDR</option>
                                            <option value="USD" @selected($iq->currency=='USD')>USD</option>
                                            <option value="EUR" @selected($iq->currency=='EUR')>EUR</option>
                                            <option value="SGD" @selected($iq->currency=='SGD')>SGD</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Catatan</label>
                                        <textarea name="notes" class="form-control">{{ $iq->notes }}</textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-primary">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                @endif

                @endforeach


                {{-- =============================
                    BARIS "CREATE" IQ (Hanya jika stage aktif & belum locked)
                ============================== --}}
                @if($isStageInquiry && !$isInquiryLocked)
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Tambahkan Inquiry & Quotation baru
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateIQ">
                            Create
                        </button>
                    </td>
                </tr>

                {{-- =============================
                    MODAL CREATE IQ
                ============================== --}}
                <div class="modal fade" id="modalCreateIQ">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('inquiry-quotation.store', $procurement->project_id) }}">
                                @csrf
                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                <div class="modal-header">
                                    <h5>Create Inquiry & Quotation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    {{-- Vendor --}}
                                    <div class="mb-3">
                                        <label>Pilih Vendor *</label>
                                        <select name="vendor_id" class="form-select" required>
                                            <option disabled selected>Pilih vendor</option>
                                            @foreach($vendors as $vendor)
                                                <option value="{{ $vendor->id_vendor }}">{{ $vendor->name_vendor }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Tanggal Inquiry *</label>
                                        <input type="date" name="tanggal_inquiry" class="form-control" required>
                                    </div>

                                    <div class="mb-3">
                                        <label>Tanggal Quotation</label>
                                        <input type="date" name="tanggal_quotation" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label>Target Quotation</label>
                                        <input type="date" name="target_quotation" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label>Lead Time</label>
                                        <input type="text" name="lead_time" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label>Nilai Harga</label>
                                        <input type="number" name="nilai_harga" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label>Mata Uang</label>
                                        <select name="currency" class="form-select">
                                            <option value="IDR">IDR</option>
                                            <option value="USD">USD</option>
                                            <option value="EUR">EUR</option>
                                            <option value="SGD">SGD</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label>Catatan</label>
                                        <textarea name="notes" class="form-control"></textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-success">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                @endif

            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ===========================
    EVATEK
=========================== --}}
@if($showTableEvatek)
<h5 class="section-title">Evatek</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Item</th>
                    <th>Revision</th>
                    <th>Tanggal Start</th>
                    <th>Tanggal Target</th>
                    <th>Hasil Evatek</th>
                    <th>Tanggal Hasil</th>
                    <th>Vendor</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @php 
                    $row = 0;
                    $existingEvatekItemIds = $evatekItems->pluck('item_id')->toArray();

                    $statusLabel = [
                        'on_progress'  => ['bg' => '#fff3cd', 'text' => '#856404', 'label'=>'On Progress'],
                        'approve'      => ['bg' => '#d4edda', 'text' => '#155724', 'label'=>'Approved'],
                        'not_approve'  => ['bg' => '#f8d7da', 'text' => '#721c24', 'label'=>'Rejected'],
                    ];
                @endphp

                {{-- ===========================
                    1. TAMPILKAN ITEM YANG SUDAH PUNYA EVATEK
                ============================ --}}
                @foreach($evatekItems as $evatek)
                    @php 
                        $row++;
                        $item = $evatek->item;
                        $latestRev = $evatek->revisions?->first();
                        
                        // Tentukan tanggal hasil berdasarkan revisi terbaru
                        $resultDate = $latestRev->approved_at 
                            ?? $latestRev->not_approved_at 
                            ?? $latestRev->date 
                            ?? null;

                        $stat = $statusLabel[$evatek->status] 
                                ?? ['bg'=>'#e2e3e5','text'=>'#383d41','label'=>ucfirst($evatek->status)];
                    @endphp

                    <tr>
                        <td>{{ $row }}</td>

                        <td>{{ $item->item_name }}</td>

                        <td>
                            <span style="background:#e8f4f8; padding:4px 12px; border-radius:4px; font-weight:600;">
                                {{ $evatek->current_revision }}
                            </span>
                        </td>

                        <td>
                            {{ $evatek->start_date?->format('d/m/Y') ?? '-' }}
                        </td>

                        <td>
                            <span style="background:#fff3cd; padding:4px 8px; border-radius:4px;">
                                {{ $evatek->target_date?->format('d/m/Y') ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span style="background:{{ $stat['bg'] }}; color:{{ $stat['text'] }};
                                padding:6px 12px; border-radius:4px; font-size:12px; font-weight:600;">
                                {{ $stat['label'] }}
                            </span>
                        </td>

                        <td>
                            @if($resultDate)
                                {{ \Carbon\Carbon::parse($resultDate)->format('d/m/Y') }}
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td>{{ $evatek->vendor->name_vendor ?? '-' }}</td>

                        <td>
                            {{-- Review selalu muncul --}}
                            <a href="{{ route('desain.review-evatek', $evatek->evatek_id) }}"
                                class="btn btn-info btn-sm"
                                style="background:#17a2b8; color:white; font-weight:600;">
                                Review
                            </a>
                        </td>
                    </tr>
                @endforeach


                {{-- ===========================
                    2. TAMPILKAN ITEM YANG BELUM PUNYA EVATEK
                ============================ --}}
                @foreach($procurement->requestProcurements as $request)
                    @foreach($request->items as $item)
                        @if(!in_array($item->item_id, $existingEvatekItemIds))
                            @php $row++; @endphp

                            <tr>
                                <td>{{ $row }}</td>

                                <td>{{ $item->item_name }}</td>

                                <td class="text-muted">-</td>
                                <td class="text-muted">-</td>
                                <td class="text-muted">-</td>
                                <td class="text-muted">-</td>
                                <td class="text-muted">-</td>

                                <td>{{ $request->vendor->name_vendor ?? '-' }}</td>

                                <td>
                                    {{-- CREATE hanya jika stage aktif dan belum locked --}}
                                    @if($isStageEvatek && !$isEvatekLocked)
                                    <button class="btn btn-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalCreateEvatek{{ $row }}">
                                        Create
                                    </button>
                                    @else
                                    <span class="text-muted">Locked</span>
                                    @endif
                                </td>
                            </tr>

                            {{-- ===========================
                                MODAL CREATE EVATEK ITEM
                            ============================ --}}
                            @if($isStageEvatek && !$isEvatekLocked)
                            <div class="modal fade" id="modalCreateEvatek{{ $row }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <form method="POST" action="{{ route('supply-chain.evatek-item.store', $procurement->project_id) }}">
                                            @csrf

                                            <div class="modal-header">
                                                <h5>Buat Evatek - {{ $item->item_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">
                                                <input type="hidden" name="item_id" value="{{ $item->item_id }}">

                                                <div class="mb-3">
                                                    <label class="form-label" style="font-weight:600;">Tanggal Target *</label>
                                                    <input type="date" name="target_date" class="form-control"
                                                        value="{{ $procurement->end_date?->format('Y-m-d') }}"
                                                        min="{{ now()->toDateString() }}"
                                                        required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label" style="font-weight:600;">Pilih Vendor *</label>
                                                    <select name="vendor_ids[]" class="form-select" multiple size="4" required>
                                                        @foreach($vendors as $vendor)
                                                            <option value="{{ $vendor->id_vendor }}"
                                                                @if($vendor->id_vendor == $request->vendor_id) selected @endif>
                                                                {{ $vendor->name_vendor }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Gunakan Ctrl+Click untuk memilih lebih dari satu vendor</small>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button class="btn btn-success">Simpan</button>
                                            </div>

                                        </form>

                                    </div>
                                </div>
                            </div>
                            @endif

                        @endif
                    @endforeach
                @endforeach

            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ===========================
    NEGOTIATION
=========================== --}}
@if ($showTableNegotiation)
<h5 class="section-title">Negotiation</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Vendor</th>
                    <th>HPS</th>
                    <th>Budget</th>
                    <th>Harga Final</th>
                    <th>Tanggal Kirim</th>
                    <th>Tanggal Terima</th>
                    <th>Catatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $row = 1; @endphp

                {{-- ===========================
                    DATA NEGOTIATION
                ============================ --}}
                @foreach($negotiations as $neg)
                <tr>
                    <td>{{ $row++ }}</td>

                    <td>{{ $neg->vendor->name_vendor ?? '-' }}</td>

                    <td>
                        @if($neg->hps)
                            Rp {{ number_format($neg->hps, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if($neg->budget)
                            Rp {{ number_format($neg->budget, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if($neg->harga_final)
                            Rp {{ number_format($neg->harga_final, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </td>

                    <td>{{ $neg->tanggal_kirim?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $neg->tanggal_terima?->format('d/m/Y') ?? '-' }}</td>

                    <td>{{ $neg->notes ?? '-' }}</td>

                    <td>
                        @if($isStageNegotiation && !$isNegLocked)
                        <button class="btn btn-warning btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditNeg{{ $neg->negotiation_id }}">
                            Edit
                        </button>
                        @else
                        <span class="text-muted">Locked</span>
                        @endif
                    </td>
                </tr>

                {{-- ===========================
                    MODAL EDIT NEGOTIATION
                ============================ --}}
                @if($isStageNegotiation && !$isNegLocked)
                <div class="modal fade" id="modalEditNeg{{ $neg->negotiation_id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('negotiation.update', $neg->negotiation_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5>Edit Negotiation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <label>Vendor</label>
                                    <select name="vendor_id" class="form-select" required>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id_vendor }}"
                                                {{ $v->id_vendor == $neg->vendor_id ? 'selected' : '' }}>
                                                {{ $v->name_vendor }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <label class="mt-2">HPS</label>
                                    <input type="number" name="hps" class="form-control"
                                        value="{{ $neg->hps }}">

                                    <label class="mt-2">Budget</label>
                                    <input type="number" name="budget" class="form-control"
                                        value="{{ $neg->budget }}">

                                    <label class="mt-2">Harga Final</label>
                                    <input type="number" name="harga_final" class="form-control"
                                        value="{{ $neg->harga_final }}">

                                    <label class="mt-2">Tanggal Kirim</label>
                                    <input type="date" name="tanggal_kirim" class="form-control"
                                        value="{{ $neg->tanggal_kirim?->format('Y-m-d') }}">

                                    <label class="mt-2">Tanggal Terima</label>
                                    <input type="date" name="tanggal_terima" class="form-control"
                                        value="{{ $neg->tanggal_terima?->format('Y-m-d') }}">

                                    <label class="mt-2">Catatan</label>
                                    <textarea name="notes" class="form-control">{{ $neg->notes }}</textarea>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-success">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                @endif

                @endforeach


                {{-- ===========================
                    CREATE NEGOTIATION
                ============================ --}}
                @if($isStageNegotiation && !$isNegLocked)
                <tr>
                    <td colspan="9" class="text-center">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateNeg">
                            Create Negotiation
                        </button>
                    </td>
                </tr>

                {{-- ===========================
                    MODAL CREATE NEGOTIATION
                ============================ --}}
                <div class="modal fade" id="modalCreateNeg" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('negotiation.store', $procurement->project_id) }}">
                                @csrf

                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                <div class="modal-header">
                                    <h5>Create Negotiation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <label>Vendor</label>
                                    <select name="vendor_id" class="form-select" required>
                                        <option value="">-- Pilih Vendor --</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id_vendor }}">{{ $v->name_vendor }}</option>
                                        @endforeach
                                    </select>

                                    <label class="mt-2">HPS</label>
                                    <input type="number" name="hps" class="form-control">

                                    <label class="mt-2">Budget</label>
                                    <input type="number" name="budget" class="form-control">

                                    <label class="mt-2">Harga Final</label>
                                    <input type="number" name="harga_final" class="form-control">

                                    <label class="mt-2">Tanggal Kirim</label>
                                    <input type="date" name="tanggal_kirim" class="form-control">

                                    <label class="mt-2">Tanggal Terima</label>
                                    <input type="date" name="tanggal_terima" class="form-control">

                                    <label class="mt-2">Catatan</label>
                                    <textarea name="notes" class="form-control"></textarea>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-success">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                @endif

            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ===========================
    PENGIRIMAN MATERIAL
=========================== --}}
@if($showTableDelivery)
<h5 class="section-title">Pengiriman Material</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Incoterms</th>
                    <th>ETD</th>
                    <th>ETA SBY Port</th>
                    <th>ETA PAL</th>
                    <th>Remark</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $row = 1; @endphp

                {{-- ===========================
                    TAMPILKAN SEMUA DELIVERY YANG SUDAH ADA
                ============================ --}}
                @foreach($materialDeliveries as $delivery)
                <tr>
                    <td>{{ $row++ }}</td>

                    <td>{{ $delivery->incoterms ?? '-' }}</td>

                    <td>{{ $delivery->etd?->format('d/m/Y') ?? '-' }}</td>

                    <td>{{ $delivery->eta_sby_port?->format('d/m/Y') ?? '-' }}</td>

                    <td>{{ $delivery->eta_pal?->format('d/m/Y') ?? '-' }}</td>

                    <td>{{ $delivery->remark ? Str::limit($delivery->remark, 40) : '-' }}</td>

                    <td>
                        @if($isStageDelivery && !$isDeliveryLocked)
                        <button class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditDelivery{{ $delivery->delivery_id }}">
                            Edit
                        </button>
                        @else
                        <span class="text-muted">Locked</span>
                        @endif
                    </td>
                </tr>

                {{-- ===========================
                    MODAL EDIT DELIVERY
                ============================ --}}
                @if($isStageDelivery && !$isDeliveryLocked)
                <div class="modal fade" id="modalEditDelivery{{ $delivery->delivery_id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('material-delivery.update', $delivery->delivery_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5>Edit Pengiriman Material</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label">Incoterms</label>
                                        <input type="text" name="incoterms" class="form-control"
                                               value="{{ $delivery->incoterms }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">ETD</label>
                                        <input type="date" name="etd" class="form-control"
                                               value="{{ $delivery->etd?->format('Y-m-d') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">ETA SBY Port</label>
                                        <input type="date" name="eta_sby_port" class="form-control"
                                               value="{{ $delivery->eta_sby_port?->format('Y-m-d') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">ETA PAL</label>
                                        <input type="date" name="eta_pal" class="form-control"
                                               value="{{ $delivery->eta_pal?->format('Y-m-d') }}">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Remark</label>
                                        <textarea name="remark" class="form-control" rows="3">{{ $delivery->remark }}</textarea>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-success">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                @endif

                @endforeach


                {{-- ===========================
                    TOMBOL CREATE DELIVERY (HANYA SEKALI)
                ============================ --}}
                @if($isStageDelivery && !$isDeliveryLocked)
                <tr>
                    <td colspan="7" class="text-center">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateDelivery">
                            Tambah Pengiriman Material
                        </button>
                    </td>
                </tr>

                {{-- ===========================
                    MODAL CREATE DELIVERY
                ============================ --}}
                <div class="modal fade" id="modalCreateDelivery" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('material-delivery.store', $procurement->project_id) }}">
                                @csrf

                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                <div class="modal-header">
                                    <h5>Input Pengiriman Material</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <div class="mb-3">
                                        <label class="form-label">Incoterms</label>
                                        <input type="text" name="incoterms" class="form-control"
                                               placeholder="Contoh: FOB, CIF, DDP">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">ETD</label>
                                        <input type="date" name="etd" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">ETA SBY Port</label>
                                        <input type="date" name="eta_sby_port" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">ETA PAL</label>
                                        <input type="date" name="eta_pal" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Remark</label>
                                        <textarea name="remark" class="form-control" rows="3"></textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-success">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                @endif

            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ===========================
    TOMBOL SELESAIKAN STAGE
=========================== --}}

@php
    // HANYA 4 STAGE YANG BUTUH TOMBOL VERIFIKASI MANUAL
    $manualStages = [
        'Inquiry & Quotation',
        'Evatek',
        'Negotiation',
        'Pengiriman Material'
    ];

    $currentCheckpoint = $procurement->procurementProgress
        ->firstWhere('status', 'in_progress');

    $currentCheckpointName = $currentCheckpoint?->checkpoint?->point_name ?? null;

    // SYARAT PENYELESAIAN
    $stageCompletionAllowed = [
        'Inquiry & Quotation' => $inquiryQuotations->count() > 0,
        'Evatek'              => $evatekItems->count() > 0 && $evatekItems->every(fn($e) => in_array($e->status, ['approve','not_approve'])),
        'Negotiation'         => $negotiations->count() > 0,
        'Pengiriman Material' => true, // opsional
    ];
@endphp

@if ($currentCheckpointName && in_array($currentCheckpointName, $manualStages))

    @php
        $allowed = $stageCompletionAllowed[$currentCheckpointName] ?? false;
    @endphp

    <div class="text-center my-4">

        @if($allowed)
            <form action="{{ route('procurement.completeStage', $procurement->procurement_id) }}" method="POST">
                @csrf
                <button class="btn btn-success px-4 py-2" style="font-weight:600; font-size:16px;">
                    ✅ Selesaikan Stage: {{ $currentCheckpointName }}
                </button>
            </form>
        @else
            <div class="alert alert-warning" style="width: 50%; margin: auto;">
                <strong>Stage "{{ $currentCheckpointName }}" belum bisa diselesaikan.</strong><br>
                Lengkapi semua data untuk melanjutkan.
            </div>
        @endif

    </div>

@endif
