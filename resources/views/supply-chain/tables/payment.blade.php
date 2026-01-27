<!-- Filter Section -->
<form id="filter-form" method="GET" action="{{ route('supply-chain.dashboard') }}" class="sc-filters">
    <input type="hidden" name="tab" value="payment">
    <div class="sc-filter-group">
        <input type="text"
            id="search-input"
            name="search"
            class="sc-filter-input"
            placeholder="Cari kode atau vendor..."
            value="{{ request('search') }}">

        <select name="type" class="sc-filter-select">
            <option value="">Semua Jenis Pembayaran</option>
            <option value="dp" {{ request('type') === 'dp' ? 'selected' : '' }}>Down Payment (DP)</option>
            <option value="termin" {{ request('type') === 'termin' ? 'selected' : '' }}>Pembayaran Termin</option>
            <option value="final" {{ request('type') === 'final' ? 'selected' : '' }}>Pembayaran Final</option>
        </select>

        <button type="button"
            onclick="document.getElementById('filter-form').reset(); document.getElementById('filter-form').submit();"
            class="sc-btn-primary">
            <i class="bi bi-arrow-clockwise"></i> Reset
        </button>
    </div>
</form>

<!-- Table -->
<h4 style="margin-bottom: 16px; color: #000;">Daftar Pembayaran</h4>
<div class="dashboard-table-wrapper">
    @if($payments->count() > 0)
    <table class="dashboard-table">
        <thead>
            <tr>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Jenis Pembayaran</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nilai Pembayaran</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Persentase</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tgl Realisasi</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No Memo</th>
            </tr>
        </thead>
        <tbody>
            @php
            $counter = ($payments->currentPage() - 1) * $payments->perPage();
            @endphp

            @foreach($payments as $procurement)
            @php
            $vendor = $procurement->requestProcurements->first()?->vendor;
            $procPayments = $procurement->pembayarans ?? collect();
            @endphp

            @forelse($procPayments as $payment)
            @php
            $counter++;
            @endphp
            <tr>
                <td style="text-align: center;">
                    <strong>{{ $counter }}</strong>
                </td>
                <td style="text-align: center;">
                    <a href="{{ route('procurements.show', $procurement->procurement_id) }}"
                        style="color: #000; font-weight: 600; text-decoration: none;">
                        {{ $procurement->code_procurement }}
                    </a>
                </td>
                <td style="text-align: center;">
                    @if ($payment->vendor)
                    {{ $payment->vendor->name_vendor }}
                    @elseif ($vendor)
                    {{ $vendor->name_vendor }}
                    @else
                    <span style="color: #999;">-</span>
                    @endif
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    SKBDN
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @if ($payment->payment_value)
                    {{ number_format($payment->payment_value, 0, ',', '.') }}
                    @else
                    <span style="color: #999;">-</span>
                    @endif
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @if ($payment->percentage)
                    {{ $payment->percentage }}%
                    @else
                    <span style="color: #999;">-</span>
                    @endif
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @if ($payment->realization_date)
                    {{ \Carbon\Carbon::parse($payment->realization_date)->format('d/m/Y') }}
                    @else
                    <span class="sc-badge sc-badge-warning">Belum Direalisasi</span>
                    @endif
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @if ($payment->no_memo)
                    <small style="font-size: 12px;">{{ Str::limit($payment->no_memo, 30) }}</small>
                    @else
                    <span style="color: #999;">-</span>
                    @endif
                </td>
            </tr>
            @empty
            @php
            $counter++;
            @endphp
            <tr>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    <strong>{{ $counter }}</strong>
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    <a href="{{ route('procurements.show', $procurement->procurement_id) }}"
                        style="color: #003d82; font-weight: 600; text-decoration: none;">
                        {{ $procurement->code_procurement }}
                    </a>
                </td>
                <td colspan="6" style="padding: 12px 8px; text-align: center; color: #999; font-style: italic;">
                    Tidak ada data pembayaran untuk pengadaan ini
                </td>
            </tr>
            @endforelse
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="sc-pagination">
        {{ $payments->links() }}
    </div>
    @else
    <div class="sc-empty">
        <div class="sc-empty-icon">💳</div>
        <p>Tidak ada data pembayaran</p>
    </div>
    @endif
</div>