<div id="negotiation">
    <h5 class="section-title">Negotiation</h5>

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
                @if($currentCheckpointSequence==4 && count($negotiations)>0)
                <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="from_checkpoint" value="4">
                    <button class="btn btn-sm btn-action-simpan">
                        <i class="bi bi-box-arrow-down"></i>Simpan
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
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">HPS</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Budget</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Harga Final</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Kirim</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Terima</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Lead Time</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Deviasi vs HPS</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Deviasi vs Budget</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Note</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    /**
                    * HITUNG KONDISI TERLEBIH DAHULU
                    */
                    
                    // Vendor yang VALID untuk Negotiation (dari Inquiry & Quotation)
                    $negotiationVendors = collect($inquiryQuotations ?? [])
                        ->map(fn ($iq) => $iq->vendor)
                        ->filter()
                        ->unique('id_vendor')
                        ->values();

                    // Total negotiations
                    $negotiationCount = $negotiations->count();
                    @endphp

                    {{-- ✅ TAMPILKAN NEGOTIATION ITEMS YANG SUDAH ADA --}}
                    @if($negotiationCount > 0)
                        @foreach($negotiations as $neg)
                        <tr>
                            {{-- No --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $loop->iteration }}</td>

                            {{-- Vendor --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->vendor->name_vendor ?? '-' }}</td>

                            {{-- HPS --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @if($neg->hps)
                                {{ number_format($neg->hps,0,',','.') }} {{ $neg->currency_hps }}
                                @else
                                -
                                @endif
                            </td>

                            {{-- Budget --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @if($neg->budget)
                                {{ number_format($neg->budget,0,',','.') }} {{ $neg->currency_budget }}
                                @else
                                -
                                @endif
                            </td>

                            {{-- Harga Final --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @if($neg->harga_final)
                                {{ number_format($neg->harga_final,0,',','.') }} {{ $neg->currency_harga_final }}
                                @else
                                -
                                @endif
                            </td>

                            {{-- Tanggal Kirim --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->tanggal_kirim?->format('d/m/Y') ?? '-' }}</td>

                            {{-- Tanggal Terima --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->tanggal_terima?->format('d/m/Y') ?? '-' }}</td>

                            {{-- Lead Time --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->lead_time ?? '-' }}</td>

                            {{-- Deviasi vs HPS --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @if(!is_null($neg->deviasi_hps))
                                @php
                                $isPositive = $neg->deviasi_hps > 0;
                                $isNegative = $neg->deviasi_hps < 0;
                                @endphp
                                <span class="{{ $isPositive || $neg->deviasi_hps == 0 ? 'text-success' : 'text-danger' }}">
                                    @if($isPositive)
                                    <span class="me-1">▼</span>
                                    @elseif($isNegative)
                                    <span class="me-1">▲</span>
                                    @endif
                                    {{ number_format(abs($neg->deviasi_hps), 0, ',', '.') }} {{ $neg->currency_hps }}
                                </span>
                                @else
                                -
                                @endif
                            </td>

                            {{-- Deviasi vs Budget --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @if(!is_null($neg->deviasi_budget))
                                @php
                                $isPositive = $neg->deviasi_budget > 0;
                                $isNegative = $neg->deviasi_budget < 0;
                                @endphp
                                <span class="{{ $isPositive || $neg->deviasi_budget == 0 ? 'text-success' : 'text-danger' }}">
                                    @if($isPositive)
                                    <span class="me-1">▼</span>
                                    @elseif($isNegative)
                                    <span class="me-1">▲</span>
                                    @endif
                                    {{ number_format(abs($neg->deviasi_budget), 0, ',', '.') }} {{ $neg->currency_hps }}
                                </span>
                                @else
                                -
                                @endif
                            </td>

                            {{-- Note --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->notes ?? '-' }}</td>

                            {{-- Aksi --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                <button class="btn btn-sm btn-action-edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditNeg{{ $neg->negotiation_id }}">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- ✅ EMPTY STATE DENGAN CREATE BUTTON (HANYA SAAT CHECKPOINT 4 & TIDAK ADA NEGOTIATION) --}}
                    @if($negotiationCount == 0 && $currentCheckpointSequence == 4)
                    <tr>
                        <td colspan="11" class="text-center text-muted" style="padding: 12px 8px;">
                            Belum ada Negotiation
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateNeg">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endif

                    {{-- ✅ ROW CREATE (HANYA SAAT CHECKPOINT 4 & ADA NEGOTIATION) --}}
                    @if($negotiationCount > 0 && $currentCheckpointSequence == 4)
                    <tr>
                        <td colspan="11"></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateNeg">
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
@if($negotiationCount > 0)
    @foreach($negotiations as $neg)
    <div class="modal fade" id="modalEditNeg{{ $neg->negotiation_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Negotiation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('negotiation.update', $neg->negotiation_id) }}">
                    @csrf
                    <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                    <div class="modal-body row g-3">
                        @php
                        $negotiationVendors = collect($inquiryQuotations ?? [])
                            ->map(fn ($iq) => $iq->vendor)
                            ->filter()
                            ->unique('id_vendor')
                            ->values();
                        @endphp

                        {{-- Vendor --}}
                        <div class="col-md-6">
                            <label class="form-label">Vendor *</label>
                            <select name="vendor_id" class="form-select" required>
                                @foreach($negotiationVendors as $vendor)
                                <option value="{{ $vendor->id_vendor }}"
                                    @selected($vendor->id_vendor == $neg->vendor_id)>
                                    {{ $vendor->name_vendor }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- HPS --}}
                        <div class="col-md-6">
                            <label class="form-label">HPS</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" id="dropdownCurrencyHps{{ $neg->negotiation_id }}">
                                    {{ $neg->currency_hps ?? 'IDR' }}
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                    <li>
                                        <a class="dropdown-item" onclick="selectCurrencyEditNegotiation('Hps', '{{ $cur }}', '{{ $neg->negotiation_id }}')">
                                            {{ $cur }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                <input type="text" name="hps" class="form-control currency-input"
                                    value="{{ $neg->hps }}">
                                <input type="hidden" name="currency_hps" id="currencyEditHps{{ $neg->negotiation_id }}"
                                    value="{{ $neg->currency_hps }}">
                            </div>
                        </div>

                        {{-- Budget --}}
                        <div class="col-md-6">
                            <label class="form-label">Budget</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" id="dropdownCurrencyBudget{{ $neg->negotiation_id }}">
                                    {{ $neg->currency_budget ?? 'IDR' }}
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                    <li>
                                        <a class="dropdown-item" onclick="selectCurrencyEditNegotiation('Budget', '{{ $cur }}', '{{ $neg->negotiation_id }}')">
                                            {{ $cur }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                <input type="text" name="budget" class="form-control currency-input"
                                    value="{{ $neg->budget }}">
                                <input type="hidden" name="currency_budget" id="currencyEditBudget{{ $neg->negotiation_id }}"
                                    value="{{ $neg->currency_budget }}">
                            </div>
                        </div>

                        {{-- Harga Final --}}
                        <div class="col-md-6">
                            <label class="form-label">Harga Final</label>
                            <div class="input-group">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" id="dropdownCurrencyHargaFinal{{ $neg->negotiation_id }}">
                                    {{ $neg->currency_harga_final ?? 'IDR' }}
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                    <li>
                                        <a class="dropdown-item" onclick="selectCurrencyEditNegotiation('HargaFinal', '{{ $cur }}', '{{ $neg->negotiation_id }}')">
                                            {{ $cur }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                <input type="text" name="harga_final" class="form-control currency-input"
                                    value="{{ $neg->harga_final }}">
                                <input type="hidden" name="currency_harga_final" id="currencyEditHargaFinal{{ $neg->negotiation_id }}"
                                    value="{{ $neg->currency_harga_final }}">
                            </div>
                        </div>

                        {{-- Tanggal Kirim --}}
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Kirim</label>
                            <input type="date" name="tanggal_kirim" class="form-control"
                                value="{{ $neg->tanggal_kirim?->format('Y-m-d') }}">
                        </div>

                        {{-- Tanggal Terima --}}
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Terima</label>
                            <input type="date" name="tanggal_terima" class="form-control"
                                value="{{ $neg->tanggal_terima?->format('Y-m-d') }}">
                        </div>

                        {{-- Lead Time --}}
                        <div class="col-md-6">
                            <label class="form-label">Lead Time</label>
                            <input type="text" name="lead_time" class="form-control"
                                value="{{ $neg->lead_time }}">
                        </div>

                        {{-- Note --}}
                        <div class="col-md-6">
                            <label class="form-label">Note</label>
                            <textarea name="notes" class="form-control">{{ $neg->notes }}</textarea>
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
@if($currentCheckpointSequence == 4)
<div class="modal fade" id="modalCreateNeg" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Negotiation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('negotiation.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">
                    @php
                    $negotiationVendors = collect($inquiryQuotations ?? [])
                        ->map(fn ($iq) => $iq->vendor)
                        ->filter()
                        ->unique('id_vendor')
                        ->values();
                    @endphp

                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Vendor --</option>
                            @if($negotiationVendors->count() > 0)
                                @foreach($negotiationVendors as $vendor)
                                <option value="{{ $vendor->id_vendor }}">
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

                    {{-- HPS --}}
                    <div class="col-md-6">
                        <label class="form-label">HPS</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                id="dropdownCurrencyHpsCreate" data-bs-toggle="dropdown">
                                IDR
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <li>
                                    <a class="dropdown-item" onclick="selectCurrencyCreateNegotiation('Hps','{{ $cur }}')">
                                        {{ $cur }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <input type="text" name="hps" class="form-control currency-input" placeholder="0">
                            <input type="hidden" name="currency_hps" id="currencyCreateHps" value="IDR">
                        </div>
                    </div>

                    {{-- Budget --}}
                    <div class="col-md-6">
                        <label class="form-label">Budget</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                id="dropdownCurrencyBudgetCreate" data-bs-toggle="dropdown">
                                IDR
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <li>
                                    <a class="dropdown-item" onclick="selectCurrencyCreateNegotiation('Budget','{{ $cur }}')">
                                        {{ $cur }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <input type="text" name="budget" class="form-control currency-input" placeholder="0">
                            <input type="hidden" name="currency_budget" id="currencyCreateBudget" value="IDR">
                        </div>
                    </div>

                    {{-- Harga Final --}}
                    <div class="col-md-6">
                        <label class="form-label">Harga Final</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                id="dropdownCurrencyHargaFinalCreate" data-bs-toggle="dropdown">
                                IDR
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <li>
                                    <a class="dropdown-item" onclick="selectCurrencyCreateNegotiation('HargaFinal','{{ $cur }}')">
                                        {{ $cur }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                            <input type="text" name="harga_final" class="form-control currency-input" placeholder="0">
                            <input type="hidden" name="currency_harga_final" id="currencyCreateHargaFinal" value="IDR">
                        </div>
                    </div>

                    {{-- Tanggal Kirim --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Kirim</label>
                        <input type="date" name="tanggal_kirim" class="form-control">
                    </div>

                    {{-- Tanggal Terima --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Terima</label>
                        <input type="date" name="tanggal_terima" class="form-control">
                    </div>

                    {{-- Lead Time --}}
                    <div class="col-md-6">
                        <label class="form-label">Lead Time</label>
                        <input type="text" name="lead_time" class="form-control" placeholder="ex: 2 minggu">
                    </div>

                    {{-- Note --}}
                    <div class="col-md-6">
                        <label class="form-label">Note</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
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