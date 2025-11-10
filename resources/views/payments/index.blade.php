@extends('layouts.app')

@section('title', 'Payment Management')

@section('content')
<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-credit-card"></i> Payment Management</h2>
        <p class="text-muted">Kelola pembayaran dan verifikasi dokumen</p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#createPaymentModal">
            <i class="bi bi-plus-circle"></i> Buat Jadwal Pembayaran
        </button>
    </div>
</div>

<!-- Payment Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card card-custom" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
                <h6 class="text-muted mb-1">Pending</h6>
                <h4 class="mb-0" id="stat-pending">Rp 0</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom" style="border-left: 4px solid #17a2b8;">
            <div class="card-body">
                <h6 class="text-muted mb-1">Verified Accounting</h6>
                <h4 class="mb-0" id="stat-accounting">Rp 0</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom" style="border-left: 4px solid #007bff;">
            <div class="card-body">
                <h6 class="text-muted mb-1">Verified Treasury</h6>
                <h4 class="mb-0" id="stat-treasury">Rp 0</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-custom" style="border-left: 4px solid #28a745;">
            <div class="card-body">
                <h6 class="text-muted mb-1">Paid</h6>
                <h4 class="mb-0" id="stat-paid">Rp 0</h4>
            </div>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="row">
    <div class="col-12">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Pembayaran</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-custom">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Verified By</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <strong>{{ $payment->project->code_project }}</strong><br>
                                    <small class="text-muted">{{ Str::limit($payment->project->name_project, 30) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ strtoupper($payment->payment_type) }}</span>
                                </td>
                                <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                                <td>{{ $payment->due_date ? $payment->due_date->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($payment->status) {
                                            'paid' => 'success',
                                            'verified_treasury', 'verified_accounting' => 'info',
                                            'rejected' => 'danger',
                                            default => 'warning'
                                        };
                                        $statusText = str_replace('_', ' ', ucwords($payment->status));
                                    @endphp
                                    <span class="badge bg-{{ $statusClass }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    <small>
                                        @if($payment->verified_by_accounting)
                                            <i class="bi bi-check-circle text-success"></i> Accounting<br>
                                        @endif
                                        @if($payment->verified_by_treasury)
                                            <i class="bi bi-check-circle text-success"></i> Treasury
                                        @endif
                                        @if(!$payment->verified_by_accounting && !$payment->verified_by_treasury)
                                            <span class="text-muted">-</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('payments.show', $payment->payment_schedule_id) }}"
                                           class="btn btn-sm btn-info btn-custom">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if(Auth::user()->roles === 'accounting' && $payment->status === 'pending')
                                        <button class="btn btn-sm btn-success btn-custom"
                                                onclick="verifyAccounting({{ $payment->payment_schedule_id }})">
                                            <i class="bi bi-check"></i> Verify
                                        </button>
                                        @endif

                                        @if(Auth::user()->roles === 'treasury' && $payment->status === 'verified_accounting')
                                        <button class="btn btn-sm btn-primary btn-custom"
                                                onclick="verifyTreasury({{ $payment->payment_schedule_id }})">
                                            <i class="bi bi-wallet2"></i> Pay
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-2">Tidak ada data pembayaran</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Load payment statistics
function loadStats() {
    $.get("{{ route('payments.statistics') }}", function(data) {
        $('#stat-pending').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.pending));
        $('#stat-accounting').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.verified_accounting));
        $('#stat-treasury').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.verified_treasury));
        $('#stat-paid').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.paid));
    });
}

function verifyAccounting(paymentId) {
    if (!confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini?')) return;

    $.post(`/payments/${paymentId}/accounting-verification`, {
        action: 'approve',
        notes: 'Verified by accounting'
    })
    .done(function() {
        alert('Pembayaran berhasil diverifikasi!');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Gagal memverifikasi pembayaran: ' + xhr.responseJSON.message);
    });
}

function verifyTreasury(paymentId) {
    const paymentDate = prompt('Masukkan tanggal pembayaran (YYYY-MM-DD):');
    if (!paymentDate) return;

    $.post(`/payments/${paymentId}/treasury-verification`, {
        action: 'approve',
        payment_date: paymentDate,
        notes: 'Payment processed by treasury'
    })
    .done(function() {
        alert('Pembayaran berhasil diproses!');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Gagal memproses pembayaran: ' + xhr.responseJSON.message);
    });
}

$(document).ready(function() {
    loadStats();
});
</script>
@endpush
