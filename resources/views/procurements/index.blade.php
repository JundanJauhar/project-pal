@extends('layouts.app')

@section('title', 'Daftar Pengadaan')

@section('content')
<style>
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .status-badge {
        font-size: 12px;
        padding: 5px 10px;
    }

    .priority-badge {
        font-size: 12px;
        padding: 5px 10px;
    }

    .procurement-row {
        transition: box-shadow 0.3s ease;
    }

    .procurement-row:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
</style>

<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3 class="mb-0">Daftar Pengadaan</h3>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('procurements.index') }}" class="btn btn-sm btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah Pengadaan
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Table Procurements --}}
    <div class="card">
        <div class="card-body">
            @if($procurements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Kode Pengadaan</th>
                                <th>Nama Pengadaan</th>
                                <th>Project</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Prioritas</th>
                                <th>Progress</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($procurements as $procurement)
                            <tr class="procurement-row">
                                <td>
                                    <strong>{{ $procurement->code_procurement }}</strong>
                                </td>
                                <td>{{ Str::limit($procurement->name_procurement, 25) }}</td>
                                <td>
                                    @if($procurement->project)
                                        <small>{{ $procurement->project->code_project }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($procurement->department)
                                        <small>{{ $procurement->department->department_name }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($procurement->status_procurement) {
                                            'completed' => 'bg-success',
                                            'in_progress' => 'bg-warning',
                                            'pending' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }} status-badge">
                                        {{ ucfirst($procurement->status_procurement) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $priorityClass = match($procurement->priority) {
                                            'high' => 'bg-danger',
                                            'medium' => 'bg-warning',
                                            'low' => 'bg-info',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $priorityClass }} priority-badge">
                                        {{ ucfirst($procurement->priority) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $total = $procurement->procurementProgress->count();
                                        $completed = $procurement->procurementProgress->where('status', 'completed')->count();
                                        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $percentage }}%" 
                                             aria-valuenow="{{ $percentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ $percentage }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('procurements.show', $procurement->procurement_id) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Lihat Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="#" class="btn btn-outline-secondary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-4">
                    {{ $procurements->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">Belum ada data pengadaan</p>
                </div>
            @endif
        </div>
    </div>

</div>

@endsection
