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
                @if($currentCheckpointSequence == 7 && $jaminans->count() > 0)
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
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Jenis Pembayaran</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Advance Payment Guarantee</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Performance Bond</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Warranty Bond</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Target Terbit</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Realisasi Terbit</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Expiry Date</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    /**
                    * HITUNG KONDISI TERLEBIH DAHULU
                    */
                    
                    // Vendor yang VALID untuk Jaminan Pembayaran (dari Inquiry & Quotation)
                    $jaminanVendors = collect($inquiryQuotations ?? [])
                        ->map(fn ($iq) => $iq->vendor)
                        ->filter()
                        ->unique('id_vendor')
                        ->values();

                    // Pembayaran yang BUKAN SKBDN (karena SKBDN tidak memerlukan jaminan)
                    $pembayaranNonSkbdn = $pembayarans->filter(fn ($p) => $p->payment_type !== 'SKBDN')->values();
                    
                    // Vendor yang memiliki pembayaran Non-SKBDN
                    $vendorWithNonSkbdnPayment = $pembayaranNonSkbdn->pluck('vendor_id')->unique()->values();

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

                            {{-- Jenis Pembayaran --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @php
                                $paymentType = $pembayarans->where('vendor_id', $jaminan->vendor_id)->first()?->payment_type ?? '-';
                                @endphp
                                {{ $paymentType }}
                            </td>

                            {{-- Advance Payment Guarantee --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->advance_guarantee ? '✔' : '-' }}</td>

                            {{-- Performance Bond --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->performance_bond ? '✔' : '-' }}</td>

                            {{-- Warranty Bond --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->warranty_bond ? '✔' : '-' }}</td>

                            {{-- Target Terbit --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->target_terbit?->format('d/m/Y') ?? '-' }}</td>

                            {{-- Realisasi Terbit --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->realisasi_terbit?->format('d/m/Y') ?? '-' }}</td>

                            {{-- Expiry Date --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $jaminan->expiry_date?->format('d/m/Y') ?? '-' }}</td>

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

                    {{-- ✅ EMPTY STATE DENGAN CREATE BUTTON (HANYA SAAT CHECKPOINT 7 & TIDAK ADA JAMINAN & ADA PEMBAYARAN NON-SKBDN) --}}
                    @if($jaminanCount == 0 && $currentCheckpointSequence == 7 && $vendorWithNonSkbdnPayment->count() > 0)
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 12px 8px;">
                            Belum ada data Jaminan Pembayaran
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateJaminan">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endif

                    {{-- ✅ EMPTY STATE TANPA BUTTON (JIKA TIDAK ADA PEMBAYARAN NON-SKBDN) --}}
                    @if($jaminanCount == 0 && $vendorWithNonSkbdnPayment->count() == 0)
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding: 12px 8px;">
                            Tidak ada jaminan pembayaran (hanya pembayaran tipe SKBDN tidak memerlukan jaminan)
                        </td>
                    </tr>
                    @endif

                    {{-- ✅ ROW CREATE (HANYA SAAT CHECKPOINT 7 & ADA JAMINAN & ADA PEMBAYARAN NON-SKBDN) --}}
                    @if($jaminanCount > 0 && $currentCheckpointSequence == 7 && $vendorWithNonSkbdnPayment->count() > 0)
                    <tr>
                        <td colspan="9"></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateJaminan">
                                Create
                            </button>
                        </td>
                    </tr>
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
                    @method('PUT')

                    <div class="modal-body row g-3">
                        @php
                        $jaminanVendors = collect($inquiryQuotations ?? [])
                            ->map(fn ($iq) => $iq->vendor)
                            ->filter()
                            ->unique('id_vendor')
                            ->values();
                        @endphp

                        {{-- Vendor --}}
                        <div class="col-md-6">
                            <label class="form-label">Vendor *</label>
                            <select name="vendor_id" class="form-select" required>
                                @foreach($jaminanVendors as $vendor)
                                <option value="{{ $vendor->id_vendor }}"
                                    @selected($vendor->id_vendor == $jaminan->vendor_id)>
                                    {{ $vendor->name_vendor }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Jenis Pembayaran (Display Only) --}}
                        <div class="col-md-6">
                            <label class="form-label">Jenis Pembayaran</label>
                            <input type="text" class="form-control" disabled
                                value="@php echo $pembayarans->where('vendor_id', $jaminan->vendor_id)->first()?->payment_type ?? '-'; @endphp">
                        </div>

                        {{-- Advance Payment Guarantee --}}
                        <div class="col-md-4">
                            <label class="form-label">Advance Payment Guarantee</label>
                            <input type="checkbox" name="advance_guarantee" value="1"
                                @checked($jaminan->advance_guarantee)>
                        </div>

                        {{-- Performance Bond --}}
                        <div class="col-md-4">
                            <label class="form-label">Performance Bond</label>
                            <input type="checkbox" name="performance_bond" value="1"
                                @checked($jaminan->performance_bond)>
                        </div>

                        {{-- Warranty Bond --}}
                        <div class="col-md-4">
                            <label class="form-label">Warranty Bond</label>
                            <input type="checkbox" name="warranty_bond" value="1"
                                @checked($jaminan->warranty_bond)>
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
{{-- MODAL CREATE (LUAR TABLE) --}}
{{-- ============================================ --}}
@if($currentCheckpointSequence == 7 && $vendorWithNonSkbdnPayment->count() > 0)
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
                    @php
                    $jaminanVendors = collect($inquiryQuotations ?? [])
                        ->map(fn ($iq) => $iq->vendor)
                        ->filter()
                        ->unique('id_vendor')
                        ->values();
                    @endphp

                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-select" required id="vendorJaminanSelect">
                            <option value="" disabled selected>-- Pilih Vendor --</option>
                            @if($jaminanVendors->count() > 0)
                                @foreach($jaminanVendors as $vendor)
                                @php
                                // Filter vendor yang memiliki pembayaran Non-SKBDN
                                $hasNonSkbdnPayment = $pembayaranNonSkbdn->where('vendor_id', $vendor->id_vendor)->count() > 0;
                                @endphp
                                @if($hasNonSkbdnPayment)
                                    <option value="{{ $vendor->id_vendor }}">
                                        {{ $vendor->name_vendor }}
                                    </option>
                                @endif
                                @endforeach
                            @else
                                <option disabled>
                                    Tidak ada vendor dari Inquiry & Quotation
                                </option>
                            @endif
                        </select>
                        <small style="color:#666;">Hanya vendor dengan pembayaran L/C atau TT</small>
                    </div>

                    {{-- Jenis Pembayaran (Display Only) --}}
                    <div class="col-md-6">
                        <label class="form-label">Jenis Pembayaran</label>
                        <input type="text" class="form-control" disabled id="paymentTypeDisplay" placeholder="-">
                    </div>

                    {{-- Advance Payment Guarantee --}}
                    <div class="col-md-4">
                        <label class="form-label">Advance Payment Guarantee</label>
                        <input type="checkbox" name="advance_guarantee" value="1">
                    </div>

                    {{-- Performance Bond --}}
                    <div class="col-md-4">
                        <label class="form-label">Performance Bond</label>
                        <input type="checkbox" name="performance_bond" value="1">
                    </div>

                    {{-- Warranty Bond --}}
                    <div class="col-md-4">
                        <label class="form-label">Warranty Bond</label>
                        <input type="checkbox" name="warranty_bond" value="1">
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