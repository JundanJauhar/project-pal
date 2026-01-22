<div id="material-delivery">
    <h5 class="section-title">Pengiriman Material</h5>

    {{-- Alert Error (LUAR TABLE) --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="dashboard-table-wrapper">
        <div class="table-responsive">
            {{-- Button Save Checkpoint (LUAR TABLE) --}}
            <div class="btn-simpan-wrapper">
                @if($currentCheckpointSequence==8 && count($materialDeliveries)>0)
                <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="from_checkpoint" value="8">
                    <button class="btn btn-sm btn-action-simpan">
                        <i class="bi bi-box-arrow-down"></i>Simpan
                    </button>
                </form>
                @endif
            </div>

            {{-- TABLE --}}
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Incoterms</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">IMO Number</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Container No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">ETD</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">ETA SBY Port</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">ETA PAL</th>
                        <th style="padding: 12px 8px; text-align: center; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php $row = 1; @endphp
                    @forelse($materialDeliveries as $delivery)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $row++ }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->incoterms ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($delivery->imo_number)
                            <a href="https://www.vesselfinder.com/vessels/details/{{ $delivery->imo_number }}"
                                target="_blank"
                                style="color: #0066cc; text-decoration: underline;"
                                title="Track vessel on VesselFinder">
                                {{ $delivery->imo_number }}
                            </a>
                            @else
                            -
                            @endif
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->container_number ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->etd?->format('d/m/Y') ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->eta_sby_port?->format('d/m/Y') ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $delivery->eta_pal?->format('d/m/Y') ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            <button class="btn btn-sm btn-action-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditDelivery{{ $delivery->delivery_id }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                    @if($materialDeliveries->count() == 0 && $currentCheckpointSequence == 8)
                    <tr>
                        <td>{{ $row }}</td>
                        <td colspan="6" class="text-center text-muted">
                            Belum ada pengiriman material</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateDelivery">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endif

                    {{-- ================= ROW CREATE (HANYA SAAT CHECKPOINT 8) ================= --}}
                    @if($materialDeliveries->count() > 0 && $currentCheckpointSequence == 8)
                    <tr>
                        <td colspan="7"></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateDelivery">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL EDIT (LUAR TABLE) --}}
{{-- ============================================ --}}
@forelse($materialDeliveries as $delivery)
<div class="modal fade" id="modalEditDelivery{{ $delivery->delivery_id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Pengiriman Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('material-delivery.update', $delivery->delivery_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">
                    {{-- Incoterms --}}
                    <div class="col-md-6">
                        <label class="form-label">Incoterms</label>
                        <input type="text" name="incoterms" class="form-control"
                            value="{{ $delivery->incoterms }}">
                    </div>

                    {{-- IMO Number --}}
                    <div class="col-md-6">
                        <label class="form-label">IMO Number (7 digit) *</label>
                        <input type="text" name="imo_number" class="form-control"
                            value="{{ $delivery->imo_number }}"
                            placeholder="Contoh: 9234567"
                            maxlength="7"
                            pattern="[0-9]{7}"
                            required>
                        <small class="text-muted">7 digit angka - untuk tracking di VesselFinder</small>
                    </div>

                    {{-- Container Number --}}
                    <div class="col-md-6">
                        <label class="form-label">Container Number</label>
                        <input type="text" name="container_number" class="form-control"
                            value="{{ $delivery->container_number }}"
                            placeholder="Contoh: TRIU8935420"
                            style="text-transform: uppercase;">
                    </div>

                    {{-- ETD --}}
                    <div class="col-md-6">
                        <label class="form-label">ETD</label>
                        <input type="date" name="etd" class="form-control"
                            value="{{ $delivery->etd?->format('Y-m-d') }}">
                    </div>

                    {{-- ETA SBY Port --}}
                    <div class="col-md-6">
                        <label class="form-label">ETA SBY Port</label>
                        <input type="date" name="eta_sby_port" class="form-control"
                            value="{{ $delivery->eta_sby_port?->format('Y-m-d') }}">
                    </div>

                    {{-- ETA PAL --}}
                    <div class="col-md-6">
                        <label class="form-label">ETA PAL</label>
                        <input type="date" name="eta_pal" class="form-control"
                            value="{{ $delivery->eta_pal?->format('Y-m-d') }}">
                    </div>

                    {{-- Remark --}}
                    <div class="col-12">
                        <label class="form-label">Remark</label>
                        <textarea name="remark" class="form-control">{{ $delivery->remark }}</textarea>
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

{{-- ============================================ --}}
{{-- MODAL CREATE (LUAR TABLE) --}}
{{-- ============================================ --}}
@if($currentCheckpointSequence == 8)
<div class="modal fade" id="modalCreateDelivery" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Pengiriman Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('material-delivery.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">
                    {{-- Incoterms --}}
                    <div class="col-md-6">
                        <label class="form-label">Incoterms</label>
                        <input type="text" name="incoterms" class="form-control">
                    </div>

                    {{-- IMO Number --}}
                    <div class="col-md-6">
                        <label class="form-label">IMO Number (7 digit) *</label>
                        <input type="text" name="imo_number" class="form-control"
                            placeholder="Contoh: 9234567"
                            maxlength="7"
                            pattern="[0-9]{7}"
                            required>
                        <small class="text-muted">7 digit angka - untuk tracking di VesselFinder</small>
                    </div>

                    {{-- Container Number --}}
                    <div class="col-md-6">
                        <label class="form-label">Container Number</label>
                        <input type="text" name="container_number" class="form-control"
                            placeholder="Contoh: TRIU8935420"
                            style="text-transform: uppercase;">
                    </div>

                    {{-- ETD --}}
                    <div class="col-md-6">
                        <label class="form-label">ETD</label>
                        <input type="date" name="etd" class="form-control">
                    </div>

                    {{-- ETA SBY Port --}}
                    <div class="col-md-6">
                        <label class="form-label">ETA SBY Port</label>
                        <input type="date" name="eta_sby_port" class="form-control">
                    </div>

                    {{-- ETA PAL --}}
                    <div class="col-md-6">
                        <label class="form-label">ETA PAL</label>
                        <input type="date" name="eta_pal" class="form-control">
                    </div>

                    {{-- Remark --}}
                    <div class="col-12">
                        <label class="form-label">Remark</label>
                        <textarea name="remark" class="form-control" rows="3"></textarea>
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
@endif