<div id="evatek">
    <h5 class="section-title">Evatek</h5>

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
            {{-- TABLE --}}
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">No</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Nama Item</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Start</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Target</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Revisi</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Revision</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Hasil</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Hasil</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    /**
                    * HITUNG KONDISI TERLEBIH DAHULU
                    */
                    
                    // Vendor yang VALID untuk Evatek (dari Inquiry & Quotation)
                    $evatekVendors = collect($inquiryQuotations ?? [])
                        ->map(fn ($iq) => $iq->vendor)
                        ->filter()
                        ->unique('id_vendor')
                        ->values();

                    // Semua items yang ada di procurement
                    $allItems = collect();
                    foreach ($procurement->requestProcurements as $request) {
                        foreach ($request->items as $item) {
                            $allItems->push([
                                'item' => $item,
                                'request' => $request,
                            ]);
                        }
                    }

                    // Item IDs yang sudah memiliki evatek
                    $evatekItemIds = $evatekItems->pluck('item_id')->toArray();

                    // Item yang belum memiliki evatek
                    $uncompletedItems = $allItems->filter(fn ($itemData) => 
                        !in_array($itemData['item']->item_id, $evatekItemIds)
                    )->values();

                    // Total items
                    $totalItems = $allItems->count();
                    $evatekCount = $evatekItems->count();
                    $uncompleteCount = $uncompletedItems->count();
                    @endphp

                    {{-- ✅ TAMPILKAN EVATEK ITEMS YANG SUDAH ADA --}}
                    @if($evatekCount > 0)
                        @foreach($evatekItems as $evatek)
                        @php
                        $item = $evatek->item;
                        $latestRevision = $evatek->revisions?->first();
                        @endphp
                        <tr>
                            {{-- No --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $loop->iteration }}
                            </td>

                            {{-- Nama Item --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $item->item_name }}
                            </td>

                            {{-- Tanggal Start --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $evatek->start_date->format('d/m/Y') }}
                            </td>

                            {{-- Tanggal Target --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $evatek->target_date?->format('d/m/Y') ?? '-' }}
                            </td>

                            {{-- Tanggal Revisi --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @if($latestRevision)
                                {{ \Carbon\Carbon::parse($latestRevision->date)->format('d/m/Y') }}
                                @else
                                <span style="color: #999;">-</span>
                                @endif
                            </td>

                            {{-- Revision --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                <span style="padding: 4px 12px; border-radius: 4px; font-weight: 600;">
                                    {{ $evatek->current_revision }}
                                </span>
                            </td>

                            {{-- Tanggal Hasil --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @if($latestRevision)
                                @php
                                $resultDate = $latestRevision->approved_at
                                    ?? $latestRevision->not_approved_at
                                    ?? $latestRevision->date;
                                @endphp
                                @if($resultDate)
                                {{ \Carbon\Carbon::parse($resultDate)->format('d/m/Y') }}
                                @else
                                <span style="color: #999;">-</span>
                                @endif
                                @else
                                <span style="color: #999;">-</span>
                                @endif
                            </td>

                            {{-- Hasil Evatek --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                @php
                                $statusColors = [
                                    'on_progress' => ['text' => '#ECAD02', 'label' => 'On Progress'],
                                    'approve' => ['text' => '#28AC00', 'label' => 'Approved'],
                                    'not_approve' => ['text' => '#F10303', 'label' => 'Rejected'],
                                ];
                                $statusConfig = $statusColors[$evatek->status] ?? ['text' => '#383d41', 'label' => ucfirst($evatek->status)];
                                @endphp
                                <span style="color: {{ $statusConfig['text'] }}; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 14px;">
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>

                            {{-- Vendor --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $evatek->vendor->name_vendor ?? '-' }}
                            </td>

                            {{-- Aksi --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                <a href="{{ route('desain.review-evatek', $evatek->evatek_id) }}"
                                    class="btn btn-sm btn-action-review">
                                    Review
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- ✅ TAMPILKAN ROW CREATE UNTUK ITEMS YANG BELUM PUNYA EVATEK --}}
                    @if($uncompleteCount > 0)
                        @foreach($uncompletedItems as $itemData)
                        @php
                        $item = $itemData['item'];
                        $request = $itemData['request'];
                        $rowNum = $evatekCount + $loop->iteration;
                        @endphp
                        <tr>
                            {{-- No --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $rowNum }}
                            </td>

                            {{-- Nama Item --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $item->item_name }}
                            </td>

                            {{-- Tanggal Start --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                            {{-- Tanggal Target --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                            {{-- Tanggal Revisi --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                            {{-- Revision --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                            {{-- Tanggal Hasil --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                            {{-- Hasil --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                            {{-- Vendor --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                {{ $request->vendor->name_vendor ?? '-' }}
                            </td>

                            {{-- Aksi --}}
                            <td style="padding: 12px 8px; text-align: center; color: #000;">
                                <button type="button"
                                    class="btn btn-sm btn-action-create"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCreateEvatek{{ $item->item_id }}">
                                    Create
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endif

                    {{-- ✅ EMPTY STATE: HANYA JIKA TIDAK ADA ITEM SAMA SEKALI --}}
                    @if($totalItems == 0)
                    <tr>
                        <td colspan="10" class="text-center text-muted" style="padding: 12px 8px;">
                            Tidak ada item untuk dimasukkan ke Evatek
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL CREATE EVATEK (LUAR TABLE) --}}
{{-- ============================================ --}}
@if($uncompleteCount > 0)
    @foreach($uncompletedItems as $itemData)
    @php
    $item = $itemData['item'];
    $request = $itemData['request'];
    @endphp

    <div class="modal fade" id="modalCreateEvatek{{ $item->item_id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Input Evatek - {{ $item->item_name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <form method="POST" action="{{ route('supply-chain.evatek-item.store', $procurement->procurement_id) }}">
                    @csrf
                    <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">
                    <input type="hidden" name="item_id" value="{{ $item->item_id }}">

                    <div class="modal-body">
                        {{-- Tanggal Target Input --}}
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 14px;">Tanggal Target *</label>
                            <input type="date"
                                name="target_date"
                                class="form-control"
                                value="{{ $procurement->end_date->format('Y-m-d') }}"
                                min="{{ now()->toDateString() }}"
                                required
                                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <small style="color: #666;">Default: Tanggal target procurement</small>
                        </div>

                        {{-- Vendor Selection (HANYA dari Inquiry & Quotation) --}}
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; font-size: 14px;">
                                Pilih Vendor *
                            </label>

                            <select name="vendor_ids[]"
                                class="form-select"
                                multiple
                                size="4"
                                required
                                style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">

                                @if($evatekVendors->count() > 0)
                                    @foreach($evatekVendors as $vendor)
                                    <option value="{{ $vendor->id_vendor }}">
                                        {{ $vendor->name_vendor }}
                                    </option>
                                    @endforeach
                                @else
                                    <option disabled>
                                        Tidak ada vendor dari Inquiry & Quotation
                                    </option>
                                @endif
                            </select>

                            <small style="color: #666;">
                                Vendor berasal dari Inquiry & Quotation yang sudah dibuat
                            </small>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-action-create">Buat Evatek</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach
@endif