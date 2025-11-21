@extends('layouts.app')

@section('title', isset($procurement) ? 'Edit Procurement' : 'Tambah Procurement Baru')

<style>
.card-custom {
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
}
</style>

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('procurements.index') }}">Procurements</a></li>
                <li class="breadcrumb-item active">{{ isset($procurement) ? 'Edit' : 'Tambah Baru' }}</li>
            </ol>
        </nav>
        <h2>
            <i class="bi bi-{{ isset($procurement) ? 'pencil' : 'plus-circle' }}"></i>
            {{ isset($procurement) ? 'Edit Procurement' : 'Tambah Procurement Baru' }}
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Procurement</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($procurement) ? route('procurements.update', $procurement->procurement_id) : route('procurements.store') }}">
                    @csrf
                    @if(isset($procurement))
                    @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_code" class="form-label">Project <span class="text-danger">*</span></label>
                            <select name="project_code" class="form-select" aria-label="Default select example" required>
                                <option value="">Pilih Project</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->project_code }}">
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
                    <select name="department_procurement" class="form-select" aria-label="Default select example" required>
                        <option value="">Pilih Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->department_id }}">
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
                            <label for="end_date" class="form-label">Tanggal Target <span class="text-danger">*</span></label>
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
                            <a href="javascript:history.back()" class="btn btn-secondary btn-custom flex-grow-1" wire:navigate>
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-custom flex-grow-1">
                                <i class="bi bi-save"></i> {{ isset($procurement) ? 'Update' : 'Simpan' }} Procurement
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
    <div class="card card-custom" style="border-radius:12px;">
        <div class="card-header-custom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Item</h5>
            <button type="button" id="btnAddItem" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah
            </button>
        </div>

        <div class="card-body" id="itemsContainer">
            <!-- Card item akan muncul di sini lewat JS -->
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
    // Auto-generate procurement code suggestion
    document.addEventListener('DOMContentLoaded', function() {
        const isEdit = {
            {
                isset($procurement) ? 'true' : 'false'
            }
        };

        if (!isEdit) {
            const codeInput = document.getElementById('code_procurement');
            if (codeInput && !codeInput.value) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const random = String(Math.floor(Math.random() * 1000)).padStart(3, '0');
                codeInput.value = `PRK-${year}${month}987-${random}`;
            }
        }

        // Validate end date > start date
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');

        startDate.addEventListener('change', function() {
            endDate.min = this.value;
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

        return `
        <div class="card p-3 mb-3 shadow-sm" style="border-radius:12px;">
            <h5><strong>Item ${itemIndex}</strong></h5>

            <div class="mb-2">
                <label class="form-label">Nama Item*</label>
                <input type="text" name="items[${itemIndex}][item_name]" class="form-control">
            </div>

            <div class="mb-2">
                <label class="form-label">Deskripsi</label>
                <textarea name="items[${itemIndex}][description]" class="form-control" rows="3"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Jumlah*</label>
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Unit*</label>
                    <input type="text" name="items[${itemIndex}][unit]" class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="form-label">Harga Estimasi*</label>
                    <input type="number" name="items[${itemIndex}][estimated_price]" class="form-control">
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label">Harga Total*</label>
                    <input type="number" name="items[${itemIndex}][total_price]" class="form-control" readonly>
                </div>
            </div>
        </div>`;
    }

    btnAdd.addEventListener("click", () => {
        container.innerHTML += createItemCard();
    });
});
</script>

@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ambil semua project dan jumlah procurement sudah ada (dari server)
    // Data ini di-render server-side ke JS object
    const projectsData = {
        @foreach($projects as $p)
            "{{ $p->project_code }}": {{ $p->procurements_count ?? 0 }},
        @endforeach
    };

    const projectSelect = document.querySelector('select[name="project_code"]');
    const codeInput = document.getElementById('code_procurement');

    function padSeq(n) {
        return String(n).padStart(2, '0'); // 01, 02, ...
    }

    function generateSuggestedCode(projectCode) {
        // gunakan count (jumlah procurement yang sudah ada) + 1 => sequence
        const count = projectsData[projectCode] ?? 0;
        const seq = padSeq(count + 1);
        // format: PROJECTCODE-01  (ubah format jika mau PROJECTCODE01 atau lainnya)
        return `${projectCode}-${seq}`;
    }

    if (projectSelect) {
        // ketika load, jika sudah ada value selected (old input), generate suggestion
        const initialProject = projectSelect.value;
        if (initialProject && !codeInput.value) {
            codeInput.value = generateSuggestedCode(initialProject);
        }

        projectSelect.addEventListener('change', function() {
            const pc = this.value;
            if (!pc) {
                codeInput.value = '';
                return;
            }
            codeInput.value = generateSuggestedCode(pc);
        });
    }

    // jika form di-edit (procurement sudah ada), biarkan code existing (readonly)
});
</script>
@endpush

