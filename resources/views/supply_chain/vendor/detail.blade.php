@extends('layouts.app')

@section('title', 'Detail Vendor - ' . $vendor->name_vendor)

@section('content')
<style>
    .detail-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .procurement-header {
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
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
        background: #ECAD02;
        color: white;
        font-weight: bold;
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

    ;
    }
    /* Posisikan logo ke tengah */
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

/* Tanda X ke kanan atas */
.close-btn {
    position: absolute;
    right: 20px;
    top: 20px;
    font-size: 32px;
    color: #DA3B3B;
    cursor: pointer;
    z-index: 10;
}

.close-btn:hover {
    opacity: 0.7;
}

.vendor-info-grid {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 12px;
    margin-top: 15px;
}

.vendor-info-label {
    font-weight: 600;
    color: #4a5568;
}

.vendor-info-value {
    color: #2d3748;
}


</style>

<div class="detail-container">
    <a href="javascript:history.back()" class="close-btn">
        <i class="bi bi-x-circle"></i>
    </a>

    <div class="header-logo-wrapper">
        <img src="{{ asset('images/logo-pal.png') }}" alt="Logo PAL" class="logo">
    </div>

    <div class="procurement-header">
        {{-- Header --}}
        <h4 class="mb-4 pb-3 border-bottom">
            <i class="bi bi-building text-primary"></i> Detail Vendor
        </h4>

        {{-- Vendor Information Grid --}}
        <div class="vendor-info-grid">
            <div class="vendor-info-label">ID Vendor:</div>
            <div class="vendor-info-value"><strong>{{ $vendor->id_vendor }}</strong></div>

            <div class="vendor-info-label">Nama Vendor:</div>
            <div class="vendor-info-value">{{ $vendor->name_vendor }}</div>

            <div class="vendor-info-label">Importir:</div>
            <div class="vendor-info-value">
                @if($vendor->is_importer)
                    <span class="badge bg-success"><i class="bi bi-globe"></i> Ya</span>
                @else
                    <span class="badge bg-secondary">Tidak</span>
                @endif
            </div>

            <div class="vendor-info-label">Terdaftar Sejak:</div>
            <div class="vendor-info-value">{{ $vendor->created_at->format('d F Y H:i') }}</div>

            <div class="vendor-info-label">Terakhir Diperbarui:</div>
            <div class="vendor-info-value">{{ $vendor->updated_at->format('d F Y H:i') }}</div>
        </div>

        {{-- Action Buttons --}}
        <div class="mt-4 pt-3 border-top">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

@endsection
