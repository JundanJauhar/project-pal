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

    <!-- {{-- REVIEW DOCUMENT --}}
    <h5 class="section-title mt-4">Review Document</h5>
    <div class="doc-card">
        {!! $procurement->review_notes ?? '<em class="text-muted">Belum ada review</em>' !!}
    </div>

    {{-- SIGN DOCUMENT --}}
    <h5 class="section-title">Sign Document</h5>
    <div class="doc-card">
        {!! $procurement->sign_notes ?? '<em class="text-muted">Belum ada tanda tangan</em>' !!}
    </div> -->


    @if(auth()->user()->roles=='supply_chain')
    @if ($currentCheckpointSequence >= 2)
    <h5 class="section-title">Inquiry & Quotation</h5>
    <div class="dashboard-table-wrapper">
        <div class="table-responsive">
            <div class="btn-simpan-wrapper">
                @if($currentStageIndex==2 && count($inquiryQuotations)>0)
                <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="from_checkpoint" value="2">
                    <button class="btn btn-sm btn-action-simpan"><i class="bi bi-box-arrow-down"></i>Simpan</button>
                </form>
                @endif
            </div>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Inquiry</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Quotation</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Target Quotation</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Lead Time</th>
                        <th style="padding: 12px 8px; text-align: center; color: #000;">Nilai Harga</th>
                        <th style="padding: 12px 8px; text-align: center; color: #000;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @php $row = 1; @endphp
                    @forelse($inquiryQuotations as $iq)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $row++ }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->vendor->name_vendor ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->tanggal_inquiry->format('d/m/Y') }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->tanggal_quotation ? $iq->tanggal_quotation->format('d/m/Y') : '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->target_quotation ? $iq->target_quotation->format('d/m/Y') : '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->lead_time ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($iq->nilai_harga)
                            {{ number_format($iq->nilai_harga, 0, ',', '.') }} {{ $iq->currency }}
                            @else
                            -
                            @endif
                        </td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            <button class="btn btn-sm btn-action-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditIQ{{ $iq->inquiry_quotation_id }}">
                                Edit
                            </button>
                        </td>
                    </tr>

                    <div class="modal fade iq-modal" id="modalEditIQ{{ $iq->inquiry_quotation_id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Inquiry & Quotation</h5>
                                </div>

                                <form method="POST" action="{{ route('inquiry-quotation.update', $iq->inquiry_quotation_id) }}">
                                    @csrf
                                    <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                    <div class="modal-body row g-3">

                                        {{-- Vendor --}}
                                        <div class="col-md-6">
                                            <label class="form-label">Vendor *</label>
                                            <select name="vendor_id" class="form-select" required>
                                                @foreach($vendors as $vendor)
                                                <option value="{{ $vendor->id_vendor }}"
                                                    @selected($vendor->id_vendor == $iq->vendor_id)>
                                                    {{ $vendor->name_vendor }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Tanggal inquiry --}}
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Inquiry *</label>
                                            <input type="date" name="tanggal_inquiry" class="form-control"
                                                value="{{ $iq->tanggal_inquiry->format('Y-m-d') }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Quotation</label>
                                            <input type="date" name="tanggal_quotation" class="form-control"
                                                value="{{ $iq->tanggal_quotation?->format('Y-m-d') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Target Quotation</label>
                                            <input type="date" name="target_quotation" class="form-control"
                                                value="{{ $iq->target_quotation?->format('Y-m-d') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Lead Time</label>
                                            <input type="text" name="lead_time" class="form-control"
                                                value="{{ $iq->lead_time }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Nilai Harga</label>
                                            <div class="input-group">

                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" id="dropdownCurrency{{ $iq->inquiry_quotation_id }}">
                                                    {{ $iq->currency ?? 'IDR' }}
                                                </button>

                                                <ul class="dropdown-menu">
                                                    @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                    <li><a class="dropdown-item" onclick="selectCurrencyEdit('{{ $cur }}', '{{ $iq->inquiry_quotation_id }}')">{{ $cur }}</a></li>
                                                    @endforeach
                                                </ul>

                                                <input type="text" name="nilai_harga" class="form-control currency-input"
                                                    value="{{ $iq->nilai_harga }}">
                                                <input type="hidden" name="currency" id="currencyEdit{{ $iq->inquiry_quotation_id }}"
                                                    value="{{ $iq->currency }}">
                                            </div>
                                        </div>


                                        <div class="col-12">
                                            <label class="form-label">Notes</label>
                                            <textarea name="notes" class="form-control">{{ $iq->notes }}</textarea>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-action-create">Simpan</button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>

                    @empty
                    @endforelse
                    <tr>
                        <td>{{ $row }}</td>
                        <td colspan="6" class="text-center text-muted">Belum ada Inquiry & Quotation</td>
                        <td>
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateIQ">
                                Create
                            </button>
                        </td>
                    </tr>


                    <div class="modal fade iq-modal" id="modalCreateIQ" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <form method="POST" action="{{ route('inquiry-quotation.store', $procurement->project_id) }}">
                                    @csrf
                                    <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Create Inquiry & Quotation</h5>
                                    </div>

                                    <div class="modal-body row g-3">

                                        <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                        {{-- Pilih Vendor --}}
                                        <div class="col-md-6">
                                            <label>Pilih Vendor *</label>
                                            <select name="vendor_id" class="form-select" required>
                                                <option value="" disabled selected>Pilih vendor</option>
                                                @foreach($vendors as $vendor)
                                                <option value="{{ $vendor->id_vendor }}">
                                                    {{ $vendor->name_vendor }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- tanggal inquiry --}}
                                        <div class="col-md-6">
                                            <label>Tanggal Inquiry *</label>
                                            <input type="date" name="tanggal_inquiry" class="form-control" required>
                                        </div>

                                        {{-- tanggal quotation --}}
                                        <div class="col-md-6">
                                            <label>Tanggal Quotation</label>
                                            <input type="date" name="tanggal_quotation" class="form-control">
                                        </div>

                                        {{-- target quotation --}}
                                        <div class="col-md-6">
                                            <label>Target Quotation</label>
                                            <input type="date" name="target_quotation" class="form-control">
                                        </div>

                                        {{-- lead time --}}
                                        <div class="col-md-6">
                                            <label>Lead Time</label>
                                            <input type="text" name="lead_time" class="form-control" placeholder="ex: 7 hari kerja">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Nilai Harga</label>
                                            <div class="input-group">
                                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCurrency" data-bs-toggle="dropdown">
                                                    {{ old('currency', 'IDR') }}
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" onclick="selectCurrency('IDR')">IDR</a></li>
                                                    <li><a class="dropdown-item" onclick="selectCurrency('USD')">USD</a></li>
                                                    <li><a class="dropdown-item" onclick="selectCurrency('EUR')">EUR</a></li>
                                                    <li><a class="dropdown-item" onclick="selectCurrency('SGD')">SGD</a></li>
                                                </ul>

                                                <input type="text" name="nilai_harga" class="form-control currency-input" placeholder="0">
                                                <input type="hidden" name="currency" id="currencyInput" value="IDR">
                                            </div>
                                        </div>

                                        {{-- Notes --}}
                                        <div class="col-12">
                                            <label>Catatan</label>
                                            <textarea name="notes" class="form-control" rows="3"></textarea>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-sm btn-action-create">Simpan</button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>

                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if ($currentCheckpointSequence >= 3)
    <h5 class="section-title">Evatek</h5>
    <div class="dashboard-table-wrapper">
        <div class="table-responsive">
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nama Item</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Start</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Target</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Revisi</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Revision</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Hasil</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Hasil</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $row = 0;
                    // Collect all item IDs yang sudah punya evatek
                    $evatekItemIds = $evatekItems->pluck('item_id')->toArray();
                    @endphp

                    {{-- ✅ TAMPILKAN EVATEK ITEMS YANG SUDAH ADA --}}
                    @forelse($evatekItems as $evatek)
                    @php
                    $row++;
                    $item = $evatek->item;
                    $latestRevision = $evatek->revisions?->first();
                    @endphp
                    <tr>
                        {{-- No --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $row }}
                        </td>

                        {{-- Nama Item --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $item->item_name }}
                        </td>


                        {{-- Tanggal Start --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $evatek->start_date->format('d/m/Y') }}
                        </td>

                        {{-- Tanggal Target --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <span style="padding: 4px 8px; border-radius: 4px;">
                                {{ $evatek->target_date?->format('d/m/Y') ?? '-' }}
                            </span>
                        </td>

                        {{-- Tanggal Revisi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($latestRevision)
                            {{ \Carbon\Carbon::parse($latestRevision->date)->format('d/m/Y') }}
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                        </td>
                        {{-- Revision --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <span style="padding: 4px 12px; border-radius: 4px; font-weight: 600;">
                                {{ $evatek->current_revision }}
                            </span>
                        </td>

                        {{-- Tanggal Hasil --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($latestRevision)
                            @php
                            $resultDate = $latestRevision->approved_at
                            ?? $latestRevision->not_approved_at
                            ?? $latestRevision->date;
                            @endphp
                            @if($resultDate)
                            {{ \Carbon\Carbon::parse($resultDate)->format('d/m/Y') }}
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                        </td>

                        {{-- Hasil Evatek --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @php
                            $statusColors = [
                            'on_progress' => ['text' => '#ECAD02', 'label' => 'On Progress'],
                            'approve' => ['text' => '#28AC00', 'label' => 'Approved'],
                            'not_approve' => ['text' => '#F10303', 'label' => 'Rejected'],
                            ];
                            $statusConfig = $statusColors[$evatek->status] ?? ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => ucfirst($evatek->status)];
                            @endphp
                            <span style="color: {{ $statusConfig['text'] }}; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 14px;">
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>


                        {{-- Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $evatek->vendor->name_vendor ?? '-' }}
                        </td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <a href="{{ route('desain.review-evatek', $evatek->evatek_id) }}"
                                class="btn btn-sm btn-action-review">
                                Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    @endforelse

                    {{-- ✅ TAMPILKAN FORM CREATE UNTUK ITEM YANG BELUM PUNYA EVATEK --}}
                    @forelse($procurement->requestProcurements as $request)
                    @foreach($request->items as $item)
                    {{-- ✅ HANYA TAMPILKAN JIKA ITEM INI BELUM PUNYA EVATEK --}}
                    @if(!in_array($item->item_id, $evatekItemIds))
                    @php $row++; @endphp
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $row }}
                        </td>

                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $item->item_name }}
                        </td>

                        <td>-</td>

                        <td>-</td>

                        <td>-</td>

                        <td>-</td>

                        <td>-</td>

                        <td>-</td>

                        {{-- Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $request->vendor->name_vendor ?? '-' }}
                        </td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <button type="button"
                                class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEvatek{{ $row }}">
                                Create
                            </button>

                            <!-- Modal untuk input data evatek -->
                            <div class="modal fade" id="modalEvatek{{ $row }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Input Evatek - {{ $item->item_name }}</h5>
                                        </div>
                                        <form method="POST" action="{{ route('supply-chain.evatek-item.store', $procurement->project_id) }}">
                                            @csrf
                                            <div class="modal-body">
                                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">
                                                <input type="hidden" name="item_id" value="{{ $item->item_id }}">

                                                {{-- Tanggal Target Input --}}
                                                <div class="mb-3">
                                                    <label class="form-label" style="font-weight: 600; font-size: 14px;">Tanggal Target *</label>
                                                    <input type="date"
                                                        name="target_date"
                                                        class="form-control"
                                                        value="{{ $procurement->end_date->format('Y-m-d') }}"
                                                        min="{{ now()->toDateString() }}"
                                                        required
                                                        style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                    <small style="color: #666;">Default: Tanggal target procurement</small>
                                                </div>

                                                {{-- Vendor Selection --}}
                                                <div class="mb-3">
                                                    <label class="form-label" style="font-weight: 600; font-size: 14px;">Pilih Vendor *</label>
                                                    <select name="vendor_ids[]"
                                                        class="form-select"
                                                        multiple
                                                        size="4"
                                                        required
                                                        style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                        @forelse($vendors as $vendor)
                                                        <option value="{{ $vendor->id_vendor }}"
                                                            @if($vendor->id_vendor == $request->vendor_id) selected @endif>
                                                            {{ $vendor->name_vendor }}
                                                        </option>
                                                        @empty
                                                        <option disabled>Tidak ada vendor yang tersedia</option>
                                                        @endforelse
                                                    </select>
                                                    <small style="color: #666;">Gunakan Ctrl+Click untuk memilih multiple vendor</small>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-sm btn-action-create">Buat Evatek</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">Tidak ada item untuk procurement ini.</td>
                    </tr>
                    @endforelse

                    {{-- ✅ JIKA TIDAK ADA EVATEK DAN TIDAK ADA ITEM --}}
                    @if($evatekItems->count() === 0 && $procurement->requestProcurements->sum(function($r) { return $r->items->count(); }) === 0)
                    <tr>
                        <td colspan="9" class="text-center py-4">Tidak ada item untuk dimasukkan ke Evatek.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if ($currentCheckpointSequence >= 4)
    <h5 class="section-title">Negotiation</h5>
    <div class="dashboard-table-wrapper">
        <div class="table-responsive">

            {{-- tombol simpan checkpoint --}}
            @if($currentCheckpointSequence==4 && count($negotiations)>0)
            <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                @csrf
                <input type="hidden" name="from_checkpoint" value="4">
                <button class="btn btn-sm btn-action-simpan">
                    <i class="bi bi-box-arrow-down"></i>Simpan
                </button>
            </form>
            @endif

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
                        <th>Note</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php $row = 1; @endphp

                    @foreach($negotiations as $neg)
                    <tr>
                        <td>{{ $row++ }}</td>
                        <td>{{ $neg->vendor->name_vendor ?? '-' }}</td>
                        <td>
                            @if($neg->hps)
                            {{ number_format($neg->hps,0,',','.') }} {{ $neg->currency_hps }}
                            @else - @endif
                        </td>

                        <td>
                            @if($neg->budget)
                            {{ number_format($neg->budget,0,',','.') }} {{ $neg->currency_budget }}
                            @else - @endif
                        </td>

                        <td>
                            @if($neg->harga_final)
                            {{ number_format($neg->harga_final,0,',','.') }} {{ $neg->currency_harga_final }}
                            @else - @endif
                        </td>

                        <td>{{ $neg->tanggal_kirim?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $neg->tanggal_terima?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $neg->notes ?? '-' }}</td>

                        <td>
                            <button data-bs-toggle="modal"
                                data-bs-target="#modalEditNeg{{ $neg->negotiation_id }}"
                                class="btn btn-sm btn-action-edit">
                                Edit
                            </button>
                        </td>
                    </tr>

                    {{-- modal edit --}}
                    <div class="modal fade" id="modalEditNeg{{ $neg->negotiation_id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <form method="POST"
                                    action="{{ route('negotiation.update', $neg->negotiation_id) }}">
                                    @csrf

                                    <div class="modal-header">
                                        <h5>Edit Negotiation</h5>
                                    </div>
                                    <div class="modal-body row g-3">

                                        {{-- vendor --}}
                                        <div class="col-md-6">
                                            <label>Vendor *</label>
                                            <select name="vendor_id" class="form-select" required>
                                                @foreach($vendors as $v)
                                                <option value="{{ $v->id_vendor }}"
                                                    @selected($v->id_vendor==$neg->vendor_id)>
                                                    {{ $v->name_vendor }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- HPS --}}
                                        <div class="col-md-6">
                                            <label>HPS</label>
                                            <div class="input-group">
                                                <select name="currency_hps" class="form-select">
                                                    @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                    <option value="{{ $c }}" @selected($neg->currency_hps==$c)>
                                                        {{ $c }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="hps" class="form-control"
                                                    value="{{ $neg->hps }}">
                                            </div>
                                        </div>

                                        {{-- BUDGET --}}
                                        <div class="col-md-6">
                                            <label>Budget</label>
                                            <div class="input-group">
                                                <select name="currency_budget" class="form-select">
                                                    @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                    <option value="{{ $c }}" @selected($neg->currency_budget==$c)>
                                                        {{ $c }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="budget" class="form-control"
                                                    value="{{ $neg->budget }}">
                                            </div>
                                        </div>

                                        {{-- HARGA FINAL --}}
                                        <div class="col-md-6">
                                            <label>Harga Final</label>
                                            <div class="input-group">
                                                <select name="currency_harga_final" class="form-select">
                                                    @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                    <option value="{{ $c }}" @selected($neg->currency_harga_final==$c)>
                                                        {{ $c }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="harga_final" class="form-control"
                                                    value="{{ $neg->harga_final }}">
                                            </div>
                                        </div>

                                        {{-- tanggal --}}
                                        <div class="col-md-6">
                                            <label>Tanggal Kirim</label>
                                            <input type="date" name="tanggal_kirim"
                                                class="form-control"
                                                value="{{ $neg->tanggal_kirim?->format('Y-m-d') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Tanggal Terima</label>
                                            <input type="date" name="tanggal_terima"
                                                class="form-control"
                                                value="{{ $neg->tanggal_terima?->format('Y-m-d') }}">
                                        </div>

                                        {{-- note --}}
                                        <div class="col-12">
                                            <label>Note</label>
                                            <textarea name="notes" class="form-control">{{ $neg->notes }}</textarea>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                        <button class="btn btn-sm btn-action-create">Simpan</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    {{-- IF EMPTY --}}
                    <tr>
                        <td>{{ $row }}</td>
                        <td colspan="7" class="text-center text-muted">Belum ada Negotiation</td>
                        <td>
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal" data-bs-target="#modalCreateNeg">
                                Create
                            </button>
                        </td>
                    </tr>

                    {{-- modal create --}}
                    <div class="modal fade" id="modalCreateNeg" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">

                                <form method="POST" action="{{ route('negotiation.store', $procurement->procurement_id) }}">
                                    @csrf
                                    <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                    <div class="modal-header">
                                        <h5>Create Negotiation</h5>
                                    </div>
                                    <div class="modal-body row g-3">

                                        {{-- vendor --}}
                                        <div class="col-md-6">
                                            <label>Vendor *</label>
                                            <select name="vendor_id" class="form-control" required>
                                                <option value="">-- Pilih Vendor --</option>
                                                @foreach($vendors as $v)
                                                <option value="{{ $v->id_vendor }}">{{ $v->name_vendor }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- HPS --}}
                                        <div class="col-md-6">
                                            <label>HPS</label>
                                            <div class="input-group">
                                                <select name="currency_hps" class="form-select">
                                                    @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                    <option value="{{ $c }}">{{ $c }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="hps" class="form-control">
                                            </div>
                                        </div>

                                        {{-- BUDGET --}}
                                        <div class="col-md-6">
                                            <label>Budget</label>
                                            <div class="input-group">
                                                <select name="currency_budget" class="form-select">
                                                    @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                    <option value="{{ $c }}">{{ $c }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="budget" class="form-control">
                                            </div>
                                        </div>

                                        {{-- HARGA FINAL --}}
                                        <div class="col-md-6">
                                            <label>Harga Final</label>
                                            <div class="input-group">
                                                <select name="currency_harga_final" class="form-select">
                                                    @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                    <option value="{{ $c }}">{{ $c }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" name="harga_final" class="form-control">
                                            </div>
                                        </div>

                                        {{-- tanggal --}}
                                        <div class="col-md-6">
                                            <label>Tanggal Kirim</label>
                                            <input type="date" name="tanggal_kirim" class="form-control">
                                        </div>

                                        <div class="col-md-6">
                                            <label>Tanggal Terima</label>
                                            <input type="date" name="tanggal_terima" class="form-control">
                                        </div>

                                        <div class="col-12">
                                            <label>Note</label>
                                            <textarea name="notes" class="form-control"></textarea>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                        <button class="btn btn-sm btn-action-create">Simpan</button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>

                </tbody>
            </table>
        </div>
    </div>

    @endif


    <h5 class="section-title">Pengiriman Material</h5>
    <div class="dashboard-table-wrapper">
        <div class="table-responsive">

            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding:14px 8px;text-align:center">No</th>
                        <th style="padding:14px 8px;text-align:center">Incoterms</th>
                        <th style="padding:14px 8px;text-align:center">ETD</th>
                        <th style="padding:14px 8px;text-align:center">ETA SBY Port</th>
                        <th style="padding:14px 8px;text-align:center">ETA PAL</th>
                        <th style="padding:14px 8px;text-align:center">Remark</th>
                        <th style="padding:14px 8px;text-align:center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php $row = 1; @endphp

                    {{-- LISTING --}}
                    @forelse($materialDeliveries as $delivery)
                    <tr>
                        <td style="text-align:center">{{ $row++ }}</td>
                        <td style="text-align:center">{{ $delivery->incoterms ?? '-' }}</td>
                        <td style="text-align:center">{{ $delivery->etd?->format('d/m/Y') ?? '-' }}</td>
                        <td style="text-align:center">{{ $delivery->eta_sby_port?->format('d/m/Y') ?? '-' }}</td>
                        <td style="text-align:center">{{ $delivery->eta_pal?->format('d/m/Y') ?? '-' }}</td>
                        <td style="text-align:center">{{ Str::limit($delivery->remark,30) ?? '-' }}</td>
                        <td style="text-align:center">
                            <button class="btn btn-sm btn-action-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditDel{{ $delivery->delivery_id }}">
                                Edit
                            </button>
                        </td>
                    </tr>

                    {{-- MODAL EDIT --}}
                    <div class="modal fade" id="modalEditDel{{ $delivery->delivery_id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('material-delivery.update', $delivery->delivery_id) }}">
                                    @csrf

                                    <div class="modal-header">
                                        <h5>Edit Pengiriman Material</h5>
                                    </div>

                                    <div class="modal-body row g-3">
                                        <div class="col-md-6">
                                            <label>Incoterms</label>
                                            <input type="text" name="incoterms" class="form-control"
                                                value="{{ $delivery->incoterms }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label>ETD</label>
                                            <input type="date" name="etd" class="form-control"
                                                value="{{ $delivery->etd?->format('Y-m-d') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label>ETA SBY Port</label>
                                            <input type="date" name="eta_sby_port" class="form-control"
                                                value="{{ $delivery->eta_sby_port?->format('Y-m-d') }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label>ETA PAL</label>
                                            <input type="date" name="eta_pal" class="form-control"
                                                value="{{ $delivery->eta_pal?->format('Y-m-d') }}">
                                        </div>

                                        <div class="col-12">
                                            <label>Remark</label>
                                            <textarea name="remark" class="form-control">{{ $delivery->remark }}</textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                        <button class="btn btn-sm btn-action-create">Simpan</button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse


                    {{-- ROW CREATE --}}
                    <tr>
                        <td>{{ $row }}</td>
                        <td colspan="5" style="text-align:center;" class="text-muted">
                            @if($row == 1)
                            Belum ada pengiriman material
                            @endif
                        </td>
                        <td style="text-align:center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateDelivery">
                                Create
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>


            {{-- MODAL CREATE --}}
            <div class="modal fade" id="modalCreateDelivery" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('material-delivery.store', $procurement->project_id) }}">
                            @csrf

                            <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                            <div class="modal-header">
                                <h5>Create Pengiriman Material</h5>
                            </div>

                            <div class="modal-body row g-3">
                                <div class="col-md-6">
                                    <label>Incoterms</label>
                                    <input type="text" name="incoterms" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label>ETD</label>
                                    <input type="date" name="etd" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label>ETA SBY Port</label>
                                    <input type="date" name="eta_sby_port" class="form-control">
                                </div>

                                <div class="col-md-6">
                                    <label>ETA PAL</label>
                                    <input type="date" name="eta_pal" class="form-control">
                                </div>

                                <div class="col-md-12">
                                    <label>Remark</label>
                                    <textarea name="remark" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                <button class="btn btn-sm btn-action-create">Simpan</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endif

</div>
</div>
@endsection

<script>
    function selectCurrencyEdit(cur, id) {
        document.getElementById('currencyEdit' + id).value = cur;
        document.getElementById('dropdownCurrency' + id).innerText = cur;
    }

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('currency-input')) {
            let value = e.target.value.replace(/\D/g, ''); // buang semua non-digit
            if (!value) {
                e.target.value = '';
                return;
            }
            e.target.value = new Intl.NumberFormat('id-ID').format(value);
        }
    });



    function selectCurrency(cur) {
        document.getElementById('currencyInput').value = cur;
        document.getElementById('dropdownCurrency').innerText = cur;

        // set placeholder / prefix
        const input = document.querySelector('input[name="nilai_harga"]');
        if (cur === 'IDR') input.placeholder = "Rp 0";
        if (cur === 'USD') input.placeholder = "$ 0";
        if (cur === 'EUR') input.placeholder = "€ 0";
    }
</script>