<!-- Filter Section -->
<form id="filter-form" method="GET" action="{{ route('supply-chain.dashboard') }}" class="sc-filters">
    <input type="hidden" name="tab" value="contract">
    <div class="sc-filter-group">
        <input type="text"
            id="search-input"
            name="search"
            class="sc-filter-input"
            placeholder="Cari kode, nama, atau vendor..."
            value="{{ request('search') }}">

        <select name="status" class="sc-filter-select">
            <option value="">Semua Status</option>
            <option value="approve" {{ request('status') === 'approve' ? 'selected' : '' }}>Disetujui</option>
            <option value="not_approve" {{ request('status') === 'not_approve' ? 'selected' : '' }}>Ditolak</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        </select>

        <button type="button"
            onclick="document.getElementById('filter-form').reset(); document.getElementById('filter-form').submit();"
            class="sc-btn-primary">
            <i class="bi bi-arrow-clockwise"></i> Reset
        </button>
    </div>
</form>

<!-- Table -->
<h4 style="margin-bottom: 16px; color: #000;">Daftar Kontrak</h4>
<div class="dashboard-table-wrapper">
    @if($contracts->count() > 0)
    <table class="dashboard-table">
        <thead>
            <tr>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode Pengadaan</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nama Pengadaan</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nilai Kontrak</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Mata Uang</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Kontrak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contracts as $procurement)
            @php
            // Get the latest contract
            $kontrak = $procurement->kontraks->first();
            $vendor = $kontrak?->vendor ?? $procurement->requestProcurements->first()?->vendor;

            // Contract data
            $nilaiKontrak = $kontrak->nilai ?? 0;
            $mataUang = $kontrak->currency ?? 'IDR';
            $tanggalKontrak = $kontrak->tgl_kontrak ?? null;
            @endphp
            <tr>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    <strong>{{ ($contracts->currentPage() - 1) * $contracts->perPage() + $loop->iteration }}</strong>
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    <a href="{{ route('procurements.show', $procurement->procurement_id) }}"
                        style="color: #000; font-weight: 600; text-decoration: none;">
                        {{ $procurement->code_procurement }}
                    </a>
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    {{ Str::limit($procurement->name_procurement, 40) }}
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @if ($vendor)
                    {{ $vendor->name_vendor }}
                    @else
                    <span style="color: #999;">-</span>
                    @endif
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @if ($nilaiKontrak > 0)
                    {{ number_format($nilaiKontrak, 0, ',', '.') }}
                    @else
                    <span style="color: #999;">-</span>
                    @endif
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    {{ $mataUang }}
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @if ($tanggalKontrak)
                    {{ \Carbon\Carbon::parse($tanggalKontrak)->format('d/m/Y') }}
                    @else
                    <span style="color: #999;">-</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="sc-pagination">
        {{ $contracts->links() }}
    </div>
    @else
    <div class="sc-empty">
        <div class="sc-empty-icon">📄</div>
        <p>Tidak ada data kontrak</p>
    </div>
    @endif
</div>