@extends('layouts.app')

@section('title', 'Dashboard - PT PAL Indonesia')

@push('styles')
<style>
    .stat-card {
        border-radius: 18px;
        padding: 20px;
        margin-bottom: 20px;
        border: none;
    }
    .stat-progress { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .stat-rejected { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .timeline-step {
        padding: 10px;
        text-align: center;
        border-radius: 5px;
        font-size: 12px;
    }
    .timeline-step.active {
        background-color: #667eea;
        color: white;
    }
    .priority-badge {
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: bold;
    }
    .priority-rendah { background-color: #28a745; color: white; }
    .priority-sedang { background-color: #ffc107; color: black; }
    .priority-tinggi { background-color: #dc3545; color: white; }
</style>
@endpush

@section('content')

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card stat-total">
                    <div class="stat-content">
                        <div class="stat-title">Total Pengadaan</div>
                        <div class="stat-value">{{ $stats['total_pengadaan'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <div class="stat-icon-inner">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-progress">
                    <div class="stat-content">
                        <div class="stat-title">Sedang Proses</div>
                        <div class="stat-value">{{ $stats['sedang_proses'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <div class="stat-icon-inner">
                            <i class="bi bi-box"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-success">
                    <div class="stat-content">
                        <div class="stat-title">Selesai</div>
                        <div class="stat-value">{{ $stats['selesai'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <div class="stat-icon-inner">
                            <i class="bi bi-check-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card stat-rejected">
                    <div class="stat-content">
                        <div class="stat-title">Ditolak</div>
                        <div class="stat-value">{{ $stats['ditolak'] }}</div>
                    </div>
                    <div class="stat-icon">
                        <div class="stat-icon-inner">
                            <i class="bi bi-x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Daftar Pengadaan</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Project</th>
                                    <th>Department</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Vendor</th>
                                    <th>Prioritas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projects as $project)
                                <tr>
                                    <td>{{ $project->code_project }}</td>
                                    <td>{{ $project->ownerDivision->nama_divisi ?? '-' }}</td>
                                    <td>{{ $project->start_date->format('d/m/Y') }}</td>
                                    <td>{{ $project->end_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($project->contracts->first())
                                            {{ $project->contracts->first()->vendor->name_vendor ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <span class="priority-badge priority-{{ strtolower($project->priority) }}">
                                            {{ strtoupper($project->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($project->status_project) {
                                                'completed', 'selesai' => 'success',
                                                'rejected' => 'danger',
                                                'in_progress', 'review_sc' => 'warning',
                                                default => 'secondary'
                                            };
                                            $statusText = match($project->status_project) {
                                                'review_sc' => 'Review SC',
                                                'persetujuan_sekretaris' => 'Persetujuan Sekretaris',
                                                'pemilihan_vendor' => 'Pemilihan Vendor',
                                                'in_progress' => 'Sedang Proses',
                                                'completed', 'selesai' => 'Success',
                                                'rejected' => 'Denied',
                                                default => ucfirst($project->status_project)
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">Tidak ada data pengadaan</td>
                                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</div>
@endsection
