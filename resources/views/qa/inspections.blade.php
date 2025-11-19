@extends('layouts.app')

@section('title', 'Quality Assurance - Inspeksi')

@section('content')

{{-- ========================= --}}
{{--  DASHBOARD STATISTIK       --}}
{{-- ========================= --}}
<div class="row mb-4">

    {{-- Total Pengadaan --}}
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0" style="border-left: 4px solid #2F80ED;">
            <h6 class="text-muted">Total Pengadaan</h6>
            <h3 class="fw-bold">
                {{ $totalProcurements ?? ($totalInspections ?? 0) }}
            </h3>
        </div>
    </div>

    {{-- Butuh Inspeksi (clickable) --}}
    <div class="col-md-3">
        <a href="{{ route('qa.list-approval', ['filter' => 'inspection']) }}" class="text-decoration-none">
            <div class="card p-3 shadow-sm border-0" style="border-left: 4px solid #F2C94C;">
                <h6 class="text-muted">Butuh Inspeksi</h6>
                <h3 class="fw-bold">
                    {{ $butuhInspeksiCount ?? 0 }}
                </h3>
            </div>
        </a>
    </div>

    {{-- Lolos Inspeksi --}}
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0" style="border-left: 4px solid #27AE60;">
            <h6 class="text-muted">Lolos Inspeksi</h6>
            <h3 class="fw-bold">
                {{ $lolosCount ?? ($procurements->where('auto_status', 'completed')->count() ?? 0) }}
            </h3>
        </div>
    </div>

    {{-- Tidak Lolos --}}
    <div class="col-md-3">
        <div class="card p-3 shadow-sm border-0" style="border-left: 4px solid #EB5757;">
            <h6 class="text-muted">Tidak Lolos Inspeksi</h6>
            <h3 class="fw-bold">
                {{ $gagalCount ?? 0 }}
            </h3>
        </div>
    </div>

</div>



{{-- ========================= --}}
{{--  HEADER TABEL              --}}
{{-- ========================= --}}
<h3 class="fw-bold mb-3">Daftar Pengadaan</h3>


{{-- ========================= --}}
{{--  TABEL UTAMA               --}}
{{-- ========================= --}}
<div class="card shadow-sm border-0">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead class="table-light">
                    <tr>
                        <th>Kode Pengadaan</th>
                        <th>Nama Pengadaan</th>
                        <th>Department</th>
                        <th>Vendor</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    {{-- MODE: QA (menampilkan procurements yang butuh inspeksi) --}}
                    @if(isset($procurements))

                        @forelse($procurements as $procurement)
                        <tr>
                            <td class="fw-semibold">{{ $procurement->code_procurement }}</td>
                            <td>{{ $procurement->name_procurement }}</td>
                            <td>{{ $procurement->department->department_name ?? '-' }}</td>
                            <td>{{ $procurement->requestProcurements->first()?->vendor->name_vendor ?? '-' }}</td>
                            <td>{{ $procurement->start_date ? $procurement->start_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $procurement->end_date ? $procurement->end_date->format('d/m/Y') : '-' }}</td>

                            <td>
                                @php
                                    $priorityClass = [
                                        'tinggi' => 'badge bg-danger',
                                        'sedang' => 'badge bg-warning text-dark',
                                        'rendah' => 'badge bg-secondary'
                                    ][$procurement->priority] ?? 'badge bg-light text-dark';
                                @endphp
                                <span class="{{ $priorityClass }}">{{ strtoupper($procurement->priority) }}</span>
                            </td>

                            <td>
                                @php
                                    $status = $procurement->auto_status;
                                    $current = $procurement->current_checkpoint;
                                    $statusClass = [
                                        'completed' => 'badge bg-success',
                                        'in_progress' => 'badge bg-warning text-dark',
                                        'not_started' => 'badge bg-secondary'
                                    ][$status] ?? 'badge bg-light text-dark';
                                    $text = match($status) {
                                        'completed' => 'Selesai',
                                        'not_started' => 'Belum Dimulai',
                                        'in_progress' => $current ?? 'Sedang Proses',
                                        default => ucfirst($status)
                                    };
                                @endphp
                                <span class="{{ $statusClass }}">{{ $text }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 40px; color:#bbb"></i>
                                <p class="text-muted mt-2">Tidak ada pengadaan yang butuh inspeksi saat ini</p>
                            </td>
                        </tr>
                        @endforelse

                    {{-- MODE: NON-QA (menampilkan inspection reports) --}}
                    @else

                        @forelse($inspections as $row)
                        <tr>
                            <td>{{ $row->project->project_code ?? '-' }}</td>
                            <td>{{ $row->project->project_name ?? '-' }}</td>
                            <td>{{ $row->project->department->department_name ?? '-' }}</td>
                            <td>{{ $row->item?->requestProcurement?->vendor->name_vendor ?? '-' }}</td>
                            <td>{{ $row->inspection_date ? \Carbon\Carbon::parse($row->inspection_date)->format('d/m/Y') : '-' }}</td>
                            <td>-</td>
                            <td>
                                @php
                                    $priority = strtolower($row->project->priority ?? '');
                                    $priorityClass = [
                                        'tinggi' => 'badge bg-danger',
                                        'sedang' => 'badge bg-warning text-dark',
                                        'rendah' => 'badge bg-secondary'
                                    ][$priority] ?? 'badge bg-light text-dark';
                                @endphp
                                <span class="{{ $priorityClass }}">{{ strtoupper($priority) }}</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = [
                                        'passed' => 'badge bg-success',
                                        'failed' => 'badge bg-danger',
                                        'conditional' => 'badge bg-warning text-dark',
                                        'pending' => 'badge bg-primary',
                                    ][$row->result] ?? 'badge bg-secondary';
                                @endphp
                                <span class="{{ $statusClass }}">{{ strtoupper($row->result) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox" style="font-size: 40px; color:#bbb"></i>
                                <p class="text-muted mt-2">Tidak ada data inspeksi</p>
                            </td>
                        </tr>
                        @endforelse

                    @endif

                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            @if(isset($procurements))
                {{ $procurements->links() }}
            @else
                {{ $inspections->links() }}
            @endif
        </div>

    </div>
</div>

@endsection
