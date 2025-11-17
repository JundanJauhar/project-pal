@extends('layouts.app')

@section('title', 'Pengadaan Proyek - ' . $project->project_name)

@section('content')
<style>
    .project-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        margin-bottom: 30px;
    }

    .project-header h2 {
        margin-bottom: 10px;
    }

    .status-badge {
        font-size: 12px;
        padding: 5px 10px;
    }

    .priority-badge {
        font-size: 12px;
        padding: 5px 10px;
    }

    .procurement-card {
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .procurement-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
</style>

<div class="container-fluid px-4">
    {{-- Back Button --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="javascript:history.back()" class="btn btn-sm btn-secondary mb-3">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Project Header --}}
    <div class="project-header">
        <h2>{{ $project->project_name }}</h2>
        <p class="mb-1"><strong>Kode Project:</strong> {{ $project->code_project }}</p>
        <p class="mb-1"><strong>Deskripsi:</strong> {{ $project->description }}</p>
        <p class="mb-0">
            <strong>Jumlah Pengadaan:</strong> 
            <span class="badge bg-light text-dark">{{ $procurements->total() }}</span>
        </p>
    </div>

    {{-- Flash Messages --}}
    @if ($message = Session::get('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ $message }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Procurements Grid or List --}}
    @if($procurements->count() > 0)
        <div class="row">
            @foreach($procurements as $procurement)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card procurement-card h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $procurement->name_procurement }}</h5>
                        <p class="card-text small text-muted">{{ $procurement->code_procurement }}</p>

                        {{-- Status & Priority --}}
                        <div class="mb-3">
                            @php
                                $statusClass = match($procurement->status_procurement) {
                                    'completed' => 'bg-success',
                                    'in_progress' => 'bg-warning',
                                    'pending' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                                $priorityClass = match($procurement->priority) {
                                    'high' => 'bg-danger',
                                    'medium' => 'bg-warning',
                                    'low' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} status-badge me-2">
                                {{ ucfirst($procurement->status_procurement) }}
                            </span>
                            <span class="badge {{ $priorityClass }} priority-badge">
                                {{ ucfirst($procurement->priority) }}
                            </span>
                        </div>

                        {{-- Dates --}}
                        <p class="small mb-1">
                            <strong>Mulai:</strong> {{ $procurement->start_date->format('d/m/Y') }}
                        </p>
                        <p class="small mb-3">
                            <strong>Target:</strong> {{ $procurement->end_date->format('d/m/Y') }}
                        </p>

                        {{-- Progress Bar --}}
                        @php
                            $total = $procurement->procurementProgress->count();
                            $completed = $procurement->procurementProgress->where('status', 'completed')->count();
                            $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                        @endphp
                        <div class="progress mb-2" style="height: 20px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $percentage }}%">
                                {{ $percentage }}%
                            </div>
                        </div>
                        <small class="text-muted">{{ $completed }} dari {{ $total }} step selesai</small>

                        {{-- Department --}}
                        <div class="mt-3 pt-3 border-top">
                            @if($procurement->department)
                                <p class="small mb-0">
                                    <strong>Department:</strong> {{ $procurement->department->department_name }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Card Footer with Action --}}
                    <div class="card-footer bg-light">
                        <a href="{{ route('procurements.show', $procurement->procurement_id) }}" 
                           class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-eye"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $procurements->links() }}
        </div>
    @else
        <div class="alert alert-info text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-3">Belum ada pengadaan untuk project ini</p>
        </div>
    @endif

</div>

@endsection
