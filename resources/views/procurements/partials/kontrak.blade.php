<div id="kontrak">
    <h5 class="section-title">Kontrak</h5>

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
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No PO</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Item</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Kontrak</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Maker</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nilai</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Payment Term</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Incoterms</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">COO</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Warranty</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Link</th>
                        <th style="padding: 12px 8px; text-align: center; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    // Vendor yang VALID untuk Kontrak (dari Inquiry & Quotation)
                    $kontrakVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();

                    // Total kontraks
                    $kontrakCount = $kontraks->count();
                    @endphp

                    {{-- ✅ TAMPILKAN KONTRAK ITEMS YANG SUDAH ADA --}}
                    @if($kontrakCount > 0)
                    @foreach($kontraks as $kontrak)
                    <tr>
                        {{-- No --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $loop->iteration }}</td>

                        {{-- No PO --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->no_po ?? '-' }}</td>

                        {{-- Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->vendor?->name_vendor ?? '-' }}</td>

                        {{-- Item --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->item?->item_name ?? '-' }}</td>

                        {{-- Tanggal Kontrak --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->tgl_kontrak?->format('d/m/Y') ?? '-' }}</td>

                        {{-- Maker --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->maker ?? '-' }}</td>

                        {{-- Nilai --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($kontrak->nilai)
                            {{ $kontrak->currency }} {{ number_format($kontrak->nilai,0,',','.') }}
                            @else
                            -
                            @endif
                        </td>

                        {{-- Payment Term --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->payment_term ?? '-' }}</td>

                        {{-- Incoterms --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->incoterms ?? '-' }}</td>

                        {{-- COO --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->coo ?? '-' }}</td>

                        {{-- Warranty --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->warranty ?? '-' }}</td>

                        {{-- Link --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($kontrak->link)
                            <a href="{{ $kontrak->link }}" target="_blank" style="color: #0066cc; text-decoration: underline; font-weight: 600;">Link</a>
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <button class="btn btn-sm btn-action-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditKontrak{{ $kontrak->kontrak_id }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- ✅ ROW CREATE (SELALU TAMPIL SAAT CHECKPOINT 6) --}}
                    @if($currentCheckpointSequence == 6)
                    <tr>
                        <td colspan="12"></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateKontrak">
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
@if($kontrakCount > 0)
@foreach($kontraks as $kontrak)
<div class="modal fade" id="modalEditKontrak{{ $kontrak->kontrak_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Kontrak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('kontrak.update', $kontrak->kontrak_id) }}">
                @csrf

                <div class="modal-body row g-3">
                    @php
                    $kontrakVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();
                    @endphp

                    {{-- No PO --}}
                    <div class="col-md-6">
                        <label class="form-label">No PO</label>
                        <input type="text" name="no_po" class="form-control"
                            value="{{ $kontrak->no_po }}">
                    </div>

                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-select" required>
                            @foreach($kontrakVendors as $vendor)
                            <option value="{{ $vendor->id_vendor }}"
                                @selected($vendor->id_vendor == $kontrak->vendor_id)>
                                {{ $vendor->name_vendor }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Item --}}
                    <div class="col-md-6">
                        <label class="form-label">Item *</label>
                        <select name="item_id" class="form-select" required>
                            @foreach($procurement->requestProcurements as $request)
                            @foreach($request->items as $item)
                            <option value="{{ $item->item_id }}"
                                @selected($item->item_id == $kontrak->item_id)>
                                {{ $item->item_name }}
                            </option>
                            @endforeach
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Kontrak --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Kontrak</label>
                        <input type="date" name="tgl_kontrak" class="form-control"
                            value="{{ $kontrak->tgl_kontrak?->format('Y-m-d') }}">
                    </div>

                    {{-- Maker --}}
                    <div class="col-md-6">
                        <label class="form-label">Maker</label>
                        <input type="text" name="maker" class="form-control"
                            value="{{ $kontrak->maker }}">
                    </div>

                    {{-- Nilai --}}
                    <div class="col-md-6">
                        <label class="form-label">Nilai</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" id="dropdownCurrencyKontrak{{ $kontrak->kontrak_id }}">
                                {{ $kontrak->currency ?? 'IDR' }}
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <li>
                                    <a class="dropdown-item" onclick="selectCurrencyKontrak('{{ $cur }}', '{{ $kontrak->kontrak_id }}')">
                                        {{ $cur }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            {{-- DISPLAY --}}
                            <input type="text"
                                class="form-control currency-input"
                                data-raw-target="nilaiKontrakRaw{{ $kontrak->kontrak_id }}"
                                value="{{ number_format($kontrak->nilai ?? 0, 0, ',', '.') }}">

                            {{-- RAW --}}
                            <input type="hidden"
                                name="nilai"
                                id="nilaiKontrakRaw{{ $kontrak->kontrak_id }}"
                                value="{{ $kontrak->nilai ?? '' }}">

                            {{-- CURRENCY --}}
                            <input type="hidden"
                                name="currency"
                                id="currencyKontrakEdit{{ $kontrak->kontrak_id }}"
                                value="{{ $kontrak->currency }}">
                        </div>
                    </div>

                    {{-- Payment Term --}}
                    <div class="col-md-6">
                        <label class="form-label">Payment Term</label>
                        <input type="text" name="payment_term" class="form-control"
                            value="{{ $kontrak->payment_term }}">
                    </div>

                    {{-- Incoterms --}}
                    <div class="col-md-6">
                        <label class="form-label">Incoterms</label>
                        <input type="text" name="incoterms" class="form-control"
                            value="{{ $kontrak->incoterms }}">
                    </div>

                    {{-- COO --}}
                    <div class="col-md-6">
                        <label class="form-label">COO</label>
                        <input type="text" name="coo" class="form-control"
                            value="{{ $kontrak->coo }}">
                    </div>

                    {{-- Warranty --}}
                    <div class="col-md-6">
                        <label class="form-label">Warranty</label>
                        <input type="text" name="warranty" class="form-control"
                            value="{{ $kontrak->warranty }}">
                    </div>

                    {{-- Link --}}
                    <div class="col-6">
                        <label class="form-label">Link</label>
                        <input type="url" name="link" class="form-control"
                            value="{{ $kontrak->link }}">
                    </div>

                    {{-- Remarks --}}
                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control">{{ $kontrak->remarks }}</textarea>
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
<div class="modal fade" id="modalCreateKontrak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Kontrak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('kontrak.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">
                    @php
                    $kontrakVendors = collect($inquiryQuotations ?? [])
                    ->map(fn ($iq) => $iq->vendor)
                    ->filter()
                    ->unique('id_vendor')
                    ->values();

                    $kontrakAuto = $kontraks->first();
                    @endphp

                    {{-- No PO --}}
                    <div class="col-md-6">
                        <label class="form-label">No PO</label>
                        <input type="text" name="no_po" class="form-control">
                    </div>

                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor</label>
                        @if($kontrakAuto)
                            {{-- Mode UPDATE: Vendor readonly dari kontrak yang sudah ada --}}
                            <input type="text" class="form-control" 
                                value="{{ $kontrakAuto->vendor?->name_vendor ?? '-' }}" 
                                disabled>
                            <input type="hidden" name="vendor_id" value="{{ $kontrakAuto->vendor_id }}">
                        @else
                            {{-- Mode INSERT: Vendor bisa dipilih --}}
                            <select name="vendor_id" class="form-select" required>
                                <option value="" disabled selected>-- Pilih Vendor --</option>
                                @if($kontrakVendors->count() > 0)
                                @foreach($kontrakVendors as $vendor)
                                <option value="{{ $vendor->id_vendor }}">
                                    {{ $vendor->name_vendor }}
                                </option>
                                @endforeach
                                @else
                                <option disabled>Tidak ada vendor dari Inquiry & Quotation</option>
                                @endif
                            </select>
                        @endif
                    </div>

                    {{-- Item --}}
                    <div class="col-md-6">
                        <label class="form-label">Item *</label>
                        <select name="item_id" class="form-select" required>
                            <option value="" disabled selected>Pilih item</option>
                            @foreach($procurement->requestProcurements as $request)
                            @foreach($request->items as $item)
                            <option value="{{ $item->item_id }}">
                                {{ $item->item_name }}
                            </option>
                            @endforeach
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal Kontrak --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Kontrak</label>
                        <input type="date" name="tgl_kontrak" class="form-control">
                    </div>

                    {{-- Maker --}}
                    <div class="col-md-6">
                        <label class="form-label">Maker</label>
                        <input type="text" name="maker" class="form-control">
                    </div>

                    {{-- Nilai --}}
                    <div class="col-md-6">
                        <label class="form-label">Nilai</label>
                        <div class="input-group">
                            @if($kontrakAuto)
                                {{-- Mode UPDATE: Nilai readonly dari kontrak yang sudah ada --}}
                                <span class="input-group-text">{{ $kontrakAuto->currency ?? 'IDR' }}</span>
                                <input type="text" class="form-control" 
                                    value="{{ number_format($kontrakAuto->nilai ?? 0, 0, ',', '.') }}" 
                                    disabled>
                                {{-- Hidden input untuk memastikan nilai tidak dikirim --}}
                                <input type="hidden" name="nilai" value="{{ $kontrakAuto->nilai }}">
                                <input type="hidden" name="currency" value="{{ $kontrakAuto->currency }}">
                            @else
                                {{-- Mode INSERT: Nilai bisa diisi --}}
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                    id="dropdownCurrencyKontrakCreate" data-bs-toggle="dropdown">
                                    IDR
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                    <li>
                                        <a class="dropdown-item" onclick="selectCurrencyCreateKontrak('{{ $cur }}')">
                                            {{ $cur }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                {{-- DISPLAY --}}
                                <input type="text"
                                    class="form-control currency-input"
                                    data-raw-target="nilaiKontrakCreateRaw"
                                    placeholder="0">

                                {{-- RAW --}}
                                <input type="hidden"
                                    name="nilai"
                                    id="nilaiKontrakCreateRaw"
                                    value="">

                                {{-- CURRENCY --}}
                                <input type="hidden"
                                    name="currency"
                                    id="currencyCreateNilaiKontrak"
                                    value="IDR">
                            @endif
                        </div>
                    </div>

                    {{-- Payment Term --}}
                    <div class="col-md-6">
                        <label class="form-label">Payment Term</label>
                        <input type="text" name="payment_term" class="form-control">
                    </div>

                    {{-- Incoterms --}}
                    <div class="col-md-6">
                        <label class="form-label">Incoterms</label>
                        <input type="text" name="incoterms" class="form-control">
                    </div>

                    {{-- COO --}}
                    <div class="col-md-6">
                        <label class="form-label">COO</label>
                        <input type="text" name="coo" class="form-control">
                    </div>

                    {{-- Warranty --}}
                    <div class="col-md-6">
                        <label class="form-label">Warranty</label>
                        <input type="text" name="warranty" class="form-control">
                    </div>

                    {{-- Link --}}
                    <div class="col-6">
                        <label class="form-label">Link</label>
                        <input type="url" name="link" class="form-control">
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