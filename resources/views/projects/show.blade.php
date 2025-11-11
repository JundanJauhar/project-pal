@extends('layouts.app')

@section('title', 'Detail Project - ' . $project->name_project)

@section('content')
<style>
    .procurement-header {
        padding: 25px;
        background: white;
    }

    .timeline-container {
        display: flex;
        justify-content: space-between;
        margin: 40px 0;
        position: relative;
    }

    .timeline-container::before {
        content: "";
        position: absolute;
        top: 24px;
        left: 0;
        width: 100%;
        height: 3px;
        background: #c7e5c6;
        z-index: 1;
    }

    .timeline-step {
        text-align: center;
        position: relative;
        z-index: 2;
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
        background: #0EB04A;
        color: white;
        font-weight: bold;
    }

    .timeline-step.completed .timeline-icon {
        background: #198754;
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
</style>

<div class="procurement-header">

    {{-- Header Project --}}
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4>Daftar Pengadaan</h4>
            <p><strong>Nama Project:</strong> {{ $project->code_project }}</p>
            <p><strong>Vendor:</strong> {{ $project->contracts->first()->vendor->name_vendor ?? '-' }}</p>
            <p><strong>Deskripsi:</strong> {{ $project->description }}</p>
        </div>

        <div class="text-end">
            <img src="/img/logo-pal.png" style="height: 50px;">
            <p><strong>Prioritas:</strong> {{ strtoupper($project->priority) }}</p>
            <p><strong>Tanggal Dibuat:</strong> {{ $project->created_at->format('d/m/Y') }}</p>
            <p><strong>Tanggal Target:</strong> {{ $project->end_date->format('d/m/Y') }}</p>
        </div>
    </div>


    {{-- Timeline --}}
    @php
        $stages = ['Diajukan', 'Review SC', 'Persetujuan Sekretaris', 'Pemilihan Vendor', 'Pengecekan Legalitas', 'Pemesanan', 'Pembayaran', 'Selesai'];
    @endphp

    <div class="timeline-container">
        @foreach ($stages as $index => $stage)
            <div class="timeline-step
                {{ $index < $currentStageIndex ? 'completed' : ($index == $currentStageIndex ? 'active' : '') }}">
                <div class="timeline-icon">
                    <i class="bi bi-check-circle"></i>
                </div>
                <small>{{ $stage }}</small>
            </div>
        @endforeach
    </div>


    {{-- Detail Pengadaan --}}
    <h5 class="section-title">Detail Pengadaan</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nama Pengadaan</th>
                <th>Spesifikasi</th>
                <th>Jumlah</th>
                <th>Harga Estimasi</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($project->requestProcurements as $req)
                @foreach ($req->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->specification }}</td>
                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                    <td>Rp {{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->estimated_price * $item->quantity, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>


    {{-- REVIEW DOCUMENT --}}
    <h5 class="section-title mt-4">Review Document</h5>
    <div class="doc-card">
        {!! $project->review_notes ?? 'Belum ada review' !!}
    </div>

    {{-- SIGN DOCUMENT --}}
    <h5 class="section-title">Sign Document</h5>
    <div class="doc-card">
        {!! $project->sign_notes ?? 'Belum ada tanda tangan' !!}
    </div>

</div>



@endsection
