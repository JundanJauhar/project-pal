<div id="contract-review">
    <h5 class="section-title">Review Kontrak</h5>

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
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Start</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tgl Kirim ke Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tgl Feedback Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Revision</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Hasil</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Status</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php
                    /**
                    * HITUNG KONDISI TERLEBIH DAHULU
                    */

                    // Vendor IDs yang sudah punya contract review
                    $reviewedVendorIds = $contractReviews->pluck('vendor_id')->toArray();

                    // Vendor yang belum punya contract review
                    $uncompletedVendors = $pengadaanOcVendors->filter(fn ($vendor) =>
                    !in_array($vendor->id_vendor, $reviewedVendorIds)
                    )->values();

                    // Total reviews dan vendors
                    $reviewCount = $contractReviews->count();
                    $uncompletedCount = $uncompletedVendors->count();
                    $totalVendors = $pengadaanOcVendors->count();
                    @endphp

                    {{-- ✅ TAMPILKAN CONTRACT REVIEWS YANG SUDAH ADA --}}
                    @if($reviewCount > 0)
                    @foreach($contractReviews as $review)
                    @php
                    $latestRevision = $review->revisions?->first();
                    @endphp
                    <tr>
                        {{-- No --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $loop->iteration }}
                        </td>

                        {{-- Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $review->vendor->name_vendor }}
                        </td>

                        {{-- Tanggal Start --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $review->start_date->format('d/m/Y') }}
                        </td>

                        {{-- Tanggal Kirim ke Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($latestRevision && $latestRevision->date_sent_to_vendor)
                            {{ \Carbon\Carbon::parse($latestRevision->date_sent_to_vendor)->format('d/m/Y') }}
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                        </td>

                        {{-- Tanggal Feedback Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($latestRevision && $latestRevision->date_vendor_feedback)
                            {{ \Carbon\Carbon::parse($latestRevision->date_vendor_feedback)->format('d/m/Y') }}
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                        </td>

                        {{-- Revision --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <span style="padding: 4px 12px; border-radius: 4px; font-weight: 600;">
                                {{ $review->current_revision }}
                            </span>
                        </td>

                        {{-- Tanggal Hasil --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($latestRevision && $latestRevision->date_result)
                            {{ \Carbon\Carbon::parse($latestRevision->date_result)->format('d/m/Y') }}
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @php
                            $statusColors = [
                            'on_progress' => ['text' => '#ECAD02', 'label' => 'On Progress'],
                            'waiting_feedback' => ['text' => '#0066CC', 'label' => 'Waiting Feedback'],
                            'completed' => ['text' => '#28AC00', 'label' => 'Completed'],
                            ];
                            $statusConfig = $statusColors[$review->status] ?? ['text' => '#383d41', 'label' => ucfirst($review->status)];
                            @endphp
                            <span style="color: {{ $statusConfig['text'] }}; padding: 6px 12px; border-radius: 4px; font-weight: 600; font-size: 14px;">
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <a href="{{ route('supply-chain.contract-review.show', $review->contract_review_id) }}"
                                class="btn btn-sm btn-action-review">
                                Review
                            </a>
                        </td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- ✅ TAMPILKAN ROW CREATE UNTUK VENDOR YANG BELUM PUNYA REVIEW --}}
                    @if($uncompletedCount > 0)
                    @foreach($uncompletedVendors as $vendor)
                    @php
                    $rowNum = $reviewCount + $loop->iteration;
                    @endphp
                    <tr>
                        {{-- No --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $rowNum }}
                        </td>

                        {{-- Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $vendor->name_vendor }}
                        </td>

                        {{-- Tanggal Start --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                        {{-- Tgl Kirim ke Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                        {{-- Tgl Feedback Vendor --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                        {{-- Revision --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                        {{-- Tanggal Hasil --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                        {{-- Status --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">-</td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <button type="button"
                                class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalContractReview{{ $vendor->id_vendor }}">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endforeach
                    @endif

                    {{-- ✅ EMPTY STATE: HANYA JIKA TIDAK ADA VENDOR SAMA SEKALI --}}
                    @if($totalVendors == 0)
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 12px 8px;">
                            Belum ada Pengadaan OC yang selesai untuk review kontrak
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- MODAL CREATE (LUAR TABLE) --}}
{{-- ============================================ --}}
@if($uncompletedCount > 0)
@foreach($uncompletedVendors as $vendor)
<div class="modal fade" id="modalContractReview{{ $vendor->id_vendor }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Review Kontrak - {{ $vendor->name_vendor }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" action="{{ route('supply-chain.contract-review.store', $procurement->procurement_id) }}">
                @csrf

                <div class="modal-body">
                    <input type="hidden" name="vendor_id" value="{{ $vendor->id_vendor }}">

                    {{-- Tanggal Start --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 14px;">Tanggal Start *</label>
                        <input type="date"
                            name="start_date"
                            class="form-control"
                            value="{{ now()->format('Y-m-d') }}"
                            required
                            style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        <small style="color: #666;">Tanggal mulai review kontrak</small>
                    </div>

                    {{-- Remarks --}}
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 14px;">
                            Remarks (Opsional)
                        </label>
                        <textarea name="remarks"
                            class="form-control"
                            rows="3"
                            placeholder="Catatan awal untuk review kontrak ini"
                            style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;"></textarea>
                        <small style="color: #666;">
                            Catatan tambahan (opsional)
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-action-abort" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm btn-action-create">Buat Review</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endif