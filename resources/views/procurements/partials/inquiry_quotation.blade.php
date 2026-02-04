<div id="inquiry">
    <h5 class="section-title">Inquiry & Quotation</h5>

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
                @if($currentCheckpointSequence==2 && count($inquiryQuotations)>0)
                <form action="{{ route('checkpoint.transition', $procurement->procurement_id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="from_checkpoint" value="2">
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
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Vendor</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Inquiry</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Tanggal Quotation</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Target Quotation</th>
                        <th style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">Lead Time</th>
                        <th style="padding: 12px 8px; text-align: center; color: #000;">Nilai Harga</th>
                        <th style="padding: 12px 8px; text-align: center; color: #000;">Link</th>
                        <th style="padding: 12px 8px; text-align: center; color: #000;">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @php $row = 1; @endphp
                    @forelse($inquiryQuotations as $iq)
                    <tr>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $row++ }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->vendor->name_vendor ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->tanggal_inquiry->format('d/m/Y') }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->tanggal_quotation ? $iq->tanggal_quotation->format('d/m/Y') : '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->target_quotation ? $iq->target_quotation->format('d/m/Y') : '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">{{ $iq->lead_time ?? '-' }}</td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($iq->nilai_harga)
                            {{ $iq->currency }} {{ number_format($iq->nilai_harga, 0, ',', '.') }}
                            @else
                            -
                            @endif
                        </td>
                        <td style="padding: 12px 8px; text-align: center; color: #000;">
                            @if($iq->link)
                            <a href="{{ $iq->link }}" target="_blank" style="color: #0066cc; text-decoration: underline; font-weight: 600;">Link</a>
                            @else
                            <span style="color: #999;">-</span>
                            @endif
                        </td>
                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #000;">
                            <button class="btn btn-sm btn-action-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditIQ{{ $iq->inquiry_quotation_id }}">
                                Edit
                            </button>
                        </td>
                    </tr>
                    @empty
                    @endforelse
                    @if($inquiryQuotations->count() == 0 && $currentCheckpointSequence == 2)
                    <tr>
                        <td>{{ $row }}</td>
                        <td colspan="7" class="text-center text-muted">
                            Belum ada Inquiry & Quotation</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateIQ">
                                Create
                            </button>
                        </td>
                    </tr>
                    @endif

                    {{-- ================= ROW CREATE (HANYA SAAT CHECKPOINT 2) ================= --}}
                    @if($inquiryQuotations->count() > 0 && $currentCheckpointSequence == 2)
                    <tr>
                        <td colspan="8"></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-action-create"
                                data-bs-toggle="modal"
                                data-bs-target="#modalCreateIQ">
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
@forelse($inquiryQuotations as $iq)
<div class="modal fade iq-modal" id="modalEditIQ{{ $iq->inquiry_quotation_id }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Inquiry & Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" 
                  action="{{ route('inquiry-quotation.update', $iq->inquiry_quotation_id) }}"
                  onsubmit="return validateVendor(this)">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">
                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Vendor *</label>
                        <div class="position-relative">
                            <input type="text"
                                class="form-control vendor-search-input"
                                data-dropdown-id="vendorDropdownEdit{{ $iq->inquiry_quotation_id }}"
                                data-vendor-id-target="vendorIdEdit{{ $iq->inquiry_quotation_id }}"
                                placeholder="Cari vendor..."
                                autocomplete="off"
                                value="{{ $iq->vendor->name_vendor ?? '' }}"
                                required>

                            <input type="hidden"
                                name="vendor_id"
                                id="vendorIdEdit{{ $iq->inquiry_quotation_id }}"
                                value="{{ $iq->vendor_id }}">

                            <div id="vendorDropdownEdit{{ $iq->inquiry_quotation_id }}"
                                 class="list-group position-absolute w-100 shadow d-none"
                                 style="z-index: 1055; max-height: 220px; overflow-y: auto; top: 100%; left: 0;">
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal Inquiry --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Inquiry *</label>
                        <input type="date" name="tanggal_inquiry" class="form-control"
                            value="{{ $iq->tanggal_inquiry->format('Y-m-d') }}" required>
                    </div>

                    {{-- Tanggal Quotation --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Quotation</label>
                        <input type="date" name="tanggal_quotation" class="form-control"
                            value="{{ $iq->tanggal_quotation?->format('Y-m-d') }}">
                    </div>

                    {{-- Target Quotation --}}
                    <div class="col-md-6">
                        <label class="form-label">Target Quotation</label>
                        <input type="date" name="target_quotation" class="form-control"
                            value="{{ $iq->target_quotation?->format('Y-m-d') }}">
                    </div>

                    {{-- Lead Time --}}
                    <div class="col-md-6">
                        <label class="form-label">Lead Time</label>
                        <input type="text" name="lead_time" class="form-control"
                            value="{{ $iq->lead_time }}">
                    </div>

                    {{-- Nilai Harga --}}
                    <div class="col-md-6">
                        <label class="form-label">Nilai Harga</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown" id="dropdownCurrency{{ $iq->inquiry_quotation_id }}">
                                {{ $iq->currency ?? 'IDR' }}
                            </button>
                            <ul class="dropdown-menu">
                                @foreach(['IDR','USD','EUR','SGD'] as $cur)
                                <li>
                                    <a class="dropdown-item" onclick="selectCurrencyEdit('{{ $cur }}', '{{ $iq->inquiry_quotation_id }}')">
                                        {{ $cur }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>

                            <input type="text"
                                class="form-control currency-input"
                                data-raw-target="nilaiHargaRaw{{ $iq->inquiry_quotation_id }}"
                                value="{{ number_format($iq->nilai_harga ?? 0, 0, ',', '.') }}">

                            <input type="hidden"
                                name="nilai_harga"
                                id="nilaiHargaRaw{{ $iq->inquiry_quotation_id }}"
                                value="{{ $iq->nilai_harga ?? '' }}">

                            <input type="hidden"
                                name="currency"
                                id="currencyEdit{{ $iq->inquiry_quotation_id }}"
                                value="{{ $iq->currency }}">
                        </div>
                    </div>

                    {{-- Link --}}
                    <div class="col-12">
                        <label class="form-label">Link</label>
                        <input type="url" name="link" class="form-control"
                            value="{{ $iq->link }}">
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control">{{ $iq->notes }}</textarea>
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
@if($currentCheckpointSequence == 2)
<div class="modal fade iq-modal" id="modalCreateIQ" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Inquiry & Quotation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" 
                  action="{{ route('inquiry-quotation.store', $procurement->procurement_id) }}"
                  onsubmit="return validateVendor(this)">
                @csrf
                <input type="hidden" name="procurement_id" value="{{ $procurement->procurement_id }}">

                <div class="modal-body row g-3">
                    {{-- Vendor --}}
                    <div class="col-md-6">
                        <label class="form-label">Pilih Vendor *</label>
                        <div class="position-relative">
                            <input type="text"
                                class="form-control vendor-search-input"
                                data-dropdown-id="vendorDropdownCreate"
                                data-vendor-id-target="vendorIdCreate"
                                placeholder="Cari vendor..."
                                autocomplete="off"
                                required>

                            <input type="hidden"
                                name="vendor_id"
                                id="vendorIdCreate"
                                value="">

                            <div id="vendorDropdownCreate"
                                 class="list-group position-absolute w-100 shadow d-none"
                                 style="z-index: 1055; max-height: 220px; overflow-y: auto; top: 100%; left: 0;">
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal Inquiry --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Inquiry *</label>
                        <input type="date" name="tanggal_inquiry" class="form-control" required>
                    </div>

                    {{-- Tanggal Quotation --}}
                    <div class="col-md-6">
                        <label class="form-label">Tanggal Quotation</label>
                        <input type="date" name="tanggal_quotation" class="form-control">
                    </div>

                    {{-- Target Quotation --}}
                    <div class="col-md-6">
                        <label class="form-label">Target Quotation</label>
                        <input type="date" name="target_quotation" class="form-control">
                    </div>

                    {{-- Lead Time --}}
                    <div class="col-md-6">
                        <label class="form-label">Lead Time</label>
                        <input type="text" name="lead_time" class="form-control" placeholder="ex: 7 hari kerja">
                    </div>

                    {{-- Nilai Harga --}}
                    <div class="col-md-6">
                        <label class="form-label">Nilai Harga</label>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownCurrency" data-bs-toggle="dropdown">
                                IDR
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" onclick="selectCurrency('IDR')">IDR</a></li>
                                <li><a class="dropdown-item" onclick="selectCurrency('USD')">USD</a></li>
                                <li><a class="dropdown-item" onclick="selectCurrency('EUR')">EUR</a></li>
                                <li><a class="dropdown-item" onclick="selectCurrency('SGD')">SGD</a></li>
                            </ul>

                            <input type="text"
                                class="form-control currency-input"
                                data-raw-target="nilaiHargaRawCreate"
                                placeholder="0">

                            <input type="hidden"
                                name="nilai_harga"
                                id="nilaiHargaRawCreate"
                                value="">

                            <input type="hidden"
                                name="currency"
                                id="currencyInput"
                                value="IDR">
                        </div>
                    </div>

                    {{-- Link --}}
                    <div class="col-12">
                        <label class="form-label">Link</label>
                        <input type="url" name="link" class="form-control">
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label class="form-label">Catatan</label>
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
@endif

{{-- ============================================ --}}
{{-- VENDOR DATA (untuk autocomplete) --}}
{{-- ============================================ --}}
<script>
const vendorsData = @json(
    $vendors->map(fn($v) => [
        'id' => $v->id_vendor,
        'name' => $v->name_vendor
    ])->values()
);
</script>

{{-- ============================================ --}}
{{-- OPTIONAL ENHANCEMENT: CSS STYLING --}}
{{-- ============================================ --}}
<style>
/* Highlight styling untuk keyword (OPTIONAL ENHANCEMENT B) */
.vendor-highlight {
    background-color: #fff3cd;
    font-weight: bold;
    padding: 0 2px;
}

/* Active item styling untuk keyboard navigation (OPTIONAL ENHANCEMENT C) */
.list-group-item.active {
    background-color: #e7f3ff;
    border-color: #0066cc;
    color: #000;
    font-weight: 500;
}
</style>

{{-- ============================================ --}}
{{-- VENDOR AUTOCOMPLETE SCRIPT (WITH ENHANCEMENTS) --}}
{{-- ============================================ --}}
<script>
// Tracking variable untuk keyboard navigation (OPTIONAL ENHANCEMENT C)
let selectedIndexMap = {};  // Store selected index per dropdown

document.addEventListener('DOMContentLoaded', function () {
    // Init semua vendor search input
    document.querySelectorAll('.vendor-search-input').forEach(input => {
        const dropdownId = input.dataset.dropdownId;
        const vendorIdTarget = input.dataset.vendorIdTarget;

        const dropdown = document.getElementById(dropdownId);
        const hiddenInput = document.getElementById(vendorIdTarget);

        if (!dropdown || !hiddenInput) {
            console.warn(`Dropdown atau hidden input tidak ditemukan: ${dropdownId}`);
            return;
        }

        // Initialize selected index for this dropdown
        selectedIndexMap[dropdownId] = -1;

        // ===== EVENT: INPUT SEARCH =====
        input.addEventListener('input', function () {
            const keyword = this.value.toLowerCase().trim();
            dropdown.innerHTML = '';
            hiddenInput.value = '';
            selectedIndexMap[dropdownId] = -1;  // Reset selection

            if (!keyword) {
                dropdown.classList.add('d-none');
                return;
            }

            const matches = vendorsData.filter(v =>
                v.name.toLowerCase().includes(keyword)
            );

            if (matches.length === 0) {
                const noResult = document.createElement('div');
                noResult.className = 'list-group-item text-muted';
                noResult.textContent = 'Vendor tidak ditemukan';
                dropdown.appendChild(noResult);
                dropdown.classList.remove('d-none');
                return;
            }

            // Render dropdown items
            matches.forEach((v, index) => {
                const item = document.createElement('button');
                item.type = 'button';
                item.className = 'list-group-item list-group-item-action';
                item.dataset.index = index;  // Store index for keyboard nav

                // ✨ OPTIONAL ENHANCEMENT B: Highlight keyword
                const highlightedName = v.name.replace(
                    new RegExp(`(${keyword})`, 'gi'),
                    '<strong>$1</strong>'
                );
                item.innerHTML = highlightedName;

                // Handle click pada dropdown item
                item.onclick = (e) => {
                    e.preventDefault();
                    selectVendor(input, hiddenInput, dropdown, v, dropdownId);
                };

                dropdown.appendChild(item);
            });

            dropdown.classList.remove('d-none');
        });

        // ===== EVENT: BLUR (KELUAR DARI INPUT) =====
        input.addEventListener('blur', () => {
            setTimeout(() => {
                dropdown.classList.add('d-none');
            }, 200);
        });

        // ===== EVENT: KEYBOARD NAVIGATION (OPTIONAL ENHANCEMENT C) =====
        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('.list-group-item-action');
            
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndexMap[dropdownId] = Math.min(
                    selectedIndexMap[dropdownId] + 1,
                    items.length - 1
                );
                updateKeyboardSelection(items, dropdownId);
            }
            else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndexMap[dropdownId] = Math.max(
                    selectedIndexMap[dropdownId] - 1,
                    -1
                );
                updateKeyboardSelection(items, dropdownId);
            }
            else if (e.key === 'Enter' && selectedIndexMap[dropdownId] >= 0) {
                e.preventDefault();
                items[selectedIndexMap[dropdownId]].click();
            }
            else if (e.key === 'Escape') {
                dropdown.classList.add('d-none');
            }
        });
    });

    // ===== EVENT: KLIK DI LUAR DROPDOWN =====
    document.addEventListener('click', e => {
        if (!e.target.closest('.vendor-search-input') &&
            !e.target.closest('.list-group')) {
            document.querySelectorAll('[id^="vendorDropdown"]').forEach(d => {
                d.classList.add('d-none');
            });
        }
    });
});

