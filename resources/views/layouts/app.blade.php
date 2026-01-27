<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PT PAL Indonesia')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <style>
        html {
            overflow-y: scroll;
        }

        :root {
            --pal-primary: #003d82;
            --pal-secondary: #0056b3;
            --pal-light: #e8f0fe;
            --stat-value-size: 20px;
            --priority-text-size: 16px;
            --priority-rendah: #;
            --priority-rendah-text: #6F6F6F;
            --priority-sedang: #;
            --priority-sedang-text: #ECAD02;
            --priority-tinggi: #;
            --priority-tinggi-text: #BD0000;
            --vendor-process: #ffc107;
            --vendor-process-text: #000000;
            --vendor-completed: #2;
            --vendor-completed-text: #000;
            --vendor-rejected: #dc3545;
            --vendor-rejected-text: #ffffff;
            --vendor-neutral: #f5f7fa;
            --vendor-neutral-text: #333333;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
        }

        .navbar-custom {
            background: #ffffff;
            height: 60px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, .1);
        }

        /* sidebar removed in favor of top navbar */
        .nav-center .nav-link {
            color: #6c757d;
            padding: 10px 25px;
            margin: 0 50px;
            font-weight: 600;
            letter-spacing: .2px;
            border-bottom: 2px solid transparent;
        }

        .navbar-custom .nav-link {
            height: 60px;
            /* match navbar height */
            display: flex;
            align-items: center;
            padding-top: 0;
            padding-bottom: 0;
        }

        .nav-center .nav-link.active {
            color: #000000;
            border-bottom: 2px solid #000000;
        }

        .nav-link.hover {
            color: #000000;
        }

        .main-content {
            padding: 60px;
        }

        .stat-card {
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .1);
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        /* Modern minimal style for stat-total */
        .stat-total {
            background: #f2f2f2 !important;
            color: #000 !important;
            border-radius: 18px !important;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px !important;
            overflow: hidden;
            gap: 24px;
        }

        .stat-total::before {
            content: '';
            position: absolute;
            left: 0;
            top: 16px;
            bottom: 16px;
            width: 5px;
            border-radius: 5px;
            background: #7dade8;
        }

        .stat-total .stat-content {
            display: flex;
            flex-direction: column;
            z-index: 1;
        }

        .stat-total .stat-title {
            color: #5e6a77;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-total .stat-value {
            color: #000000;
            font-size: var(--stat-value-size, 40px);
            font-weight: 800;
            line-height: 1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-total .stat-icon {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #7dade8;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-total .stat-icon-inner {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #056ce8;
            background: rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-total .stat-icon-inner i {
            font-size: 24px;
            color: #056ce8;
        }

        .stat-progress {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        /* Modern minimal style for stat-success (green theme) */
        .stat-success {
            background: #f2f2f2 !important;
            color: #000 !important;
            border-radius: 18px !important;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px !important;
            overflow: hidden;
            gap: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .05);
        }

        .stat-success::before {
            content: '';
            position: absolute;
            left: 0;
            top: 16px;
            bottom: 16px;
            width: 5px;
            border-radius: 5px;
            background: #28a745;
            /* green accent */
        }

        .stat-success .stat-content {
            display: flex;
            flex-direction: column;
            z-index: 1;
        }

        .stat-success .stat-title {
            color: #6c757d;
            /* gray */
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-success .stat-value {
            color: #000000;
            font-size: var(--stat-value-size, 40px);
            font-weight: 800;
            line-height: 1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-success .stat-icon {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #28a745;
            /* green circle */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-success .stat-icon-inner {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #0b6b1c;
            /* darker green ring */
            background: rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-success .stat-icon-inner i {
            font-size: 22px;
            color: #0b6b1c;
            /* icon color */
        }

        /* Modern minimal style for stat-rejected (red theme) */
        .stat-rejected {
            background: #f2f2f2 !important;
            color: #000 !important;
            border-radius: 18px !important;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px !important;
            overflow: hidden;
            gap: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .05);
        }

        .stat-rejected::before {
            content: '';
            position: absolute;
            left: 0;
            top: 16px;
            bottom: 16px;
            width: 5px;
            border-radius: 5px;
            background: #dc3545;
            /* red accent */
        }

        .stat-rejected .stat-content {
            display: flex;
            flex-direction: column;
            z-index: 1;
        }

        .stat-rejected .stat-title {
            color: #6c757d;
            /* gray */
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-rejected .stat-value {
            color: #000000;
            font-size: var(--stat-value-size, 40px);
            font-weight: 800;
            line-height: 1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-rejected .stat-icon {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #c8102e;
            /* deep red circle */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-rejected .stat-icon-inner {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #7a0f16;
            /* darker ring */
            background: rgba(255, 255, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-rejected .stat-icon-inner i {
            font-size: 22px;
            color: #7a0f16;
            /* icon color */
        }

        /* Modern minimal style for stat-progress (yellow theme) */
        .stat-progress {
            background: #f2f2f2 !important;
            color: #000 !important;
            border-radius: 18px !important;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 28px !important;
            overflow: hidden;
            gap: 24px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .05);
        }

        .stat-progress::before {
            content: '';
            position: absolute;
            left: 0;
            top: 16px;
            bottom: 16px;
            width: 5px;
            border-radius: 5px;
            background: #ffc107;
            /* yellow accent */
        }

        .stat-progress .stat-content {
            display: flex;
            flex-direction: column;
            z-index: 1;
        }

        .stat-progress .stat-title {
            color: #6c757d;
            /* gray */
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-progress .stat-value {
            color: #000000;
            font-size: var(--stat-value-size, 40px);
            font-weight: 800;
            line-height: 1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .stat-progress .stat-icon {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #ffc107;
            /* yellow circle */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-progress .stat-icon-inner {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 2px solid #b37f00;
            /* darker yellow/brown ring */
            background: rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-progress .stat-icon-inner i {
            font-size: 22px;
            color: #b37f00;
            /* icon color */
        }

        /* REMOVED - card-custom styling that was conflicting */
        /* REMOVED - card-header-custom styling that was conflicting */

        .timeline-progress {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 10px;
            overflow-x: auto;
        }

        .timeline-step {
            text-align: center;
            flex: 1;
            min-width: 100px;
            position: relative;
        }

        .timeline-step::after {
            content: '';
            position: absolute;
            top: 25px;
            left: 60%;
            width: 80%;
            height: 2px;
            background: #ddd;
            z-index: 0;
        }

        .timeline-step:last-child::after {
            display: none;
        }

        .timeline-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-size: 20px;
            position: relative;
            z-index: 1;
        }

        .timeline-step.completed .timeline-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .timeline-step.active .timeline-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(240, 147, 251, 0.7);
            }

            50% {
                box-shadow: 0 0 0 10px rgba(240, 147, 251, 0);
            }
        }

        .timeline-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }

        .badge-priority {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: var(--priority-text-size, 20px);
            font-weight: 600;
            display: inline-block;
        }

        /* Priority color variants use CSS variables so user can edit them */
        .badge-priority.badge-rendah {
            background: var(--priority-rendah);
            color: var(--priority-rendah-text);
        }

        .badge-priority.badge-sedang {
            background: var(--priority-sedang);
            color: var(--priority-sedang-text);
        }

        .badge-priority.badge-tinggi {
            background: var(--priority-tinggi);
            color: var(--priority-tinggi-text);
        }

        /* Vendor pill styles */
        .vendor-pill {
            padding: 6px 12px;
            border-radius: 16px;
            display: inline-block;
            font-weight: 600;
            font-size: 13px;
        }

        .vendor-status-process {
            background: var(--vendor-process);
            color: var(--vendor-process-text);
        }

        .vendor-status-completed {
            background: var(--vendor-completed);
            color: var(--vendor-completed-text);
        }

        .vendor-status-rejected {
            background: var(--vendor-rejected);
            color: var(--vendor-rejected-text);
        }

        .vendor-status-neutral {
            background: var(--vendor-neutral);
            color: var(--vendor-neutral-text);
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .table-custom {
            border-radius: 8px;
            overflow: hidden;
        }

        .table-custom thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #333;
        }

        .btn-custom {
            border-radius: 8px;
            padding: 8px 20px;
            font-weight: 500;
        }

        .notification-badge {
            position: relative;
        }

        .notification-badge .badge {
            position: absolute;
            top: -8px;
            right: -8px;
        }

        .profile-toggle {
            position: relative;
        }

        .notif-dot {
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background: #dc3545;
            border-radius: 50%;
            display: none;
        }

        .logo-pal {
            height: 100%;
            max-height: 120px;
            /* keep within 60px navbar */
            object-fit: contain;
            margin-right: 15px;
            margin-top: 0;
            margin-bottom: 0;
            display: block;
        }

        .navbar-nav {
            margin-left: 100px;
        }

        /* center menu in navbar */
        @media (min-width: 768px) {
            .navbar .nav-center {
                position: static;
                transform: none;
                margin-left: auto;
                /* push nav group to the right side */
            }
        }

        /* Paksa agar badge tidak bisa ditimpa oleh JS/Bootstrap */
        .status-badge {
            background-color: var(--badge-color) !important;
            color: #fff !important;
            padding: 6px 12px !important;
            font-weight: 600 !important;
            font-size: 12px !important;
            border-radius: 8px !important;
            min-width: 110px;
            text-align: center;
        }

        .nav-center .nav-link {
            color: #6c757d;
            padding: 10px 25px;
            font-weight: 600;
            font-size: 15px;
            border-bottom: 2px solid transparent;
        }

        .nav-center .nav-link.active {
            color: #000;
            border-bottom: 2px solid #000;
        }

        .nav-center {
            display: flex;
            gap: 40px;
            /* membuat sejajar & rapi seperti Figma */
        }

        .ums-container {
            background: #ffffff;
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Navbar -->
    @if(!isset($hideNavbar) || !$hideNavbar)
    @if(Auth::guard('vendor')->check())
    {{-- Navbar khusus Vendor: Logo + Nama Vendor + Logout --}}
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center ms-4" href="{{ route('vendor.index') }}" wire:navigate>
                <img src="{{ asset('images/logo-pal.png') }}" class="logo-pal" alt="PAL Logo">
            </a>

            <div class="ms-auto d-flex align-items-center me-4 gap-3">
                {{-- Notification Bell --}}
                @php
                // Check for notification count
                $vendorId = Auth::guard('vendor')->id();
                $notifCount = 0;

                if($vendorId) {
                // 1. STORED NOTIFICATIONS (Events)
                $storedCount = \App\Models\VendorNotification::where('vendor_id', $vendorId)
                ->where('is_read', false)
                ->count();
                $notifCount += $storedCount;

                // 2. COMPUTED TASKS (Contract Review)
                // "Tasks" are always considered "Action Needed"
                $reviews = \App\Models\ContractReview::where('vendor_id', $vendorId)
                ->with(['revisions' => function($q) {
                $q->orderBy('contract_review_revision_id', 'desc');
                }])
                ->get();

                foreach($reviews as $rev) {
                $latest = $rev->revisions->first();
                if(!$latest) continue;

                // Action Needed (Pending/Revisi + No Link)
                if((!$latest->result || $latest->result == 'pending' || $latest->result == 'revisi') && empty($latest->vendor_link)) {
                $notifCount++;
                }
                }

                // 3. COMPUTED TASKS (Evatek)
                $evatekItems = \App\Models\EvatekItem::where('vendor_id', $vendorId)
                ->with('latestRevision')
                ->get();

                foreach($evatekItems as $evk) {
                $latest = $evk->latestRevision;
                if(!$latest) continue;

                // Action Needed (Pending + No Link) OR Revisi + No Link
                if(($latest->status == 'pending' || $latest->status == 'revisi') && empty($latest->vendor_link)) {
                $notifCount++;
                }
                }
                }
                @endphp
                <a href="{{ route('vendor.notifications') }}" class="text-dark position-relative me-2" style="font-size: 20px; text-decoration: none;">
                    <i class="bi bi-bell"></i>
                    @if($notifCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; padding: 3px 6px; min-width: 18px;">
                        {{ $notifCount }}
                        <span class="visually-hidden">unread messages</span>
                    </span>
                    @endif
                </a>

                {{-- Nama Vendor --}}
                <span class="text-dark fw-semibold" style="font-size: 15px;">
                    {{ Auth::guard('vendor')->user()->name_vendor ?? 'Vendor' }}
                </span>

                {{-- Logout Button --}}
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-dark d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </nav>
    @elseif(Auth::check())

    @php
    $division = Auth::user()->division?->division_name;
    @endphp
    {{-- Navbar untuk Internal Users (Dashboard, Projects, dll) --}}
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center ms-4" href="{{ route('dashboard') }}" wire:navigate>
                <img src="{{ asset('images/logo-pal.png') }}" class="logo-pal" alt="PAL Logo">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav nav-center ms-auto me-3 padding align-items-center">

                    {{-- DASHBOARD --}}
                    <li class="nav-item">
                        @if($division === 'Sekretaris Direksi')
                        <a class="nav-link {{ request()->routeIs('sekdir.dashboard*') ? 'active' : '' }}"
                            href="{{ route('sekdir.dashboard') }}" wire:navigate>
                            Dashboard
                        </a>
                        @else
                        <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}"
                            href="{{ route('dashboard') }}" wire:navigate>
                            Dashboard
                        </a>
                        @endif
                    </li>

                    {{-- USER DIVISION --}}
                    @if($division === 'User Division')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}"
                            href="{{ route('user.list') }}" wire:navigate>
                            Pengadaan
                        </a>
                    </li>
                    @endif

                    {{-- SEKRETARIS DIREKSI
                    @if($division === 'Sekretaris Direksi')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('sekdir.approval*') ? 'active' : '' }}"
                            href="{{ route('sekdir.approval') }}" wire:navigate>
                            Department
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('projects*') ? 'active' : '' }}"
                            href="{{ route('projects.index') }}" wire:navigate>
                            Projects
                        </a>
                    </li>
                    @endif --}}

                    {{-- DESAIN & SUPPLY CHAIN --}}
                    @if(in_array($division, ['Desain', 'Supply Chain']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('desain.list-project*') ? 'active' : '' }}"
                            href="{{ route('desain.list-project') }}" wire:navigate>
                            Projects
                        </a>
                    </li>
                    @endif

                    {{-- SUPPLY CHAIN --}}
                    @if($division === 'Supply Chain')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('supply-chain.dashboard*') ? 'active' : '' }}"
                            href="{{ route('supply-chain.dashboard') }}" wire:navigate>
                            Department
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('supply-chain.vendor.kelola*') ? 'active' : '' }}"
                            href="{{ route('supply-chain.vendor.kelola') }}" wire:navigate>
                            Kelola Vendor
                        </a>
                    </li>
                    @endif

                    {{-- TREASURY & ACCOUNTING --}}
                    @if(in_array($division, ['Treasury', 'Accounting']))
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('payments*') ? 'active' : '' }}"
                            href="{{ route('payments.index') }}" wire:navigate>
                            Payments
                        </a>
                    </li>
                    @endif

                    {{-- QUALITY ASSURANCE --}}
                    @if($division === 'Quality Assurance')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('inspections.index') ? 'active' : '' }}"
                            href="{{ route('inspections.index') }}">
                            Department
                        </a>
                    </li>
                    @endif

                </ul>

                {{-- USER MENU --}}
                <ul class="navbar-nav align-items-center me-5">
                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center text-dark profile-toggle" href="#"
                            data-bs-toggle="dropdown">
                            <span class="ms-2">{{ Auth::user()->name }}</span>
                            <i class="bi bi-person-circle ms-3" style="font-size:22px; color:#000"></i>
                            <span class="notif-dot" id="notif-dot"></span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between"
                                    href="{{ route('notifications.index') }}" wire:navigate>
                                    <span><i class="bi bi-bell-fill me-2"></i> Notifikasi</span>
                                    <span class="badge rounded-pill bg-danger" id="notif-count-dd"
                                        style="display:none;">0</span>
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    @endif
    @endif

    <div class="container-fluid">
        <div class="row justify-content-center">
            <!-- Main Content (full width) -->
            <main class="col-12 main-content">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Setup CSRF token for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Load notification count
        function loadNotificationCount() {
            $.get('/notifications/unread-count', function(data) {
                const count = data.count || 0;
                const $ddBadge = $('#notif-count-dd');
                const $dot = $('#notif-dot');

                $ddBadge.text(count);
                if (count > 0) {
                    $ddBadge.show();
                } else {
                    $ddBadge.hide();
                }
                if (count > 0) {
                    $dot.show();
                } else {
                    $dot.hide();
                }
            });
        }

        // Load on page load
        $(document).ready(function() {
            loadNotificationCount();

            // Refresh every 30 seconds
            setInterval(loadNotificationCount, 30000);
        });
    </script>

    @stack('scripts')
</body>

</html>