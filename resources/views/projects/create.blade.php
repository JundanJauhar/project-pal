@extends('layouts.app')

@section('title', 'Pengadaan Baru')

@php
    $defaultDivision = old('owner_division_id', Auth::user()->division_id ?? ($divisions->first()->divisi_id ?? ''));
    $defaultEndDate = old('end_date', \Carbon\Carbon::now()->addWeeks(2)->format('Y-m-d'));
@endphp

@section('content')
<style>
    .procurement-wrapper {
        background: #f7f7f7;
        padding: 32px 40px;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
    .procurement-section-title {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 24px;
    }
    .panel-title {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 20px;
    }
    .field-group {
        margin-bottom: 20px;
    }
    .field-group label {
        font-weight: 600;
        display: block;
        margin-bottom: 8px;
        font-size: 15px;
    }
    .field-group input,
    .field-group textarea,
    .field-group select {
        background: #efefef;
        border: none;
        border-radius: 14px;
        padding: 14px 18px;
        width: 100%;
        font-size: 15px;
        box-shadow: inset 0 2px 6px rgba(0,0,0,0.08);
    }
    .field-group textarea {
        min-height: 160px;
        resize: vertical;
    }
    .items-wrapper {
        background: transparent;
        border-radius: 18px;
        padding: 0;
    }
    .items-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .btn-add-item,
    .btn-submit-procurement {
        background: #0d3f96;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 12px 24px;
        font-weight: 600;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .btn-add-item:hover,
    .btn-submit-procurement:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(13, 63, 150, 0.35);
    }
    .items-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }
    .items-grid .wide {
        grid-column: span 2;
    }
    .items-list {
        margin-top: 24px;
    }
    .items-list-card {
        background: #fff;
        border-radius: 16px;
        padding: 16px 18px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 12px;
    }
    .items-list-card h6 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 6px 0;
    }
    .items-list-card p {
        margin: 0;
        font-size: 14px;
        color: #555;
    }
    .items-remove {
        background: transparent;
        border: none;
        color: #c0392b;
        font-weight: 600;
        cursor: pointer;
    }
    @media (max-width: 992px) {
        .items-grid {
            grid-template-columns: 1fr;
        }
        .items-grid .wide {
            grid-column: span 1;
        }
    }
</style>

<form method="POST" action="{{ route('projects.store') }}" id="procurement-form">
    @csrf
    <input type="hidden" name="code_project" id="code_project" value="{{ old('code_project') }}">
    <input type="hidden" name="owner_division_id" value="{{ $defaultDivision }}">
    <input type="hidden" name="end_date" id="end_date" value="{{ $defaultEndDate }}">

    <div class="procurement-wrapper">
        <div class="row g-5">
            <div class="col-lg-6">
                <h2 class="panel-title">Informasi Umum</h2>

                <!-- Versi dari branch main (theirs) -->
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

                    @if(isset($project))
                    <div class="mb-3">
                        <label for="status_project" class="form-label">Status Project</label>
                        <select class="form-select @error('status_project') is-invalid @enderror"
                                id="status_project"
                                name="status_project">
                            <option value="draft" {{ old('status_project', $project->status_project) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="review_sc" {{ old('status_project', $project->status_project) === 'review_sc' ? 'selected' : '' }}>Review SC</option>
                            <option value="persetujuan_sekretaris" {{ old('status_project', $project->status_project) === 'persetujuan_sekretaris' ? 'selected' : '' }}>Persetujuan Sekretaris</option>
                            <option value="pemilihan_vendor" {{ old('status_project', $project->status_project) === 'pemilihan_vendor' ? 'selected' : '' }}>Pemilihan Vendor</option>
                            <option value="pengecekan_legalitas" {{ old('status_project', $project->status_project) === 'pengecekan_legalitas' ? 'selected' : '' }}>Pengecekan Legalitas</option>
                            <option value="pemesanan" {{ old('status_project', $project->status_project) === 'pemesanan' ? 'selected' : '' }}>Pemesanan</option>
                            <option value="pembayaran" {{ old('status_project', $project->status_project) === 'pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                            <option value="selesai" {{ old('status_project', $project->status_project) === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="rejected" {{ old('status_project', $project->status_project) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status_project')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @endif
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
        @if(!isset($project))
        const codeInput = document.getElementById('code_project');
        if (codeInput && !codeInput.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const random = String(Math.floor(Math.random() * 1000)).padStart(3, '0');
            codeInput.value = `KCJ-${year}${month}987-${random}`;
        }
        @endif

        // Validate end date > start date (if start_date exists)
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');
        if (startDate && endDate) {
            startDate.addEventListener('change', function() {
                endDate.min = this.value;
            });
        }
    });
    </script>
@endpush
