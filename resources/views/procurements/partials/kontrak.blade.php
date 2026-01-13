<div id="kontrak">
<h5 class="section-title">Kontrak</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">

        {{-- tombol simpan checkpoint --}}
        <div class="btn-simpan-wrapper">
            @if($currentCheckpointSequence == 7 && $kontraks->count() > 0)
            <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                @csrf
                <input type="hidden" name="from_checkpoint" value="7">
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
                    <th style="padding: 12px 8px; text-align: center; color: #000;">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @php
                /**
                * Vendor yang VALID untuk Kontrak
                * = vendor yang sudah dikirimi Inquiry & Quotation
                */
                $kontrakVendors = collect($inquiryQuotations ?? [])
                ->map(fn ($iq) => $iq->vendor)
                ->filter() // buang null
                ->unique('id_vendor') // cegah duplikat
                ->values();
                @endphp

                @php $row = 1; @endphp

                {{-- ================= DATA KONTRAK ================= --}}
                @forelse($kontraks as $kontrak)
                <tr>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $row++ }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->no_po ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->vendor?->name_vendor ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->item?->item_name ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->tgl_kontrak?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->maker ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        @if($kontrak->nilai)
                        {{ number_format($kontrak->nilai,0,',','.') }} {{ $kontrak->currency }}
                        @else
                        -
                        @endif
                    </td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->payment_term ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->incoterms ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->coo ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $kontrak->warranty ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                        <button class="btn btn-sm btn-action-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditKontrak{{ $kontrak->kontrak_id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                {{-- ================= MODAL EDIT ================= --}}
                <div class="modal fade" id="modalEditKontrak{{ $kontrak->kontrak_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('kontrak.update', $kontrak->kontrak_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Kontrak</h5>
                                </div>

                                <div class="modal-body row g-3">

                                    {{-- No PO --}}
                                    <div class="col-md-6">
                                        <label class="form-label">No PO</label>
                                        <input type="text" name="no_po"
                                            class="form-control"
                                            value="{{ $kontrak->no_po }}">
                                    </div>

                                    {{-- vendor --}}
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

                                    {{-- Item (AMBIL DARI PROCUREMENT) --}}
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

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Kontrak</label>
                                        <input type="date" name="tgl_kontrak"
                                            class="form-control"
                                            value="{{ $kontrak->tgl_kontrak?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Maker</label>
                                        <input type="text" name="maker"
                                            class="form-control"
                                            value="{{ $kontrak->maker }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Nilai</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" id="dropdownCurrencyKontrak{{ $kontrak->kontrak_id }}">
                                                {{ $kontrak->currency_nilai_kontrak ?? 'IDR' }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li><a class="dropdown-item" onclick="selectCurrencyKontrak('{{ $cur }}', '{{ $kontrak->kontrak_id }}')">{{ $cur }}</a></li>
                                                @endforeach
                                            </ul>
                                            <input type="text" name="nilai"
                                                class="form-control currency-input"
                                                value="{{ $kontrak->nilai }}">
                                            <input type="hidden" name="currency" id="currencyKontrakEdit{{ $kontrak->kontrak_id }}"
                                                value="{{ $kontrak->currency }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Payment Term</label>
                                        <input type="text" name="payment_term"
                                            class="form-control"
                                            value="{{ $kontrak->payment_term }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Incoterms</label>
                                        <input type="text" name="incoterms"
                                            class="form-control"
                                            value="{{ $kontrak->incoterms }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">COO</label>
                                        <input type="text" name="coo"
                                            class="form-control"
                                            value="{{ $kontrak->coo }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Warranty</label>
                                        <input type="text" name="warranty"
                                            class="form-control"
                                            value="{{ $kontrak->warranty }}">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Remarks</label>
                                        <textarea name="remarks"
                                            class="form-control">{{ $kontrak->remarks }}</textarea>
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
                @if($kontraks->count() == 0 && $currentCheckpointSequence == 6)
                <tr>
                    <td>{{ $row }}</td>
                    <td colspan="10" class="text-center text-muted">
                        Belum ada Kontrak</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateKontrak">
                            Create
                        </button>
                    </td>
                </tr>
                @endif

                {{-- ================= ROW CREATE (HANYA SAAT CHECKPOINT 6) ================= --}}
                @if($kontraks->count() > 0 && $currentCheckpointSequence == 6)
                <tr>
                    <td colspan="11"></td>
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

{{-- ================= MODAL CREATE ================= --}}
<div class="modal fade" id="modalCreateKontrak" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" action="{{ route('kontrak.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Create Kontrak</h5>
                </div>

                <div class="modal-body row g-3">

                    {{-- No PO --}}
                    <div class="col-md-6">
                        <label class="form-label">No PO</label>
                        <input type="text" name="no_po" class="form-control">
                    </div>

                    {{-- vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Vendor --</option>

                            @forelse($kontrakVendors as $vendor)
                            <option value="{{ $vendor->id_vendor }}">
                                {{ $vendor->name_vendor }}
                            </option>
                            @empty
                            <option disabled>
                                Tidak ada vendor dari Inquiry & Quotation
                            </option>
                            @endforelse
                        </select>
                    </div>

                    {{-- Item dari procurement --}}
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

                    <div class="col-md-6">
                        <label class="form-label">Tanggal Kontrak</label>
                        <input type="date" name="tgl_kontrak" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Maker</label>
                        <input type="text" name="maker" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nilai</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCurrencyKontrakCreate" data-bs-toggle="dropdown">
                                IDR
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <li>
                                    <a class="dropdown-item"
                                        onclick="selectCurrencyCreateKontrak('HargaFinal','{{ $cur }}')">
                                        {{ $cur }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>

                            <input type="text" name="nilai" class="form-control currency-input" placeholder="0">
                            <input type="hidden" name="currency" id="currencyCreateNilaiKontrak" value="IDR">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Payment Term</label>
                        <input type="text" name="payment_term" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Incoterms</label>
                        <input type="text" name="incoterms" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">COO</label>
                        <input type="text" name="coo" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Warranty</label>
                        <input type="text" name="warranty" class="form-control">
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
</div>
</div>