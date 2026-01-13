<div id="inquiry">
<h5 class="section-title">Inquiry & Quotation</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">
        <div class="btn-simpan-wrapper">
            @if($currentCheckpointSequence==2 && count($inquiryQuotations)>0)
            <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                @csrf
                <input type="hidden" name="from_checkpoint" value="2">
                <button class="btn btn-sm btn-action-simpan">
                    <i class="bi bi-box-arrow-down"></i>Simpan
                </button>
            </form>
            @endif
        </div>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Inquiry</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Quotation</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Target Quotation</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Lead Time</th>
                    <th style="padding: 12px 8px; text-align: center; color: #000;">Nilai Harga</th>
                    <th style="padding: 12px 8px; text-align: center; color: #000;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @php $row = 1; @endphp
                @forelse($inquiryQuotations as $iq)
                <tr>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $row++ }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->vendor->name_vendor ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->tanggal_inquiry->format('d/m/Y') }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->tanggal_quotation ? $iq->tanggal_quotation->format('d/m/Y') : '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->target_quotation ? $iq->target_quotation->format('d/m/Y') : '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->lead_time ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        @if($iq->nilai_harga)
                        {{ number_format($iq->nilai_harga, 0, ',', '.') }} {{ $iq->currency }}
                        @else
                        -
                        @endif
                    </td>
                    <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                        <button class="btn btn-sm btn-action-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditIQ{{ $iq->inquiry_quotation_id }}">
                            Edit
                        </button>
                    </td>
                </tr>
                
                {{-- modal edit --}}
                <div class="modal fade iq-modal" id="modalEditIQ{{ $iq->inquiry_quotation_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">Edit Inquiry & Quotation</h5>
                            </div>

                            <form method="POST" action="{{ route('inquiry-quotation.update', $iq->inquiry_quotation_id) }}">
                                @csrf
                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                <div class="modal-body row g-3">

                                    {{-- Vendor --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Vendor *</label>
                                        <select name="vendor_id" class="form-select" required>
                                            @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id_vendor }}"
                                                @selected($vendor->id_vendor == $iq->vendor_id)>
                                                {{ $vendor->name_vendor }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Tanggal inquiry --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Inquiry *</label>
                                        <input type="date" name="tanggal_inquiry" class="form-control"
                                            value="{{ $iq->tanggal_inquiry->format('Y-m-d') }}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Quotation</label>
                                        <input type="date" name="tanggal_quotation" class="form-control"
                                            value="{{ $iq->tanggal_quotation?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Target Quotation</label>
                                        <input type="date" name="target_quotation" class="form-control"
                                            value="{{ $iq->target_quotation?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Lead Time</label>
                                        <input type="text" name="lead_time" class="form-control"
                                            value="{{ $iq->lead_time }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nilai Harga</label>
                                        <div class="input-group">

                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" id="dropdownCurrency{{ $iq->inquiry_quotation_id }}">
                                                {{ $iq->currency ?? 'IDR' }}
                                            </button>

                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li><a class="dropdown-item" onclick="selectCurrencyEdit('{{ $cur }}', '{{ $iq->inquiry_quotation_id }}')">{{ $cur }}</a></li>
                                                @endforeach
                                            </ul>

                                            <input type="text" name="nilai_harga" class="form-control currency-input"
                                                value="{{ $iq->nilai_harga }}">
                                            <input type="hidden" name="currency" id="currencyEdit{{ $iq->inquiry_quotation_id }}"
                                                value="{{ $iq->currency }}">
                                        </div>
                                    </div>


                                    <div class="col-12">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control">{{ $iq->notes }}</textarea>
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

                @empty
                @endforelse
                @if($inquiryQuotations->count() == 0 && $currentCheckpointSequence == 2)
                <tr>
                    <td>{{ $row }}</td>
                    <td colspan="6" class="text-center text-muted">
                        Belum ada Inquiry & Quotation
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateIQ">
                            Create
                        </button>
                    </td>
                </tr>
                @endif

                {{-- ================= ROW CREATE (HANYA SAAT CHECKPOINT 2) ================= --}}
                @if($inquiryQuotations->count() > 0 && $currentCheckpointSequence == 2)
                <tr>
                    <td colspan="7"></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateIQ">
                            Create
                        </button>
                    </td>
                </tr>
                @endif



                <div class="modal fade iq-modal" id="modalCreateIQ" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('inquiry-quotation.store', $procurement->procurement_id) }}">
                                @csrf
                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                <div class="modal-header">
                                    <h5 class="modal-title">Create Inquiry & Quotation</h5>
                                </div>

                                <div class="modal-body row g-3">

                                    {{-- Pilih Vendor --}}
                                    <div class="col-md-6">
                                        <label>Pilih Vendor *</label>
                                        <select name="vendor_id" class="form-select" required>
                                            <option value="" disabled selected>Pilih vendor</option>
                                            @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id_vendor }}">
                                                {{ $vendor->name_vendor }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- tanggal inquiry --}}
                                    <div class="col-md-6">
                                        <label>Tanggal Inquiry *</label>
                                        <input type="date" name="tanggal_inquiry" class="form-control" required>
                                    </div>

                                    {{-- tanggal quotation --}}
                                    <div class="col-md-6">
                                        <label>Tanggal Quotation</label>
                                        <input type="date" name="tanggal_quotation" class="form-control">
                                    </div>

                                    {{-- target quotation --}}
                                    <div class="col-md-6">
                                        <label>Target Quotation</label>
                                        <input type="date" name="target_quotation" class="form-control">
                                    </div>

                                    {{-- lead time --}}
                                    <div class="col-md-6">
                                        <label>Lead Time</label>
                                        <input type="text" name="lead_time" class="form-control" placeholder="ex: 7 hari kerja">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nilai Harga</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCurrency" data-bs-toggle="dropdown">
                                                {{ old('currency', 'IDR') }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" onclick="selectCurrency('IDR')">IDR</a></li>
                                                <li><a class="dropdown-item" onclick="selectCurrency('USD')">USD</a></li>
                                                <li><a class="dropdown-item" onclick="selectCurrency('EUR')">EUR</a></li>
                                                <li><a class="dropdown-item" onclick="selectCurrency('SGD')">SGD</a></li>
                                            </ul>

                                            <input type="text" name="nilai_harga" class="form-control currency-input" placeholder="0">
                                            <input type="hidden" name="currency" id="currencyInput" value="IDR">
                                        </div>
                                    </div>

                                    {{-- Notes --}}
                                    <div class="col-12">
                                        <label>Catatan</label>
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

            </tbody>
        </table>
    </div>
</div>
</div>
</div>