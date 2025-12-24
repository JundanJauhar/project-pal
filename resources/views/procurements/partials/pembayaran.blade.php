<h5 class="section-title">Pembayaran</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">

        {{-- Tombol Simpan (Checkpoint Logic jika dibutuhkan) --}}
        <div class="btn-simpan-wrapper">
            @if($currentCheckpointSequence == 6 && $pembayarans->count() > 0)
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
                    <th>Status Pembayaran</th>
                    <th>Persen</th>
                    <th>Nilai Pembayaran</th>
                    <th>No Memo</th>
                    <th>Link</th>
                    <th>Target</th>
                    <th>Realisasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $row = 1; @endphp

                @forelse($pembayarans as $pay)
                <tr>
                    <td>{{ $row++ }}</td>
                    <td>{{ $pay->vendor->name_vendor }}</td>
                    <td>{{ $pay->payment_type }}</td>
                    <td>{{ $pay->percentage }}%</td>
                    <td>
                        {{ number_format($pay->payment_value,0,',','.') }}
                        {{ $pay->currency }}
                    </td>
                    <td>{{ $pay->no_memo }}</td>
                    <td>
                        @if($pay->link)
                        <a href="{{ $pay->link }}" target="_blank">Link</a>
                        @else
                        -
                        @endif
                    </td>
                    <td>{{ $pay->target_date?->format('d/m/Y') }}</td>
                    <td>{{ $pay->realization_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        <button class="btn btn-sm btn-action-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditPembayaran{{ $pay->id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                {{-- ================= MODAL EDIT ================= --}}
                <div class="modal fade" id="modalEditPembayaran{{ $pay->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('pembayaran.update',$pay->id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Pembayaran</h5>
                                </div>

                                <div class="modal-body row g-3">

                                    <div class="col-md-6">
                                        <label>Jenis Pembayaran</label>
                                        <select name="payment_type" class="form-select">
                                            @foreach(['SKBDN','L/C','TT'] as $type)
                                            <option value="{{ $type }}" @selected($pay->payment_type==$type)>
                                                {{ $type }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Persentase (%)</label>
                                        <input type="number" name="percentage" class="form-control"
                                            value="{{ $pay->percentage }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>No Memo</label>
                                        <input type="text" name="no_memo" class="form-control"
                                            value="{{ $pay->no_memo }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Link</label>
                                        <input type="url" name="link" class="form-control"
                                            value="{{ $pay->link }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Target</label>
                                        <input type="date" name="target_date" class="form-control"
                                            value="{{ $pay->target_date?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Realisasi</label>
                                        <input type="date" name="realization_date" class="form-control"
                                            value="{{ $pay->realization_date?->format('Y-m-d') }}">
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">
                                        Batal
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-action-create">
                                        Simpan
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">
                        Belum ada data Pembayaran
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreatePembayaran">
                            Create
                        </button>
                    </td>
                </tr>
                @endforelse

                {{-- Tombol Create tambahan --}}
                @if($pembayarans->count() > 0)
                <tr>
                    <td colspan="9"></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreatePembayaran">
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
<div class="modal fade" id="modalCreatePembayaran" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" action="{{ route('pembayaran.store',$procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Create Pembayaran</h5>
                </div>

                <div class="modal-body row g-3">

                    <div class="col-md-6">
                        <label>Jenis Pembayaran</label>
                        <select name="payment_type" class="form-select" required>
                            <option disabled selected>Pilih</option>
                            <option>SKBDN</option>
                            <option>L/C</option>
                            <option>TT</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label>Persentase (%)</label>
                        <input type="number" name="percentage" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label>No Memo</label>
                        <input type="text" name="no_memo" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Link</label>
                        <input type="url" name="link" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Target</label>
                        <input type="date" name="target_date" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-sm btn-action-create">
                        Simpan
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>
