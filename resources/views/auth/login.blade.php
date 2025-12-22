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
            translate=z-2;
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
    </style>
</head>

<body>
    <div class=login-wrapper>
        <div class=logo> {{-- asset() mengacu ke folder public/, jangan sertakan 'public/' di path --}}
            <img src="{{ asset('images/logo-pal.png') }}" class="logo-pal" alt="PAL Logo">
        </div>
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
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                            name="email" value="" placeholder="" required
                            autofocus>
                        @error('email')
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
                        <div class="text-end mt-2">
                            <a href="#" class="forgot-password">Forgot Password</a>
                        </div>
                    </div>

                    <!-- CAPTCHA -->
                    <div class="mb-3">
                        <label class="form-label">Captcha</label>

                        <div class="captcha-box">
                            <img src="{{ route('captcha.generate') }}"
                                 alt="captcha"
                                 onclick="this.src='{{ route('captcha.generate') }}?'+Math.random()">

                            <input type="text" name="captcha"
                                   class="form-control @error('captcha') is-invalid @enderror"
                                   placeholder="Masukkan captcha"
                                   required>
                        </div>

                        <div class="captcha-note">
                            Klik gambar untuk memperbarui captcha
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember Me
                        </label>
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
    </script>
</body>

</html>
