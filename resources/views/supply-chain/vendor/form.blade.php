@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
<style>
    .iti { width: 100%; }
    .iti__flag-container {
        z-index: 5;
    }

    .vendor-form-wrapper {
        max-width: 800px;
        margin: 30px auto;
        padding: 30px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .form-title {
        font-size: 24px;
        font-weight: 700;
        margin-bottom: 24px;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 8px;
        display: block;
        color: #333;
    }

    .form-label .text-danger {
        color: #d60000;
        margin-left: 2px;
    }

    .form-control,
    .form-select {
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
        width: 100%;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #003d82;
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 61, 130, 0.1);
    }

    .form-control.is-valid,
    .form-control.is-invalid {
        background-position: right calc(2.25rem + 0.75rem) center;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .form-row.full {
        grid-template-columns: 1fr;
    }

    .form-check {
        display: flex;
        align-items: center;
        margin-top: 8px;
    }

    .form-check input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        margin-right: 8px;
    }

    .form-check label {
        margin: 0;
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
    }

    .small {
        font-size: 12px;
        color: #666;
        display: block;
        margin-top: 6px;
    }

    .form-text {
        font-size: 12px;
        color: #666;
        margin-top: 6px;
    }

    .form-buttons {
        display: flex;
        gap: 12px;
        margin-top: 30px;
    }

    .btn {
        padding: 10px 20px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-primary {
        background: #003d82;
        color: white;
        flex: 1;
    }

    .btn-primary:hover {
        background: #002e5c;
    }

    .btn-secondary {
        background: #f0f0f0;
        color: #333;
        flex: 1;
    }

    .btn-secondary:hover {
        background: #e0e0e0;
    }

    /* Alert */
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-danger {
        background: #fee;
        border: 1px solid #fcc;
        color: #c33;
    }

    .alert-danger ul {
        margin: 0;
        padding-left: 20px;
    }

    .alert-danger li {
        margin: 4px 0;
    }

    /* Info Box */
    .info-box {
        background: #f0f8ff;
        border-left: 4px solid #003d82;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #333;
    }

    .info-box strong {
        color: #003d82;
    }

    .invalid-feedback {
        color: #d60000;
        font-size: 13px;
        margin-top: 4px;
        display: block;
    }

    .valid-feedback {
        color: #28AC00;
        font-size: 13px;
        margin-top: 4px;
        display: block;
    }

    /* Phone validation */
    #phone-validation-msg {
        margin-top: 6px;
    }

    #phone-details {
        margin-top: 12px;
    }

    /* Responsive */
    @media (max-width: 600px) {
        .vendor-form-wrapper {
            margin: 20px;
            padding: 20px;
        }

        .form-row {
            grid-template-columns: 1fr;
        }

        .form-buttons {
            flex-direction: column;
        }

        .form-title {
            font-size: 20px;
        }
    }
</style>
@endpush

@section('title', isset($vendor) ? 'Edit Vendor - PT PAL Indonesia' : 'Tambah Vendor Baru - PT PAL Indonesia')

@section('content')

