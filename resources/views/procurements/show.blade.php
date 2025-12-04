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

    .bi-x-circle {
        font-size: 24px;
        color: #dc3545;
        margin-bottom: 30px;
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

    .close-btn {
        position: absolute;
        right: 90px;
        top: 110px;
        font-size: 28px;
        color: #DA3B3B;
        cursor: pointer;
    }

    .close-btn:hover {
        opacity: 0.7;
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

    .badge-rendah {
        background-color: #28a745;
        color: white;
    }

    .badge-sedang {
        background-color: #ffc107;
        color: #333;
    }

    .badge-tinggi {
        background-color: #dc3545;
        color: white;
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
</style>

<div class="header-logo-wrapper">
    <img src="{{ asset('images/logo-pal.png') }}" alt="Logo PAL" class="logo">
</div>

<a href="javascript:history.back()" class="close-btn">
    <i class="bi bi-x-circle"></i>
</a>

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
    
    @php
        $grandTotal = 0;
    @endphp

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
                        @php
                            $itemTotal = $item->unit_price * $item->amount;
                            $grandTotal += $itemTotal;
                        @endphp
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



        <h5 class="section-title">Inquiry & Quotation</h5>
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
                                <td>{{ $row++ }}</td>
                                <td>{{ $iq->vendor->name_vendor ?? '-' }}</td>
                                <td>{{ $iq->tanggal_inquiry->format('d/m/Y') }}</td>
                                <td>{{ $iq->tanggal_quotation ? $iq->tanggal_quotation->format('d/m/Y') : '-' }}</td>
                                <td>{{ $iq->target_quotation ? $iq->target_quotation->format('d/m/Y') : '-' }}</td>
                                <td>{{ $iq->lead_time ?? '-' }}</td>
                                <td>
                                    @if($iq->nilai_harga)
                                        Rp {{ number_format($iq->nilai_harga, 0, ',', '.') }} {{ $iq->currency }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditIQ{{ $iq->inquiry_quotation_id }}">
                                        Edit
                                    </button>
                                </td>
                            </tr>

                            {{-- =============================
                                MODAL EDIT
                            ============================== --}}
                            <div class="modal fade" id="modalEditIQ{{ $iq->inquiry_quotation_id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Inquiry - {{ $iq->vendor->name_vendor }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <form method="POST" action="{{ route('inquiry-quotation.update', $iq->inquiry_quotation_id) }}">
                                            @csrf
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

                        @empty
                        @endforelse


                        {{-- =============================
                            2. BARIS KOSONG UNTUK CREATE IQ
                        ============================== --}}
                        <tr>
                            <td>{{ $row }}</td>
                            <td colspan="6" class="text-center text-muted">Belum ada Inquiry & Quotation</td>
                            <td>
                                <button class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCreateIQ">
                                    Create
                                </button>
                            </td>
                        </tr>


                        {{-- =============================
                            MODAL CREATE INQUIRY
                        ============================== --}}
                        <div class="modal fade" id="modalCreateIQ" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Create Inquiry & Quotation</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form method="POST" action="{{ route('inquiry-quotation.store', $procurement->project_id) }}">
                                        @csrf

                                        <div class="modal-body">

                                            <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                            {{-- Pilih Vendor --}}
                                            <div class="mb-3">
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
                                            <div class="mb-3">
                                                <label>Tanggal Inquiry *</label>
                                                <input type="date" name="tanggal_inquiry" class="form-control" required>
                                            </div>

                                            {{-- tanggal quotation --}}
                                            <div class="mb-3">
                                                <label>Tanggal Quotation</label>
                                                <input type="date" name="tanggal_quotation" class="form-control">
                                            </div>

                                            {{-- target quotation --}}
                                            <div class="mb-3">
                                                <label>Target Quotation</label>
                                                <input type="date" name="target_quotation" class="form-control">
                                            </div>

                                            {{-- lead time --}}
                                            <div class="mb-3">
                                                <label>Lead Time</label>
                                                <input type="text" name="lead_time" class="form-control" placeholder="ex: 7 hari kerja">
                                            </div>

                                            {{-- nilai harga --}}
                                            <div class="mb-3">
                                                <label>Nilai Harga</label>
                                                <input type="number" name="nilai_harga" class="form-control" placeholder="0">
                                            </div>

                                            {{-- currency --}}
                                            <div class="mb-3">
                                                <label>Mata Uang</label>
                                                <select name="currency" class="form-select">
                                                    <option value="IDR">IDR</option>
                                                    <option value="USD">USD</option>
                                                    <option value="EUR">EUR</option>
                                                    <option value="SGD">SGD</option>
                                                </select>
                                            </div>

                                            {{-- Notes --}}
                                            <div class="mb-3">
                                                <label>Catatan</label>
                                                <textarea name="notes" class="form-control" rows="3"></textarea>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Simpan</button>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>

                    </tbody>
                </table>
            </div>
        </div>



        <h5 class="section-title">Evatek</h5>
        <div class="dashboard-table-wrapper">
            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nama Item</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Revision</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Start</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Target</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Hasil Evatek</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Hasil</th>
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

                                {{-- Revision --}}
                                <td style="padding: 12px 8px; text-align: center; color: #000;">
                                    <span style="background: #e8f4f8; padding: 4px 12px; border-radius: 4px; font-weight: 600;">
                                        {{ $evatek->current_revision }}
                                    </span>
                                </td>

                                {{-- Tanggal Start --}}
                                <td style="padding: 12px 8px; text-align: center; color: #000;">
                                    {{ $evatek->start_date->format('d/m/Y') }}
                                </td>

                                {{-- Tanggal Target --}}
                                <td style="padding: 12px 8px; text-align: center; color: #000;">
                                    <span style="background: #fff3cd; padding: 4px 8px; border-radius: 4px;">
                                        {{ $evatek->target_date?->format('d/m/Y') ?? '-' }}
                                    </span>
                                </td>

                                {{-- Hasil Evatek --}}
                                <td style="padding: 12px 8px; text-align: center; color: #000;">
                                    @php
                                        $statusColors = [
                                            'on_progress' => ['bg' => '#fff3cd', 'text' => '#856404', 'label' => 'On Progress'],
                                            'approve' => ['bg' => '#d4edda', 'text' => '#155724', 'label' => 'Approved'],
                                            'not_approve' => ['bg' => '#f8d7da', 'text' => '#721c24', 'label' => 'Rejected'],
                                        ];
                                        $statusConfig = $statusColors[$evatek->status] ?? ['bg' => '#e2e3e5', 'text' => '#383d41', 'label' => ucfirst($evatek->status)];
                                    @endphp
                                    <span style="background: {{ $statusConfig['bg'] }}; color: {{ $statusConfig['text'] }}; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 12px;">
                                        {{ $statusConfig['label'] }}
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

                                {{-- Vendor --}}
                                <td style="padding: 12px 8px; text-align: center; color: #000;">
                                    {{ $evatek->vendor->name_vendor ?? '-' }}
                                </td>

                                {{-- Aksi --}}
                                <td style="padding: 12px 8px; text-align: center; color: #000;">
                                    <a href="{{ route('desain.review-evatek', $evatek->evatek_id) }}" 
                                    class="btn btn-sm btn-info" 
                                    style="background: #17a2b8; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 12px; font-weight: 600;">
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
                                        {{-- No --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            {{ $row }}
                                        </td>

                                        {{-- Nama Item --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            {{ $item->item_name }}
                                        </td>

                                        {{-- Revision --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            <span style="color: #999;">-</span>
                                        </td>

                                        {{-- Tanggal Start --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            <span style="color: #999;">-</span>
                                        </td>

                                        {{-- Tanggal Target --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            <span style="color: #999;">-</span>
                                        </td>

                                        {{-- Hasil Evatek --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            <span style="color: #999;">-</span>
                                        </td>

                                        {{-- Tanggal Hasil --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            <span style="color: #999;">-</span>
                                        </td>

                                        {{-- Vendor --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            {{ $request->vendor->name_vendor ?? '-' }}
                                        </td>

                                        {{-- Aksi --}}
                                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                                            <button type="button" 
                                                    class="btn btn-primary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEvatek{{ $row }}"
                                                    style="background: #007bff; color: white; padding: 6px 12px; border-radius: 4px; border: none; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                Create
                                            </button>

                                            <!-- Modal untuk input data evatek -->
                                            <div class="modal fade" id="modalEvatek{{ $row }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Input Evatek - {{ $item->item_name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary" style="background: #28a745; border: none;">Buat Evatek</button>
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
                            <th>Note</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $row = 1; @endphp

                        {{-- Tampilkan semua negotiation --}}
                        @foreach($negotiations as $neg)
                            <tr>
                                <td>{{ $row++ }}</td>
                                <td>{{ $neg->vendor->nama_vendor ?? '-' }}</td>
                                <td>{{ $neg->hps ? 'Rp '.number_format($neg->hps,0,',','.') : '-' }}</td>
                                <td>{{ $neg->budget ? 'Rp '.number_format($neg->budget,0,',','.') : '-' }}</td>
                                <td>{{ $neg->harga_final ? 'Rp '.number_format($neg->harga_final,0,',','.') : '-' }}</td>
                                <td>{{ $neg->tanggal_kirim ? $neg->tanggal_kirim->format('d/m/Y') : '-' }}</td>
                                <td>{{ $neg->tanggal_terima ? $neg->tanggal_terima->format('d/m/Y') : '-' }}</td>
                                <td>{{ $neg->notes ?? '-' }}</td>
                                <td>
                                    <button class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditNeg{{ $neg->negotiation_id }}">
                                        Edit
                                    </button>
                                </td>
                            </tr>

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="modalEditNeg{{ $neg->negotiation_id }}">
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
                                                <select name="vendor_id" class="form-control" required>
                                                    @foreach($vendors as $v)
                                                        <option value="{{ $v->id_vendor }}"
                                                            {{ $v->id_vendor == $neg->vendor_id ? 'selected' : '' }}>
                                                            {{ $v->nama_vendor }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <label class="mt-2">HPS</label>
                                                <input type="number" name="hps" class="form-control" value="{{ $neg->hps }}">

                                                <label class="mt-2">Budget</label>
                                                <input type="number" name="budget" class="form-control" value="{{ $neg->budget }}">

                                                <label class="mt-2">Harga Final</label>
                                                <input type="number" name="harga_final" class="form-control" value="{{ $neg->harga_final }}">

                                                <label class="mt-2">Tanggal Kirim</label>
                                                <input type="date" name="tanggal_kirim" class="form-control"
                                                    value="{{ $neg->tanggal_kirim ? $neg->tanggal_kirim->format('Y-m-d') : '' }}">

                                                <label class="mt-2">Tanggal Terima</label>
                                                <input type="date" name="tanggal_terima" class="form-control"
                                                    value="{{ $neg->tanggal_terima ? $neg->tanggal_terima->format('Y-m-d') : '' }}">

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
                        @endforeach

                        {{-- Tombol CREATE selalu muncul --}}
                        <tr>
                            <td colspan="9" class="text-center">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateNeg">
                                    Create Negotiation
                                </button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>


{{-- Modal Create --}}
<div class="modal fade" id="modalCreateNeg">
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
                    <select name="vendor_id" class="form-control" required>
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id_vendor }}">{{ $v->nama_vendor }}</option>
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


        <h5 class="section-title">Pengiriman Material</h5>
        <div class="dashboard-table-wrapper">
            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #000; border-bottom: 2px solid #C9C9C9; font-size: 14px; text-transform: uppercase;">No</th>
                            <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #000; border-bottom: 2px solid #C9C9C9; font-size: 14px; text-transform: uppercase;">Nama Item</th>
                            <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #000; border-bottom: 2px solid #C9C9C9; font-size: 14px; text-transform: uppercase;">Incoterms</th>
                            <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #000; border-bottom: 2px solid #C9C9C9; font-size: 14px; text-transform: uppercase;">ETD</th>
                            <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #000; border-bottom: 2px solid #C9C9C9; font-size: 14px; text-transform: uppercase;">ETA SBY Port</th>
                            <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #000; border-bottom: 2px solid #C9C9C9; font-size: 14px; text-transform: uppercase;">ETA PAL</th>
                            <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #000; border-bottom: 2px solid #C9C9C9; font-size: 14px; text-transform: uppercase;">Remark</th>
                            <th style="padding: 14px 8px; text-align: center; font-weight: 600; color: #000; border-bottom: 2px solid #C9C9C9; font-size: 14px; text-transform: uppercase;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $row = 0; @endphp

                        {{-- TAMPILKAN MATERIAL DELIVERIES YANG SUDAH ADA --}}
                        @forelse($materialDeliveries as $delivery)
                            @php $row++; @endphp
                            <tr>
                                <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">{{ $row }}</td>
                                <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">-</td>
                                <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">{{ $delivery->incoterms ?? '-' }}</td>
                                <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">{{ $delivery->etd ? $delivery->etd->format('d/m/Y') : '-' }}</td>
                                <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">{{ $delivery->eta_sby_port ? $delivery->eta_sby_port->format('d/m/Y') : '-' }}</td>
                                <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">{{ $delivery->eta_pal ? $delivery->eta_pal->format('d/m/Y') : '-' }}</td>
                                <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">{{ substr($delivery->remark ?? '-', 0, 30) }}</td>
                                <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">
                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditDel{{ $row }}" style="background: #ffc107; color: #000; padding: 6px 12px; border-radius: 4px; border: none; font-size: 12px; font-weight: 600; cursor: pointer;">Edit</button>

                                    <div class="modal fade" id="modalEditDel{{ $row }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Pengiriman Material</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="{{ route('material-delivery.update', $delivery->delivery_id) }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label" style="font-weight: 600; font-size: 14px;">Incoterms</label>
                                                            <input type="text" name="incoterms" class="form-control" value="{{ $delivery->incoterms ?? '' }}" placeholder="Contoh: FOB, CIF, DDP" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label" style="font-weight: 600; font-size: 14px;">ETD (Estimated Time Departure)</label>
                                                            <input type="date" name="etd" class="form-control" value="{{ $delivery->etd ? $delivery->etd->format('Y-m-d') : '' }}" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label" style="font-weight: 600; font-size: 14px;">ETA SBY Port</label>
                                                            <input type="date" name="eta_sby_port" class="form-control" value="{{ $delivery->eta_sby_port ? $delivery->eta_sby_port->format('Y-m-d') : '' }}" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label" style="font-weight: 600; font-size: 14px;">ETA PAL</label>
                                                            <input type="date" name="eta_pal" class="form-control" value="{{ $delivery->eta_pal ? $delivery->eta_pal->format('Y-m-d') : '' }}" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label" style="font-weight: 600; font-size: 14px;">Remark</label>
                                                            <textarea name="remark" class="form-control" rows="3" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">{{ $delivery->remark ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary" style="background: #28a745; border: none;">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse

                        {{-- TAMPILKAN FORM CREATE UNTUK ITEMS YANG BELUM PUNYA MATERIAL DELIVERY --}}
                        @forelse($procurement->requestProcurements as $request)
                            @foreach($request->items as $item)
                                @php
                                    // Material deliveries are no longer tracked per item_id
                                    $existingDel = null;
                                @endphp
                                @if(!$existingDel)
                                    @php $row++; @endphp
                                    <tr>
                                        <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">{{ $row }}</td>
                                        <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">{{ $item->item_name }}</td>
                                        <td colspan="5" style="padding: 14px 8px; text-align: center; color: #999; border-bottom: 1px solid #DFDFDF; font-size: 15px;">-</td>
                                        <td style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateDel{{ $row }}" style="background: #007bff; color: white; padding: 6px 12px; border-radius: 4px; border: none; font-size: 12px; font-weight: 600; cursor: pointer;">Create</button>

                                            <div class="modal fade" id="modalCreateDel{{ $row }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Input Pengiriman Material - {{ $item->item_name }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST" action="{{ route('material-delivery.store', $procurement->project_id) }}">
                                                            @csrf
                                                            <div class="modal-body">
                                                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">
                                                                <input type="hidden" name="item_id" value="{{ $item->item_id }}">
                                                                <div class="mb-3">
                                                                    <label class="form-label" style="font-weight: 600; font-size: 14px;">Incoterms</label>
                                                                    <input type="text" name="incoterms" class="form-control" placeholder="Contoh: FOB, CIF, DDP" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label" style="font-weight: 600; font-size: 14px;">ETD</label>
                                                                    <input type="date" name="etd" class="form-control" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label" style="font-weight: 600; font-size: 14px;">ETA SBY Port</label>
                                                                    <input type="date" name="eta_sby_port" class="form-control" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label" style="font-weight: 600; font-size: 14px;">ETA PAL</label>
                                                                    <input type="date" name="eta_pal" class="form-control" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label" style="font-weight: 600; font-size: 14px;">Remark</label>
                                                                    <textarea name="remark" class="form-control" rows="3" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary" style="background: #28a745; border: none;">Simpan</button>
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
                                <td colspan="8" style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">Tidak ada item untuk procurement ini.</td>
                            </tr>
                        @endforelse

                        @if($materialDeliveries->count() === 0 && $procurement->requestProcurements->sum(function($r) { return $r->items->count(); }) === 0)
                            <tr>
                                <td colspan="8" style="padding: 14px 8px; text-align: center; color: #000; border-bottom: 1px solid #DFDFDF; font-size: 15px;">Tidak ada item untuk dimasukkan ke Pengiriman Material.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection