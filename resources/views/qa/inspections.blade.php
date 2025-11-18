@extends('layouts.app')

@section('title', 'Department')

@section('content')

{{-- ========================= --}}
{{-- Bagian Statistik           --}}
{{-- ========================= --}}
<div class="row mb-4">

    {{-- Total Pengadaan --}}
    <div class="col-md-3">
        <div class="card p-3 shadow-sm" style="border-left: 4px solid #2F80ED;">
            <h6 class="text-muted">Total Pengadaan</h6>
            <h3 class="fw-bold">{{ $inspections->count() }}</h3>
        </div>
    </div>

    {{-- Butuh Inspeksi --}}
    <div class="col-md-3">
        <div class="card p-3 shadow-sm" style="border-left: 4px solid #F2C94C;">
            <h6 class="text-muted">Butuh Inspeksi</h6>
            <h3 class="fw-bold">
                {{ $inspections->where('result', 'pending')->count() }}
            </h3>
        </div>
    </div>

    {{-- Lolos Inspeksi --}}
    <div class="col-md-3">
        <div class="card p-3 shadow-sm" style="border-left: 4px solid #27AE60;">
            <h6 class="text-muted">Lolos Inspeksi</h6>
            <h3 class="fw-bold">
                {{ $inspections->where('result', 'passed')->count() }}
            </h3>
        </div>
    </div>

    {{-- Tidak Lolos Inspeksi --}}
    <div class="col-md-3">
        <div class="card p-3 shadow-sm" style="border-left: 4px solid #EB5757;">
            <h6 class="text-muted">Tidak Lolos Inspeksi</h6>
            <h3 class="fw-bold">
                {{ $inspections->where('result', 'failed')->count() }}
            </h3>
        </div>
    </div>

</div>



{{-- ========================= --}}
{{-- JUDUL TABEL               --}}
{{-- ========================= --}}
<h3 class="fw-bold mb-3">Daftar Pengadaan</h3>


{{-- ========================= --}}
{{-- TABEL DAFTAR PENGADAAN   --}}
{{-- ========================= --}}
<div class="card shadow-sm">
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover align-middle">

                <thead>
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

                    @forelse($inspections as $row)
                    <tr>
                        {{-- KODE --}}
                        <td class="fw-semibold">
                            {{ $row->project->code_project }}
                        </td>

                        {{-- NAMA PENGADAAN --}}
                        <td>
                            {{ $row->project->name_project }}
                        </td>

                        {{-- DEPARTMENT --}}
                        <td>
                            {{ $row->project->department->name ?? '-' }}
                        </td>

                        {{-- VENDOR --}}
                        <td>
                            {{ $row->vendor->vendor_name ?? '-' }}
                        </td>

                        {{-- TANGGAL MULAI --}}
                        <td>
                            {{ $row->start_date ? $row->start_date->format('d/m/Y') : '-' }}
                        </td>

                        {{-- TANGGAL SELESAI --}}
                        <td>
                            {{ $row->end_date ? $row->end_date->format('d/m/Y') : '-' }}
                        </td>

                        {{-- PRIORITAS --}}
                        <td>
                            @php
                                $priorityClass = match(strtoupper($row->priority)) {
                                    'TINGGI' => 'text-danger fw-bold',
                                    'SEDANG' => 'text-warning fw-bold',
                                    'RENDAH' => 'text-secondary fw-bold',
                                    default => 'text-muted'
                                };
                            @endphp
                            <span class="{{ $priorityClass }}">
                                {{ strtoupper($row->priority) }}
                            </span>
                        </td>

                        {{-- STATUS INSPEKSI --}}
                        <td>
                            @php
                                $statusClass = match($row->result) {
                                    'passed' => 'text-success fw-bold',
                                    'failed' => 'text-danger fw-bold',
                                    'conditional' => 'text-warning fw-bold',
                                    'pending' => 'text-primary fw-bold',
                                    default => 'text-muted'
                                };
                            @endphp
                            <span class="{{ $statusClass }}">
                                {{ strtoupper($row->result) }}
                            </span>
                        </td>

                    </tr>
                    @empty

                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 40px; color:#bbb"></i>
                            <p class="text-muted mt-2">Tidak ada data pengadaan</p>
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="mt-3">
            {{ $inspections->links() }}
        </div>

    </div>
</div>

@endsection
