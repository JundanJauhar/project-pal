@extends('ums.layouts.app')

@section('title', 'Session Monitoring')

@section('content')

<style>
.session-wrapper {
    padding: 26px 30px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    margin-top: 20px;
}

.session-title {
    font-size: 22px;
    font-weight: 800;
}

.session-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 18px;
}

.session-table {
    width: 100%;
    border-collapse: collapse;
}

.session-table th {
    font-size: 13px;
    font-weight: 700;
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

.session-table td {
    font-size: 13px;
    padding: 14px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    text-align: center;
}

.user-name { font-weight: 700; }
.email-muted { font-size: 11px; color: #666; }

.ua-text {
    font-size: 11px;
    color: #555;
    word-break: break-word;
}

.logout-btn {
    background: #c62828;
    border: none;
    color: #fff;
    width: 28px;
    height: 28px;
    border-radius: 6px;
}
</style>

<div class="session-wrapper">

    <div class="session-title">Session Monitoring</div>
    <div class="session-subtitle">
        Monitoring dan kontrol session aktif pengguna (keamanan sistem)
    </div>

    <table class="session-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Session ID</th>
                <th>IP</th>
                <th>Device</th>
                <th>Last Activity</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        @forelse($sessions as $s)
            <tr>
                <td>
                    <div class="user-name">{{ $s->name ?? 'Guest' }}</div>
                    <div class="email-muted">{{ $s->email }}</div>
                </td>

                <td>{{ Str::limit($s->id, 20) }}</td>
                <td>{{ $s->ip_address }}</td>

                <td>
                    <div class="ua-text">{{ $s->user_agent }}</div>
                </td>

                <td>
                    {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->format('Y-m-d H:i:s') }}
                    <div class="email-muted">
                        {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}
                    </div>
                </td>

                <td>
                    @if($s->user_id && $s->user_id !== auth()->id())
                        <form method="POST"
                              action="{{ route('ums.sessions.forceLogoutSession', $s->id) }}"
                              onsubmit="return confirm('Hentikan session ini?')">
                            @csrf
                            <button class="logout-btn" title="Force Logout Session">
                                <i class="bi bi-box-arrow-right"></i>
                            </button>
                        </form>
                    @else
                        -
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-muted py-4">
                    Tidak ada session aktif.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-3 d-flex">
        <div class="ms-auto">
            {{ $sessions->links() }}
        </div>
    </div>

</div>

@endsection
