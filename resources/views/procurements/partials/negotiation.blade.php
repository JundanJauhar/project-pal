<h5 class="section-title">Negotiation</h5>
<div class="dashboard-table-wrapper">
    <div class="table-responsive">

        {{-- tombol simpan checkpoint --}}
        @if($currentCheckpointSequence==4 && count($negotiations)>0)
        <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
            @csrf
            <input type="hidden" name="from_checkpoint" value="4">
            <button class="btn btn-sm btn-action-simpan">
                <i class="bi bi-box-arrow-down"></i>Simpan
            </button>
        </form>
        @endif

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Vendor</th>
                    <th>HPS</th>
                    <th>Budget</th>
                    <th>Harga Final</th>
                    <th>Tanggal Kirim</th>
                    <th>Tanggal Terima</th>
                    <th>Note</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $row = 1; @endphp

                @foreach($negotiations as $neg)
                <tr>
                    <td>{{ $row++ }}</td>
                    <td>{{ $neg->vendor->name_vendor ?? '-' }}</td>
                    <td>
                        @if($neg->hps)
                        {{ number_format($neg->hps,0,',','.') }} {{ $neg->currency_hps }}
                        @else - @endif
                    </td>

                    <td>
                        @if($neg->budget)
                        {{ number_format($neg->budget,0,',','.') }} {{ $neg->currency_budget }}
                        @else - @endif
                    </td>

                    <td>
                        @if($neg->harga_final)
                        {{ number_format($neg->harga_final,0,',','.') }} {{ $neg->currency_harga_final }}
                        @else - @endif
                    </td>

                    <td>{{ $neg->tanggal_kirim?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $neg->tanggal_terima?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $neg->notes ?? '-' }}</td>

                    <td>
                        <button data-bs-toggle="modal"
                            data-bs-target="#modalEditNeg{{ $neg->negotiation_id }}"
                            class="btn btn-sm btn-action-edit">
                            Edit
                        </button>
                    </td>
                </tr>

                {{-- modal edit --}}
                <div class="modal fade" id="modalEditNeg{{ $neg->negotiation_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form method="POST"
                                action="{{ route('negotiation.update', $neg->negotiation_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5>Edit Negotiation</h5>
                                </div>
                                <div class="modal-body row g-3">

                                    {{-- vendor --}}
                                    <div class="col-md-6">
                                        <label>Vendor *</label>
                                        <select name="vendor_id" class="form-select" required>
                                            @foreach($vendors as $v)
                                            <option value="{{ $v->id_vendor }}"
                                                @selected($v->id_vendor==$neg->vendor_id)>
                                                {{ $v->name_vendor }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- HPS --}}
                                    <div class="col-md-6">
                                        <label>HPS</label>
                                        <div class="input-group">
                                            <select name="currency_hps" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                <option value="{{ $c }}" @selected($neg->currency_hps==$c)>
                                                    {{ $c }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="hps" class="form-control"
                                                value="{{ $neg->hps }}">
                                        </div>
                                    </div>

                                    {{-- BUDGET --}}
                                    <div class="col-md-6">
                                        <label>Budget</label>
                                        <div class="input-group">
                                            <select name="currency_budget" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                <option value="{{ $c }}" @selected($neg->currency_budget==$c)>
                                                    {{ $c }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="budget" class="form-control"
                                                value="{{ $neg->budget }}">
                                        </div>
                                    </div>

                                    {{-- HARGA FINAL --}}
                                    <div class="col-md-6">
                                        <label>Harga Final</label>
                                        <div class="input-group">
                                            <select name="currency_harga_final" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                <option value="{{ $c }}" @selected($neg->currency_harga_final==$c)>
                                                    {{ $c }}
                                                </option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="harga_final" class="form-control"
                                                value="{{ $neg->harga_final }}">
                                        </div>
                                    </div>

                                    {{-- tanggal --}}
                                    <div class="col-md-6">
                                        <label>Tanggal Kirim</label>
                                        <input type="date" name="tanggal_kirim"
                                            class="form-control"
                                            value="{{ $neg->tanggal_kirim?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Tanggal Terima</label>
                                        <input type="date" name="tanggal_terima"
                                            class="form-control"
                                            value="{{ $neg->tanggal_terima?->format('Y-m-d') }}">
                                    </div>

                                    {{-- note --}}
                                    <div class="col-12">
                                        <label>Note</label>
                                        <textarea name="notes" class="form-control">{{ $neg->notes }}</textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-sm btn-action-create">Simpan</button>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- IF EMPTY --}}
                <tr>
                    <td>{{ $row }}</td>
                    <td colspan="7" class="text-center text-muted">Belum ada Negotiation</td>
                    <td>
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal" data-bs-target="#modalCreateNeg">
                            Create
                        </button>
                    </td>
                </tr>

                {{-- modal create --}}
                <div class="modal fade" id="modalCreateNeg" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('negotiation.store', $procurement->procurement_id) }}">
                                @csrf
                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                                <div class="modal-header">
                                    <h5>Create Negotiation</h5>
                                </div>
                                <div class="modal-body row g-3">

                                    {{-- vendor --}}
                                    <div class="col-md-6">
                                        <label>Vendor *</label>
                                        <select name="vendor_id" class="form-control" required>
                                            <option value="">-- Pilih Vendor --</option>
                                            @foreach($vendors as $v)
                                            <option value="{{ $v->id_vendor }}">{{ $v->name_vendor }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- HPS --}}
                                    <div class="col-md-6">
                                        <label>HPS</label>
                                        <div class="input-group">
                                            <select name="currency_hps" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                <option value="{{ $c }}">{{ $c }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="hps" class="form-control">
                                        </div>
                                    </div>

                                    {{-- BUDGET --}}
                                    <div class="col-md-6">
                                        <label>Budget</label>
                                        <div class="input-group">
                                            <select name="currency_budget" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                <option value="{{ $c }}">{{ $c }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="budget" class="form-control">
                                        </div>
                                    </div>

                                    {{-- HARGA FINAL --}}
                                    <div class="col-md-6">
                                        <label>Harga Final</label>
                                        <div class="input-group">
                                            <select name="currency_harga_final" class="form-select">
                                                @foreach(['IDR','USD','EUR','SGD'] as $c)
                                                <option value="{{ $c }}">{{ $c }}</option>
                                                @endforeach
                                            </select>
                                            <input type="text" name="harga_final" class="form-control">
                                        </div>
                                    </div>

                                    {{-- tanggal --}}
                                    <div class="col-md-6">
                                        <label>Tanggal Kirim</label>
                                        <input type="date" name="tanggal_kirim" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label>Tanggal Terima</label>
                                        <input type="date" name="tanggal_terima" class="form-control">
                                    </div>

                                    <div class="col-12">
                                        <label>Note</label>
                                        <textarea name="notes" class="form-control"></textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                                    <button class="btn btn-sm btn-action-create">Simpan</button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            </tbody>
        </table>
    </div>
</div>