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
    }

    .timeline-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #c7e5c6;
        color: green;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 20px;
        margin: auto;
    }

    .timeline-step.active .timeline-icon {
        background: #ECAD02;
        color: white;
    }

    .timeline-step.completed .timeline-icon {
        background: #28AC00;
        color: white;
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

    {{-- Timeline --}}
    <div class="timeline-container">
        @forelse($checkpoints as $index => $checkpoint)
        <div class="timeline-step {{ $currentStageIndex !== null && $index < $currentStageIndex ? 'completed' : ($currentStageIndex !== null && $index == $currentStageIndex ? 'active' : '') }}">
            <div class="timeline-icon">
                <i class="bi bi-check-circle"></i>
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
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Nama Item</th>
                        <th width="25%">Spesifikasi</th>
                        <th width="10%">Jumlah</th>
                        <th width="15%">Harga Satuan</th>
                        <th width="15%">Total Harga</th>
                        <th width="5%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($request->items as $index => $item)
                    @php
                        $itemTotal = $item->unit_price * $item->amount;
                        $grandTotal += $itemTotal;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $item->item_name }}</strong></td>
                        <td>{{ $item->item_description ?? $item->specification ?? '-' }}</td>
                        <td>{{ $item->amount }} {{ $item->unit }}</td>
                        <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td><strong>Rp {{ number_format($itemTotal, 0, ',', '.') }}</strong></td>
                        <td>
                            <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="table-active">
                        <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                        <td colspan="2"><strong>Rp {{ number_format($grandTotal, 0, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    @empty
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada item untuk procurement ini.
        </div>
    @endforelse

    {{-- REVIEW DOCUMENT --}}
    <h5 class="section-title mt-4">Review Document</h5>
    <div class="doc-card">
        {!! $procurement->review_notes ?? '<em class="text-muted">Belum ada review</em>' !!}
    </div>

    {{-- SIGN DOCUMENT --}}
    <h5 class="section-title">Sign Document</h5>
    <div class="doc-card">
        {!! $procurement->sign_notes ?? '<em class="text-muted">Belum ada tanda tangan</em>' !!}
    </div>

@if(session('success'))
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div class="toast show" role="alert">
        <div class="toast-header bg-success text-white">
            <strong class="me-auto">Success</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
        <div class="toast-body">
            {{ session('success') }}
        </div>
    </div>
</div>
@endif

@endsection