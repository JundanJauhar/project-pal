<h5 class="section-title">Pengadaan OC</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">

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
                * Vendor yang VALID untuk Pengadaan OC
                * = vendor yang sudah dikirimi Inquiry & Quotation
                */
                $poVendors = collect($inquiryQuotations ?? [])
                ->map(fn ($iq) => $iq->vendor)
                ->filter() // buang null
                ->unique('id_vendor') // cegah duplikat
                ->values();
                @endphp

                @php $row = 1; @endphp

                @forelse($pengadaanOcs as $po)
                <tr>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $row++ }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->vendor?->name_vendor ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        @if($po->nilai)
                        {{ number_format($po->nilai, 0, ',', '.') }} {{ $po->currency }}
                        @else
                        -
                        @endif
                    </td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->tgl_kadep_to_kadiv?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->tgl_kadiv_to_cto?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->tgl_cto_to_ceo?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->tgl_acc?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $po->remarks ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        <button class="btn btn-sm btn-action-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditPO{{ $po->pengadaan_oc_id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                <div class="modal fade" id="modalEditPO{{ $po->pengadaan_oc_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST"
                                action="{{ route('pengadaan-oc.update', $po->pengadaan_oc_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Pengadaan OC</h5>
                                </div>

                                <div class="modal-body row g-3">

                                    {{-- vendor --}}
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

                                    {{-- nilai --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Nilai</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" id="dropdownCurrencyEdit{{ $po->pengadaan_oc_id }}">
                                                {{ $po->currency ?? 'IDR' }}
                                            </button>

                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li><a class="dropdown-item" onclick="selectCurrencyEditPO('{{ $cur }}', '{{ $po->pengadaan_oc_id }}')">{{ $cur }}</a></li>
                                                @endforeach
                                            </ul>

                                            <input type="text" name="nilai" class="form-control currency-input"
                                                value="{{ $po->nilai }}">
                                            <input type="hidden" name="currency" id="currencyEditPO{{ $po->pengadaan_oc_id }}"
                                                value="{{ $po->currency }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Kadep → Kadiv</label>
                                        <input type="date" name="tgl_kadep_to_kadiv"
                                            class="form-control"
                                            value="{{ $po->tgl_kadep_to_kadiv?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Kadiv → CTO</label>
                                        <input type="date" name="tgl_kadiv_to_cto"
                                            class="form-control"
                                            value="{{ $po->tgl_kadiv_to_cto?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">CTO → CEO</label>
                                        <input type="date" name="tgl_cto_to_ceo"
                                            class="form-control"
                                            value="{{ $po->tgl_cto_to_ceo?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal ACC</label>
                                        <input type="date" name="tgl_acc"
                                            class="form-control"
                                            value="{{ $po->tgl_acc?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Remarks</label>
                                        <textarea name="remarks"
                                            class="form-control">{{ $po->remarks }}</textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button"
                                        class="btn btn-sm btn-action-abort"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit"
                                        class="btn btn-sm btn-action-create">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

                @empty
                @endforelse
                @if($pengadaanOcs->count() == 0 && $currentCheckpointSequence == 5)
                <tr>
                    <td>{{ $row }}</td>
                    <td colspan="7" class="text-center text-muted">
                        Belum ada Pengadaan OC</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreatePO">
                            Create
                        </button>
                    </td>
                </tr>
                @endif

                <div class="modal fade" id="modalCreatePO" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST"
                                action="{{ route('pengadaan-oc.store', $procurement->procurement_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title">Create Pengadaan OC</h5>
                                </div>

                                <div class="modal-body row g-3">

                                    {{-- vendor --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Vendor *</label>
                                        <select name="vendor_id" id="vendorSelectPO" class="form-select" required>
                                            <option value="">-- Pilih Vendor --</option>

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
                                        </select>

                                        <small style="color:#666;">
                                            Vendor berasal dari Inquiry & Quotation
                                        </small>
                                    </div>

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

                                    <div class="col-md-6">
                                        <label class="form-label">Kadep → Kadiv</label>
                                        <input type="date" name="tgl_kadep_to_kadiv" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Kadiv → CTO</label>
                                        <input type="date" name="tgl_kadiv_to_cto" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">CTO → CEO</label>
                                        <input type="date" name="tgl_cto_to_ceo" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal ACC</label>
                                        <input type="date" name="tgl_acc" class="form-control">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Remarks</label>
                                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button"
                                        class="btn btn-sm btn-action-abort"
                                        data-bs-dismiss="modal">Batal</button>
                                    <button type="submit"
                                        class="btn btn-sm btn-action-create">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalCreatePO" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST"
                action="{{ route('pengadaan-oc.store', $procurement->procurement_id) }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Create Pengadaan OC</h5>
                </div>

                <div class="modal-body row g-3">

                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" id="vendorSelectPO" class="form-select" required>
                            <option value="">-- Pilih Vendor --</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id_vendor }}" data-negotiations="{{ base64_encode(json_encode($negotiations->where('vendor_id', $vendor->id_vendor)->values())) }}">
                                {{ $vendor->name_vendor }}
                            </option>
                            @endforeach
                        </select>
                    </div>

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

                    <div class="col-md-6">
                        <label class="form-label">Kadep → Kadiv</label>
                        <input type="date" name="tgl_kadep_to_kadiv" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Kadiv → CTO</label>
                        <input type="date" name="tgl_kadiv_to_cto" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">CTO → CEO</label>
                        <input type="date" name="tgl_cto_to_ceo" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tanggal ACC</label>
                        <input type="date" name="tgl_acc" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-sm btn-action-abort"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit"
                        class="btn btn-sm btn-action-create">Simpan</button>
                </div>

            </form>

        </div>
    </div>
</div>