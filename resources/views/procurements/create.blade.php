@extends('layouts.app')

@section('title', isset($procurement) ? 'Edit Procurement' : 'Tambah Procurement Baru')

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
                            <label for="code_procurement" class="form-label">Project <span class="text-danger">*</span></label>
                            <select class="form-select" aria-label="Default select example" placeholder="Pilih Project" required>
                                @foreach($divisions as $division)
                                <option value="{{ $division->department_id }}"
                                    {{ old('department_procurement', $procurement->department_procurement ?? '') == $division->department_id ? 'selected' : '' }}>
                                    {{ $division->department_name }}
                                </option>
                                @endforeach
                                <option value="1">W000301</option>
                                <option value="2">W000302</option>
                                <option value="3">W000303</option>
                                <option value="4">W000304</option>
                                <option value="5">W000305</option>
                                <option value="6">W000306</option>
                                <option value="7">W000307</option>
                                <option value="8">W000308</option>
                            </select>
                            @error('code_procurement')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: PRK-YYYYMMM-XXX</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="department_procurement" class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select @error('department_procurement') is-invalid @enderror"
                                id="department_procurement"
                                name="department_procurement"
                                required>
                                <option value="">Pilih Department</option>
                                @foreach($divisions as $division)
                                <option value="{{ $division->department_id }}"
                                    {{ old('department_procurement', $procurement->department_procurement ?? '') == $division->department_id ? 'selected' : '' }}>
                                    {{ $division->department_name }}
                                </option>
                                @endforeach
                            </select>
                            @error('department_procurement')
                            <div class="invalid-feedback">{{ $message }}</div>
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
@endpush
