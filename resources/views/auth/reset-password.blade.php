@extends('layouts.app')

@section('content')

<!-- Bootstrap Icons -->
<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<div class="container" style="max-width:420px;margin-top:80px;">
    <h4>Reset Password</h4>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email"
                   value="{{ old('email', $email) }}"
                   class="form-control" required>
        </div>

        <!-- Password Baru -->
        <div class="mb-3">
            <label>Password Baru</label>
            <div class="input-group">
                <input type="password" name="password"
                       id="password"
                       class="form-control" required>

                <button type="button" class="btn btn-outline-secondary"
                        onclick="togglePassword('password', 'toggleIcon1')">
                    <i id="toggleIcon1" class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <!-- Konfirmasi Password -->
        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <div class="input-group">
                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       class="form-control" required>

                <button type="button" class="btn btn-outline-secondary"
                        onclick="togglePassword('password_confirmation', 'toggleIcon2')">
                    <i id="toggleIcon2" class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <button class="btn btn-success w-100">
            Reset Password
        </button>
    </form>
</div>

<script>
function togglePassword(fieldId, iconId) {
    const password = document.getElementById(fieldId);
    const toggleIcon = document.getElementById(iconId);

    if (password.type === 'password') {
        password.type = 'text';
        toggleIcon.className = 'bi bi-eye';
    } else {
        password.type = 'password';
        toggleIcon.className = 'bi bi-eye-slash';
    }
}
</script>

@endsection
