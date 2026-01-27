<!-- Filter Section -->
<form id="filter-form" method="GET" action="{{ route('supply-chain.dashboard') }}" class="sc-filters">
    <input type="hidden" name="tab" value="procurement">
    <div class="sc-filter-group">
        <input type="text"
            id="search-input"
            name="search"
            class="sc-filter-input"
            placeholder="Cari kode, nama, atau project..."
            value="{{ request('search') }}">

        <select name="priority" class="sc-filter-select">
            <option value="">Semua Prioritas</option>
            <option value="tinggi" {{ request('priority') === 'tinggi' ? 'selected' : '' }}>Tinggi</option>
            <option value="sedang" {{ request('priority') === 'sedang' ? 'selected' : '' }}>Sedang</option>
            <option value="rendah" {{ request('priority') === 'rendah' ? 'selected' : '' }}>Rendah</option>
        </select>

        <select name="status" class="sc-filter-select">
            <option value="">Semua Status</option>
            <option value="belum_ada_vendor" {{ request('status') === 'belum_ada_vendor' ? 'selected' : '' }}>Belum ada vendor</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Proses</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
        </select>

        <button type="button"
            onclick="document.getElementById('filter-form').reset(); document.getElementById('filter-form').submit();"
            class="sc-btn-primary">
            <i class="bi bi-arrow-clockwise"></i> Reset
        </button>

        <a href="{{ route('procurements.create') }}" class="sc-btn-primary" style="text-decoration: none; margin-left: 10px;">
            <i class="bi bi-plus-circle"></i> Tambah
        </a>
    </div>
</form>

<!-- Table -->
<h4 style="margin-bottom: 16px; color: #000;">Daftar Pengadaan</h4>
<div class="dashboard-table-wrapper">
    @if($procurements->count() > 0)
    <table class="dashboard-table">
        <thead>
            <tr>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Project</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kode</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nama Pengadaan</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Department</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tgl Mulai</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tgl Selesai</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Prioritas</th>
                <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($procurements as $procurement)
            @php
            $vendor = $procurement->requestProcurements->first()?->vendor;
            @endphp
            <tr>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    <strong>{{ ($procurements->currentPage() - 1) * $procurements->perPage() + $loop->iteration }}</strong>
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    <strong>{{ $procurement->project->project_code ?? '-' }}</strong>
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
                    {{ $procurement->department->department_name ?? '-' }}
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @if ($vendor)
                    <span class="sc-badge sc-badge-success">
                        {{ $vendor->name_vendor }}
                    </span>
                    @else
                    <span class="sc-badge sc-badge-warning">
                        Belum dipilih
                    </span>
                    @endif
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    {{ $procurement->start_date->format('d/m/Y') }}
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    {{ $procurement->end_date->format('d/m/Y') }}
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @php
                    $priorityClass = match(strtolower($procurement->priority)) {
                    'tinggi' => 'sc-badge-danger',
                    'sedang' => 'sc-badge-warning',
                    'rendah' => 'sc-badge-info',
                    default => 'sc-badge-info',
                    };
                    @endphp
                    <span class="sc-badge {{ $priorityClass }}">
                        {{ ucfirst($procurement->priority) }}
                    </span>
                </td>
                <td style="padding: 12px 8px; text-align: center; color: #000;">
                    @php
                    $status = $procurement->status_procurement ?? 'unknown';
                    $statusClass = match($status) {
                    'in_progress' => 'sc-badge-warning',
                    'completed' => 'sc-badge-success',
                    'cancelled' => 'sc-badge-danger',
                    default => 'sc-badge-info',
                    };
                    $statusLabel = match($status) {
                    'in_progress' => 'Dalam Proses',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                    default => 'Unknown',
                    };
                    @endphp
                    <span class="sc-badge {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="sc-pagination">
        {{ $procurements->links() }}
    </div>
    @else
    <div class="sc-empty">
        <div class="sc-empty-icon">📦</div>
        <p>Tidak ada data pengadaan</p>
    </div>
    @endif
</div>