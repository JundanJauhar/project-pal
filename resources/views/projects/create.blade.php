@extends('layouts.app')

@section('title', isset($project) ? 'Edit Project' : 'Tambah Project Baru')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('projects.index') }}">Projects</a></li>
                <li class="breadcrumb-item active">{{ isset($project) ? 'Edit' : 'Tambah Baru' }}</li>
            </ol>
        </nav>
        <h2>
            <i class="bi bi-{{ isset($project) ? 'pencil' : 'plus-circle' }}"></i>
            {{ isset($project) ? 'Edit Project' : 'Tambah Project Baru' }}
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Project</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($project) ? route('projects.update', $project->project_id) : route('projects.store') }}">
                    @csrf
                    @if(isset($project))
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="code_project" class="form-label">Kode Project <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('code_project') is-invalid @enderror"
                                   id="code_project"
                                   name="code_project"
                                   value="{{ old('code_project', $project->code_project ?? '') }}"
                                   placeholder="KCJ-2025987-308"
                                   {{ isset($project) ? 'readonly' : 'required' }}>
                            @error('code_project')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: KCJ-YYYYMMM-XXX</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="owner_division_id" class="form-label">Department <span class="text-danger">*</span></label>
                            <select class="form-select @error('owner_division_id') is-invalid @enderror"
                                    id="owner_division_id"
                                    name="owner_division_id"
                                    required>
                                <option value="">Pilih Department</option>
                                @foreach($divisions as $division)
                                <option value="{{ $division->divisi_id }}"
                                        {{ old('owner_division_id', $project->owner_division_id ?? '') == $division->divisi_id ? 'selected' : '' }}>
                                    {{ $division->nama_divisi }}
                                </option>
                                @endforeach
                            </select>
                            @error('owner_division_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name_project" class="form-label">Nama Project <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name_project') is-invalid @enderror"
                               id="name_project"
                               name="name_project"
                               value="{{ old('name_project', $project->name_project ?? '') }}"
                               placeholder="Desain Struktur dan Perlengkapan Lambung"
                               required>
                        @error('name_project')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="Pengadaan Deck Light bertujuan untuk meningkatkan visibilitas dan keamanan di area dek kapal...">{{ old('description', $project->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="end_date" class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   id="end_date"
                                   name="end_date"
                                   value="{{ old('end_date', isset($project) ? $project->end_date->format('Y-m-d') : '') }}"
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
                                <option value="rendah" {{ old('priority', $project->priority ?? '') === 'rendah' ? 'selected' : '' }}>
                                    RENDAH
                                </option>
                                <option value="sedang" {{ old('priority', $project->priority ?? '') === 'sedang' ? 'selected' : '' }}>
                                    SEDANG
                                </option>
                                <option value="tinggi" {{ old('priority', $project->priority ?? '') === 'tinggi' ? 'selected' : '' }}>
                                    TINGGI
                                </option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 d-flex align-items-end gap-2">
                            <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-custom flex-grow-1">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-custom flex-grow-1">
                                <i class="bi bi-save"></i> {{ isset($project) ? 'Update' : 'Simpan' }} Project
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h5>
            </div>
            <div class="card-body">
                <h6>Petunjuk Pengisian:</h6>
                <ul class="small">
                    <li><strong>Kode Project:</strong> Format KCJ-YYYYMMM-XXX</li>
                    <li><strong>Nama Project:</strong> Deskripsi singkat project</li>
                    <li><strong>Department:</strong> Divisi yang mengajukan</li>
                    <li><strong>Prioritas:</strong>
                        <ul>
                            <li>Rendah: Tidak urgent</li>
                            <li>Sedang: Perlu perhatian</li>
                            <li>Tinggi: Urgent & critical</li>
                        </ul>
                    </li>
                </ul>

                @if(!isset($project))
                <div class="alert alert-info mt-3">
                    <i class="bi bi-lightbulb"></i>
                    <small>Setelah project dibuat, status akan otomatis "Draft" dan akan dikirim notifikasi ke tim Supply Chain untuk review.</small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-generate project code suggestion
    document.addEventListener('DOMContentLoaded', function() {
        const isEdit = {{ isset($project) ? 'true' : 'false' }};

        if (!isEdit) {
            const codeInput = document.getElementById('code_project');
            if (codeInput && !codeInput.value) {
                const now = new Date();
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const random = String(Math.floor(Math.random() * 1000)).padStart(3, '0');
                codeInput.value = `KCJ-${year}${month}987-${random}`;
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
