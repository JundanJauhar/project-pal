<div id="contract-review">
<h5 class="section-title">Review Kontrak</h5>
<div class="dashboard-table-wrapper">
    <div class="table-responsive">
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
                $row = 0;
                // Collect vendor IDs yang sudah punya contract review
                $reviewedVendorIds = $contractReviews->pluck('vendor_id')->toArray();
                @endphp

                {{-- ✅ TAMPILKAN CONTRACT REVIEWS YANG SUDAH ADA --}}
                @forelse($contractReviews as $review)
                @php
                $row++;
                $latestRevision = $review->revisions?->first();
                @endphp
                <tr>
                    {{-- No --}}
                    <td style="padding: 12px 8px; text-align: center; color: #000;">
                        {{ $row }}
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
                @empty
                @endforelse

                {{-- ✅ TAMPILKAN FORM CREATE UNTUK VENDOR YANG BELUM PUNYA REVIEW --}}
                @forelse($pengadaanOcVendors as $vendor)
                    {{-- ✅ HANYA TAMPILKAN JIKA VENDOR INI BELUM PUNYA CONTRACT REVIEW --}}
                    @if(!in_array($vendor->id_vendor, $reviewedVendorIds))
                    @php $row++; @endphp
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $row }}
                        </td>

                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            {{ $vendor->name_vendor }}
                        </td>

                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>

                        {{-- Aksi --}}
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            <button type="button"
                                class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalContractReview{{ $row }}">
                                Create
                            </button>

                            <div class="modal fade" id="modalContractReview{{ $row }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Buat Review Kontrak - {{ $vendor->name_vendor }}</h5>
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
                        </td>
                    </tr>
                    @endif
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        Tidak ada vendor dari Pengadaan OC yang sudah selesai.
                    </td>
                </tr>
                @endforelse

                {{-- ✅ JIKA TIDAK ADA REVIEW DAN TIDAK ADA VENDOR --}}
                @if($contractReviews->count() === 0 && $pengadaanOcVendors->count() === 0)
                <tr>
                    <td colspan="9" class="text-center py-4">
                        Belum ada Pengadaan OC yang selesai untuk review kontrak.
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
</div>
