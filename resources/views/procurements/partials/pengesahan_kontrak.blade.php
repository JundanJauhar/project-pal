<h5 class="section-title">Pengesahan Kontrak</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">

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

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Vendor</th>
                    <th>Nilai</th>
                    <th>Kadep → Kadiv</th>
                    <th>Kadiv → CTO</th>
                    <th>CTO → CEO</th>
                    <th>Tgl ACC</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $no = 1; @endphp

                @forelse($pengesahanKontraks as $pk)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $pk->vendor?->name_vendor ?? '-' }}</td>
                    <td>
                        @if($pk->nilai)
                            {{ number_format($pk->nilai, 0, ',', '.') }} {{ $pk->currency }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $pk->tgl_kadep_to_kadiv?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $pk->tgl_kadiv_to_cto?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $pk->tgl_cto_to_ceo?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $pk->tgl_acc?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        <button class="btn btn-sm btn-action-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditPK{{ $pk->pengesahan_id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                <div class="modal fade" id="modalEditPK{{ $pk->pengesahan_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST"
                                action="{{ route('pengesahan-kontrak.update', $pk->pengesahan_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Pengesahan Kontrak</h5>
                                </div>

                                <div class="modal-body row g-3">

                                    <div class="col-md-6">
                                        <label>Vendor</label>
                                        <select name="vendor_id" class="form-select">
                                            <option value="">-</option>
                                            @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id_vendor }}"
                                                @selected($vendor->id_vendor == $pk->vendor_id)>
                                                {{ $vendor->name_vendor }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nilai</label>
                                        <div class="input-group">
                                            <input type="number" name="nilai"class="form-control"
                                                value="{{ $pk->nilai }}">
                                            <select name="currency" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <option value="{{ $cur }}"@selected($cur == $pk->currency)>
                                                    {{ $cur }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    @foreach([
                                        'tgl_kadep_to_kadiv' => 'Kadep → Kadiv',
                                        'tgl_kadiv_to_cto' => 'Kadiv → CTO',
                                        'tgl_cto_to_ceo' => 'CTO → CEO',
                                        'tgl_acc' => 'Tanggal ACC'
                                    ] as $field => $label)
                                    <div class="col-md-6">
                                        <label>{{ $label }}</label>
                                        <input type="date"
                                            name="{{ $field }}"
                                            class="form-control"
                                            value="{{ $pk->$field?->format('Y-m-d') }}">
                                    </div>
                                    @endforeach

                                    <div class="col-12">
                                        <label>Catatan</label>
                                        <textarea name="remarks"
                                            class="form-control">{{ $pk->remarks }}</textarea>
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
                    <td colspan="7" class="text-center text-muted">
                        Belum ada Pengesahan Kontrak
                    </td>
                    <td>
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreatePK">
                            Create
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalCreatePK" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST"
                action="{{ route('pengesahan-kontrak.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id"
                    value="{{ $procurement->procurement_id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Create Pengesahan Kontrak</h5>
                </div>

                <div class="modal-body row g-3">

                    <div class="col-md-6">
                        <label>Vendor</label>
                        <select name="vendor_id" class="form-select">
                            <option value="">-</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id_vendor }}">
                                {{ $vendor->name_vendor }}
                            </option>
                            @endforeach
                        </select>
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

                    @foreach([
                        'tgl_kadep_to_kadiv' => 'Kadep → Kadiv',
                        'tgl_kadiv_to_cto' => 'Kadiv → CTO',
                        'tgl_cto_to_ceo' => 'CTO → CEO',
                        'tgl_acc' => 'Tanggal ACC'
                    ] as $field => $label)
                    <div class="col-md-6">
                        <label>{{ $label }}</label>
                        <input type="date" name="{{ $field }}" class="form-control">
                    </div>
                    @endforeach

                    <div class="col-12">
                        <label>Catatan</label>
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
