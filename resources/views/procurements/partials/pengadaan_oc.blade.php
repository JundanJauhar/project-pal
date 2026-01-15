<div id="pengadaan-oc">
    <h5 class="section-title">Pengadaan OC</h5>

    {{-- Alert Error (LUAR TABLE) --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="dashboard-table-wrapper">
        <div class="table-responsive">
            {{-- Button Save Checkpoint (LUAR TABLE) --}}
            <div class="btn-simpan-wrapper">
                @if($currentCheckpointSequence == 5 && $pengadaanOcs->count() > 0)
                <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="from_checkpoint" value="5">
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
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nilai</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kadep → Kadiv</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Kadiv → CTO</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">CTO → CEO</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tgl ACC</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Remarks</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    /**
                    * HITUNG KONDISI TERLEBIH DAHULU
                    */

                    // Vendor yang VALID untuk Pengadaan OC (dari Inquiry & Quotation)
                    $poVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();

                    // Total pengadaan OCs
                    $poCount = $pengadaanOcs->count();
                    @endphp

                    {{-- ✅ TAMPILKAN PENGADAAN OC ITEMS YANG SUDAH ADA --}}
                    @if($poCount > 0)
                    @foreach($pengadaanOcs as $po)
                    <tr>
                        {{-- No --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $loop->iteration }}</td>

                        {{-- Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->vendor?->name_vendor ?? '-' }}</td>

                        {{-- Nilai --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($po->nilai)
                            {{ number_format($po->nilai, 0, ',', '.') }} {{ $po->currency }}
                            @else
                            -
                            @endif
                        </td>

                        {{-- Kadep → Kadiv --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->tgl_kadep_to_kadiv?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Kadiv → CTO --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->tgl_kadiv_to_cto?->format('d/m/Y') ?? '-' }}</td>

                        {{-- CTO → CEO --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->tgl_cto_to_ceo?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Tgl ACC --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->tgl_acc?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Remarks --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->remarks ?? '-' }}</td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <button class="btn btn-sm btn-action-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditPO{{ $po->pengadaan_oc_id }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- ✅ EMPTY STATE DENGAN CREATE BUTTON (HANYA SAAT CHECKPOINT 5 & TIDAK ADA PO) --}}
                    @if($poCount == 0 && $currentCheckpointSequence == 5)
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 12px 8px;">
                            Belum ada Pengadaan OC
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreatePO">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endif

                    {{-- ✅ ROW CREATE (HANYA SAAT CHECKPOINT 5 & ADA PO) --}}
                    @if($poCount > 0 && $currentCheckpointSequence == 5)
                    <tr>
                        <td colspan="8"></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreatePO">
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
@if($poCount > 0)
@foreach($pengadaanOcs as $po)
<div class="modal fade" id="modalEditPO{{ $po->pengadaan_oc_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pengadaan OC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('pengadaan-oc.update', $po->pengadaan_oc_id) }}">
                @csrf

                <div class="modal-body row g-3">
                    @php
                    $poVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();
                    @endphp

                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-select" required>
                            @foreach($poVendors as $vendor)
                            <option value="{{ $vendor->id_vendor }}"
                                @selected($vendor->id_vendor == $po->vendor_id)>
                                {{ $vendor->name_vendor }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nilai --}}
                    <div class="col-md-6">
                        <label class="form-label">Nilai</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" id="dropdownCurrencyEdit{{ $po->pengadaan_oc_id }}">
                                {{ $po->currency ?? 'IDR' }}
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <li>
                                    <a class="dropdown-item" onclick="selectCurrencyEditPO('{{ $cur }}', '{{ $po->pengadaan_oc_id }}')">
                                        {{ $cur }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <input type="text" name="nilai" class="form-control currency-input"
                                value="{{ $po->nilai }}">
                            <input type="hidden" name="currency" id="currencyEditPO{{ $po->pengadaan_oc_id }}"
                                value="{{ $po->currency }}">
                        </div>
                    </div>

                    {{-- Kadep → Kadiv --}}
                    <div class="col-md-6">
                        <label class="form-label">Kadep → Kadiv</label>
                        <input type="date" name="tgl_kadep_to_kadiv" class="form-control"
                            value="{{ $po->tgl_kadep_to_kadiv?->format('Y-m-d') }}">
                    </div>

                    {{-- Kadiv → CTO --}}
                    <div class="col-md-6">
                        <label class="form-label">Kadiv → CTO</label>
                        <input type="date" name="tgl_kadiv_to_cto" class="form-control"
                            value="{{ $po->tgl_kadiv_to_cto?->format('Y-m-d') }}">
                    </div>

                    {{-- CTO → CEO --}}
                    <div class="col-md-6">
                        <label class="form-label">CTO → CEO</label>
                        <input type="date" name="tgl_cto_to_ceo" class="form-control"
                            value="{{ $po->tgl_cto_to_ceo?->format('Y-m-d') }}">
                    </div>

                    {{-- Tgl ACC --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal ACC</label>
                        <input type="date" name="tgl_acc" class="form-control"
                            value="{{ $po->tgl_acc?->format('Y-m-d') }}">
                    </div>

                    {{-- Remarks --}}
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control">{{ $po->remarks }}</textarea>
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
@if($currentCheckpointSequence == 5)
<div class="modal fade" id="modalCreatePO" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Pengadaan OC</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('pengadaan-oc.store', $procurement->procurement_id) }}">
                @csrf

                <div class="modal-body row g-3">
                    @php
                    $poVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();
                    @endphp

                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" id="vendorSelectPO" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Vendor --</option>
                            @if($poVendors->count() > 0)
                            @foreach($poVendors as $vendor)
                            @php
                            $neg = $negotiations->firstWhere('vendor_id', $vendor->id_vendor);
                            @endphp
                            <option value="{{ $vendor->id_vendor }}"
                                data-harga="{{ $neg?->harga_final }}"
                                data-currency="{{ $neg?->currency_harga_final ?? 'IDR' }}">
                                {{ $vendor->name_vendor }}
                            </option>
                            @endforeach
                            @else
                            <option disabled>
                                Tidak ada vendor dari Inquiry & Quotation
                            </option>
                            @endif
                        </select>
                        <small style="color:#666;">Vendor berasal dari Inquiry & Quotation</small>
                    </div>

                    {{-- Nilai (Display Only) --}}
                    <div class="col-md-6">
                        <label class="form-label">Nilai</label>
                        <div class="input-group">
                            <span class="input-group-text" id="currencyCreatePODisplay">
                                IDR
                            </span>
                            <input type="text" class="form-control" placeholder="0" disabled id="nilaiPODisplay">
                        </div>
                        <input type="hidden" name="nilai" id="nilaiPO" value="">
                        <input type="hidden" name="currency" id="currencyCreatePO" value="IDR">
                    </div>

                    {{-- Kadep → Kadiv --}}
                    <div class="col-md-6">
                        <label class="form-label">Kadep → Kadiv</label>
                        <input type="date" name="tgl_kadep_to_kadiv" class="form-control">
                    </div>

                    {{-- Kadiv → CTO --}}
                    <div class="col-md-6">
                        <label class="form-label">Kadiv → CTO</label>
                        <input type="date" name="tgl_kadiv_to_cto" class="form-control">
                    </div>

                    {{-- CTO → CEO --}}
                    <div class="col-md-6">
                        <label class="form-label">CTO → CEO</label>
                        <input type="date" name="tgl_cto_to_ceo" class="form-control">
                    </div>

                    {{-- Tgl ACC --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal ACC</label>
                        <input type="date" name="tgl_acc" class="form-control">
                    </div>

                    {{-- Remarks --}}
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
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