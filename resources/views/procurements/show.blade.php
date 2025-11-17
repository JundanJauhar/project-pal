@extends('layouts.app')

@section('title', 'Detail Pengadaan - ' . $procurement->name_procurement)

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
        flex: 1;
        min-width: 80px;
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
        margin-top: 20px;
        font-size: 16px;
    }

    .doc-card {
        background: #F7F7F7;
        border-radius: 10px;
        padding: 15px 18px;
        margin-bottom: 20px;
    }

    .progress-item {
        padding: 15px;
        border-left: 4px solid #ECAD02;
        background: #F9F9F9;
        margin-bottom: 10px;
        border-radius: 4px;
    }

    .progress-item.completed {
        border-left-color: #28AC00;
    }

    .progress-item.in_progress {
        border-left-color: #ECAD02;
    }

    .progress-item.blocked {
        border-left-color: #DC3545;
    }

    .badge-status {
        font-size: 11px;
        padding: 4px 8px;
    }

    .logo {
    height: 100px;    
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
</style>

<div class="header-logo-wrapper">
    <img src="{{ asset('images/logo-pal.png') }}" alt="Logo PAL" class="logo">
</div>
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <a href="javascript:history.back()" class="btn btn-sm btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Header Procurement --}}
    <div class="procurement-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h3>{{ $procurement->name_procurement }}</h3>
                <p class="mb-1"><strong>Kode:</strong> {{ $procurement->code_procurement }}</p>
                <p class="mb-1"><strong>Project:</strong> {{ $procurement->project->project_name ?? '-' }}</p>
                <p class="mb-1"><strong>Department:</strong> {{ $procurement->department->department_name ?? '-' }}</p>
                <p class="mb-1"><strong>Deskripsi:</strong> {{ $procurement->description }}</p>
            </div>

            <div class="text-end">
                <p class="mb-1">
                    <strong>Prioritas:</strong> 
                    <span class="badge badge-{{ strtolower($procurement->priority) }}">
                        {{ strtoupper($procurement->priority) }}
                    </span>
                </p>
                <p class="mb-1"><strong>Tanggal Mulai:</strong> {{ $procurement->start_date->format('d/m/Y') }}</p>
                <p class="mb-1"><strong>Tanggal Target:</strong> {{ $procurement->end_date->format('d/m/Y') }}</p>
            </div>
        </div>

        {{-- Items/Barang yang Diadakan --}}
        <h5 class="section-title">Daftar Barang/Jasa</h5>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Nama Item</th>
                    <th>Deskripsi</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Total</th>
                    <th>Vendor</th>
                </tr>
            </thead>
            <tbody>
                @forelse($procurement->items as $item)
                <tr>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->item_description }}</td>
                    <td>{{ $item->amount }} {{ $item->unit }}</td>
                    <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                    <td>{{ $item->requestProcurement->vendor->name_vendor ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted">Belum ada item</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

            {{-- Timeline Checkpoints --}}
            <h6 class="mb-3">Timeline Proses</h6>
            <div class="timeline-container">
                @foreach($checkpoints as $checkpoint)
                    @php
                        $progress = $procurement->procurementProgress->where('checkpoint_id', $checkpoint->point_id)->first();
                        $status = $progress ? $progress->status : 'not_started';
                    @endphp
                    <div class="timeline-step {{ $status === 'completed' ? 'completed' : ($status === 'in_progress' ? 'active' : '') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-{{ $status === 'completed' ? 'check-circle' : 'circle' }}"></i>
                        </div>
                        <small class="d-block mt-2">{{ $checkpoint->point_name }}</small>
                        @if($progress)
                            <small class="text-muted d-block">{{ ucfirst($status) }}</small>
                        @endif
                    </div>
                @endforeach
            </div>
            </div>
        </div>
    </div>

</div>

@endsection
