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
    gap: 20px;               /* jarak antar titik */
    margin: 40px auto;
    padding: 0 20px;         /* biar garis tidak terlalu mepet */
    width: fit-content;      /* mengikuti lebar titik-titik saja */
}

.timeline-container::before {
    content: "";
    position: absolute;
    top: 24px;
    left: 0;
    right: 0;
    height: 3px;
    background: #c7e5c6;

    width: calc(100% - 45px);  /* 🔥 garis hanya sepanjang isi */
    margin: auto;
    z-index: 1;
}

.timeline-step {
    text-align: center;
    width: 90px;
    z-index: 2;

    /* 🔥 Samakan tinggi setiap step */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}

.timeline-step small {
    margin-top: 6px;

    /* 🔥 Fix agar tinggi teks sama */
    height: 38px;              /* sesuaikan */
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

    /* Tanda X ke kiri mengikuti margin konten */
    .close-btn {
        position: absolute;
        right: 90px;
        /* Geser ke kiri sesuai kebutuhan */
        top: 110px;
        /* Sejajarkan dengan konten berikutnya */
        font-size: 28px;
        color: #DA3B3B;
        cursor: pointer;
    }

    .close-btn:hover {
        opacity: 0.7;
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
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h4>Daftar Pengadaan</h4>
            <p><strong>Nama procurement:</strong> {{ $procurement->code_procurement }}</p>
            <p><strong>Vendor:</strong>
                {{ $procurement->requestProcurements->first()?->vendor?->name_vendor ?? '-' }}
            </p>
            <p><strong>Deskripsi:</strong> {{ $procurement->description }}</p>
        </div>

        <div class="text-end">
            <p><strong>Prioritas:</strong> {{ strtoupper($procurement->priority) }}</p>
            <p><strong>Tanggal Dibuat:</strong> {{ $procurement->created_at->format('d/m/Y') }}</p>
            <p><strong>Tanggal Target:</strong> {{ $procurement->end_date->format('d/m/Y') }}</p>
        </div>
    </div>


    {{-- Timeline --}}
    <div class="timeline-container">
        @forelse($checkpoints as $index => $checkpoint)
        <div
            class="timeline-step
                        {{ $currentStageIndex !== null && $index < $currentStageIndex ? 'completed' : ($currentStageIndex !== null && $index == $currentStageIndex ? 'active' : '') }}">
            <div class="timeline-icon">
                <i class="bi bi-check-circle"></i>
            </div>
            <small>{{ $checkpoint->point_name }}</small>
        </div>
        @empty
        <div class="text-center">Tidak ada checkpoint yang tersedia</div>
        @endforelse
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
            @foreach ($procurement->requestProcurements as $req)
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
        {!! $procurement->review_notes ?? 'Belum ada review' !!}
    </div>

    {{-- SIGN DOCUMENT --}}
    <h5 class="section-title">Sign Document</h5>
    <div class="doc-card">
        {!! $procurement->sign_notes ?? 'Belum ada tanda tangan' !!}
    </div>

    <!-- <div class="d-flex justify-content-center align-items-center gap-3 mt-4">
        <form action="{{ route('procurements.update', $procurement->procurement_id) }}" method="post">
            @csrf
            @method('put')
            <button type="submit" class="btn btn-sm btn-success btn-custom">
                <i class="bi bi-check-lg"></i> Accept
            </button>
        </form>
        <a href="{{ route('procurements.show', $procurement->procurement_id) }}"
            class="btn btn-sm btn-danger btn-custom">
            <i class="bi bi-x-lg"></i> Rejected
        </a>
    </div> -->

</div>



@endsection
