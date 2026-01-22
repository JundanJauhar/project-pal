@extends('layouts.app')

@section('title', 'Notifikasi')

@push('styles')
<style>
    .notif-container {
        max-width: 900px;
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
        border-left: 5px solid #ccc; /* Default */
    }
    .notif-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #ccc;
    }
    .notif-card.unread {
        background-color: #f8faff; /* Very light blue */
        border-color: #0d6efd;
    }
    .notif-icon-box {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        font-size: 24px;
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
        background: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-action:hover {
        background: #5a6268;
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
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex align-items-center">
            <button class="btn btn-outline-secondary btn-sm me-3" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            <h2 class="fw-bold mb-0">Notifikasi</h2>
        </div>
        <div>
            <form action="{{ route('notifications.read-all') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm rounded-pill">
                    <i class="bi bi-check-all me-1"></i> Tandai Semua Dibaca
                </button>
            </form>
        </div>
    </div>

    @if(isset($notifications) && $notifications->count() > 0)
        @foreach($notifications as $notif)
            @php
                // Use helpers or attributes if available, else default
                // Assuming controller transforms these objects to have standard properties
                $isRead = $notif->is_read ?? false;
                $isStored = $notif->is_stored ?? true; // Default true if not specified
                $cardClass = (!$isRead) ? 'notif-card unread' : 'notif-card';
                
                // Colors and Icons from controller or defaults
                $color = $notif->color ?? '#6c757d';
                $icon = $notif->icon ?? 'bi-bell-fill';
                $link = $notif->link ?? '#';
                $actionLabel = $notif->action_label ?? 'Lihat';
                
                // If it's a stored notification (standard DB), click marks as read
                $onclick = ($isStored && !$isRead) ? "markAsRead('{$notif->id}', this)" : "";
            @endphp

            <div class="{{ $cardClass }}" 
                 style="border-left-color: {{ $color }}; cursor: pointer;"
                 onclick="handleCardClick('{{ $link }}', '{{ $notif->id }}', {{ ($isStored && !$isRead) ? 'true' : 'false' }}, this)">
                
                <div class="d-flex align-items-center flex-grow-1">
                    <div class="notif-icon-box" style="background: {{ $color }}15;">
                        <i class="bi {{ $icon }}" style="color: {{ $color }};"></i>
                    </div>
                    <div class="notif-content">
                        <div class="notif-title">
                            {{ $notif->title }} 
                            @if(!$isRead) <span class="badge bg-danger rounded-pill ms-2" style="font-size: 10px;">Baru</span> @endif
                        </div>
                        <div class="notif-message">{{ $notif->message }}</div>
                        <div class="notif-time">
                            <i class="bi bi-clock"></i> 
                            @if(isset($notif->date))
                                {{ \Carbon\Carbon::parse($notif->date)->diffForHumans() }}
                            @else
                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                </div>
                <div style="flex-shrink: 0;">
                    {{-- Stop propagation if clicking button directly --}}
                    @if($link && $link != '#')
                    <a href="javascript:void(0);" onclick="event.stopPropagation(); handleCardClick('{{ $link }}', '{{ $notif->id }}', {{ ($isStored && !$isRead) ? 'true' : 'false' }}, this.closest('.notif-card'));" class="btn-action" style="background: {{ $color }};">
                        {{ $actionLabel }} <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    @endif
                </div>
            </div>
        @endforeach

        @if(method_exists($notifications, 'links'))
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="bi bi-bell-slash empty-icon"></i>
            <h4>Tidak ada notifikasi baru</h4>
            <p>Anda tidak memiliki pesan atau tugas yang perlu ditindaklanjuti saat ini.</p>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function handleCardClick(url, id, shouldMarkRead, element) {
        // Prepare navigation function
        const navigate = () => {
            if (url && url !== '#' && url !== '') {
                window.location.href = url;
            }
        };

        // If we need to mark as read, do it first (async), then navigate
        if (shouldMarkRead && id) {
            markAsRead(id, element, navigate);
        } else {
            // Just navigate immediately if no need to mark read (or already read)
            navigate();
        }
    }

    function markAsRead(id, element, callback) {
        if(!id) {
            if(callback) callback();
            return;
        }
        
        // Virtual ID check
        if(id.toString().startsWith('task_')) {
            if(callback) callback();
            return;
        }

        // Add visual feedback immediately (optimistic UI)
        if(element) {
            element.classList.remove('unread');
            const badge = element.querySelector('.badge');
            if(badge) badge.remove();
        }

        fetch(`{{ url('/notifications') }}/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Success handled
        })
        .catch(err => console.error(err))
        .finally(() => {
            if(callback) callback();
        });
    }
</script>
@endpush
@endsection
