@extends('layouts.app')

@section('title', 'Payment Management')

@push('styles')
<style>
    /* ===== DASHBOARD CARDS ===== */
    .payment-topcards {
        display: flex;
        gap: 20px;
        margin-bottom: 30px;
        margin-top: 10px;
    }

    .payment-card {
        flex: 1;
        padding: 22px;
        border-radius: 12px;
        background: #F4F4F4;
        border: 1px solid #E0E0E0;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .payment-card h6 {
        color: #676767;
        font-size: 14px;
        margin-bottom: 5px;
    }

    .payment-card h3 {
        font-weight: 700;
        font-size: 32px;
    }

    .payment-card.yellow {
        border-left: 5px solid #F2C94C;
    }

    .payment-card.cyan {
        border-left: 5px solid #17a2b8;
    }

    .payment-card.blue {
        border-left: 5px solid #1E90FF;
    }

    .payment-card.green {
        border-left: 5px solid #27AE60;
    }

    /* ===== TABLE WRAPPER ===== */
    .payment-table-wrapper {
        padding: 25px;
        border-radius: 14px;
        margin-top: 20px;
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.12);
        background: #FFFFFF;
    }

    /* Title */
    .payment-table-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* Search + Filters container */
    .filters-wrap {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Search */
    .payment-search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #F0F0F0;
        border-radius: 25px;
        padding: 6px 12px;
        width: 240px;
        border: 1px solid #ddd;
        font-size: 14px;
    }

    .payment-search-box input {
        border: none;
        background: transparent;
        width: 100%;
        outline: none;
        font-size: 14px;
    }

    .payment-search-box i {
        font-size: 14px;
        color: #777;
    }

    /* Filter selects */
    .filter-select {
        background: #fff;
        border: 1px solid #ddd;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 14px;
    }

    /* ===== TABLE STYLE ===== */
    .payment-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .payment-table thead th {
        padding: 14px 6px;
        border-bottom: 2px solid #C9C9C9;
        font-size: 14px;
        text-transform: uppercase;
        color: #555;
        text-align: center;
    }

    .payment-table tbody td {
        padding: 14px 6px;
        border-bottom: 1px solid #DFDFDF;
        font-size: 15px;
        color: #333;
        text-align: center;
    }

    .payment-table tbody tr:hover {
        background: #EFEFEF;
    }

    .vendor-table-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* STATUS COLORS */
    .status-pending {
        color: #F2C94C;
        font-weight: bold;
    }

    .status-accounting {
        color: #17a2b8;
        font-weight: bold;
    }

    .status-treasury {
        color: #1E90FF;
        font-weight: bold;
    }

    .status-paid {
        color: #27AE60;
        font-weight: bold;
    }

    .status-rejected {
        color: #D60000;
        font-weight: bold;
    }

    /* BUTTONS */
    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-action.btn-view {
        background: #17a2b8;
        color: white;
    }

    .btn-action.btn-view:hover {
        background: #138496;
    }

    .btn-action.btn-verify {
        background: #27AE60;
        color: white;
    }

    .btn-action.btn-verify:hover {
        background: #229954;
    }

    .btn-action.btn-pay {
        background: #1E90FF;
        color: white;
    }

    .btn-action.btn-pay:hover {
        background: #1873CC;
    }

    .action-group {
        display: flex;
        gap: 6px;
        justify-content: center;
    }

    /* Verified icons */
    .verified-info {
        font-size: 13px;
        line-height: 1.4;
    }

    .verified-info i {
        font-size: 12px;
    }

    /* responsive tweaks */
    @media (max-width: 900px) {
        .payment-topcards {
            flex-direction: column;
        }

        .filters-wrap {
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
        }
    }
</style>
@endpush

@section('content')

{{-- ===== TOP CARDS ===== --}}
<div class="payment-topcards">

    {{-- Perlu Verifikasi Accounting (semua role) --}}
    <div class="payment-card yellow">
        <h6>Perlu Verifikasi Accounting</h6>
        <h3>{{ $pendingAccountingCount ?? 0 }}</h3>
    </div>

    @if(!Auth::user()->hasRole('accounting'))
    {{-- Perlu Dibayar Treasury (hidden untuk accounting) --}}
    <div class="payment-card blue">
        <h6>Perlu Dibayar Treasury</h6>
        <h3>{{ $needPaymentCount ?? 0 }}</h3>
    </div>
    @endif

    @if(!Auth::user()->hasRole('treasury'))
    {{-- Sudah Diverifikasi Accounting (hidden untuk treasury) --}}
    <div class="payment-card cyan">
        <h6>Sudah Diverifikasi Accounting</h6>
        <h3>{{ $verifiedAccountingCount ?? 0 }}</h3>
    </div>
    @endif

    {{-- Sudah Terbayar (semua role) --}}
    <div class="payment-card green">
        <h6>Sudah Terbayar</h6>
        <h3>{{ $paidCount ?? 0 }}</h3>
    </div>

