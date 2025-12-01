@extends('ums.layouts.app')

@section('title', 'Audit Log Detail')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
    body { font-family: 'Inter', sans-serif; background: #fff; }
    .card-custom { border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
    .meta-label { font-weight:700; }
    pre.details-pre { white-space: pre-wrap; word-break: break-word; }
</style>

<h3 class="fw-bold mb-4">Detail Audit Log</h3>

<div class="card card-custom shadow-sm border-0">
    <div class="card-body p-4">

        <p><strong>User:</strong>
            {{ optional($log->actor)->name ?? 'User ID: '.$log->actor_user_id }} <br>
            <small class="text-muted">{{ optional($log->actor)->email ?? '' }}</small>
        </p>

        <p><strong>Aksi:</strong> <span class="badge bg-secondary">{{ $log->action }}</span></p>

        <p><strong>Target:</strong> {{ $log->target_table ?? '-' }} (ID: {{ $log->target_id ?? '-' }})</p>

        <p><strong>Waktu:</strong>
            {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}
            <br>
            <small class="text-muted">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</small>
        </p>

        <p><strong>IP:</strong> {{ $log->ip ?? ($log->details['ip'] ?? ($log->details['ip_address'] ?? '-')) }}</p>
        <p><strong>Device / UA:</strong> {{ $log->user_agent ?? ($log->details['ua'] ?? ($log->details['user_agent'] ?? '-')) }}</p>

        <hr>

        <h5>Detail (raw):</h5>
        <pre class="bg-light p-3 rounded details-pre">
{{ json_encode($log->details ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}
        </pre>

        <a href="{{ route('ums.audit_logs.index') }}" class="btn btn-secondary mt-3">Kembali</a>

    </div>
</div>

@endsection
