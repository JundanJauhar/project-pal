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
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $no = 1; @endphp

                @forelse($pengadaanOcs as $po)
                <tr>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $no++ }}</td>
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

                                    <div class="col-md-6">
                                        <label>Vendor</label>
                                        <select name="vendor_id" class="form-select">
                                            <option value="">-</option>
                                            @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id_vendor }}"
                                                @selected($vendor->id_vendor == $po->vendor_id)>
                                                {{ $vendor->name_vendor }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Nilai</label>
                                        <div class="input-group">
                                            <input type="number" name="nilai" class="form-control"
                                                value="{{ $po->nilai }}">
                                            <select name="currency" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <option value="{{ $cur }}" @selected($cur == $po->currency)>
                                                    {{ $cur }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Kadep → Kadiv</label>
                                        <input type="date" name="tgl_kadep_to_kadiv"
                                            class="form-control"
                                            value="{{ $po->tgl_kadep_to_kadiv?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Kadiv → CTO</label>
                                        <input type="date" name="tgl_kadiv_to_cto"
                                            class="form-control"
                                            value="{{ $po->tgl_kadiv_to_cto?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>CTO → CEO</label>
                                        <input type="date" name="tgl_cto_to_ceo"
                                            class="form-control"
                                            value="{{ $po->tgl_cto_to_ceo?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Tanggal ACC</label>
                                        <input type="date" name="tgl_acc"
                                            class="form-control"
                                            value="{{ $po->tgl_acc?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-12">
                                        <label>Catatan</label>
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
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        Belum ada Pengadaan OC
                    </td>
                    <td>
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreatePO">
                            Create
                        </button>
                    </td>
                </tr>
                @endforelse
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

                    <div class="col-md-6">
                        <label>Kadep → Kadiv</label>
                        <input type="date" name="tgl_kadep_to_kadiv" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Kadiv → CTO</label>
                        <input type="date" name="tgl_kadiv_to_cto" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>CTO → CEO</label>
                        <input type="date" name="tgl_cto_to_ceo" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Tanggal ACC</label>
                        <input type="date" name="tgl_acc" class="form-control">
                    </div>

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
