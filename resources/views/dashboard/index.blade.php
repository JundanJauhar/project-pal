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
        .stat-total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-progress { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .stat-rejected { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        .timeline-step {
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            font-size: 12px;
        }
        .priority-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        .priority-rendah { background-color: #28AC00; color: white; }
        .priority-sedang { background-color: #FFBB00; color: black; }
        .priority-tinggi { background-color: #BD0000; color: white; }

        .card-header {
            background-color: #ffffff;
        }

        /* ✅ tambahkan ini biar badge status tidak berubah warna lagi */
        .custom-status-badge {
            border-radius: 8px;
            display: inline-block;
        }
</style>
@endpush

@section('content')

<div class="row">
    <div class="col-md-3">
        <div class="stat-card stat-total">
            <div class="stat-content">
                <div class="stat-title">Total Pengadaan</div>
                <div class="stat-value">{{ $stats['total_pengadaan'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-check-lg"></i></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-progress">
            <div class="stat-content">
                <div class="stat-title">Sedang Proses</div>
                <div class="stat-value">{{ $stats['sedang_proses'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-box"></i></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-success">
            <div class="stat-content">
                <div class="stat-title">Selesai</div>
                <div class="stat-value">{{ $stats['selesai'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-check-lg"></i></div></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-rejected">
            <div class="stat-content">
                <div class="stat-title">Ditolak</div>
                <div class="stat-value">{{ $stats['ditolak'] }}</div>
            </div>
            <div class="stat-icon"><div class="stat-icon-inner"><i class="bi bi-x"></i></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header text-black">
                <h5 class="mb-0">Daftar Pengadaan</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Kode Project</th>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Nama Project</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Mulai</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Selesai</th>
                            <th style="padding: 12px 8px; text-align: left; font-weight: 600; color: #000;">Vendor</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                            <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($projects as $project)
                        <tr>
                            <td style="padding: 12px 8px;"><strong>{{ $project->code_project }}</strong></td>
                            <td style="padding: 12px 8px;">{{ Str::limit($project->name_project, 40) }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $project->ownerDivision->nama_divisi ?? '-' }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $project->start_date->format('d/m/Y') }}</td>
                            <td style="padding: 12px 8px; text-align: center;">{{ $project->end_date->format('d/m/Y') }}</td>
                            <td>{{ $project->contracts->first()->vendor->name_vendor ?? '-' }}</td>

                            <td style="padding: 12px 8px; text-align: center;">
                                <span class="priority-badge priority-{{ strtolower($project->priority) }}"
                                style="padding: 5px 12px; font-size: 11px; font-weight: 600;">
                                    {{ strtoupper($project->priority) }}
                                </span>
                            </td>

                            <td style="padding: 12px 8px; text-align: center;">
                                @php
                                    $statusColors = [
                                        'draft'     => '#555555',
                                        'completed' => '#28AC00',
                                        'decline'   => '#BD0000',
                                    ];

                                    $badgeColor = $statusColors[$project->status_project] ?? '#ECAD02';

                                    $statusText = match($project->status_project) {
                                        'review_sc' => 'Review SC',
                                        'persetujuan_sekretaris' => 'Persetujuan Sekretaris',
                                        'pemilihan_vendor' => 'Pemilihan Vendor',
                                        'in_progress' => 'Sedang Proses',
                                        'completed' => 'Completed',
                                        'decline' => 'Decline',
                                        default => ucfirst($project->status_project)
                                    };
                                @endphp

                                <span class="badge custom-status-badge"
                                    style="background-color: {{ $badgeColor }} !important;
                                           color: #fff !important;
                                           padding: 6px 12px !important;
                                           font-weight: 600 !important;
                                           font-size: 12px;">
                                    {{ $statusText }}
                                </span>
                            </td>

                            <td style="padding: 12px 8px; text-align: center;">
                                <a href="{{ route('projects.show', $project->project_id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center">Tidak ada data pengadaan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
