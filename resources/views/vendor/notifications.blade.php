@extends('layouts.app')

@section('title', 'Notifikasi - Vendor')

@push('styles')
<style>
    .notif-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .notif-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .notif-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #ccc;
    }
    .notif-icon-box {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #ffebee;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
    }
    .notif-icon-box i {
        font-size: 24px;
        color: #d32f2f;
    }
    .notif-content {
        flex: 1;
    }
    .notif-title {
        font-weight: 700;
        font-size: 16px;
        color: #333;
        margin-bottom: 4px;
    }
    .notif-message {
        font-size: 14px;
        color: #666;
        margin-bottom: 4px;
    }
    .notif-time {
        font-size: 12px;
        color: #999;
    }
    .btn-action {
        background: #d32f2f;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: background 0.2s;
    }
    .btn-action:hover {
        background: #b71c1c;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 50px;
        color: #888;
    }
    .empty-icon {
        font-size: 60px;
        color: #ddd;
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')
<div class="container notif-container mt-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('vendor.index') }}" class="btn btn-light rounded-circle me-3 border" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="fw-bold mb-0">Notifikasi</h2>
    </div>

    @if($notifications->count() > 0)
        @foreach($notifications as $notif)
            <div class="notif-card" style="border-left: 5px solid {{ $notif->color }};">
                <div class="d-flex align-items-center flex-grow-1">
                    <div class="notif-icon-box" style="background: {{ $notif->color }}15;">
                        <i class="bi {{ $notif->icon }}" style="color: {{ $notif->color }};"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">{{ $notif->title }}</div>
                        <div class="notif-message">{{ $notif->message }}</div>
                        <div class="notif-time">
                            <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($notif->date)->diffForHumans() }}
                        </div>
                    </div>
                </div>
                <div>
                    <a href="{{ $notif->link }}" class="btn-action" style="background: {{ $notif->color }};">
                        {{ $notif->action_label ?? 'Lihat Detail' }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        @endforeach
    @else
        <div class="empty-state">
            <i class="bi bi-bell-slash empty-icon"></i>
            <h4>Tidak ada notifikasi baru</h4>
            <p>Anda belum memiliki tugas atau pesan yang perlu ditindaklanjuti saat ini.</p>
        </div>
    @endif
</div>
@endsection
