@extends('layouts.app')

@section('title', isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor Baru  ')

@section('content')
<div class="row " style="justify-content: center; align-items: center; ">
    <div class="col-md-8" style="align-items: center;">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Vendor</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($vendor) ? route('projects.update', $vendor->vendor_id) : route('projects.store') }}">
                    @csrf
                    @if(isset($vendor))
                        @method('PUT')
                    @endif

                     <div class="mb-3">
                        <label for="name_project" class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name_project') is-invalid @enderror"
                               id="nama_perusahaan"
                               name="Nama_Perusahaan"
                               value=""
                               placeholder=""
                               required>
                        @error('name_project')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="code_project" class="form-label">No Telepon <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('code_project') is-invalid @enderror"
                                   id="no_telepon"
                                   name="no_telepon"
                                   value="no_telepon"
                                   placeholder="+62 812-3456-7890"
                                   {{ isset($project) ? 'readonly' : 'required' }}>
                            @error('code_project')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="owner_division_id" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('code_project') is-invalid @enderror"
                                   id="code_project"
                                   name="code_project"
                                   value=""
                                   placeholder="example@gmail.com"
                                   {{ isset($project) ? 'readonly' : 'required' }}>
                            @error('owner_division_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Alamat Perusahaan</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="Pengadaan Deck Light bertujuan untuk meningkatkan visibilitas dan keamanan di area dek kapal...">{{ old('description', $project->description ?? '') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
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

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('projects.index') }}" class="btn btn-secondary btn-custom">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-custom">
                            <i class="bi bi-save"></i> {{ isset($project) ? 'Update' : 'Simpan' }} Project
                        </button>
                    </div>
                </form>
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

        // Validate end date > start date
        const startDate = document.getElementById('start_date');
        const endDate = document.getElementById('end_date');

        startDate.addEventListener('change', function() {
            endDate.min = this.value;
        });
    });
</script>
@endpush
