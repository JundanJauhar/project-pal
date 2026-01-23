<div id="pengesahan-kontrak">
    <h5 class="section-title">Pengesahan Kontrak</h5>

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
                @if($currentCheckpointSequence == 6 && $pengesahanKontraks->count() > 0)
                <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="from_checkpoint" value="6">
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

                    // Vendor yang VALID untuk Pengesahan Kontrak (dari Inquiry & Quotation)
                    $pkVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();

                    // Total pengesahan kontraks
                    $pkCount = $pengesahanKontraks->count();
                    @endphp

                    {{-- ✅ TAMPILKAN PENGESAHAN KONTRAK ITEMS YANG SUDAH ADA --}}
                    @if($pkCount > 0)
                    @foreach($pengesahanKontraks as $pk)
                    <tr>
                        {{-- No --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $loop->iteration }}</td>

                        {{-- Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pk->vendor?->name_vendor ?? '-' }}</td>

                        {{-- Nilai --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($pk->nilai)
                            {{ number_format($pk->nilai, 0, ',', '.') }} {{ $pk->currency }}
                            @else
                            -
                            @endif
                        </td>

                        {{-- Kadep → Kadiv --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pk->tgl_kadep_to_kadiv?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Kadiv → CTO --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pk->tgl_kadiv_to_cto?->format('d/m/Y') ?? '-' }}</td>

                        {{-- CTO → CEO --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pk->tgl_cto_to_ceo?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Tgl ACC --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pk->tgl_acc?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Remarks --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pk->remarks ?? '-' }}</td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <button class="btn btn-sm btn-action-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditPK{{ $pk->pengesahan_id }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- ✅ EMPTY STATE DENGAN CREATE BUTTON (HANYA SAAT CHECKPOINT 6 & TIDAK ADA PK) --}}
                    @if($pkCount == 0 && $currentCheckpointSequence == 6)
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 12px 8px;">
                            Belum ada Kontrak yang disahkan
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreatePK">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endif

                    {{-- ✅ ROW CREATE (HANYA SAAT CHECKPOINT 6 & ADA PK) --}}
                    @if($pkCount > 0 && $currentCheckpointSequence == 6)
                    <tr>
                        <td colspan="8"></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreatePK">
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
@if($pkCount > 0)
@foreach($pengesahanKontraks as $pk)
<div class="modal fade" id="modalEditPK{{ $pk->pengesahan_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pengesahan Kontrak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('pengesahan-kontrak.update', $pk->pengesahan_id) }}">
                @csrf

                <div class="modal-body row g-3">
                    @php
                    $pkVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();
                    @endphp

                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-select" required>
                            @foreach($pkVendors as $vendor)
                            <option value="{{ $vendor->id_vendor }}"
                                @selected($vendor->id_vendor == $pk->vendor_id)>
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
                                data-bs-toggle="dropdown" id="dropdownCurrencyEdit{{ $pk->pengesahan_id }}">
                                {{ $pk->currency ?? 'IDR' }}
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <li>
                                    <a class="dropdown-item" onclick="selectCurrencyEditPK('{{ $cur }}', '{{ $pk->pengesahan_id }}')">
                                        {{ $cur }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            {{-- DISPLAY --}}
                            <input type="text"
                                class="form-control currency-input"
                                data-raw-target="nilaiPKRaw{{ $pk->pengesahan_id }}"
                                value="{{ number_format($pk->nilai ?? 0, 0, ',', '.') }}"
                                readonly>

                            {{-- RAW --}}
                            <input type="hidden"
                                name="nilai"
                                id="nilaiPKRaw{{ $pk->pengesahan_id }}"
                                value="{{ $pk->nilai ?? '' }}">

                            {{-- CURRENCY --}}
                            <input type="hidden"
                                name="currency"
                                id="currencyEditPK{{ $pk->pengesahan_id }}"
                                value="{{ $pk->currency }}">
                        </div>
                    </div>

                    {{-- Kadep → Kadiv --}}
                    <div class="col-md-6">
                        <label class="form-label">Kadep → Kadiv</label>
                        <input type="date" name="tgl_kadep_to_kadiv" class="form-control"
                            value="{{ $pk->tgl_kadep_to_kadiv?->format('Y-m-d') }}">
                    </div>

                    {{-- Kadiv → CTO --}}
                    <div class="col-md-6">
                        <label class="form-label">Kadiv → CTO</label>
                        <input type="date" name="tgl_kadiv_to_cto" class="form-control"
                            value="{{ $pk->tgl_kadiv_to_cto?->format('Y-m-d') }}">
                    </div>

                    {{-- CTO → CEO --}}
                    <div class="col-md-6">
                        <label class="form-label">CTO → CEO</label>
                        <input type="date" name="tgl_cto_to_ceo" class="form-control"
                            value="{{ $pk->tgl_cto_to_ceo?->format('Y-m-d') }}">
                    </div>

                    {{-- Tgl ACC --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal ACC</label>
                        <input type="date" name="tgl_acc" class="form-control"
                            value="{{ $pk->tgl_acc?->format('Y-m-d') }}">
                    </div>

                    {{-- Remarks --}}
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control">{{ $pk->remarks }}</textarea>
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
@if($currentCheckpointSequence == 6)
<div class="modal fade" id="modalCreatePK" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Pengesahan Kontrak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('pengesahan-kontrak.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">
                    @php
                    $pkVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();
                    @endphp

                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" id="vendorSelectPK" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Vendor --</option>
                            @if($pkVendors->count() > 0)
                            @foreach($pkVendors as $vendor)
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
                            <span class="input-group-text" id="currencyCreatePKDisplay">
                                IDR
                            </span>
                            <input type="text" class="form-control" placeholder="0" disabled id="nilaiPKDisplay">
                        </div>
                        <input type="hidden" name="nilai" id="nilaiPK" value="">
                        <input type="hidden" name="currency" id="currencyCreatePK" value="IDR">
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