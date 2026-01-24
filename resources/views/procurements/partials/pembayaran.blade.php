<div id="pembayaran">
    <h5 class="section-title">Pembayaran</h5>

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
                @if($currentCheckpointSequence == 7 && $pembayarans->count() > 0)
                <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="from_checkpoint" value="7">
                    <button class="btn btn-sm btn-action-simpan">
                        <i class="bi bi-box-arrow-down"></i> Simpan
                    </button>
                </form>
                @endif
            </div>

            {{-- TABLE --}}
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status Pembayaran</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Persen</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nilai Pembayaran</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No Memo</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Link</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Target</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Realisasi</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    /**
                    * HITUNG KONDISI TERLEBIH DAHULU
                    */
                    
                    // Total pembayarans
                    $pembayaranCount = $pembayarans->count();
                    @endphp

                    {{-- ✅ TAMPILKAN PEMBAYARAN ITEMS YANG SUDAH ADA --}}
                    @if($pembayaranCount > 0)
                        @foreach($pembayarans as $pay)
                        <tr>
                            {{-- No --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $loop->iteration }}</td>

                            {{-- Vendor --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pay->vendor->name_vendor }}</td>

                            {{-- Status Pembayaran --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pay->payment_type }}</td>

                            {{-- Persen --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pay->percentage }}%</td>

                            {{-- Nilai Pembayaran --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $pay->currency }} {{ number_format($pay->payment_value,0,',','.') }}
                            </td>

                            {{-- No Memo --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pay->no_memo }}</td>

                            {{-- Link --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @if($pay->link)
                                <a href="{{ $pay->link }}" target="_blank">Link</a>
                                @else
                                -
                                @endif
                            </td>

                            {{-- Target --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pay->target_date?->format('d/m/Y') }}</td>

                            {{-- Realisasi --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $pay->realization_date?->format('d/m/Y') ?? '-' }}</td>

                            {{-- Aksi --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                <button class="btn btn-sm btn-action-edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditPembayaran{{ $pay->id }}">
                                    Edit
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- ✅ EMPTY STATE DENGAN CREATE BUTTON (HANYA SAAT CHECKPOINT 7 & TIDAK ADA PEMBAYARAN) --}}
                    @if($pembayaranCount == 0 && $currentCheckpointSequence == 7)
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 12px 8px;">
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
                    @endif

                    {{-- ✅ ROW CREATE (HANYA SAAT CHECKPOINT 7 & ADA PEMBAYARAN) --}}
                    @if($pembayaranCount > 0 && $currentCheckpointSequence == 7)
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
</div>

{{-- ============================================ --}}
{{-- MODAL EDIT (LUAR TABLE) --}}
{{-- ============================================ --}}
@if($pembayaranCount > 0)
    @foreach($pembayarans as $pay)
    <div class="modal fade" id="modalEditPembayaran{{ $pay->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('pembayaran.update',$pay->id) }}">
                    @csrf

                    <div class="modal-body row g-3">
                        {{-- Jenis Pembayaran --}}
                        <div class="col-md-6">
                            <label class="form-label">Jenis Pembayaran</label>
                            <select name="payment_type" class="form-select">
                                @foreach(['SKBDN','L/C','TT'] as $type)
                                <option value="{{ $type }}" @selected($pay->payment_type==$type)>
                                    {{ $type }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Persentase --}}
                        <div class="col-md-6">
                            <label class="form-label">Persentase (%)</label>
                            <input type="number" name="percentage" class="form-control"
                                value="{{ $pay->percentage }}">
                        </div>

                        {{-- No Memo --}}
                        <div class="col-md-6">
                            <label class="form-label">No Memo</label>
                            <input type="text" name="no_memo" class="form-control"
                                value="{{ $pay->no_memo }}">
                        </div>

                        {{-- Link --}}
                        <div class="col-md-6">
                            <label class="form-label">Link</label>
                            <input type="url" name="link" class="form-control"
                                value="{{ $pay->link }}">
                        </div>

                        {{-- Target Date --}}
                        <div class="col-md-6">
                            <label class="form-label">Target</label>
                            <input type="date" name="target_date" class="form-control"
                                value="{{ $pay->target_date?->format('Y-m-d') }}">
                        </div>

                        {{-- Realization Date --}}
                        <div class="col-md-6">
                            <label class="form-label">Realisasi</label>
                            <input type="date" name="realization_date" class="form-control"
                                value="{{ $pay->realization_date?->format('Y-m-d') }}">
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
    @endforeach
@endif

{{-- ============================================ --}}
{{-- MODAL CREATE (LUAR TABLE) --}}
{{-- ============================================ --}}
@if($currentCheckpointSequence == 7)
<div class="modal fade" id="modalCreatePembayaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('pembayaran.store',$procurement->procurement_id) }}">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">
                    {{-- Jenis Pembayaran --}}
                    <div class="col-md-6">
                        <label class="form-label">Jenis Pembayaran *</label>
                        <select name="payment_type" class="form-select" required>
                            <option value="" disabled selected>Pilih</option>
                            <option value="SKBDN">SKBDN</option>
                            <option value="L/C">L/C</option>
                            <option value="TT">TT</option>
                        </select>
                    </div>

                    {{-- Persentase --}}
                    <div class="col-md-6">
                        <label class="form-label">Persentase (%) *</label>
                        <input type="number" name="percentage" class="form-control" required>
                    </div>

                    {{-- No Memo --}}
                    <div class="col-md-6">
                        <label class="form-label">No Memo</label>
                        <input type="text" name="no_memo" class="form-control">
                    </div>

                    {{-- Link --}}
                    <div class="col-md-6">
                        <label class="form-label">Link</label>
                        <input type="url" name="link" class="form-control">
                    </div>

                    {{-- Target Date --}}
                    <div class="col-md-6">
                        <label class="form-label">Target</label>
                        <input type="date" name="target_date" class="form-control">
                    </div>

                    {{-- Realization Date (Opsional di Create) --}}
                    <div class="col-md-6">
                        <label class="form-label">Realisasi</label>
                        <input type="date" name="realization_date" class="form-control">
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