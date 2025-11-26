@extends('layouts.app', ['hideNavbar' => true])

@section('title', 'Detail Approvel - ' . $procurement->name_procurement)

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
        width: 50px;
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
        width: 90px;
        z-index: 3;
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
   <div class="procurement-header">
    {{-- Header procurement --}}
    {{-- Detail Pengadaan --}}
    {{-- REVIEW DOCUMENT (FORM UTAMA) --}}
    <h5 class="section-title mt-4">Keputusan Approval Sekretaris Direksi</h5>

    {{-- PASTIKAN RUTE INI BENAR SESUAI NAMA METHOD DI CONTROLLER ANDA --}}
    <form action="{{ route('sekdir.approval.submit', $procurement->procurement_id) }}" method="POST">
        @csrf
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-body">

                {{-- Link Pengadaan (Ditambahkan REQUIRED) --}}
                <div class="mb-3">
                    <label for="procurement_link" class="form-label fw-semibold">Link Dokumen Pengadaan (Wajib Diisi)</label>
                    <input type="url"
                           class="form-control @error('procurement_link') is-invalid @enderror"
                           id="procurement_link"
                           name="procurement_link"
                           placeholder="https://contoh.com/dokumen-final"
                           value="{{ old('procurement_link', $procurement->procurement_link) }}"
                           required> @error('procurement_link')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Catatan --}}
                <div class="mb-3">
                    <label for="notes" class="form-label fw-semibold">Catatan</label>
                    <textarea class="form-control @error('notes') is-invalid @enderror"
                              id="notes"
                              name="notes"
                              rows="4"
                              placeholder="Tambahkan catatan persetujuan atau alasan penolakan...">{{ old('notes', $procurement->notes) }}</textarea>

                    @error('notes')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                {{-- Tombol Submit (Mengirimkan 'action' untuk Approve atau Reject) --}}
                <div class="d-flex justify-content-end pt-3 border-top">
                    <button type="submit" name="action" value="approve" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Ke Step Berikutnya
                    </button>
                </div>
            </div>
        </div>
    </form>
</div></div>



@endsection