<h5 class="section-title">Pengiriman Material</h5>
<div class="dashboard-table-wrapper">
    <div class="table-responsive">

        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Incoterms</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">ETD</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">ETA SBY Port</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">ETA PAL</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Remark</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php $row = 1; @endphp

                {{-- LISTING --}}
                @forelse($materialDeliveries as $delivery)
                <tr>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $row++ }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->incoterms ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->etd?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->eta_sby_port?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->eta_pal?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ Str::limit($delivery->remark,30) ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        <button class="btn btn-sm btn-action-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditDel{{ $delivery->delivery_id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                {{-- MODAL EDIT --}}
                <div class="modal fade" id="modalEditDel{{ $delivery->delivery_id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('material-delivery.update', $delivery->delivery_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5>Edit Pengiriman Material</h5>
                                </div>

                                <div class="modal-body row g-3">
                                    <div class="col-md-6">
                                        <label>Incoterms</label>
                                        <input type="text" name="incoterms" class="form-control"
                                            value="{{ $delivery->incoterms }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>ETD</label>
                                        <input type="date" name="etd" class="form-control"
                                            value="{{ $delivery->etd?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>ETA SBY Port</label>
                                        <input type="date" name="eta_sby_port" class="form-control"
                                            value="{{ $delivery->eta_sby_port?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label>ETA PAL</label>
                                        <input type="date" name="eta_pal" class="form-control"
                                            value="{{ $delivery->eta_pal?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-12">
                                        <label>Remark</label>
                                        <textarea name="remark" class="form-control">{{ $delivery->remark }}</textarea>
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
                @empty
                @endforelse


                {{-- ROW CREATE --}}
                <tr>
                    <td>{{ $row }}</td>
                    <td colspan="5" style="text-align:center;" class="text-muted">
                        @if($row == 1)
                        Belum ada pengiriman material
                        @endif
                    </td>
                    <td style="text-align:center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateDelivery">
                            Create
                        </button>
                    </td>
                </tr>

            </tbody>
        </table>


        {{-- MODAL CREATE --}}
        <div class="modal fade" id="modalCreateDelivery" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('material-delivery.store', $procurement->procurement_id) }}">
                        @csrf

                        <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                        <div class="modal-header">
                            <h5>Create Pengiriman Material</h5>
                        </div>

                        <div class="modal-body row g-3">
                            <div class="col-md-6">
                                <label>Incoterms</label>
                                <input type="text" name="incoterms" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label>ETD</label>
                                <input type="date" name="etd" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label>ETA SBY Port</label>
                                <input type="date" name="eta_sby_port" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label>ETA PAL</label>
                                <input type="date" name="eta_pal" class="form-control">
                            </div>

                            <div class="col-md-12">
                                <label>Remark</label>
                                <textarea name="remark" class="form-control" rows="3"></textarea>
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

    </div>
</div>