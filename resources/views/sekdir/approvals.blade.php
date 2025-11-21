@extends('layouts.app')

@section('title', 'Persetujuan Pengadaan - Sekretaris Direktur')

@section('content')

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-check-square"></i> Persetujuan Pengadaan</h2>
        <p class="text-muted">Tinjau dan setujui permintaan pengadaan</p>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="stat-card stat-warning" style="background: #f2f2f2 !important; color: #000 !important; border-radius: 18px !important; position: relative; display: flex; align-items: center; justify-content: space-between; padding: 24px 28px !important; overflow: hidden; gap: 24px;">
            <div style="position: absolute; left: 0; top: 16px; bottom: 16px; width: 5px; border-radius: 5px; background: #ffc107; z-index: 0;"></div>
            <div class="stat-content" style="z-index: 1;">
                <div class="stat-title" style="color: #6c757d; font-size: 16px; font-weight: 700; margin-bottom: 10px;">Total Menunggu Persetujuan</div>
                <div class="stat-value" style="font-size: var(--stat-value-size, 40px); font-weight: 800; color: #000000;">{{ $stats['menunggu_total'] }}</div>
            </div>
            <div class="stat-icon" style="position: relative; width: 64px; height: 64px; border-radius: 50%; background: #ffc107; display: flex; align-items: center; justify-content: center;">
                <div class="stat-icon-inner" style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid #b37f00; background: rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-clock" style="font-size: 22px; color: #b37f00;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card stat-warning" style="background: #f2f2f2 !important; color: #000 !important; border-radius: 18px !important; position: relative; display: flex; align-items: center; justify-content: space-between; padding: 24px 28px !important; overflow: hidden; gap: 24px;">
            <div style="position: absolute; left: 0; top: 16px; bottom: 16px; width: 5px; border-radius: 5px; background: #ffc107; z-index: 0;"></div>
            <div class="stat-content" style="z-index: 1;">
                <div class="stat-title" style="color: #6c757d; font-size: 16px; font-weight: 700; margin-bottom: 10px;">Hari Ini</div>
                <div class="stat-value" style="font-size: var(--stat-value-size, 40px); font-weight: 800; color: #000000;">{{ $stats['menunggu_hari_ini'] }}</div>
            </div>
            <div class="stat-icon" style="position: relative; width: 64px; height: 64px; border-radius: 50%; background: #ffc107; display: flex; align-items: center; justify-content: center;">
                <div class="stat-icon-inner" style="width: 44px; height: 44px; border-radius: 50%; border: 2px solid #b37f00; background: rgba(255,255,255,0.4); display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-sun" style="font-size: 22px; color: #b37f00;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <form method="GET" action="{{ route('sekdir.approvals') }}" class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Cari kode/nama project..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4">
                        <select name="priority" class="form-select">
                            <option value="">Semua Prioritas</option>
                            <option value="rendah" {{ request('priority') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="sedang" {{ request('priority') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="tinggi" {{ request('priority') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Requests Table -->
<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">Daftar Permohonan Pengadaan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Project</th>
                                <th>Department</th>
                                <th>Tanggal Dibuat</th>
                                <th>Prioritas</th>
                                <th>Total Item</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($approvals as $project)
                            <tr>
                                <td><strong>{{ $project->code_project }}</strong></td>
                                <td>{{ Str::limit($project->name_project, 50) }}</td>
                                <td>{{ $project->ownerDivision->nama_divisi ?? '-' }}</td>
                                <td>{{ $project->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge-priority badge-{{ strtolower($project->priority) }}">
                                        {{ strtoupper($project->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('sekdir.approval-detail', $project->project_id) }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Tinjau
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox" style="font-size: 32px;"></i>
                                    <p class="mt-2">Tidak ada pengadaan menunggu persetujuan</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($approvals->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $approvals->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
