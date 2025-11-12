@extends('layouts.app')

@section('title', 'Tinjau Persetujuan - ' . $project->code_project)

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('sekdir.approvals') }}" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <h2><i class="bi bi-check-square"></i> Tinjau Persetujuan Pengadaan</h2>
    </div>
</div>

<!-- Project Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4 style="margin-bottom: 6px; font-weight: 700;">
                            {{ $project->name_project }}
                            <small class="text-muted">{{ $project->code_project }}</small>
                        </h4>
                        <p style="margin: 0; color: #666;">{{ Str::limit($project->description, 200) }}</p>
                        <div style="margin-top: 10px;">
                            <small class="text-muted">
                                <strong>Department:</strong> {{ $project->ownerDivision->nama_divisi ?? '-' }}
                                &nbsp;&middot;&nbsp;
                                <strong>Start:</strong> {{ $project->start_date?->format('d/m/Y') ?? '-' }}
                                &nbsp;&middot;&nbsp;
                                <strong>End:</strong> {{ $project->end_date?->format('d/m/Y') ?? '-' }}
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div style="margin-bottom: 8px;">
                            @if($project->contracts->first())
                            <div class="vendor-pill vendor-status-process">
                                {{ Str::limit($project->contracts->first()->vendor->name_vendor ?? '-', 24) }}
                            </div>
                            @endif
                        </div>
                        <div style="margin-bottom: 8px;">
                            <span class="badge-priority badge-{{ strtolower($project->priority) }}">
                                {{ strtoupper($project->priority) }}
                            </span>
                        </div>
                        <div>
                            <span class="badge bg-warning" style="padding: 6px 14px; border-radius: 18px; font-weight: 700;">
                                Menunggu Persetujuan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Items Details -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <h5 class="mb-0">Detail Pengadaan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Item</th>
                                <th>Spesifikasi</th>
                                <th>Jumlah</th>
                                <th>Harga Estimasi</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $totalAmount = 0; @endphp
                            @forelse($project->requestProcurements as $req)
                                @forelse($req->items as $item)
                                <tr>
                                    <td>{{ $loop->parent->index + 1 }}.{{ $loop->index + 1 }}</td>
                                    <td><strong>{{ $item->item_name }}</strong></td>
                                    <td>{{ $item->specification }}</td>
                                    <td>{{ $item->quantity }} {{ $item->unit }}</td>
                                    <td>Rp {{ number_format($item->estimated_price, 0, ',', '.') }}</td>
                                    <td>
                                        <strong>Rp {{ number_format($item->estimated_price * $item->quantity, 0, ',', '.') }}</strong>
                                        @php $totalAmount += $item->estimated_price * $item->quantity; @endphp
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Tidak ada item</td>
                                </tr>
                                @endforelse
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Tidak ada permintaan pengadaan</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8f9fa; font-weight: 700;">
                                <td colspan="5" class="text-end">Total Estimasi:</td>
                                <td>Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approval Decision Form -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header card-header-custom">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Keputusan Persetujuan</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('sekdir.approve', $project->project_id) }}">
                    @csrf

                    <div class="mb-3">
                        <label for="approval_decision" class="form-label">Keputusan <span class="text-danger">*</span></label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="approval_decision" id="approved" value="approved" checked>
                                <label class="form-check-label" for="approved">
                                    <i class="bi bi-check-circle text-success"></i> Setujui
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="approval_decision" id="rejected" value="rejected">
                                <label class="form-check-label" for="rejected">
                                    <i class="bi bi-x-circle text-danger"></i> Tolak
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                                  placeholder="Tambahkan catatan untuk keputusan Anda..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <a href="{{ route('sekdir.approvals') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-x"></i> Batal
                            </a>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle"></i> Simpan Keputusan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
