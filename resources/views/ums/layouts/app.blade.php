<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - UMS</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            overflow-y: scroll;
        }

        /* --------------- SIDEBAR --------------- */
        .ums-sidebar {
            width: 240px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e4e4e4;
            position: fixed;
            top: 0;
            left: 0;
            padding: 28px 20px;
        }

        .ums-sidebar .logo {
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
        }

        .ums-sidebar .logo img {
            width: 130px;
        }

        .ums-sidebar .menu-title {
            font-size: 12px;
            font-weight: 700;
            color: #6d6d6d;
            margin-top: 20px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .ums-sidebar .nav-link {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 4px;
        }

        .ums-sidebar .nav-link:hover {
            background: #f0f0f0;
        }

        .ums-sidebar .nav-link.active {
            background: #003d82;
            color: #fff !important;
        }

        /* --------------- TOPBAR --------------- */
        .ums-topbar {
            position: fixed;
            left: 240px;
            right: 0;
            top: 0;
            height: 58px;
            background: #ffffff;
            border-bottom: 1px solid #e4e4e4;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 50;
        }

        .ums-topbar .page-title {
            font-size: 20px;
            font-weight: 700;
            color: #000;
        }

        .ums-topbar .user {
            font-weight: 600;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* --------------- CONTENT WRAPPER --------------- */
        .ums-content {
            margin-left: 240px;
            margin-top: 70px;
            padding: 32px 40px;
        }

        /* SUCCESS & ERROR ALERT */
        .alert-custom {
            border-radius: 10px;
            font-size: 14px;
            padding: 12px 16px;
        }
    </style>

    @stack('styles')
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="ums-sidebar">
        <div class="logo">
            <img src="{{ asset('images/logo-pal.png') }}" alt="PAL">
        </div>

        <div class="menu-title">User Management</div>
        <ul class="nav flex-column">

            <li class="nav-item">
                <a class="nav-link {{ request()->is('ums/users*') ? 'active' : '' }}"
                    href="{{ route('ums.users.index') }}">
                    <i class="bi bi-people-fill me-2"></i> Users
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->is('ums/scopes*') ? 'active' : '' }}"
                    href="{{ route('ums.admin_scopes.index') }}">
                    <i class="bi bi-shield-lock-fill me-2"></i> Admin Scopes
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->is('ums/audit*') ? 'active' : '' }}"
                    href="{{ route('ums.audit_logs.index') }}">
                    <i class="bi bi-clipboard-data me-2"></i> Audit Logs
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->is('ums/activity-logs*') ? 'active' : '' }}"
                href="{{ route('ums.activity_logs.index') }}">
                    <i class="bi bi-clock-history me-2"></i> Activity Logs
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->is('ums/settings*') ? 'active' : '' }}"
                    href="{{ route('ums.settings.index') }}">
                    <i class="bi bi-gear-fill me-2"></i> Settings
                </a>
            </li>
        </ul>
    </aside>

    <!-- TOPBAR -->
    <header class="ums-topbar">
        <div class="page-title">
            @yield('title')
        </div>

        <div class="user dropdown">
            <span>{{ Auth::user()->name }}</span>
            <i class="bi bi-person-circle fs-4" data-bs-toggle="dropdown" style="cursor: pointer;"></i>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <!-- CONTENT WRAPPER -->
    <main class="ums-content">

        {{-- Success notification --}}
        @if(session('success'))
            <div class="alert alert-success alert-custom">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Error notification --}}
        @if(session('error'))
            <div class="alert alert-danger alert-custom">
                <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>


    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    @stack('scripts')
</body>

</html>
