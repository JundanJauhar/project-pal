@extends('ums.layouts.app')

@section('title', 'Activity Logs')

@section('content')

<style>
.activity-wrapper {
    padding: 26px 30px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    margin-top: 20px;
}

.activity-title {
    font-size: 22px;
    font-weight: 800;
}

.activity-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 18px;
}

/* FILTER */
.filter-bar {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}

.filter-bar input,
.filter-bar select {
    height: 32px;
    border-radius: 6px;
    border: 1px solid #ccc;
    padding: 0 10px;
    font-size: 12px;
}

/* TABLE */
.activity-table {
    width: 100%;
    border-collapse: collapse;
}

.activity-table th {
    font-size: 13px;
    font-weight: 700;
    padding: 12px 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

.activity-table td {
    font-size: 13px;
    padding: 14px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    text-align: center;
}

.user-name { font-weight: 700; }
.email-muted { font-size: 11px; color: #666; }

.action-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 12px;
    background: #eef1f5;
    font-weight: 600;
    color: #444;
}

.ua-text {
    font-size: 11px;
    color: #555;
    word-break: break-word;
}

.detail-btn {
    width: 26px;
    height: 26px;
    background: #34495e;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}
</style>

<div class="activity-wrapper">

    <div class="activity-title">Activity Logs</div>
    <div class="activity-subtitle">
        Catatan aktivitas operasional pengguna di dalam sistem
    </div>

    {{-- FILTER BAR --}}
    <form method="GET" class="filter-bar">
        <input type="text" name="module" value="{{ request('module') }}" placeholder="Module">
        <input type="text" name="action" value="{{ request('action') }}" placeholder="Action">
        <input type="date" name="date" value="{{ request('date') }}">
        <button class="btn btn-sm btn-secondary">Filter</button>
        <a href="{{ route('ums.activity_logs.index') }}" class="btn btn-sm btn-light">Reset</a>
    </form>

    <table class="activity-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Module</th>
                <th>Action</th>
                <th>Timestamp</th>
                <th>IP</th>
                <th>Device</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>
                    <div class="user-name">
                        {{ optional($log->actor)->name ?? 'Unknown User' }}
                    </div>
                    <div class="email-muted">
                        {{ optional($log->actor)->email }}
                    </div>
                </td>

                <td>{{ $log->module }}</td>

                <td>
                    <span class="action-badge">
                        {{ ucfirst(str_replace('_',' ', $log->action)) }}
                    </span>
                </td>

                <td>
                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                    <div class="email-muted">
                        {{ $log->created_at->diffForHumans() }}
                    </div>
                </td>

                <td>{{ $log->ip }}</td>

                <td>
                    <div class="ua-text">{{ $log->user_agent }}</div>
                </td>

                <td>
                    <a href="{{ route('ums.activity_logs.show', $log->id) }}" class="detail-btn">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-muted py-4">
                    Tidak ada activity logs ditemukan.
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
