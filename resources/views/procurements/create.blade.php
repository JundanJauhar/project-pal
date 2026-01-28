@extends('layouts.app')

@section('title', isset($procurement) ? 'Edit Procurement' : 'Tambah Procurement Baru')

@push('styles')
<style>
/* Card styling */
.card .card-custom {
    /* border-radius: 12px; */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}

/* Header card - KEDUA BIRU dengan ukuran sama */
.card-header-custom,
.card-header-custom-white {
    background-color: #03418C;
    background: #03418C;
    color: white;
    padding: 1rem 1.25rem;
    border-radius: 12px 12px 0 0;
    min-height: 60px;
    display: flex;
    align-items: center;
}

.card-header-custom h5,
.card-header-custom-white h5 {
    color: white;
    margin: 0;  
    font-size: 1.25rem;
}

/* Tombol Tambah - PUTIH dengan text BIRU */
.btn-add-item-custom {
    background-color: white;
    background: white;
    color: #03418C;
    border: 1px solid #03418C;
    padding: 6px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-add-item-custom:hover {
    background-color: #f0f0f0;
    background: #f0f0f0;
    color: #03418C;
    border-color: #03418C;
}

/* Tombol Simpan - BIRU dengan hover */
.btn-primary.btn-custom {
    background-color: #03418C;
    background: #03418C;
    border-color: #03418C;
    color: white;
}

.btn-primary.btn-custom:hover {
    background-color: #022d5f;
    background: #022d5f;
    border-color: #022d5f;
    color: white;
}

/* Tombol Batal tetap abu-abu */
.btn-secondary.btn-custom {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary.btn-custom:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.btn-remove-item {
    background-color: #dc3545;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-remove-item:hover {
    background-color: #c82333;
}

.card-body {
    border: none;
}
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2>
            <i class="bi bi-{{ isset($procurement) ? 'pencil' : 'plus-circle' }}"></i>
            {{ isset($procurement) ? 'Edit Procurement' : 'Tambah Procurement Baru' }}
        </h2>
    </div>
</div>

<form method="POST" action="{{ isset($procurement) ? route('procurements.update', $procurement->procurement_id) : route('procurements.store') }}" id="mainForm">
    @csrf
    @if(isset($procurement))
    @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Procurement</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_code" class="form-label">Project <span class="text-danger">*</span></label>
                            <select name="project_code" id="project_code" class="form-select" required>
                                <option value="">Pilih Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_code }}" 
                                        {{ old('project_code', $procurement->project_code ?? '') == $project->project_code ? 'selected' : '' }}>
                                        {{ $project->project_code }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="code_procurement" class="form-label">Kode Procurement <span class="text-danger">*</span></label>
                            <input type="text"
                                id="code_procurement"
                                name="code_procurement"
                                class="form-control @error('code_procurement') is-invalid @enderror"
                                value="{{ old('code_procurement', $procurement->code_procurement ?? '') }}"
                                placeholder="Akan ter-generate berdasarkan project"
                                readonly
                                required>
                            @error('code_procurement')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="department_procurement" class="form-label">Department <span class="text-danger">*</span></label>
                            <select name="department_procurement" id="department_procurement" class="form-select" required>
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
                        <label for="name_procurement" class="form-label">Nama Procurement <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('name_procurement') is-invalid @enderror"
                            id="name_procurement"
                            name="name_procurement"
                            value="{{ old('name_procurement', $procurement->name_procurement ?? '') }}"
                            placeholder="Pengadaan Material & Komponen"
                            required>
                        @error('name_procurement')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Deskripsi detail procurement...">{{ old('description', $procurement->description ?? '') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label">Tanggal Digunakan <span class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                id="end_date"
                                name="end_date"
                                value="{{ old('end_date', isset($procurement) ? $procurement->end_date->format('Y-m-d') : '') }}"
                                required>
                            @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="priority" class="form-label">Prioritas <span class="text-danger">*</span></label>
                            <select class="form-select @error('priority') is-invalid @enderror"
                                id="priority"
                                name="priority"
                                required>
                                <option value="">Pilih Prioritas</option>
                                <option value="rendah" {{ old('priority', $procurement->priority ?? '') === 'rendah' ? 'selected' : '' }}>
                                    RENDAH
                                </option>
                                <option value="sedang" {{ old('priority', $procurement->priority ?? '') === 'sedang' ? 'selected' : '' }}>
                                    SEDANG
                                </option>
                                <option value="tinggi" {{ old('priority', $procurement->priority ?? '') === 'tinggi' ? 'selected' : '' }}>
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

        <div class="col-md-4">
            <div class="card card-custom" style="border-radius:12px;">
                <div class="card-header-custom-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-box"></i> Item</h5>
                    <button type="button" id="btnAddItem" class="btn btn-add-item-custom">
                        <i class="bi bi-plus-lg"></i> Tambah
                    </button>
                </div>

                <div class="card-body" id="itemsContainer">
                    <!-- Card item akan muncul di sini lewat JS -->
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data projects dari server
    const projectsData = {
        @foreach($projects as $p)
            "{{ $p->project_code }}": {{ $p->procurements_count ?? 0 }},
        @endforeach
    };

    const projectSelect = document.getElementById('project_code');
    const codeInput = document.getElementById('code_procurement');

    function padSeq(n) {
        return String(n).padStart(2, '0');
    }

    function generateSuggestedCode(projectCode) {
        const count = projectsData[projectCode] ?? 0;
        const seq = padSeq(count + 1);
        return `${projectCode}-${seq}`;
    }

    if (projectSelect) {
        const initialProject = projectSelect.value;
        const isEdit = {{ isset($procurement) ? 'true' : 'false' }};
        
        if (initialProject && !isEdit) {
            codeInput.value = generateSuggestedCode(initialProject);
        }

        projectSelect.addEventListener('change', function() {
            const pc = this.value;
            if (!pc) {
                codeInput.value = '';
                return;
            }
            if (!isEdit) {
                codeInput.value = generateSuggestedCode(pc);
            }
        });
    }

    // Validasi sebelum submit - pastikan ada minimal 1 item
    const mainForm = document.getElementById('mainForm');
    mainForm.addEventListener('submit', function(e) {
        const items = document.querySelectorAll('#itemsContainer .card');
        if (items.length === 0) {
            e.preventDefault();
            alert('Minimal harus ada 1 item untuk procurement ini!');
            return false;
        }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("itemsContainer");
    const btnAdd = document.getElementById("btnAddItem");

    let itemIndex = 0;

    function createItemCard() {
        itemIndex++;

        const card = document.createElement('div');
        card.className = 'card p-3 mb-3 shadow-sm position-relative';
        card.style.borderRadius = '12px';
        card.dataset.itemId = itemIndex;

        card.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><strong>Item ${itemIndex}</strong></h6>
                <button type="button" class="btn-remove-item" onclick="removeItem(${itemIndex})">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="mb-2">
                <label class="form-label">Nama Item <span class="text-danger">*</span></label>
                <input type="text" name="items[${itemIndex}][item_name]" class="form-control" required>
            </div>

            <div class="mb-2">
                <label class="form-label">Deskripsi</label>
                <textarea name="items[${itemIndex}][description]" class="form-control" rows="2"></textarea>
            </div>

            <div class="row">
                <div class="col-6 mb-2">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" data-index="${itemIndex}" min="1">
                </div>
                <div class="col-6 mb-2">
                    <label class="form-label">Unit</label>
                    <input type="text" name="items[${itemIndex}][unit]" class="form-control" placeholder="pcs, kg, dll">
                </div>
            </div>
        `;

        container.appendChild(card);
    }

    // Fungsi untuk menghapus item
    window.removeItem = function(index) {
        const card = container.querySelector(`[data-item-id="${index}"]`);
        if (card) {
            card.remove();
        }
    };

    btnAdd.addEventListener("click", () => {
        createItemCard();
    });

    // Tambah 1 item default saat halaman load
    createItemCard();
});
</script>
@endpush