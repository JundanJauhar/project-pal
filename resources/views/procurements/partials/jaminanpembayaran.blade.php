<div id="jaminan-pembayaran">
    <h5 class="section-title">Jaminan Pembayaran</h5>

    {{-- Alert Error (LUAR TABLE) --}}
    @if ($errors->jaminan->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->jaminan->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="dashboard-table-wrapper">
        <div class="table-responsive">
            {{-- Button Save Checkpoint (LUAR TABLE) --}}
            <div class="btn-simpan-wrapper">
                @if($currentCheckpointSequence == 7 && $pembayarans->count() > 0)
                <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="from_checkpoint" value="7">
                    <button class="btn btn-sm btn-action-simpan">
                        <i class="bi bi-box-arrow-down"></i> Simpan
                    </button>
                </form>
                @endif
            </div>

            {{-- TABLE --}}
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Jenis Jaminan</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Target Terbit</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Realisasi Terbit</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Expiry Date</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Link</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    // Total jaminans
                    $jaminanCount = $jaminans->count();
                    @endphp

                    {{-- ✅ TAMPILKAN JAMINAN PEMBAYARAN ITEMS YANG SUDAH ADA --}}
                    @if($jaminanCount > 0)
                    @foreach($jaminans as $jaminan)
                    <tr>
                        {{-- No --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $loop->iteration }}</td>

                        {{-- Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->vendor->name_vendor ?? '-' }}</td>

                        {{-- Jenis Jaminan --}}
                        <td class="text-center">
                            @switch($jaminan->jenis_jaminan)
                            @case('advance_payment_guarantee') Advance Payment Guarantee @break
                            @case('performance_bond') Performance Bond @break
                            @case('warranty_bond') Warranty Bond @break
                            @endswitch
                        </td>

                        {{-- Target Terbit --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->target_terbit?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Realisasi Terbit --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->realisasi_terbit?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Expiry Date --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->expiry_date?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Link --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($jaminan->link)
                            <a href="{{ $jaminan->link }}" target="_blank" style="color: #0066cc; text-decoration: underline; font-weight: 600;">Link</a>
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <button class="btn btn-sm btn-action-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditJaminan{{ $jaminan->jaminan_pembayaran_id }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- ✅ EMPTY STATE DENGAN CREATE BUTTON (TERINTEGRASI) --}}
                    @if($jaminanCount == 0)
                    <tr>
                        <td colspan="7" class="text-center text-muted" style="padding: 12px 8px;">
                            Tidak ada data Jaminan Pembayaran.
                        </td>
                        <td class="text-center">
                            @if($currentCheckpointSequence == 7 && $pembayarans->count() > 0)
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateJaminan">
                                Create
                            </button>
                            @endif
                        </td>
                    </tr>
                    @else
                    {{-- ✅ ROW CREATE (SAAT ADA DATA & CHECKPOINT 7) --}}
                    @if($currentCheckpointSequence == 7 && $pembayarans->count() > 0)
                    <tr>
                        <td colspan="7"></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateJaminan">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endif
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL EDIT (LUAR TABLE) --}}
{{-- ============================================ --}}
@if($jaminanCount > 0)
@foreach($jaminans as $jaminan)
<div class="modal fade" id="modalEditJaminan{{ $jaminan->jaminan_pembayaran_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jaminan Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('jaminan-pembayaran.update', $jaminan->jaminan_pembayaran_id) }}">
                @csrf
                <div class="modal-body row g-3">

                    {{-- VENDOR (DISPLAY ONLY - TIDAK BISA DIUBAH) --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor (Otomatis)</label>
                        <input type="text" class="form-control" disabled
                            value="{{ $jaminan->vendor->name_vendor ?? '-' }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Jenis Jaminan</label>
                        <select name="jenis_jaminan" class="form-select" required>
                            <option value="advance_payment_guarantee"
                                @selected($jaminan->jenis_jaminan === 'advance_payment_guarantee')>
                                Advance Payment Guarantee
                            </option>
                            <option value="performance_bond"
                                @selected($jaminan->jenis_jaminan === 'performance_bond')>
                                Performance Bond
                            </option>
                            <option value="warranty_bond"
                                @selected($jaminan->jenis_jaminan === 'warranty_bond')>
                                Warranty Bond
                            </option>
                        </select>
                    </div>

                    {{-- Target Terbit --}}
                    <div class="col-md-4">
                        <label class="form-label">Target Terbit</label>
                        <input type="date" name="target_terbit" class="form-control"
                            value="{{ $jaminan->target_terbit?->format('Y-m-d') }}">
                    </div>

                    {{-- Realisasi Terbit --}}
                    <div class="col-md-4">
                        <label class="form-label">Realisasi Terbit</label>
                        <input type="date" name="realisasi_terbit" class="form-control"
                            value="{{ $jaminan->realisasi_terbit?->format('Y-m-d') }}">
                    </div>

                    {{-- Expiry Date --}}
                    <div class="col-md-4">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control"
                            value="{{ $jaminan->expiry_date?->format('Y-m-d') }}">
                    </div>

                    {{-- Link --}}
                    <div class="col-md-12">
                        <label class="form-label">Link</label>
                        <input type="url" name="link" class="form-control"
                            value="{{ $jaminan->link }}">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-create">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif

{{-- ============================================ --}}
{{-- MODAL CREATE (LUAR TABLE - SELALU ADA SAAT CHECKPOINT 7) --}}
{{-- ============================================ --}}
@if($currentCheckpointSequence == 7 && $pembayarans->count() > 0)
<div class="modal fade" id="modalCreateJaminan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Jaminan Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('jaminan-pembayaran.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">

                    {{-- VENDOR SELECTION (DROPDOWN PEMBAYARAN YANG ADA) --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-select" id="vendorJaminanSelect" required>
                            <option value="" disabled selected>Pilih Vendor</option>
                            @foreach($pembayarans->unique('vendor_id') as $payment)
                            <option value="{{ $payment->vendor_id }}">
                                {{ $payment->vendor->name_vendor ?? '-' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- JENIS JAMINAN --}}
                    <div class="col-md-6">
                        <label class="form-label">Jenis Jaminan *</label>
                        <select name="jenis_jaminan" class="form-select" required>
                            <option value="" disabled selected>Pilih Jenis Jaminan</option>
                            <option value="advance_payment_guarantee">Advance Payment Guarantee</option>
                            <option value="performance_bond">Performance Bond</option>
                            <option value="warranty_bond">Warranty Bond</option>
                        </select>
                    </div>

                    {{-- Target Terbit --}}
                    <div class="col-md-4">
                        <label class="form-label">Target Terbit</label>
                        <input type="date" name="target_terbit" class="form-control">
                    </div>

                    {{-- Realisasi Terbit --}}
                    <div class="col-md-4">
                        <label class="form-label">Realisasi Terbit</label>
                        <input type="date" name="realisasi_terbit" class="form-control">
                    </div>

                    {{-- Expiry Date --}}
                    <div class="col-md-4">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
                    </div>

                    {{-- Link --}}
                    <div class="col-md-12">
                        <label class="form-label">Link</label>
                        <input type="url" name="link" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-create">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif