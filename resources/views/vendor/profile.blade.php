@extends('layouts.app')

@section('title', 'Profil Vendor')

@push('styles')
<style>
    .profile-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .profile-card {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        padding: 40px;
        margin-bottom: 30px;
        border: 1px solid #f0f0f0;
    }

    .profile-header {
        display: flex;
        align-items: center;
        text-align: center;
        justify-content: center;
        gap: 20px;
        margin-bottom: 35px;
        padding-bottom: 25px;
        border-bottom: 2px solid #f5f5f5;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 32px;
        font-weight: 700;
        text-transform: uppercase;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }

    .profile-info h2 {
        font-size: 24px;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 5px;
    }

    .profile-info p {
        color: #6c757d;
        font-size: 14px;
        margin: 0;
    }

    .section-title {
        font-size: 18px;
        font-weight: 700;
        color: #1a1a2e;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .section-title i {
        color: #667eea;
        font-size: 22px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group .form-control {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e8e8e8;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s ease;
        background: #fafafa;
    }

    .form-group .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
        background: #fff;
        outline: none;
    }

    .form-group .form-control:disabled,
    .form-group .form-control[readonly] {
        background: #f0f0f0;
        color: #888;
        cursor: not-allowed;
    }

    .form-hint {
        font-size: 12px;
        color: #888;
        margin-top: 6px;
    }

    .btn-save {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 14px 40px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-cancel {
        background: #f5f5f5;
        color: #333;
        border: none;
        padding: 14px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 15px;
        transition: all 0.3s ease;
        margin-right: 15px;
    }

    .btn-cancel:hover {
        background: #e8e8e8;
        color: #333;
    }

    .alert-success-custom {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border: none;
        border-left: 4px solid #28a745;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 25px;
        color: #155724;
        font-weight: 500;
    }

    .alert-danger-custom {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border: none;
        border-left: 4px solid #dc3545;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 25px;
        color: #721c24;
        font-weight: 500;
    }

    .password-toggle {
        position: relative;
    }

    .password-toggle .toggle-btn {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #888;
        cursor: pointer;
        padding: 0;
        font-size: 18px;
    }

    .password-toggle .toggle-btn:hover {
        color: #667eea;
    }

    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #e8f4fd;
        color: #0d6efd;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .divider {
        height: 1px;
        background: #f0f0f0;
        margin: 30px 0;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
        margin-bottom: 25px;
        transition: all 0.2s;
    }

    .back-link:hover {
        color: #764ba2;
        gap: 12px;
    }
</style>
@endpush

@section('content')
<div class="profile-container">
    <a href="{{ route('vendor.index') }}" class="back-link">
        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
    </a>

    @if(session('error'))
    <div class="alert-danger-custom">
        <i class="bi bi-x-circle-fill me-2"></i>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert-danger-custom">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-info">
                <h1>{{ $vendor->name_vendor }}</h1>
                <p><i class="bi bi-envelope me-1"></i> {{ $vendor->user_vendor }}</p>
                <div class="mt-2">
                    <span class="info-badge">
                        <i class="bi bi-building"></i>
                        {{ $vendor->specialization_label ?? 'Vendor' }}
                    </span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('vendor.profile.update') }}">
            @csrf
            @method('PUT')

            <!-- Nama Perusahaan Section -->
            <div class="section-title">
                <i class="bi bi-building"></i>
                Informasi Perusahaan
            </div>

            <div class="form-group">
                <label for="name_vendor">Nama Perusahaan (PT) <span class="text-danger">*</span></label>
                <input type="text"
                    class="form-control @error('name_vendor') is-invalid @enderror"
                    id="name_vendor"
                    name="name_vendor"
                    value="{{ old('name_vendor', $vendor->name_vendor) }}"
                    required>
                <div class="form-hint">Masukkan nama lengkap perusahaan, contoh: PT Mega Persada Indonesia</div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <input type="text"
                            class="form-control @error('address') is-invalid @enderror"
                            id="address"
                            name="address"
                            value="{{ old('address', $vendor->address) }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone_number">Nomor Telepon</label>
                        <input type="text"
                            class="form-control @error('phone_number') is-invalid @enderror"
                            id="phone_number"
                            name="phone_number"
                            value="{{ old('phone_number', $vendor->phone_number) }}">
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Email Section -->
            <div class="section-title">
                <i class="bi bi-envelope"></i>
                Email & Login
            </div>

            <div class="form-group">
                <label for="user_vendor">Username Login</label>
                <input type="text"
                    class="form-control"
                    id="user_vendor"
                    value="{{ $vendor->user_vendor }}"
                    readonly
                    disabled>
                <div class="form-hint">Username login tidak dapat diubah</div>
            </div>

            <div class="form-group">
                <label for="email">Email Kontak <span class="text-danger">*</span></label>
                <input type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    id="email"
                    name="email"
                    value="{{ old('email', $vendor->email) }}"
                    required>
                <div class="form-hint">Email ini digunakan untuk menerima notifikasi dan komunikasi</div>
            </div>

            <div class="divider"></div>

            <!-- Password Section -->
            <div class="section-title">
                <i class="bi bi-shield-lock"></i>
                Ubah Password
            </div>

            <div class="form-group">
                <label for="current_password">Password Saat Ini</label>
                <div class="password-toggle">
                    <input type="password"
                        class="form-control @error('current_password') is-invalid @enderror"
                        id="current_password"
                        name="current_password"
                        placeholder="Masukkan password saat ini">
                    <button type="button" class="toggle-btn" onclick="togglePassword('current_password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                <div class="form-hint">Kosongkan jika tidak ingin mengubah password</div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <div class="password-toggle">
                            <input type="password"
                                class="form-control @error('new_password') is-invalid @enderror"
                                id="new_password"
                                name="new_password"
                                placeholder="Minimal 6 karakter">
                            <button type="button" class="toggle-btn" onclick="togglePassword('new_password', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                        <div class="password-toggle">
                            <input type="password"
                                class="form-control"
                                id="new_password_confirmation"
                                name="new_password_confirmation"
                                placeholder="Ketik ulang password baru">
                            <button type="button" class="toggle-btn" onclick="togglePassword('new_password_confirmation', this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end">
                <a href="{{ route('vendor.index') }}" class="btn btn-cancel">Batal</a>
                <button type="submit" class="btn btn-save">
                    <i class="bi bi-check-lg"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endpush