<div class="vendor-form-wrapper">
    <h1 class="form-title">
        <i class="bi bi-{{ isset($vendor) ? 'pencil' : 'plus' }}-circle"></i>
        {{ isset($vendor) ? 'Edit' : 'Tambah' }} Vendor
    </h1>

    {{-- Error Alert --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>Terjadi kesalahan:</strong>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Info Box --}}
    @if (!isset($vendor))
    <div class="info-box">
        <strong>Format Kode Vendor:</strong>
        <br>
        AS = Jasa | AD = Material Lokal | AL = Material Impor
    </div>
    @endif

    <form method="POST" action="{{ isset($vendor) ? route('supply-chain.vendor.update', $vendor->id_vendor) : route('supply-chain.vendor.store') }}">
        @csrf
        @if(isset($vendor))
            @method('PUT')
        @endif
        <input type="hidden" name="redirect" value="{{ $redirect ?? 'kelola' }}">

        {{-- Vendor Code & Specialization --}}
        <div class="form-row">
            {{-- Vendor Code --}}
            <div class="form-group">
                <label class="form-label">
                    Kode Vendor <span class="text-danger">*</span>
                </label>
                <input type="text"
                       name="vendor_code"
                       class="form-control @error('vendor_code') is-invalid @enderror"
                       placeholder="Contoh: AS / AD / AL"
                       value="{{ old('vendor_code', $vendor->vendor_code ?? '') }}"
                       style="text-transform: uppercase;"
                       maxlength="2"
                       pattern="[A-Z]{2}"
                       required>
                <small>AS = Jasa, AD = Material Lokal, AL = Material Impor</small>
                @error('vendor_code')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Specialization --}}
            <div class="form-group">
                <label class="form-label">
                    Spesialisasi <span class="text-danger">*</span>
                </label>
                <select name="specialization" class="form-select @error('specialization') is-invalid @enderror" required>
                    <option value="">-- Pilih --</option>
                    <option value="jasa" @selected(old('specialization', $vendor->specialization ?? '') == 'jasa')>
                        Jasa (AS)
                    </option>
                    <option value="material_lokal" @selected(old('specialization', $vendor->specialization ?? '') == 'material_lokal')>
                        Material Lokal (AD)
                    </option>
                    <option value="material_impor" @selected(old('specialization', $vendor->specialization ?? '') == 'material_impor')>
                        Material Impor (AL)
                    </option>
                </select>
                @error('specialization')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Nama Perusahaan --}}
        <div class="form-group">
            <label class="form-label">
                Nama Perusahaan <span class="text-danger">*</span>
            </label>
            <input type="text"
                   name="name_vendor"
                   class="form-control @error('name_vendor') is-invalid @enderror"
                   placeholder="Contoh: PT Vendor Indonesia"
                   value="{{ old('name_vendor', $vendor->name_vendor ?? '') }}"
                   required>
            @error('name_vendor')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Phone & Email --}}
        <div class="form-row">
            {{-- Phone Number --}}
            <div class="form-group">
                <label class="form-label">
                    No Telepon <span class="text-danger">*</span>
                </label>
                <input type="tel"
                       class="form-control @error('phone_number') is-invalid @enderror"
                       id="phone_number"
                       name="phone_number"
                       value="{{ old('phone_number', $vendor->phone_number ?? '') }}"
                       placeholder="Nomor telepon"
                       required>
                @error('phone_number')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted" id="phone-validation-msg" style="display:none;"></small>
                <div id="phone-details" style="display:none;"></div>
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label class="form-label">
                    Email <span class="text-danger">*</span>
                </label>
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="Email perusahaan"
                       value="{{ old('email', $vendor->email ?? '') }}"
                       required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">
                    <i class="bi bi-info-circle"></i> Email perusahaan
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div class="form-group">
            <label class="form-label">Alamat Perusahaan</label>
            <textarea name="address"
                      class="form-control @error('address') is-invalid @enderror"
                      id="address"
                      rows="4"
                      placeholder="Masukkan alamat lengkap perusahaan">{{ old('address', $vendor->address ?? '') }}</textarea>
            @error('address')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Is Importer Checkbox --}}
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox"
                       name="is_importer"
                       id="is_importer"
                       class="form-check-input"
                       value="1"
                       @checked(old('is_importer', $vendor->is_importer ?? false))>
                <label class="form-check-label" for="is_importer">
                    <i class="bi bi-globe"></i> Vendor Importer
                </label>
            </div>
            <small>Centang jika vendor ini adalah importer/pemasok dari luar negeri</small>
        </div>

        {{-- Buttons --}}
        <div class="form-buttons">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> {{ isset($vendor) ? 'Perbarui Vendor' : 'Tambah Vendor' }}
            </button>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/intlTelInput.min.js"></script>
<script>
    $(document).ready(function() {
        // Init Intl Tel Input
        var input = document.querySelector("#phone_number");
        var iti = window.intlTelInput(input, {
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/js/utils.js",
            initialCountry: "auto",
            geoIpLookup: function(callback) {
                fetch("https://ipapi.co/json")
                    .then(res => res.json())
                    .then(data => callback(data.country_code))
                    .catch(() => callback("id")); // default to Indonesia on failure
            },
            separateDialCode: true,
            autoPlaceholder: "off"
        });

        // Pastikan nomor full (dengan kode negara) yang terkirim ke database
        var form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function() {
                var fullNumber = iti.getNumber();
                if (fullNumber) {
                    input.value = fullNumber;
                }
            });
        }
    });
</script>
@endpush