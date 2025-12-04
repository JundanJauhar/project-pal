@extends('ums.layouts.app')

@section('title', 'Activity Detail')

@section('content')

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-3">Activity Detail</h4>

        <p><strong>User:</strong> {{ optional($log->actor)->name }}  
            <br><small>{{ optional($log->actor)->email }}</small></p>

        <p><strong>Module:</strong> {{ $log->module }}</p>
        <p><strong>Action:</strong> {{ $log->action }}</p>

        <p><strong>Timestamp:</strong> {{ $log->created_at->format('Y-m-d H:i:s') }}</p>

        <p><strong>IP:</strong> {{ $log->ip }}</p>
        <p><strong>User Agent:</strong> <small>{{ $log->user_agent }}</small></p>

        <hr>

        <h5>Details (JSON)</h5>
        <pre class="bg-light p-3 rounded">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>

        <a href="{{ route('ums.activity_logs.index') }}" class="btn btn-secondary mt-3">Back</a>
    </div>
</div>

@endsection
