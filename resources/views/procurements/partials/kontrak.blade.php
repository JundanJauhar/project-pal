<h5 class="section-title">Kontrak</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">

        {{-- tombol simpan checkpoint --}}
        @if($currentCheckpointSequence == 7 && $kontraks->count() > 0)
        <div class="btn-simpan-wrapper">
            <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                @csrf
                <input type="hidden" name="from_checkpoint" value="7">
                <button class="btn btn-sm btn-action-simpan">
                    <i class="bi bi-box-arrow-down"></i> Simpan
                </button>
            </form>
        </div>
        @endif

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Vendor</th>
                    <th>Item</th>
                    <th>Tanggal Kontrak</th>
                    <th>Maker</th>
                    <th>Nilai</th>
                    <th>Payment Term</th>
                    <th>Incoterms</th>
                    <th>COO</th>
                    <th>Warranty</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $no = 1; @endphp

                @forelse($kontraks as $kontrak)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $kontrak->vendor?->name_vendor ?? '-' }}</td>
                    <td>{{ $kontrak->item?->item_name ?? '-' }}</td>
                    <td>{{ $kontrak->tgl_kontrak?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $kontrak->maker ?? '-' }}</td>
                    <td>
                        @if($kontrak->nilai)
                            {{ number_format($kontrak->nilai,0,',','.') }} {{ $kontrak->currency }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $kontrak->payment_term ?? '-' }}</td>
                    <td>{{ $kontrak->incoterms ?? '-' }}</td>
                    <td>{{ $kontrak->coo ?? '-' }}</td>
                    <td>{{ $kontrak->warranty ?? '-' }}</td>
                    <td>
                        <button class="btn btn-sm btn-action-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditKontrak{{ $kontrak->kontrak_id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                {{-- ================= MODAL EDIT ================= --}}
                <div class="modal fade"
                     id="modalEditKontrak{{ $kontrak->kontrak_id }}"
                     tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST"
                                  action="{{ route('kontrak.update', $kontrak->kontrak_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Kontrak</h5>
                                </div>

                                <div class="modal-body row g-3">

                                    <div class="col-md-6">
                                        <label>Vendor</label>
                                        <select name="vendor_id" class="form-select">
                                            <option value="">-</option>
                                            @foreach($vendors as $v)
                                            <option value="{{ $v->id_vendor }}"
                                                @selected($v->id_vendor == $kontrak->vendor_id)>
                                                {{ $v->name_vendor }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Item</label>
                                        <select name="item_id" class="form-select">
                                            <option value="">-</option>
                                            @foreach($items as $item)
                                            <option value="{{ $item->item_id }}"
                                                @selected($item->item_id == $kontrak->item_id)>
                                                {{ $item->item_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Tanggal Kontrak</label>
                                        <input type="date" name="tgl_kontrak"
                                               class="form-control"
                                               value="{{ $kontrak->tgl_kontrak?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Maker</label>
                                        <input type="text" name="maker"
                                               class="form-control"
                                               value="{{ $kontrak->maker }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nilai</label>
                                        <div class="input-group">
                                            <input type="number" name="nilai"
                                                   class="form-control"
                                                   value="{{ $kontrak->nilai }}">
                                            <select name="currency" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <option value="{{ $cur }}"
                                                    @selected($cur == $kontrak->currency)>
                                                    {{ $cur }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Payment Term</label>
                                        <input type="text" name="payment_term"
                                               class="form-control"
                                               value="{{ $kontrak->payment_term }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Incoterms</label>
                                        <input type="text" name="incoterms"
                                               class="form-control"
                                               value="{{ $kontrak->incoterms }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>COO</label>
                                        <input type="text" name="coo"
                                               class="form-control"
                                               value="{{ $kontrak->coo }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Warranty</label>
                                        <input type="text" name="warranty"
                                               class="form-control"
                                               value="{{ $kontrak->warranty }}">
                                    </div>

                                    <div class="col-12">
                                        <label>Remarks</label>
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
                    <td colspan="10" class="text-center text-muted">
                        Belum ada Kontrak
                    </td>
                    <td>
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateKontrak">
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

            <form method="POST"
                  action="{{ route('kontrak.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id"
                       value="{{ $procurement->procurement_id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Create Kontrak</h5>
                </div>

                <div class="modal-body row g-3">

                    <div class="col-md-6">
                        <label>Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">-</option>
                            @foreach($vendors as $v)
                            <option value="{{ $v->id_vendor }}">
                                {{ $v->name_vendor }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Item</label>
                        <select name="item_id" class="form-select">
                            <option value="">-</option>
                            @foreach($items as $item)
                            <option value="{{ $item->item_id }}">
                                {{ $item->item_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Tanggal Kontrak</label>
                        <input type="date" name="tgl_kontrak" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Maker</label>
                        <input type="text" name="maker" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Nilai</label>
                        <div class="input-group">
                            <input type="number" name="nilai" class="form-control">
                            <select name="currency" class="form-select">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <option value="{{ $cur }}">{{ $cur }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label>Payment Term</label>
                        <input type="text" name="payment_term" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Incoterms</label>
                        <input type="text" name="incoterms" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>COO</label>
                        <input type="text" name="coo" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Warranty</label>
                        <input type="text" name="warranty" class="form-control">
                    </div>

                    <div class="col-12">
                        <label>Remarks</label>
                        <textarea name="remarks" class="form-control"></textarea>
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
