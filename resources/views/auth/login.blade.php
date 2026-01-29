<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - PT PAL Indonesia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-wrapper {
            height: auto;
            width: 500px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: -100px;
        }

        .login-container {
            background: #c1c1c1;
            border-radius: 20px;

            overflow: hidden;
            max-width: 500px;
            width: 100%;
            height: auto;
            position: relative;
            z-index: 1;
        }

        .login-header {
            background: #c1c1c1;
            padding: 10px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 10px;
            margin-top: 10px;
        }

        .logo-pal {
            height: 250px;
            ;
        }

        .logo {
            translate: z-2;
        }

        .company-name {
            color: #003d82;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .login-body {
            background: #c1c1c1;
            padding: 20px;
            padding-top: 0
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 5px;
            margin-top: 5px;
        }

        .form-control:focus {
            border-color: #003d82;
            box-shadow: 0 0 0 0.2rem rgba(0, 61, 130, 0.15);
        }

        .form-label {
            font-weight: 700;
            font: bold color: #000000;
            margin-bottom: 8px;
        }

        .btn-login {
            background: #03418C;
            color: white;
            padding: 12px;
            border-radius: 10px;
            border: none;
            width: 100%;
            font-weight: 600;
            font-size: 16px;
            transition: transform 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 61, 130, 0.3);
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }

        .form-check-label {
            font-size: 14px;
            color: #666;
        }

        .forgot-password {
            color: #03418C;
            font-size: 14px;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .captcha-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .captcha-image-wrapper {
            height: 44px;
            min-width: 120px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
        }

        .captcha-row .captcha-image-wrapper,
        .captcha-row .form-control {
            margin-top: 5px;
            margin-bottom: 5px;
            height: 48px;               /* samakan tinggi visual */
        }
        .captcha-image-wrapper img {
            height: 28px;
            width: auto;
            cursor: pointer;
        }

        .captcha-image-wrapper:hover {
            border-color: #003d82;
        }
        .captcha-note {
            font-size: 12px;
            color: #555;
            margin-top: 4px;
        }

        .captcha-error {
            font-size: 12px;
            color: #dc3545;
            margin-top: 4px;
            min-height: 16px;
        }

        .scm-title {
            margin-top: -50px;
            margin-bottom: 50px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 2px;
            color: #003d82;
            text-align: center;
        }

        /* ===== FORGOT PASSWORD OVERLAY ===== */
        .overlay-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(6px);
            background: rgba(0,0,0,0.4);
            z-index: 999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .forgot-modal {
            background: #ffffff;
            border-radius: 16px;
            width: 100%;
            max-width: 420px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: fadeInScale 0.2s ease;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .forgot-modal h4 {
            font-weight: 700;
            margin-bottom: 10px;
            color: #003d82;
        }

        .forgot-modal .btn-close-custom {
            position: absolute;
            top: 16px;
            right: 20px;
            cursor: pointer;
            font-size: 20px;
        }

    </style>
</head>

<!-- ===== FORGOT PASSWORD OVERLAY ===== -->
<div class="overlay-backdrop" id="forgotOverlay">
    <div class="forgot-modal position-relative">
        <div class="btn-close-custom" onclick="closeForgotModal()">
            <i class="bi bi-x-lg"></i>
        </div>

        <h4>Lupa Password</h4>
        <p class="text-muted mb-3" style="font-size:13px;">
            Masukkan email Anda untuk menerima link reset password.
        </p>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       placeholder="email@domain.com"
                       required>

                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-login">
                Kirim Link Reset Password
            </button>
        </form>
    </div>
</div>

<body>
    <div class=login-wrapper>
        <div class=logo> {{-- asset() mengacu ke folder public/, jangan sertakan 'public/' di path --}}
            <img src="{{ asset('images/logo-pal.png') }}" class="logo-pal" alt="PAL Logo">
        </div>

        <h1 class="scm-title">SUPPLY CHAIN MANAGEMENT</h1>

        <div class="login-container">
            <div class="login-header">
                <div class="logo-container">
                </div>
            </div>

            <div class="login-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle"></i>
                        @foreach($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="login" class="form-label">Email / Name</label>
                        <input type="text"
                            class="form-control @error('login') is-invalid @enderror"
                            id="login"
                            name="login"
                            value="{{ old('login') }}"
                            placeholder="Email atau Nama"
                            required
                            autofocus>
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text=mute">Password</label>
                        <div style="position: relative;">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password" placeholder="" required>
                            <span class="password-toggle" onclick="togglePassword()">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </span>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- CAPTCHA -->
                    <div class="mb-3">
                        <label class="form-label">Captcha</label>

                        <div class="d-flex align-items-stretch gap-2 captcha-row">
                            <!-- Captcha Image -->
                            <div class="captcha-image-wrapper">
                                <img id="captchaImage"
                                    src="{{ route('captcha.generate') }}"
                                    alt="captcha"
                                    onclick="refreshCaptcha()">
                            </div>

                            <!-- Input -->
                            <div class="flex-grow-1">
                                <input type="text" name="captcha"
                                    class="form-control @error('captcha') is-invalid @enderror"
                                    placeholder="Masukkan captcha"
                                    oninput="this.value = this.value.toUpperCase()"
                                    required>

                                <div class="captcha-error">
                                    @error('captcha')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
</div>


                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <!-- Remember Me (Kiri) -->
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember Me
                            </label>
                        </div>

                        <!-- Forgot Password (Kanan) -->
                        <a href="javascript:void(0)" class="forgot-password" onclick="openForgotModal()">
                            Forgot Password
                        </a>
                    </div>

                    <button type="submit" class="btn btn-login">
                        SIGN IN
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (password.type === 'password') {
                password.type = 'text';
                toggleIcon.className = 'bi bi-eye';
            } else {
                password.type = 'password';
                toggleIcon.className = 'bi bi-eye-slash';
            }
        }
    
        function refreshCaptcha() {
            const img = document.getElementById('captchaImage');
            img.src = "{{ route('captcha.generate') }}?" + Date.now();
        }

        // auto refresh captcha setiap 20 detik
        setInterval(refreshCaptcha, 20000);
    </script>

    <script>
        function openForgotModal() {
            document.getElementById('forgotOverlay').style.display = 'flex';
        }

        function closeForgotModal() {
            document.getElementById('forgotOverlay').style.display = 'none';
        }

        // Auto buka overlay jika ada error/status dari reset password
        @if ($errors->has('email') || session('status'))
            document.addEventListener('DOMContentLoaded', function () {
                openForgotModal();
            });
        @endif
    </script>
</body>
</html>
