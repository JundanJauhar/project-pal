<h5 class="section-title">Jaminan Pembayaran</h5>

<div class="dashboard-table-wrapper">
    <div class="table-responsive">

        {{-- Tombol Simpan Checkpoint --}}
        <div class="btn-simpan-wrapper">
            @if($currentCheckpointSequence == 7 && $jaminans->count() > 0)
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
                    <th>No</th>
                    <th>Vendor</th>
                    <th>Advance Payment Guarantee</th>
                    <th>Performance Bond</th>
                    <th>Warranty Bond</th>
                    <th>Target Terbit</th>
                    <th>Realisasi Terbit</th>
                    <th>Expiry Date</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @php
                /**
                * Vendor yang VALID untuk Jaminan Pembayaran
                * = vendor yang sudah dikirimi Inquiry & Quotation
                */
                $jaminanVendors = collect($inquiryQuotations ?? [])
                ->map(fn ($iq) => $iq->vendor)
                ->filter() // buang null
                ->unique('id_vendor') // cegah duplikat
                ->values();
                @endphp

                @php $row = 1; @endphp

                @forelse($jaminans as $jaminan)
                <tr>
                    <td>{{ $row++ }}</td>
                    <td>{{ $jaminan->vendor->name_vendor ?? '-' }}</td>
                    <td>{{ $jaminan->advance_guarantee ? '✔' : '-' }}</td>
                    <td>{{ $jaminan->performance_bond ? '✔' : '-' }}</td>
                    <td>{{ $jaminan->warranty_bond ? '✔' : '-' }}</td>
                    <td>{{ $jaminan->target_terbit?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $jaminan->realisasi_terbit?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $jaminan->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>
                        <button class="btn btn-sm btn-action-edit"
                            data-bs-toggle="modal"
                            data-bs-target="#modalEditJaminan{{ $jaminan->jaminan_pembayaran_id }}">
                            Edit
                        </button>
                    </td>
                </tr>

                {{-- ================= MODAL EDIT ================= --}}
                <div class="modal fade" id="modalEditJaminan{{ $jaminan->jaminan_pembayaran_id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <form method="POST" action="{{ route('jaminan-pembayaran.update', $jaminan->jaminan_pembayaran_id) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Jaminan Pembayaran</h5>
                                </div>

                                <div class="modal-body row g-3">
                                    {{-- vendor --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Vendor *</label>
                                        <select name="vendor_id" class="form-select" required>
                                            @foreach($jaminanVendors as $vendor)
                                            <option value="{{ $vendor->id_vendor }}"
                                                @selected($vendor->id_vendor == $jaminan->vendor_id)>
                                                {{ $vendor->name_vendor }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Advance Payment Guarantee</label>
                                        <input type="checkbox" name="advance_guarantee"
                                            @checked($jaminan->advance_guarantee)>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Performance Bond</label>
                                        <input type="checkbox" name="performance_bond"
                                            @checked($jaminan->performance_bond)>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Warranty Bond</label>
                                        <input type="checkbox" name="warranty_bond"
                                            @checked($jaminan->warranty_bond)>
                                    </div>

                                    <div class="col-md-4">
                                        <label>Target Terbit</label>
                                        <input type="date" name="target_terbit" class="form-control"
                                            value="{{ $jaminan->target_terbit?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label>Realisasi Terbit</label>
                                        <input type="date" name="realisasi_terbit" class="form-control"
                                            value="{{ $jaminan->realisasi_terbit?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-4">
                                        <label>Expiry Date</label>
                                        <input type="date" name="expiry_date" class="form-control"
                                            value="{{ $jaminan->expiry_date?->format('Y-m-d') }}">
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
                @endforelse

                {{-- ================= ROW CREATE ================= --}}
                @if($jaminans->count() == 0 && $currentCheckpointSequence == 7)
                <tr>
                    <td>{{ $row }}</td>
                    <td colspan="7" class="text-center text-muted">
                        Belum ada data Jaminan Pembayaran
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateJaminan">
                            Create
                        </button>
                    </td>
                </tr>
                @endif

                @if($jaminans->count() > 0 && $currentCheckpointSequence == 7)
                <tr>
                    <td colspan="8"></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateJaminan">
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
<div class="modal fade" id="modalCreateJaminan" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" action="{{ route('jaminan-pembayaran.store', $procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-header">
                    <h5 class="modal-title">Create Jaminan Pembayaran</h5>
                </div>

                <div class="modal-body row g-3">

                    <div class="col-md-4">
                        <label>Advance Payment Guarantee</label>
                        <input type="hidden" name="advance_guarantee" value="0">
                        <input type="checkbox" name="advance_guarantee" value="1"
                            @checked(old('advance_guarantee', $jaminan->advance_guarantee ?? false))>

                    </div>

                    <div class="col-md-4">
                        <label>Performance Bond</label>
                        <input type="hidden" name="performance_bond" value="0">
                        <input type="checkbox" name="performance_bond" value="1"
                            @checked(old('performance_bond', $jaminan->performance_bond ?? false))>

                    </div>

                    <div class="col-md-4">
                        <label>Warranty Bond</label>
                        <input type="hidden" name="warranty_bond" value="0">
                        <input type="checkbox" name="warranty_bond" value="1"
                            @checked(old('warranty_bond', $jaminan->warranty_bond ?? false))>

                    </div>

                    {{-- vendor --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Vendor *</label>
                                        <select name="vendor_id" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih Vendor --</option>

                                            @forelse($jaminanVendors as $vendor)
                                            <option value="{{ $vendor->id_vendor }}">
                                                {{ $vendor->name_vendor }}
                                            </option>
                                            @empty
                                            <option disabled>
                                                Tidak ada vendor dari Inquiry & Quotation
                                            </option>
                                            @endforelse
                                        </select>

                                        <small style="color:#666;">
                                            Vendor berasal dari Inquiry & Quotation
                                        </small>
                                    </div>

                    <div class="col-md-6">
                        <label>Target Terbit</label>
                        <input type="date" name="target_terbit" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Realisasi Terbit</label>
                        <input type="date" name="realisasi_terbit" class="form-control">
                    </div>

                    <div class="col-md-6">
                        <label>Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control">
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
