@extends('layouts.app')

@section('title', 'Form Pengadaan - PT PAL Indonesia')

@push('styles')
<style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .page-wrapper {
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 20px;
        overflow-x: hidden;
    }

    .form-container {
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
    }



    .section-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 30px;
        color: #000;
    }

    .form-box {
        background: #F5F5F5;
        border-radius: 8px;
        border: none;
        padding: 12px 16px;
        width: 100%;
        font-size: 14px;
        color: #333;
    }

    .form-box:focus {
        outline: none;
        background: #ECECEC;
    }

    .form-box:disabled,
    .form-box[readonly] {
        background: #F5F5F5;
        color: #666;
        cursor: not-allowed;
    }

    textarea.form-box {
        resize: vertical;
        min-height: 100px;
    }

    .label-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        color: #000;
        display: block;
    }

    .btn-tambah-item {
        background: #004A99;
        color: white;
        font-weight: 600;
        padding: 10px 24px;
        border-radius: 8px;
        border: none;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-tambah-item:hover {
        background: #003875;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 74, 153, 0.3);
    }

    .btn-hapus-item {
        background: #dc3545;
        color: white;
        font-weight: 600;
        padding: 8px 20px;
        border-radius: 6px;
        border: none;
        font-size: 13px;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.3s ease;
    }

    .btn-hapus-item:hover {
        background: #c82333;
    }

    .btn-kirim {
        background: #004A99;
        color: white;
        font-weight: 600;
        padding: 14px 40px;
        border-radius: 8px;
        border: none;
        margin-top: 40px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-kirim:hover {
        background: #003875;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 74, 153, 0.4);
    }

    .form-section {
        margin-bottom: 25px;
    }

    .item-row {
        background: #FAFAFA;
        border: 1px solid #E0E0E0;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        position: relative;
        transition: all 0.3s ease;
    }

    .item-row:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .item-number {
        position: absolute;
        top: -12px;
        left: 20px;
        background: #004A99;
        color: white;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .divider {
        border-bottom: 2px solid #E0E0E0;
        margin: 40px 0;
    }

    .info-section {
        background: #F9F9F9;
        border-radius: 10px;
        padding: 30px;
        margin-bottom: 40px;
    }

    @media (max-width: 768px) {
        .page-wrapper {
            padding: 20px 15px;
            align-items: flex-start;
        }

        .form-container {
            max-width: 100%;
        }

        .btn-kirim {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')

<div class="page-wrapper">
    <div class="form-container">

        <button onclick="history.back()" class="btn btn-secondary mb-4">
            <i class="bi bi-arrow-left-circle me-2"></i>Kembali
        </button>

        <form method="POST"
            action="{{ isset($procurement) ? route('procurements.update', $procurement->procurement_id) : route('procurements.store') }}"
            id="mainForm">
            @csrf
            @if(isset($procurement))
            @method('PUT')
            @endif

            <div class="card card-custom">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Procurement</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_code" class="form-label">Project <span
                                    class="text-danger">*</span></label>
                            <select name="project_code" id="project_code" class="form-select" required>
                                <option value="">Pilih Project</option>

                                @forelse($projects as $project)
                                @foreach($project->procurements as $procurement)
                                <option value="{{ $procurement->code_procurement }}"
                                    {{ old('code_procurement', $procurement->project_code ?? '') == $procurement->project->project_code ? 'selected' : '' }}>
                                    {{ $procurement->code_procurement }}
                                </option>
                                @endforeach
                                @empty
                                <option disabled>Tidak ada project</option>
                                @endforelse

                            </select>

                            @error('project_code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="code_procurement" class="form-label">Vendor <span
                                    class="text-danger">*</span></label>
                            <select name="vendor_id" id="vendor_id" class="form-select" required>
                                <option value="">Pilih Vendor</option>

                                @forelse($projects as $project)
                                @forelse($project->requests as $req)
                                @if($req->vendor)
                                <option value="{{ $req->vendor->vendor_name }}"
                                    {{ $req->vendor->vendor_name }}
                                    </option>
                                    @endif
                                    @empty
                                    @endforelse
                                    @empty
                                <option disabled>Tidak ada project</option>
                                @endforelse
                            </select>


                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="department_procurement" class="form-label">Department <span
                                    class="text-danger">*</span></label>
                            <select name="department_procurement" id="department_procurement" class="form-select"
                                required>
                                <option value="">Pilih Department</option>
                                @foreach($departments as $department)
                                <option value="{{ $department->department_id }}"
                                    {{ old('department_procurement', $procurement->department_procurement ?? '') == $department->department_id ? 'selected' : '' }}>
                                    {{ $department->department_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('department_procurement')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name_procurement" class="form-label">Nama Procurement <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name_procurement') is-invalid @enderror"
                            id="name_procurement" name="name_procurement"
                            value="{{ old('name_procurement', $procurement->name_procurement ?? '') }}"
                            placeholder="Pengadaan Material & Komponen" required>
                        @error('name_procurement')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                            name="description" rows="4"
                            placeholder="Deskripsi detail procurement...">{{ old('description', $procurement->description ?? '') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label">Tanggal Target <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                id="end_date" name="end_date"
                                value="{{ old('end_date', isset($procurement) ? $procurement->end_date->format('Y-m-d') : '') }}"
                                required>
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="priority" class="form-label">Prioritas <span
                                    class="text-danger">*</span></label>
                            <select class="form-select @error('priority') is-invalid @enderror" id="priority"
                                name="priority" required>
                                <option value="">Pilih Prioritas</option>
                                <option value="rendah"
                                    {{ old('priority', $procurement->priority ?? '') === 'rendah' ? 'selected' : '' }}>
                                    RENDAH
                                </option>
                                <option value="sedang"
                                    {{ old('priority', $procurement->priority ?? '') === 'sedang' ? 'selected' : '' }}>
                                    SEDANG
                                </option>
                                <option value="tinggi"
                                    {{ old('priority', $procurement->priority ?? '') === 'tinggi' ? 'selected' : '' }}>
                                    TINGGI
                                </option>
                            </select>
                            @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 d-flex align-items-end gap-2">
                            <a href="javascript:history.back()" class="btn btn-secondary btn-custom flex-grow-1">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-custom flex-grow-1">
                                <i class="bi bi-save"></i> {{ isset($procurement) ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>
</form>

</div>
</div>

@endsection

@push('scripts')
<script>
    let itemCounter = 1; // langsung mulai dari 1 karena sudah muncul

    addItemBtn.addEventListener('click', function() {
        itemCounter++;

        const html = `
    <div class="item-row" data-item-index="${itemCounter}">
        <span class="item-number">Item ${itemCounter}</span>
        ...
        (form input sama seperti di atas)
    </div>
    `;

        document.getElementById('itemContainer').insertAdjacentHTML('beforeend', html);
        updateItemNumbers();
    });

    const addItemBtn = document.getElementById('addItemBtn');

    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            itemCounter++;
            const container = document.getElementById('itemContainer');

            // Remove alert if exists
            const alert = container.querySelector('.alert');
            if (alert) {
                alert.remove();
            }

            container.insertAdjacentHTML('beforeend', html);
            updateItemNumbers();
        });
    }

    function removeItem(button) {
        if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
            button.closest('.item-row').remove();
            updateItemNumbers();

            // Show alert if no items
            const container = document.getElementById('itemContainer');
            const items = container.querySelectorAll('.item-row');
            if (items.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>Belum ada item. Klik "Tambah Item" untuk menambahkan barang.
                    </div>
                `;
            }
        }
    }

    function updateItemNumbers() {
        const items = document.querySelectorAll('.item-row');
        items.forEach((item, index) => {
            const numberSpan = item.querySelector('.item-number');
            if (numberSpan) {
                numberSpan.textContent = `Item ${index + 1}`;
            }
            item.setAttribute('data-item-index', index);
        });
        itemCounter = items.length;
    }

    const form = document.getElementById('pengadaanForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const items = document.querySelectorAll('.item-row');

            if (items.length === 0) {
                e.preventDefault();
                alert('Mohon tambahkan minimal 1 item sebelum mengirim pengadaan!');
                return false;
            }

            if (!confirm('Apakah Anda yakin ingin mengirim pengadaan ini?')) {
                e.preventDefault();
                return false;
            }
        });
    }
</script>
@endpush