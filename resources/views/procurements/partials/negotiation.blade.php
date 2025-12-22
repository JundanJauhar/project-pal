<h5 class="section-title">Negotiation</h5>
<div class="dashboard-table-wrapper">
    <div class="table-responsive">
        <div class="btn-simpan-wrapper">
            @if($currentCheckpointSequence==4 && count($negotiations)>0)
            <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                @csrf
                <input type="hidden" name="from_checkpoint" value="4">
                <button class="btn btn-sm btn-action-simpan">
                    <i class="bi bi-box-arrow-down"></i>Simpan
                </button>
            </form>
            @endif
        </div>
        <table class="dashboard-table">
            <thead>
                <tr>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">HPS</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Budget</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Harga Final</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Kirim</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Terima</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Deviasi vs HPS</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Deviasi vs Budget</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Note</th>
                    <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @php $row = 1; @endphp
                @forelse($negotiations as $neg)
                <tr>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $row++ }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->vendor->name_vendor ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        @if($neg->hps)
                        {{ number_format($neg->hps,0,',','.') }} {{ $neg->currency_hps }}
                        @else - @endif
                    </td>

                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        @if($neg->budget)
                        {{ number_format($neg->budget,0,',','.') }} {{ $neg->currency_budget }}
                        @else - @endif
                    </td>

                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        @if($neg->harga_final)
                        {{ number_format($neg->harga_final,0,',','.') }} {{ $neg->currency_harga_final }}
                        @else - @endif
                    </td>

                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->tanggal_kirim?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->tanggal_terima?->format('d/m/Y') ?? '-' }}</td>
                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        @if(!is_null($neg->deviasi_hps))
                        @php
                        $isPositive = $neg->deviasi_hps > 0;
                        $isNegative = $neg->deviasi_hps < 0;
                            @endphp

                            <span class="{{ $isPositive || $neg->deviasi_hps == 0 ? 'text-success' : 'text-danger' }}">
                            @if($isPositive)
                            <span class="me-1">▼</span>
                            @elseif($isNegative)
                            <span class="me-1">▲</span>
                            @endif

                            {{ number_format(abs($neg->deviasi_hps), 0, ',', '.') }}
                            {{ $neg->currency_hps }}
                            </span>
                            @else
                            -
                            @endif
                    </td>

                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        @if(!is_null($neg->deviasi_budget))
                        @php
                        $isPositive = $neg->deviasi_budget > 0;
                        $isNegative = $neg->deviasi_budget < 0;
                            @endphp

                            <span class="{{ $isPositive || $neg->deviasi_budget == 0 ? 'text-success' : 'text-danger' }}">
                            @if($isPositive)
                            <span class="me-1">▼</span>
                            @elseif($isNegative)
                            <span class="me-1">▲</span>
                            @endif

                            {{ number_format(abs($neg->deviasi_budget), 0, ',', '.') }}
                            {{ $neg->currency_hps }}
                            </span>
                            @else
                            -
                            @endif
                    </td>

                    <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $neg->notes ?? '-' }}</td>

                    <td style="padding: 12px 8px; text-align: center; color: #000;">
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
                                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">
                                <div class="modal-header">
                                    <h5>Edit Negotiation</h5>
                                </div>
                                <div class="modal-body row g-3">

                                    {{-- vendor --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Vendor *</label>
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
                                        <label class="form-label">HPS</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" id="dropdownCurrencyHps{{ $neg->negotiation_id }}">
                                                {{ $neg->currency_hps ?? 'IDR' }}
                                            </button>

                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li><a class="dropdown-item" onclick="selectCurrencyEditNegotiation('Hps', '{{ $cur }}', '{{ $neg->negotiation_id }}')">{{ $cur }}</a></li>
                                                @endforeach
                                            </ul>

                                            <input type="text" name="hps" class="form-control currency-input"
                                                value="{{ $neg->hps }}">
                                            <input type="hidden" name="currency_hps" id="currencyEditHps{{ $neg->negotiation_id }}"
                                                value="{{ $neg->currency_hps }}">
                                        </div>
                                    </div>

                                    {{-- BUDGET --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Budget</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" id="dropdownCurrencyBudget{{ $neg->negotiation_id }}">
                                                {{ $neg->currency_budget ?? 'IDR' }}
                                            </button>

                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li><a class="dropdown-item" onclick="selectCurrencyEditNegotiation('Budget', '{{ $cur }}', '{{ $neg->negotiation_id }}')">{{ $cur }}</a></li>
                                                @endforeach
                                            </ul>

                                            <input type="text" name="budget" class="form-control currency-input"
                                                value="{{ $neg->budget }}">
                                            <input type="hidden" name="currency_budget" id="currencyEditBudget{{ $neg->negotiation_id }}"
                                                value="{{ $neg->currency_budget }}">
                                        </div>
                                    </div>

                                    {{-- HARGA FINAL --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Harga Final</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" id="dropdownCurrencyHargaFinal{{ $neg->negotiation_id }}">
                                                {{ $neg->currency_harga_final ?? 'IDR' }}
                                            </button>

                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li><a class="dropdown-item" onclick="selectCurrencyEditNegotiation('HargaFinal', '{{ $cur }}', '{{ $neg->negotiation_id }}')">{{ $cur }}</a></li>
                                                @endforeach
                                            </ul>

                                            <input type="text" name="harga_final" class="form-control currency-input"
                                                value="{{ $neg->harga_final }}">
                                            <input type="hidden" name="currency_harga_final" id="currencyEditHargaFinal{{ $neg->negotiation_id }}"
                                                value="{{ $neg->currency_harga_final }}">
                                        </div>
                                    </div>

                                    {{-- tanggal --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Kirim</label>
                                        <input type="date" name="tanggal_kirim"
                                            class="form-control"
                                            value="{{ $neg->tanggal_kirim?->format('Y-m-d') }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Terima</label>
                                        <input type="date" name="tanggal_terima"
                                            class="form-control"
                                            value="{{ $neg->tanggal_terima?->format('Y-m-d') }}">
                                    </div>

                                    {{-- note --}}
                                    <div class="col-12">
                                        <label class="form-label">Note</label>
                                        <textarea name="notes" class="form-control">{{ $neg->notes }}</textarea>
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
                @endempty

                {{-- IF EMPTY --}}
                @if($negotiations->count() == 0 && $currentCheckpointSequence == 4)
                <tr>
                    <td>{{ $row }}</td>
                    <td colspan="9" class="text-center text-muted">Belum ada Negotiation</td>
                    <td>
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal" data-bs-target="#modalCreateNeg">
                            Create
                        </button>
                    </td>
                </tr>
                @endif

                {{-- ================= ROW CREATE (HANYA SAAT CHECKPOINT 4) ================= --}}
                @if($negotiations->count() > 0 && $currentCheckpointSequence == 4)
                <tr>
                    <td colspan="10"></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-action-create"
                            data-bs-toggle="modal"
                            data-bs-target="#modalCreateNeg">
                            Create
                        </button>
                    </td>
                </tr>
                @endif

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
                                        <label class="form-label">Vendor *</label>
                                        <select name="vendor_id" class="form-select" required>
                                            <option value="" disabled selected>-- Pilih Vendor --</option>
                                            @foreach($vendors as $v)
                                            <option value="{{ $v->id_vendor }}">{{ $v->name_vendor }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- HPS --}}
                                    <div class="col-md-6">
                                        <label class="form-label">HPS</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCurrencyHpsCreate" data-bs-toggle="dropdown">
                                                IDR
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li>
                                                    <a class="dropdown-item"
                                                        onclick="selectCurrencyCreateNegotiation('Hps','{{ $cur }}')">
                                                        {{ $cur }}
                                                    </a>
                                                </li>
                                                @endforeach
                                            </ul>

                                            <input type="text" name="hps" class="form-control currency-input" placeholder="0">
                                            <input type="hidden" name="currency_hps" id="currencyCreateHps" value="IDR">
                                        </div>
                                    </div>

                                    {{-- BUDGET --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Budget</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCurrencyBudgetCreate" data-bs-toggle="dropdown">
                                                IDR
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li>
                                                    <a class="dropdown-item"
                                                        onclick="selectCurrencyCreateNegotiation('Budget','{{ $cur }}')">
                                                        {{ $cur }}
                                                    </a>
                                                </li>
                                                @endforeach
                                            </ul>

                                            <input type="text" name="budget" class="form-control currency-input" placeholder="0">
                                            <input type="hidden" name="currency_budget" id="currencyCreateBudget" value="IDR">
                                        </div>
                                    </div>

                                    {{-- HARGA FINAL --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Harga Final</label>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCurrencyHargaFinalCreate" data-bs-toggle="dropdown">
                                                IDR
                                            </button>
                                            <ul class="dropdown-menu">
                                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                                <li>
                                                    <a class="dropdown-item"
                                                        onclick="selectCurrencyCreateNegotiation('HargaFinal','{{ $cur }}')">
                                                        {{ $cur }}
                                                    </a>
                                                </li>
                                                @endforeach
                                            </ul>

                                            <input type="text" name="harga_final" class="form-control currency-input" placeholder="0">
                                            <input type="hidden" name="currency_harga_final" id="currencyCreateHargaFinal" value="IDR">
                                        </div>
                                    </div>

                                    {{-- tanggal --}}
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Kirim</label>
                                        <input type="date" name="tanggal_kirim" class="form-control">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Terima</label>
                                        <input type="date" name="tanggal_terima" class="form-control">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Note</label>
                                        <textarea name="notes" class="form-control" rows="3"></textarea>
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

            </tbody>
        </table>
    </div>
</div>