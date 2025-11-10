@extends('layouts.app')

@section('title', 'Detail Project - ' . $project->name_project)

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                <li class="breadcrumb-item active">{{ $project->code_project }}</li>
            </ol>
        </nav>
        <h2><i class="bi bi-folder-fill"></i> {{ $project->name_project }}</h2>
    </div>
    <div class="col-md-4 text-end">
        @if(Auth::user()->roles === 'supply_chain' || Auth::user()->roles === 'user')
        <a href="{{ route('projects.edit', $project->project_id) }}" class="btn btn-warning btn-custom">
            <i class="bi bi-pencil"></i> Edit Project
        </a>
        @endif
    </div>
</div>

<!-- Project Info Card -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Project</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Kode Project:</strong> {{ $project->code_project }}</p>
                        <p><strong>Nama Project:</strong> {{ $project->name_project }}</p>
                        <p><strong>Department:</strong> {{ $project->ownerDivision->nama_divisi ?? '-' }}</p>
                        <p><strong>Deskripsi:</strong><br>{{ $project->description ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Tanggal Mulai:</strong> {{ $project->start_date->format('d/m/Y') }}</p>
                        <p><strong>Tanggal Selesai:</strong> {{ $project->end_date->format('d/m/Y') }}</p>
                        <p><strong>Prioritas:</strong>
                            <span class="badge-priority badge-{{ strtolower($project->priority) }}">
                                {{ strtoupper($project->priority) }}
                            </span>
                        </p>
                        <p><strong>Status:</strong>
                            @php
                                $statusClass = match($project->status_project) {
                                    'completed', 'selesai' => 'success',
                                    'rejected' => 'danger',
                                    default => 'warning'
                                };
                            @endphp
                            <span class="badge bg-{{ $statusClass }}">
                                {{ str_replace('_', ' ', ucwords($project->status_project)) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-building"></i> Vendor</h5>
            </div>
            <div class="card-body">
                @if($project->contracts->first())
                    @php $contract = $project->contracts->first(); @endphp
                    <p><strong>Nama Vendor:</strong><br>{{ $contract->vendor->name_vendor ?? '-' }}</p>
                    <p><strong>Kontak:</strong><br>{{ $contract->vendor->contact_vendor ?? '-' }}</p>
                    <p><strong>Status Kontrak:</strong><br>
                        <span class="badge bg-info">{{ ucfirst($contract->status) }}</span>
                    </p>
                @else
                    <p class="text-muted">Vendor belum dipilih</p>
                    @if(Auth::user()->roles === 'supply_chain')
                    <a href="{{ route('supply-chain.vendors') }}" class="btn btn-primary btn-sm btn-custom">
                        <i class="bi bi-plus"></i> Pilih Vendor
                    </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Procurement Timeline -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-diagram-3"></i> Tahapan Procurement</h5>
            </div>
            <div class="card-body">
                <div class="timeline-progress">
                    @php
                        $stages = [
                            ['name' => 'Diajukan', 'icon' => 'file-text'],
                            ['name' => 'Review SC', 'icon' => 'search'],
                            ['name' => 'Persetujuan Sekretaris', 'icon' => 'person-check'],
                            ['name' => 'Pemilihan Vendor', 'icon' => 'people'],
                            ['name' => 'Pengecekan Legalitas', 'icon' => 'shield-check'],
                            ['name' => 'Pemesanan', 'icon' => 'cart'],
                            ['name' => 'Pembayaran', 'icon' => 'credit-card'],
                            ['name' => 'Selesai', 'icon' => 'check-circle'],
                        ];

                        $currentStageIndex = match($project->status_project) {
                            'draft' => 0,
                            'review_sc' => 1,
                            'persetujuan_sekretaris' => 2,
                            'pemilihan_vendor' => 3,
                            'pengecekan_legalitas' => 4,
                            'pemesanan' => 5,
                            'pembayaran' => 6,
                            'selesai', 'completed' => 7,
                            default => 0
                        };
                    @endphp

                    @foreach($stages as $index => $stage)
                    <div class="timeline-step {{ $index < $currentStageIndex ? 'completed' : ($index == $currentStageIndex ? 'active' : '') }}">
                        <div class="timeline-icon">
                            <i class="bi bi-{{ $stage['icon'] }}"></i>
                        </div>
                        <div class="timeline-label">{{ $stage['name'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Progress -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Progress Detail</h5>
            </div>
            <div class="card-body">
                @if($progress && $progress->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Checkpoint</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($progress as $p)
                            <tr>
                                <td>{{ $p->checkpoint->point_name ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($p->status_progress) {
                                            'completed' => 'success',
                                            'in_progress' => 'warning',
                                            'blocked' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">
                                        {{ ucfirst($p->status_progress) }}
                                    </span>
                                </td>
                                <td>{{ $p->tanggal_selesai ? $p->tanggal_selesai->format('d/m/Y') : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">Belum ada progress yang tercatat</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-box-seam"></i> Detail Pengadaan</h5>
            </div>
            <div class="card-body">
                @if($project->requestProcurements->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Spesifikasi</th>
                                <th>Jumlah</th>
                                <th>Harga Estimasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($project->requestProcurements as $req)
                                @foreach($req->items as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->specification }}</td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>Rp {{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted">Belum ada item pengadaan</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- HPS & Evaluation -->
@if($project->hps->count() > 0 || $project->evaluations->count() > 0)
<div class="row mb-4">
    @if($project->hps->count() > 0)
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-calculator"></i> HPS (Harga Perkiraan Sendiri)</h5>
            </div>
            <div class="card-body">
                @foreach($project->hps as $hps)
                <div class="mb-3">
                    <p><strong>Total Amount:</strong> Rp {{ number_format($hps->total_amount, 0, ',', '.') }}</p>
                    <p><strong>Tanggal:</strong> {{ $hps->hps_date ? $hps->hps_date->format('d/m/Y') : '-' }}</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $hps->status === 'approved' ? 'success' : ($hps->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($hps->status) }}
                        </span>
                    </p>
                    @if($hps->notes)
                    <p><strong>Catatan:</strong> {{ $hps->notes }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($project->evaluations->count() > 0)
    <div class="col-md-6">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-clipboard-data"></i> Evaluasi Teknis (Evatek)</h5>
            </div>
            <div class="card-body">
                @foreach($project->evaluations as $eval)
                <div class="mb-3">
                    <p><strong>Score:</strong> {{ $eval->evaluation_score }}/100</p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-{{ $eval->status === 'approved' ? 'success' : ($eval->status === 'rejected' ? 'danger' : 'warning') }}">
                            {{ ucfirst($eval->status) }}
                        </span>
                    </p>
                    @if($eval->notes)
                    <p><strong>Catatan:</strong> {{ $eval->notes }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Action Buttons based on Role -->
<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-gear"></i> Actions</h5>
            </div>
            <div class="card-body">
                @if(Auth::user()->roles === 'supply_chain')
                    @if($project->status_project === 'review_sc')
                    <button class="btn btn-success btn-custom" onclick="approveProject()">
                        <i class="bi bi-check-circle"></i> Approve Review
                    </button>
                    @endif

                    @if($project->status_project === 'pemilihan_vendor')
                    <a href="{{ route('supply-chain.vendors') }}" class="btn btn-primary btn-custom">
                        <i class="bi bi-people"></i> Pilih Vendor
                    </a>
                    @endif

                    <button class="btn btn-info btn-custom" onclick="updateMaterialArrival()">
                        <i class="bi bi-truck"></i> Update Kedatangan Material
                    </button>
                @endif

                @if(Auth::user()->roles === 'sekretaris_direksi' && $project->status_project === 'persetujuan_sekretaris')
                    <button class="btn btn-success btn-custom" onclick="approveContract()">
                        <i class="bi bi-file-earmark-check"></i> Setujui Kontrak
                    </button>
                    <button class="btn btn-danger btn-custom">
                        <i class="bi bi-x-circle"></i> Tolak Kontrak
                    </button>
                @endif

                @if(in_array(Auth::user()->roles, ['accounting', 'treasury']) && $project->status_project === 'pembayaran')
                    <a href="{{ route('payments.create', $project->project_id) }}" class="btn btn-primary btn-custom">
                        <i class="bi bi-credit-card"></i> Buat Jadwal Pembayaran
                    </a>
                @endif

                @if(Auth::user()->roles === 'qa')
                    <a href="{{ route('inspections.create', $project->project_id) }}" class="btn btn-warning btn-custom">
                        <i class="bi bi-clipboard-check"></i> Buat Laporan Inspeksi
                    </a>
                @endif

                <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-custom">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function approveProject() {
    if (!confirm('Apakah Anda yakin ingin approve review project ini?')) return;

    $.post("{{ route('supply-chain.approve-review', $project->project_id) }}", {
        notes: 'Project reviewed and approved'
    })
    .done(function() {
        alert('Project berhasil diapprove!');
        location.reload();
    })
    .fail(function() {
        alert('Gagal approve project!');
    });
}

function updateMaterialArrival() {
    const arrivalDate = prompt('Masukkan tanggal kedatangan material (YYYY-MM-DD):');
    if (!arrivalDate) return;

    $.post("{{ route('supply-chain.material-arrival', $project->project_id) }}", {
        arrival_date: arrivalDate,
        notes: 'Material has arrived'
    })
    .done(function() {
        alert('Status kedatangan material berhasil diupdate!');
        location.reload();
    })
    .fail(function() {
        alert('Gagal update status!');
    });
}

function approveContract() {
    if (!confirm('Apakah Anda yakin ingin menyetujui kontrak ini?')) return;

    $.post("{{ route('projects.update-status', $project->project_id) }}", {
        status_project: 'pemesanan',
        notes: 'Contract approved by Sekretaris Direksi'
    })
    .done(function() {
        alert('Kontrak berhasil disetujui!');
        location.reload();
    })
    .fail(function() {
        alert('Gagal approve kontrak!');
    });
}
</script>
@endpush
