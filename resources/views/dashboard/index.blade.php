@extends('layouts.app')

@section('title', 'Dashboard - PT PAL Indonesia')

@push('styles')
<style>
    .stat-card {
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        color: white;
    }
    .stat-total {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .stat-progress {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .stat-rejected {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }
    .timeline-step {
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        font-size: 12px;
    }
    .badge-priority {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        display: inline-block;
    }
    .badge-priority.badge-tinggi {
        color: #BD0000;
        font-size: 16px;
    }
    .badge-priority.badge-sedang {
        color: #FFBB00;
        font-size: 16px;
    }
    .badge-priority.badge-rendah {
        color: #6f6f6f;
        font-size: 16px;
    }

    .card-header {
        background-color: #ffffff;
    }

    .custom-status-badge {
        border-radius: 8px;
        display: inline-block;
    }
</style>
@endpush

@section('content')

<div class="container-fluid px-4">
    <div class="row">
    <div class="col-md-3">
        <div class="stat-card stat-total">
            <div class="stat-content">
                <div class="stat-title">Total Project</div>
                <div class="stat-value">{{ $stats['total_project'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-folder"></i></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-progress">
            <div class="stat-content">
                <div class="stat-title">Total Procurement</div>
                <div class="stat-value">{{ $stats['total_procurement'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-box"></i></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-content">
                <div class="stat-title">Total Vendor</div>
                <div class="stat-value">{{ $stats['total_vendor'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-building"></i></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-rejected">
            <div class="stat-content">
                <div class="stat-title">Total Requests</div>
                <div class="stat-value">{{ $stats['total_requests'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-file-text"></i></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header text-black">
                <h5 class="mb-0">Daftar Project</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Kode Project</th>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Project</th>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Procurement</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td style="padding: 12px 8px;"><strong>{{ $project->project_code ?? '-' }}</strong></td>
                            <td style="padding: 12px 8px;">{{ $project->project_name }}</td>
                            <td style="padding: 12px 8px;">{{ $project->procurement->name_procurement ?? '-' }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $project->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">Tidak ada data project</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>

@endsection
