@extends('ums.layouts.app')

@section('title', 'Security Audit Logs')

@section('content')

<style>
.audit-wrapper {
    padding: 26px 30px;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    margin-top: 20px;
    background: #fff;
}

.audit-title {
    font-size: 22px;
    font-weight: 800;
    margin-bottom: 4px;
}

.audit-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 20px;
}

.audit-table {
    width: 100%;
    border-collapse: collapse;
}

.audit-table th {
    font-size: 13px;
    font-weight: 700;
    padding: 12px 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

.audit-table td {
    font-size: 13px;
    padding: 14px 10px;
    vertical-align: middle;
    text-align: center;
}

.audit-row.success { border-left: 4px solid #2ecc71; }
.audit-row.failed  { border-left: 4px solid #e74c3c; }

.user-name { font-weight: 700; }
.email-muted { font-size: 11px; color: #666; }

.badge-success {
    background: #2ecc71;
    color: #fff;
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 600;
}

.badge-failed {
    background: #e74c3c;
    color: #fff;
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 12px;
    font-weight: 600;
}

.ua-text {
    font-size: 11px;
    color: #555;
    word-break: break-word;
}

.detail-btn {
    width: 28px;
    height: 28px;
    background: #34495e;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
</style>

<div class="audit-wrapper">

    <div class="audit-title">Security Audit Logs</div>
    <div class="audit-subtitle">
        Monitoring aktivitas autentikasi pengguna (login & login gagal)
    </div>

    <table class="audit-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Action</th>
                <th>Timestamp</th>
                <th>IP</th>
                <th>Device</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
        @forelse($logs as $log)
            @php $failed = $log->action === 'login_failed'; @endphp
            <tr class="audit-row {{ $failed ? 'failed' : 'success' }}">
                <td>
                    <div class="user-name">
                        {{ optional($log->actor)->name ?? 'User ID: '.$log->actor_user_id }}
                    </div>
                    <div class="email-muted">
                        {{ optional($log->actor)->email }}
                    </div>
                </td>

                <td>
                    @if($failed)
                        <span class="badge-failed">Login Failed</span>
                    @else
                        <span class="badge-success">Login Success</span>
                    @endif
                </td>

                <td>
                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                    <div class="email-muted">
                        {{ $log->created_at->diffForHumans() }}
                    </div>
                </td>

                <td>{{ $log->ip ?? '-' }}</td>

                <td>
                    <div class="ua-text">{{ $log->user_agent ?? '-' }}</div>
                </td>

                <td>
                    <a href="{{ route('ums.audit_logs.show', $log->id) }}" class="detail-btn">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-muted py-4">
                    Tidak ada audit login ditemukan.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-3 d-flex">
        <div class="ms-auto">
            {{ $logs->links() }}
        </div>
    </div>

</div>

@endsection