</div>

{{-- ===== TABLE ===== --}}
<div class="payment-table-wrapper">
    <div class="vendor-table-title">
        <span>
            @if(Auth::user()->hasRole('treasury'))
            Daftar Pembayaran
            @elseif(Auth::user()->hasRole('accounting'))
            Daftar Verifikasi
            @endif
        </span>
    </div>
    <!-- <div class="payment-table-title">
        <span>Daftar Pembayaran</span>

        <form method="GET" style="margin:0;">
            <div class="filters-wrap">
                <div class="payment-search-box" title="Cari kode project">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari project..." />
                    <i class="bi bi-search"></i>
                </div>

                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="verified_accounting" {{ request('status') === 'verified_accounting' ? 'selected' : '' }}>Verified Accounting</option>
                    <option value="verified_treasury" {{ request('status') === 'verified_treasury' ? 'selected' : '' }}>Verified Treasury</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>

                <select name="type" class="filter-select" onchange="this.form.submit()">
                    <option value="">Semua Tipe</option>
                    <option value="dp" {{ request('type') === 'dp' ? 'selected' : '' }}>DP</option>
                    <option value="termin" {{ request('type') === 'termin' ? 'selected' : '' }}>Termin</option>
                    <option value="pelunasan" {{ request('type') === 'pelunasan' ? 'selected' : '' }}>Pelunasan</option>
                </select>

                <button type="submit" class="filter-select" style="cursor:pointer;">Terapkan</button>
            </div>
        </form>
    </div> -->

    <div class="table-responsive">
        <table class="payment-table">
            <thead>
                <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Project</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Type</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Amount</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Due Date</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Verified By</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($payments as $payment)
                @php
                $statusClass = match($payment->status) {
                'paid' => 'status-paid',
                'verified_treasury' => 'status-treasury',
                'verified_accounting' => 'status-accounting',
                'rejected' => 'status-rejected',
                default => 'status-pending'
                };
                $statusText = str_replace('_', ' ', strtoupper($payment->status));
                @endphp

                <tr>
                    <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                        <strong>{{ $payment->project->code_project }}</strong><br>
                        <small class="text-muted">{{ Str::limit($payment->project->name_project, 30) }}</small>
                    </td>
                    <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                        <span style="background:#6c757d; color:white; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600;">
                            {{ strtoupper($payment->payment_type) }}
                        </span>
                    </td>
                    <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                    <td>{{ $payment->due_date ? $payment->due_date->format('d/m/Y') : '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                        <span class="{{ $statusClass }}">{{ $statusText }}</span>
                    </td>
                    <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                        <div class="verified-info">
                            @if($payment->verified_by_accounting)
                            <div><i class="bi bi-check-circle text-success"></i> Accounting</div>
                            @endif
                            @if($payment->verified_by_treasury)
                            <div><i class="bi bi-check-circle text-success"></i> Treasury</div>
                            @endif
                            @if(!$payment->verified_by_accounting && !$payment->verified_by_treasury)
                            <span class="text-muted">-</span>
                            @endif
                        </div>
                    </td>
                    <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                        <div class="action-group">
                            <a href="{{ route('payments.show', $payment->payment_schedule_id) }}"
                                class="btn-action btn-view">
                                <i class="bi bi-eye"></i> View
                            </a>

                            @if(Auth::user()->hasRole('accounting') && $payment->status === 'pending')
                            <button class="btn-action btn-verify"
                                onclick="verifyAccounting({{ $payment->payment_schedule_id }})">
                                <i class="bi bi-check"></i> Verify
                            </button>
                            @endif

                            @if(Auth::user()->hasRole('treasury') && $payment->status === 'verified_accounting')
                            <button class="btn-action btn-pay"
                                onclick="verifyTreasury({{ $payment->payment_schedule_id }})">
                                <i class="bi bi-wallet2"></i> Pay
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>

                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size:40px; color:#bbb;"></i>
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

@endsection

@push('scripts')
<script>
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
        // Stats are already loaded from controller, no need to fetch
    });
</script>
@endpush