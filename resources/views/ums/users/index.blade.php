@extends('ums.layouts.app')

@section('title', 'User Management')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body { background: #ffffff !important; font-family: 'Inter', sans-serif; }

    /* WRAPPER */
    .ums-wrapper {
        background: #ffffff;
        padding: 40px 50px;
        border-radius: 16px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        margin-top: 20px;
    }

    /* HEADER */
    .ums-title {
        font-size: 32px;
        font-weight: 800;
        color: #000;
    }

    .count {
        font-size: 18px;
        color: #6b6b6b;
        margin-left: 6px;
    }

    /* TOP BAR */
    .top-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* SEARCH BAR */
    .search-box {
        display: flex;
        align-items: center;
        background: #f0f0f0;
        padding: 8px 16px;
        border-radius: 8px;
        width: 340px;
    }
    .search-box input {
        border: none;
        background: none;
        outline: none;
        flex: 1;
        font-size: 14px;
    }

    /* FILTER */
    .filter-box {
        display: flex;
        gap: 12px;
    }

    select.filter-select {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
    }

    /* ADD BUTTON */
    .add-btn {
        background: #003d82;
        color: #fff !important;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none !important;
        white-space: nowrap;
    }

    /* TABLE */
    table.user-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
        table-layout: fixed;
    }

    thead th {
        font-size: 15px;
        font-weight: 700;
        padding-bottom: 16px;
        color: #000;
        border-bottom: 2px solid #000;
    }

    tbody tr {
        height: 64px;
        background: #f6f6f6;
        border-bottom: 7px solid #ffffff;
    }

    td {
        vertical-align: middle;
        padding: 10px 12px;
        font-size: 14px;
    }

    th:nth-child(1), td:nth-child(1) { width: 26%; }
    th:nth-child(2), td:nth-child(2) { width: 18%; }
    th:nth-child(3), td:nth-child(3) { width: 12%; }
    th:nth-child(4), td:nth-child(4) { width: 13%; }
    th:nth-child(5), td:nth-child(5) { width: 13%; }
    th:nth-child(6), td:nth-child(6) { text-align: right; width: 18%; }

    /* USER NAME */
    .user-name {
        font-size: 15px;
        font-weight: 700;
    }

    .email-muted {
        font-size: 12px;
        color: #777;
        margin-top: -3px;
    }

    /* ROLE BADGE */
    .role-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 600;
    }

    /* STATUS */
    .status-active, .status-inactive {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 14px;
    }
    .status-active i { color: #12a132; font-size: 10px; }
    .status-inactive i { color: #d00000; font-size: 10px; }

    /* ACTION ICONS */
    .action-icon {
        font-size: 19px;
        margin-left: 10px;
        cursor: pointer;
        color: #333;
        transition: 0.15s;
    }
    .action-icon:hover {
        color: #000;
    }
</style>

<div class="ums-wrapper">

    <!-- HEADER -->
    <div class="top-actions mb-4">

        <div class="ums-title">
            All Users <span class="count">{{ $users->count() }}</span>
        </div>

        <div class="d-flex align-items-center gap-3">

            <!-- SEARCH BAR -->
            <div class="search-box">
                <i class="bi bi-search me-2"></i>
                <input type="text" id="searchInput" placeholder="Search user...">
            </div>

            <!-- FILTERS -->
            <div class="filter-box">
                <!-- Filter Role -->
                <select id="filterRole" class="filter-select">
                    <option value="">All Roles</option>
                    @foreach(array_unique($users->pluck('roles')->toArray()) as $role)
                        <option value="{{ strtolower($role) }}">
                            {{ ucwords(str_replace('_',' ',$role)) }}
                        </option>
                    @endforeach
                </select>

                <!-- Filter Status -->
                <select id="filterStatus" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- ADD NEW USER -->
            <a href="{{ route('ums.users.create') }}" class="add-btn">
                <i class="bi bi-plus-lg"></i> Add New User
            </a>

        </div>
    </div>

    <!-- TABLE -->
    <table class="user-table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Roles</th>
                <th>Status</th>
                <th>Created</th>
                <th>Updated</th>
                <th style="text-align:right;">Aksi</th>
            </tr>
        </thead>

        <tbody id="userTableBody">
            @foreach($users as $u)
                @php
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

                <tr class="user-row"
                    data-name="{{ strtolower($u->name) }}"
                    data-email="{{ strtolower($u->email) }}"
                    data-role="{{ strtolower($u->roles) }}"
                    data-status="{{ strtolower($u->status) }}">

                    <td>
                        <div class="user-name">{{ $u->name }}</div>
                        <div class="email-muted">{{ $u->email }}</div>
                    </td>

                    <td>
                        <span class="role-badge" style="{{ $badgeColor }}">
                            {{ ucwords(str_replace('_',' ',$u->roles)) }}
                        </span>
                    </td>

                    <td>
                        @if (strtolower($u->status) === 'active')
                            <span class="status-active"><i class="bi bi-circle-fill"></i> Active</span>
                        @else
                            <span class="status-inactive"><i class="bi bi-circle-fill"></i> Inactive</span>
                        @endif
                    </td>

                    <td>{{ $u->created_at->format('M d, Y') }}</td>
                    <td>{{ $u->updated_at->format('M d, Y') }}</td>

                    <td style="text-align:right;">

                        <!-- EDIT -->
                        <a href="{{ route('ums.users.edit', $u->user_id) }}" title="Edit">
                            <i class="bi bi-pencil-square action-icon"></i>
                        </a>

                        <!-- FORCE LOGOUT -->
                        <form action="{{ route('ums.users.forceLogout', $u->user_id) }}"
                              method="POST" style="display:inline;">
                            @csrf
                            <button class="action-icon" style="border:none;background:none;" title="Force Logout">
                                <i class="bi bi-power text-warning"></i>
                            </button>
                        </form>

                        <!-- DELETE -->
                        <form action="{{ route('ums.users.destroy', $u->user_id) }}"
                              method="POST" style="display:inline;">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Hapus user ini?')"
                                    style="border:none;background:none;" title="Delete">
                                <i class="bi bi-trash text-danger action-icon"></i>
                            </button>
                        </form>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>

<!-- FILTER + SEARCH SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    const searchInput = document.getElementById("searchInput");
    const filterRole = document.getElementById("filterRole");
    const filterStatus = document.getElementById("filterStatus");
    const rows = document.querySelectorAll(".user-row");

    function filterTable() {
        const search = searchInput.value.toLowerCase();
        const role = filterRole.value;
        const status = filterStatus.value;

        rows.forEach(row => {
            const name = row.dataset.name;
            const email = row.dataset.email;
            const userRole = row.dataset.role;
            const userStatus = row.dataset.status;

            const matchSearch =
                name.includes(search) ||
                email.includes(search) ||
                userRole.includes(search) ||
                userStatus.includes(search);

            const matchRole = role === "" || userRole === role;
            const matchStatus = status === "" || userStatus === status;

            if (matchSearch && matchRole && matchStatus) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    searchInput.addEventListener("keyup", filterTable);
    filterRole.addEventListener("change", filterTable);
    filterStatus.addEventListener("change", filterTable);
});
</script>

@endsection
