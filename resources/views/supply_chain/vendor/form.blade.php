@extends('layouts.app')

@section('title', isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor Baru')

@push('styles')
<style>
    .card-custom {
        border: none;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
    }

    .btn-custom {
        border-radius: 5px;
        padding: 10px 25px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ ($redirect ?? 'kelola') === 'pilih' ? route('supply-chain.vendor.pilih') : route('supply-chain.vendor.kelola') }}" 
           class="text-decoration-none text-primary">
            <h4><i class="bi bi-arrow-left"></i> Kembali</h4>
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-custom">
                <div class="card-header-custom">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ isset($vendor) ? 'pencil-square' : 'plus-circle' }}"></i> 
                        {{ isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor Baru' }}
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ isset($vendor) ? route('supply-chain.vendor.update', $vendor->id_vendor) : route('supply-chain.vendor.store') }}">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $redirect ?? 'kelola' }}">
                        
                        <!-- Nama Vendor -->
                        <div class="mb-3">
                            <label for="name_vendor" class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('name_vendor') is-invalid @enderror"
                                id="name_vendor"
                                name="name_vendor"
                                value="{{ old('name_vendor', $vendor->name_vendor ?? '') }}"
                                placeholder="PT Vendor Contoh"
                                required>
                            @error('name_vendor')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Is Importer Checkbox -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input"
                                    type="checkbox"
                                    id="is_importer"
                                    name="is_importer"
                                    value="1"
                                    {{ old('is_importer', $vendor->is_importer ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_importer">
                                    <i class="bi bi-globe"></i> Vendor adalah Importir
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ ($redirect ?? 'kelola') === 'pilih' ? route('supply-chain.vendor.pilih') : route('supply-chain.vendor.kelola') }}" 
                               class="btn btn-secondary btn-custom">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-custom">
                                <i class="bi bi-{{ isset($vendor) ? 'save' : 'plus-circle' }}"></i> 
                                {{ isset($vendor) ? 'Update Vendor' : 'Simpan Vendor' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
