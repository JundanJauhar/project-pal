@extends('ums.layouts.app')

@section('title', 'Activity Logs')

@section('content')

<style>
    .wrapper {
        background: #fff;
        padding: 35px;
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    }
    .badge-create { background:#0d6efd; }
    .badge-update { background:#ffc107; color:#000; }
    .badge-delete { background:#dc3545; }
    .badge-force { background:#6f42c1; }
</style>

<div class="wrapper">
    <h3 class="fw-bold mb-4">Activity Logs</h3>

    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>User</th>
                <th>Module</th>
                <th>Action</th>
                <th>Timestamp</th>
                <th>IP</th>
                <th>Device</th>
                <th class="text-end">Detail</th>
            </tr>
        </thead>

        <tbody>
        @forelse($logs as $log)
            <tr>
                <td>
                    <strong>{{ optional($log->actor)->name ?? 'Unknown User' }}</strong><br>
                    <small class="text-muted">{{ optional($log->actor)->email }}</small>
                </td>

                <td class="text-capitalize">{{ $log->module }}</td>

                <td>
                    <span class="badge 
                        {{ $log->action == 'create' ? 'badge-create' : '' }}
                        {{ $log->action == 'update' ? 'badge-update' : '' }}
                        {{ $log->action == 'delete' ? 'badge-delete' : '' }}
                        {{ $log->action == 'force_logout' ? 'badge-force' : '' }}
                    ">
                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                    </span>
                </td>

                <td>
                    {{ $log->created_at->format('Y-m-d H:i:s') }}<br>
                    <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                </td>

                <td>{{ $log->ip }}</td>
                <td><small>{{ $log->user_agent }}</small></td>

                <td class="text-end">
                    <a href="{{ route('ums.activity_logs.show', $log->id) }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-muted">No activity logs found.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-end">
        {{ $logs->links() }}
    </div>
</div>

@endsection
