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
            background: linear-gradient(135deg, #003d82 0%, #0056b3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .login-header {
            background: white;
            padding: 40px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo-pal {
            height: 80px;
            margin-bottom: 10px;
        }

        .company-name {
            color: #003d82;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }

        .login-body {
            background: #f5f7fa;
            padding: 40px;
        }

        .form-control {
            padding: 12px 15px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .form-control:focus {
            border-color: #003d82;
            box-shadow: 0 0 0 0.2rem rgba(0,61,130,0.15);
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .btn-login {
            background: linear-gradient(135deg, #003d82 0%, #0056b3 100%);
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
            box-shadow: 0 5px 15px rgba(0,61,130,0.3);
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
            color: #003d82;
            font-size: 14px;
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-container">
                <i class="bi bi-building" style="font-size: 60px; color: #003d82;"></i>
            </div>
            <h1 class="company-name">PT PAL</h1>
            <p style="color: #003d82; font-weight: 600; margin: 0;">INDONESIA</p>
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
                    <input type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="supplychain@gmail.com"
                           required
                           autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div style="position: relative;">
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               placeholder="••••••••••••"
                               required>
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

            <div class="mt-4 text-center">
                <small class="text-muted">
                    Demo Accounts:<br>
                    <strong>supplychain@pal.com</strong> |
                    <strong>treasury@pal.com</strong> |
                    <strong>qa@pal.com</strong><br>
                    Password: <strong>password</strong>
                </small>
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
