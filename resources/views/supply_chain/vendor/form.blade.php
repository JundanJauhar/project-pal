@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@18.2.1/build/css/intlTelInput.css">
<style>
    .iti { width: 100%; }
    /* Fix for bootstrap input group integration */
    .iti__flag-container {
        z-index: 5;
    }
</style>
@endpush


@section('title', isset($vendor) ? 'Edit Vendor' : 'Tambah Vendor Baru ')

@section('content')
<div class="row " style="justify-content: center; align-items: center; ">
    <div class="col-md-8" style="align-items: center;">
        <div class="card card-custom">
            <div class="card-header-custom">
                <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Buat Vendor</h5>
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
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <input type="text"
                                        class="form-control @error('phone_number') is-invalid @enderror"
                                        id="phone_number"
                                        name="phone_number"
                                        value="{{ isset($vendor) ? $vendor->phone_number : old('phone_number') }}"
                                        required>
                                </div>
                            </div>
                            @error('phone_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <small class="text-muted" id="phone-validation-msg" style="display:none;"></small>
                            <div id="phone-details" class="mt-2" style="display:none; font-size: 0.9em;">
                                <!-- Details will be populated here -->
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ isset($vendor) ? $vendor->email : old('email') }}"
                                required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <small><i class="bi bi-info-circle"></i> Email perusahaan</small>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Alamat Perusahaan</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                            id="address"
                            name="address"
                            rows="4"
                            placeholder="Masukkan alamat lengkap perusahaan">{{ isset($vendor) ? $vendor->address : old('address') }}</textarea>
                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                type="checkbox"
                                id="is_importer"
                                name="is_importer"
                                value="1"
                                {{ (isset($vendor) && $vendor->is_importer) || old('is_importer') ? 'checked' : '' }}>
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

        $('#btn-validate-phone').on('click', function() {
            // Gunakan nomor lengkap dari plugin (termasuk kode negara)
            var phoneNumber = iti.getNumber();
            
            // Validasi dasar client-side via plugin
            if (!phoneNumber || !iti.isValidNumber()) {
                 // Fallback jika kosong, tapi coba kirim ke API jika user memaksa atau nomor terlihat oke
                 if (!phoneNumber) {
                    alert('Masukkan nomor telepon terlebih dahulu');
                    return;
                 }
            }

            var $btn = $(this);
            var $msg = $('#phone-validation-msg');
            var $details = $('#phone-details');

            // UI Loading State
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cek...');
            $msg.hide();
            $details.hide().empty();

            // --- IMPLEMENTASI INTEGRASI API ---
            // Di sini Anda akan melakukan request ke API (misalnya numverify)
            simulateApiCall(phoneNumber)
                .then(function(response) {
                    // Logic penanganan response JSON
                    if (response.valid) {
                        // 1. Tampilkan status Valid
                        $msg.html('<i class="bi bi-check-circle-fill text-success"></i> Nomor Valid').show();
                        $('#phone_number').removeClass('is-invalid').addClass('is-valid');

                        // 2. Format ulang input text & update negara jika perlu
                        // Misalnya API mengembalikan format internasional
                        if (response.intl_format) {
                             // Set nomor ke input (plugin akan menyesuaikan)
                             iti.setNumber(response.intl_format);
                        }
                        // Update flag negara sesuai response API
                        if (response.country_code) {
                            iti.setCountry(response.country_code.toLowerCase());
                        }

                        // 3. Tampilkan Detail Informasi
                        var html = `
                            <div class="card bg-light border-0">
                                <div class="card-body p-2">
                                    <div class="row g-1">
                                        <div class="col-4 fw-bold">Negara:</div>
                                        <div class="col-8">${response.country_name} (${response.country_code})</div>
                                        
                                        <div class="col-4 fw-bold">Lokasi:</div>
                                        <div class="col-8">${response.location}</div>
                                        
                                        <div class="col-4 fw-bold">Carrier:</div>
                                        <div class="col-8">${response.carrier}</div>
                                        
                                        <div class="col-4 fw-bold">Tipe:</div>
                                        <div class="col-8 text-capitalize">${response.line_type}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $details.html(html).fadeIn();

                    } else {
                        // Handle jika nomor valid: false
                        $msg.html('<i class="bi bi-x-circle-fill text-danger"></i> Nomor Tidak Valid').show();
                        $('#phone_number').addClass('is-invalid');
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    $msg.html('<span class="text-danger">Terjadi kesalahan saat memvalidasi.</span>').show();
                })
                .finally(function() {
                    $btn.prop('disabled', false).html('<i class="bi bi-search"></i> Cek');
                });
        });

        // Fungsi Simulasi (Ganti ini dengan real API call nanti)
        function simulateApiCall(number) {
            return new Promise((resolve) => {
                setTimeout(() => {
                    // Contoh response statis sesuai request user
                    resolve({
                        "valid": true,
                        "local_format": "4158586273",
                        "intl_format": "+14158586273",
                        "country_code": "US",
                        "country_name": "United States of America",
                        "location": "Novato",
                        "carrier": "AT&T Mobility LLC",
                        "line_type": "mobile"
                    });
                }, 1000);
            });
        }
    });
</script>
@endpush