/**
 * Select vendor dan update display
 */
function selectVendor(input, hiddenInput, dropdown, vendor, dropdownId) {
    input.value = vendor.name;
    hiddenInput.value = vendor.id;
    dropdown.classList.add('d-none');
    selectedIndexMap[dropdownId] = -1;  // Reset selection
}

/**
 * Update keyboard selection visual (OPTIONAL ENHANCEMENT C)
 */
function updateKeyboardSelection(items, dropdownId) {
    const selectedIndex = selectedIndexMap[dropdownId];
    
    items.forEach((item, index) => {
        if (index === selectedIndex) {
            item.classList.add('active');
            item.scrollIntoView({ block: 'nearest' });
        } else {
            item.classList.remove('active');
        }
    });
}

/**
 * Validasi vendor_id sebelum submit (OPTIONAL ENHANCEMENT A)
 */
function validateVendor(form) {
    const vendorIdInput = form.querySelector('input[name="vendor_id"]');
    
    if (!vendorIdInput) {
        return true;
    }
    
    if (!vendorIdInput.value.trim()) {
        alert('⚠️ Silakan pilih vendor dari daftar dropdown');
        const vendorInput = form.querySelector('.vendor-search-input');
        if (vendorInput) vendorInput.focus();
        return false;
    }
    
    return true;
}
</script>

{{-- ============================================ --}}
{{-- CURRENCY INPUT HANDLER (EXISTING) --}}
{{-- ============================================ --}}
<script>
function selectCurrency(currency) {
    document.getElementById('currencyInput').value = currency;
    document.getElementById('dropdownCurrency').textContent = currency;
}

function selectCurrencyEdit(currency, id) {
    document.getElementById(`currencyEdit${id}`).value = currency;
    document.getElementById(`dropdownCurrency${id}`).textContent = currency;
}

// Currency input formatter
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.currency-input').forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            let formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            this.value = formatted;

            const rawTargetId = this.dataset.rawTarget;
            document.getElementById(rawTargetId).value = value || '';
        });
    });
});
</script>