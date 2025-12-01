@extends('layouts.app')

@section('title', isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor Baru ')

@section('content')
<div class="row " style="justify-content: center; align-items: center; ">
    <div class="col-md-8" style="align-items: center;">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Vendor</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($vendor) ? route('supply-chain.vendor.update', $vendor->id_vendor) : route('supply-chain.vendor.store') }}">
                    @csrf
                    @if(isset($vendor))
                    @method('PUT')
                    @endif
                    <input type="hidden" name="redirect" value="{{ $redirect ?? 'kelola' }}">
                    <div class="mb-3">
                        <label for="name_vendor" class="form-label">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('name_vendor') is-invalid @enderror"
                            id="name_vendor"
                            name="name_vendor"
                            value="{{ isset($vendor) ? $vendor->name_vendor : old('name_vendor') }}"
                            placeholder="Contoh : PT Vendor "
                            required>
                        @error('name_vendor')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone_number" class="form-label">No Telepon <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('phone_number') is-invalid @enderror"
                                id="phone_number"
                                name="phone_number"
                                value="{{ isset($vendor) ? $vendor->phone_number : ''}}"
                                placeholder="+62 812 3456 7890"
                                {{ isset($project) ? 'readonly' : 'required' }}>
                            @error('phone_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="owner_division_id" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ isset($vendor) ? $vendor->email : ''}}"
                                placeholder="example@gmail.com"
                                {{ isset($project) ? 'readonly' : 'required' }}>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Alamat Perusahaan</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                            id="address"
                            name="address"
                            rows="4"
                            value="{{ isset($vendor) ? $vendor->address : ''}}"
                            placeholder="">
                        </textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <!-- <div class="mb-3">
                        <label for="legal_status" class="form-label">Status Legal</label>
                        <select class="form-select @error('legal_status') is-invalid @enderror"
                            id="legal_status"
                            name="legal_status">
                            <option value="">Pilih Status Legal</option>
                            <option value="verified" {{ old('legal_status') == 'verified' ? 'selected' : '' }}>Verified (Terverifikasi)</option>
                            <option value="pending" {{ old('legal_status') == 'pending' ? 'selected' : '' }}>Pending (Menunggu Verifikasi)</option>
                            <option value="rejected" {{ old('legal_status') == 'rejected' ? 'selected' : '' }}>Rejected (Ditolak)</option>
                            value="{{ isset($vendor) ? $vendor->legal_status : ''}}"
                        </select>
                        @error('legal_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                <strong>Verified:</strong> Vendor sudah diverifikasi dan dapat dipilih untuk project<br>
                                <strong>Pending:</strong> Menunggu proses verifikasi dokumen legal<br>
                                <strong>Rejected:</strong> Vendor tidak lolos verifikasi
                            </small>
                        </div>
                    </div> -->

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                id="is_importer"
                                name="is_importer"
                                value="1"
                                {{ old('is_importer') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_importer">
                                Vendor adalah Importir
                            </label>
                        </div>
                    </div>


                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-custom">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>

                        <button type="submit" class="btn btn-primary btn-custom">
                            <i class="bi bi-save"></i> {{ isset($vendor) ? 'Update Vendor' : 'Tambah Vendor' }}
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