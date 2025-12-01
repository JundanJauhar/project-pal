@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    body { background: #ffffff !important; font-family: 'Inter', sans-serif; }

    .audit-wrapper {
        background: #ffffffff;
        padding: 40px;
        border-radius: 18px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.08);
        margin-top: 20px;
    }

    .audit-title {
        font-size: 32px;
        font-weight: 800;
        color: #000;
        margin-bottom: 25px;
    }

    .audit-table { width:100%; border-collapse:separate !important; border-spacing:0 !important; font-size:15px; }
    .audit-table thead th { border-bottom:2px solid #000 !important; padding-bottom:14px; font-weight:700; color:#000; }
    .audit-table tbody tr { height:80px; border-bottom:1px solid #d4d4d4; }

    .user-name { font-weight:700; font-size:15px; margin-bottom:3px; }
    .email-muted { font-size:12px; color:#777; margin-top:-3px; }

    .badge-login { background:#56d364; padding:6px 16px; border-radius:25px; display:inline-block; font-weight:600; font-size:12px; color:#fff; }
    .badge-failed { background:#ff5c5c; padding:6px 16px; border-radius:25px; display:inline-block; font-weight:600; font-size:12px; color:#fff; }

    .target-table { font-weight:600; font-size:14px; }
    .target-id { font-size:12px; color:#777; }

    .timestamp { font-weight:600; font-size:14px; }
    .timestamp-ago { font-size:12px; color:#777; }

    .ip-text { font-size:14px; font-weight:600; color:#000; }
    .device-text { font-size:12px; color:#777; }

    /* pagination styling */
    .pagination { margin-top: 18px; justify-content: flex-end; }
</style>

<div class="audit-wrapper">

    <div class="audit-title">Audit Logs</div>

    <div class="table-responsive">
        <table class="table audit-table align-middle">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Aksi</th>
                    <th>Target</th>
                    <th>Timestamp</th>
                    <th>IP</th>
                    <th>Device</th>
                    <th class="text-end">Detail</th>
                </tr>
            </thead>

            <tbody>
                @forelse($logs as $l)
                <tr>
                    <!-- USER -->
                    <td>
                        <div class="user-name">{{ optional($l->actor)->name ?? 'User ID: '.$l->actor_user_id }}</div>
                        <div class="email-muted">{{ optional($l->actor)->email ?? '' }}</div>
                    </td>

                    <!-- ACTION BADGE -->
                    <td>
                        @php
                            $action = strtolower($l->action ?? '');
                        @endphp

                        @if($action === 'login')
                            <span class="badge-login">Login</span>
                        @elseif($action === 'login_failed' || str_contains($action, 'failed'))
                            <span class="badge-failed">{{ str_replace('_', ' ', ucfirst($action)) }}</span>
                        @else
                            <span class="badge-failed">{{ $l->action }}</span>
                        @endif
                    </td>

                    <!-- TARGET -->
                    <td>
                        <div class="target-table">{{ $l->target_table ?? '-' }}</div>
                        @if($l->target_id)
                            <div class="target-id">ID : {{ $l->target_id }}</div>
                        @endif
                    </td>

                    <!-- TIMESTAMP -->
                    <td>
                        <div class="timestamp">{{ $l->created_at ? $l->created_at->format('Y-m-d H:i:s') : '-' }}</div>
                        <div class="timestamp-ago">{{ $l->created_at ? $l->created_at->diffForHumans() : '' }}</div>
                    </td>

                    <!-- IP -->
                    <td>
                        <div class="ip-text">{{ $l->ip ?? ($l->details['ip'] ?? ($l->details['ip_address'] ?? '-')) }}</div>
                    </td>

                    <!-- DEVICE -->
                    <td>
                        <div class="device-text">{{ $l->user_agent ?? ($l->details['ua'] ?? ($l->details['user_agent'] ?? '-')) }}</div>
                    </td>

                    <!-- DETAIL -->
                    <td class="text-end">
                        <a href="{{ route('ums.audit_logs.show', $l->id) }}" class="btn btn-sm btn-primary">
                           <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">No audit logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex">
        <div class="ms-auto">
            {{ $logs->links() }}
        </div>
    </div>

</div>

@endsection
