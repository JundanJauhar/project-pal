@extends('ums.layouts.app')

@section('title', 'Audit Logs')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    body { background:#ffffff !important; font-family:'Inter',sans-serif; }

    .audit-wrapper {
        background:#fff;
        padding:40px;
        border-radius:16px;
        box-shadow:0 4px 14px rgba(0,0,0,0.06);
        margin-top:20px;
    }

    .audit-title {
        font-size:32px;
        font-weight:800;
        color:#000;
        margin-bottom:25px;
    }

    table.audit-table {
        width:100%;
        border-collapse:collapse;
        table-layout:fixed;
    }

    thead th {
        font-size:15px;
        font-weight:700;
        padding-bottom:14px;
        color:#000;
        border-bottom:2px solid #000;
    }

    tbody tr {
        height:72px;
        background:#f8f8f8;
        border-bottom:8px solid #ffffff;
    }

    td {
        padding:10px 8px;
        font-size:14px;
        vertical-align:middle;
    }

    /* COLUMN WIDTHS */
    th:nth-child(1), td:nth-child(1) { width:23%; }
    th:nth-child(2), td:nth-child(2) { width:14%; }
    th:nth-child(3), td:nth-child(3) { width:20%; }
    th:nth-child(4), td:nth-child(4) { width:12%; }
    th:nth-child(5), td:nth-child(5) { width:23%; }
    th:nth-child(6), td:nth-child(6) { width:8%; text-align:right; }

    /* USER */
    .user-name {
        font-weight:700;
        font-size:15px;
    }
    .email-muted {
        font-size:12px;
        color:#777;
        margin-top:-3px;
    }

    /* ACTION BADGES */
    .badge-login {
        background:#42c96c;
        padding:6px 14px;
        border-radius:50px;
        color:#fff;
        font-size:12px;
        font-weight:600;
    }
    .badge-failed {
        background:#ff5c5c;
        padding:6px 14px;
        border-radius:50px;
        color:#fff;
        font-size:12px;
        font-weight:600;
    }

    .timestamp { font-weight:600; }
    .timestamp-ago { font-size:12px; color:#777; }

    .ip-text { font-weight:600; }
    .ua-text { font-size:12px; color:#555; }

</style>

<div class="audit-wrapper">

    <div class="audit-title">Audit Logs</div>

    <div class="table-responsive">
        <table class="audit-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Aksi</th>
                    <th>Timestamp</th>
                    <th>IP</th>
                    <th>Device</th>
                    <th class="text-end">Detail</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($logs as $l)

                    {{-- Ignore all non-login logs --}}
                    @php
                        $action = strtolower($l->action);
                        $isLoginLog = in_array($action, ['login', 'login_failed']);
                    @endphp

                    @if(!$isLoginLog)
                        @continue
                    @endif

                    <tr>
                        <!-- Nama -->
                        <td>
                            <div class="user-name">{{ optional($l->actor)->name ?? 'User ID: '.$l->actor_user_id }}</div>
                            <div class="email-muted">{{ optional($l->actor)->email }}</div>
                        </td>

                        <!-- Aksi -->
                        <td>
                            @if($action === 'login')
                                <span class="badge-login">Login Succeeded</span>
                            @elseif($action === 'login_failed')
                                <span class="badge-failed">Login Failed</span>
                            @endif
                        </td>

                        <!-- Timestamp -->
                        <td>
                            <div class="timestamp">{{ $l->created_at->format('Y-m-d H:i:s') }}</div>
                            <div class="timestamp-ago">{{ $l->created_at->diffForHumans() }}</div>
                        </td>

                        <!-- IP -->
                        <td>
                            <div class="ip-text">{{ $l->ip ?? '-' }}</div>
                        </td>

                        <!-- Device -->
                        <td>
                            <div class="ua-text">{{ $l->user_agent ?? '-' }}</div>
                        </td>

                        <!-- Detail button -->
                        <td class="text-end">
                            <a href="{{ route('ums.audit_logs.show', $l->id) }}"
                               class="btn btn-primary btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            No login logs found.
                        </td>
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
