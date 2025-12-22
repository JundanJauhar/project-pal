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
                @php $no = 1; @endphp

                {{-- ================= DATA KONTRAK ================= --}}
                @forelse($kontraks as $kontrak)
                <tr>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $no++ }}</td>
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

                                    {{-- Vendor --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Vendor *</label>
                                        <select name="vendor_id" class="form-select" required>
                                            @foreach($vendors as $v)
                                            <option value="{{ $v->id_vendor }}"
                                                @selected($v->id_vendor == $kontrak->vendor_id)>
                                                {{ $v->name_vendor }}
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
                                                {{ $kontrak->currency ?? 'IDR' }}
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li><a class="dropdown-item" onclick="selectCurrencyKontrakEdit('{{ $cur }}', '{{ $kontrak->kontrak_id }}')">{{ $cur }}</a></li>
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
                <tr>
                    <td>{{ $no }}</td>
                    <td colspan="9," class="text-center text-muted">Belum ada Kontrak</td>
                    <td>
                        <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal" data-bs-target="#modalCreateKontrak">
                                Create
                        </button>
                    </td>
                </tr>
                @endforelse
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

                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <select name="vendor_id" class="form-select" required>
                            <option value="" disabled selected>Pilih vendor</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id_vendor }}">
                                {{ $v->name_vendor }}
                            </option>
                            @endforeach
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
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCurrencyKontrak" data-bs-toggle="dropdown">
                                {{ old('currency', 'IDR') }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" onclick="selectCurrencyKontrak('IDR')">IDR</a></li>
                                <li><a class="dropdown-item" onclick="selectCurrencyKontrak('USD')">USD</a></li>
                                <li><a class="dropdown-item" onclick="selectCurrencyKontrak('EUR')">EUR</a></li>
                                <li><a class="dropdown-item" onclick="selectCurrencyKontrak('SGD')">SGD</a></li>
                            </ul>
                            <input type="text" name="nilai" class="form-control currency-input" placeholder="0">
                            <input type="hidden" name="currency" id="currencyInputKontrak" value="IDR">
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