@extends('layouts.app')

@section('title', 'User Management')

@section('content')

{{-- Import Google Font Inter --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body { background: #ffffff !important; }

    /* WRAPPER CARD */
    .ums-wrapper {
        background: #fff;
        padding: 36px 40px;
        border-radius: 14px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        margin-top: 25px;
    }

    /* HEADER */
    .ums-title {
        font-size: 30px;
        font-weight: 800;
        color: #000;
    }

    .ums-title .count {
        font-size: 18px;
        color: #6b6b6b;
        margin-left: 6px;
    }

    /* BUTTONS */
    .filter-btn {
        background: #fff;
        border: 1px solid #cccccc;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .add-btn {
        background: #0d6efd;
        color: #fff;
        padding: 9px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* TABLE REFINEMENT */
    table.user-table {
        width: 100%;
        border-collapse: collapse !important;
        font-size: 15px;
    }

    /* Hilangkan padding default bootstrap */
    .table > :not(caption) > * > * {
        padding: 14px 0 !important;
    }

    /* HEADER ROW */
    thead th {
        font-weight: 700;
        border-bottom: 2px solid #000 !important;
        padding-bottom: 12px !important;
        font-size: 15px;
        letter-spacing: 0.3px;
    }

    /* BODY ROW */
    tbody tr {
        height: 72px;
        border-bottom: 1px solid #E6E6E6 !important;
    }

    /* CHECKBOX COLUMN */
    .checkbox-cell {
        width: 42px;
        text-align: center;
    }

    .checkbox-cell input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    /* NAME + EMAIL */
    .user-name {
        font-size: 15px;
        font-weight: 700;
    }

    .email-muted {
        font-size: 12px;
        color: #777;
        margin-top: -3px;
        letter-spacing: 0.2px;
    }

    /* ROLE BADGE */
    .role-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    /* STATUS */
    .status-active,
    .status-inactive {
        display: flex;
        align-items: center;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.2px;
    }

    .status-active i,
    .status-inactive i {
        font-size: 9px;
        margin-right: 6px;
    }

    .status-active { color: #1fa13c; }
    .status-inactive { color: #d40000; }

    /* ACTION */
    .reset-action {
        color: #555;
        font-size: 14px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-right: 18px;
    }

    .reset-action i {
        font-size: 16px;
    }

    .delete-btn {
        background: none;
        border: none;
        color: #444;
        font-size: 18px;
        cursor: pointer;
    }
</style>

<div class="ums-wrapper">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="ums-title">
            All Users <span class="count">{{ $users->count() }}</span>
        </div>

        <div class="d-flex align-items-center" style="gap: 16px;">
            <button class="filter-btn">
                <i class="bi bi-sliders"></i> Filters
            </button>

            <a href="{{ route('ums.users.create') }}" class="add-btn">
                <i class="bi bi-plus-lg"></i> Add New User
            </a>
        </div>
    </div>

    <!-- TABLE -->
    <table class="table user-table align-middle">
        <thead>
            <tr>
                <th class="checkbox-cell"><input type="checkbox" id="select-all"></th>
                <th>Nama</th>
                <th>Roles</th>
                <th>Status</th>
                <th>Created</th>
                <th class="text-end">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $u)
                @php
                    // EXACT BADGE COLORS PROTOTYPE
                    $roleColors = [
                        'superadmin' => 'background:#CFCFCF;color:#000;',
                        'admin' => 'background:#DDE8FF;color:#004EBD;',
                        'sekretaris_direksi' => 'background:#FFF5C9;color:#7C6600;',
                        'sekretaris' => 'background:#FFF5C9;color:#7C6600;',
                        'treasury' => 'background:#FFD7D7;color:#A10000;',
                        'accounting' => 'background:#FFE5C0;color:#8A5F00;',
                        'akuntansi' => 'background:#FFE5C0;color:#8A5F00;',
                        'supply_chain' => 'background:#E5D5FF;color:#5E00A3;',
                        'desain' => 'background:#E0FFD9;color:#008000;',
                        'qa' => 'background:#D9FFFF;color:#008A8A;',
                        'user' => 'background:#EFEFEF;color:#555;',
                    ];
                    $badgeColor = $roleColors[$u->roles] ?? 'background:#E0E0E0;color:#333;';
                @endphp

                <tr>
                    <td class="checkbox-cell">
                        <input type="checkbox" value="{{ $u->user_id }}">
                    </td>

                    <td>
                        <div class="user-name">{{ $u->name }}</div>
                        <div class="email-muted">{{ $u->email }}</div>
                    </td>

                    <td>
                        <span class="role-badge" style="{{ $badgeColor }}">
                            {{ ucwords(str_replace('_', ' ', $u->roles)) }}
                        </span>
                    </td>

                    <td>
                        @if (strtolower($u->status) === 'active')
                            <span class="status-active"><i class="bi bi-circle-fill"></i> Active</span>
                        @else
                            <span class="status-inactive"><i class="bi bi-circle-fill"></i> Inactive</span>
                        @endif
                    </td>

                    <td>
                        {{ $u->created_at ? $u->created_at->format('M d, Y') : '-' }}
                    </td>

                    <td class="text-end">
                        <a href="{{ route('ums.users.edit', $u->user_id) }}" class="reset-action">
                            <i class="bi bi-arrow-clockwise"></i> Reset Password
                        </a>

                        <form action="{{ route('ums.users.destroy', $u->user_id) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button class="delete-btn" onclick="return confirm('Delete user?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>

            @endforeach
        </tbody>
    </table>

</div>

@endsection
