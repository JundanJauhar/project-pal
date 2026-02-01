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

        /* ================= SIDEBAR ================= */
        .ums-sidebar {
            width: 260px;
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #e4e4e4;
            position: fixed;
            top: 0;
            left: 0;
            padding: 24px 18px;
            display: flex;
            flex-direction: column;
        }

        .ums-sidebar .logo {
            text-align: center;
            margin-bottom: 26px;
        }

        .ums-sidebar .logo img {
            width: 140px;
        }

        .menu-section {
            margin-bottom: 20px;
        }

        .menu-title {
            font-size: 12px;
            font-weight: 700;
            color: #6d6d6d;
            margin: 16px 10px 8px;
            text-transform: uppercase;
        }

        .nav-link {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link:hover {
            background: #f2f2f2;
        }

        .nav-link.active {
            background: #003d82;
            color: #fff !important;
        }

        /* ================= USER DROPDOWN ================= */
        .sidebar-user {
            margin-top: auto;
            border-top: 1px solid #e4e4e4;
            padding-top: 14px;
        }

        .sidebar-user button {
            width: 100%;
            background: transparent;
            border: none;
            padding: 10px 12px;
            border-radius: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-user button:hover {
            background: #f0f0f0;
        }

        /* ================= CONTENT ================= */
        .ums-content {
            margin-left: 260px;
            padding: 32px 40px;
        }

        /* ================= GLOBAL TABLE STYLE ================= */
        /* Wrapper halaman tabel */
        .page-card,
        .audit-wrapper {
            background: #ffffffff !important;
        }

        /* Baris tabel */
        table.user-table tbody tr,
        table.audit-table tbody tr {
            background: #ffffffff !important;
        }

        /* Header tabel */
        table.user-table thead th,
        table.audit-table thead th {
            background: #ffffffff !important;
        }

        /* ================= ALERT ================= */
        .alert-custom {
            border-radius: 10px;
            font-size: 14px;
            padding: 12px 16px;
        }
    </style>

    @stack('styles')
</head>

<body>

<!-- ================= SIDEBAR ================= -->
<aside class="ums-sidebar">

    <div class="logo">
        <img src="{{ asset('images/logo-pal.png') }}" alt="PAL Indonesia">
    </div>

    {{-- <a class="nav-link {{ request()->routeIs('ums.dashboard') ? 'active' : '' }}"
    href="{{ route('ums.dashboard') }}">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a> --}}


    <div class="menu-section">
        <div class="menu-title">Access Control</div>

        <a class="nav-link {{ request()->is('ums/users*') ? 'active' : '' }}"
           href="{{ route('ums.users.index') }}">
            <i class="bi bi-people"></i> User Management
        </a>

        <a class="nav-link {{ request()->is('ums/divisions*') ? 'active' : '' }}"
        href="{{ route('ums.divisions.index') }}">
            <i class="bi bi-diagram-3"></i> Divisi Management
        </a>

        <a href="{{ route('ums.project.index') }}" 
        class="nav-link {{ request()->is('ums/project*') ? 'active' : '' }}">
            <i class="bi bi-cart-check"></i>
            <span>Project Management</span>
        </a>

    </div>

    <div class="menu-section">
        <div class="menu-title">Audit & Monitoring</div>

        <a class="nav-link {{ request()->is('ums/audit*') ? 'active' : '' }}"
           href="{{ route('ums.audit_logs.index') }}">
            <i class="bi bi-clipboard-data"></i> Audit Logs
        </a>

        <a class="nav-link {{ request()->is('ums/activity-logs*') ? 'active' : '' }}"
           href="{{ route('ums.activity_logs.index') }}">
            <i class="bi bi-activity"></i> Activity
        </a>

        <a class="nav-link {{ request()->is('ums/sessions*') ? 'active' : '' }}"
            href="{{ route('ums.sessions.index') }}">
            <i class="bi bi-pc-display"></i> Sessions Monitoring
        </a>

    </div>

    <a class="nav-link {{ request()->is('ums/settings*') ? 'active' : '' }}"
       href="{{ route('ums.settings.index') }}">
        <i class="bi bi-gear"></i> System Settings
    </a>

    <div class="sidebar-user dropdown">
        <button data-bs-toggle="dropdown">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-circle fs-5"></i>
                <span>{{ Auth::user()->name }}</span>
            </div>
            <i class="bi bi-chevron-up"></i>
        </button>

        <ul class="dropdown-menu w-100">
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="dropdown-item">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>

</aside>

<!-- ================= CONTENT ================= -->
<main class="ums-content">

    @if(session('success'))
        <div class="alert alert-success alert-custom">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-custom">
            <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
        </div>
    @endif

    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>
