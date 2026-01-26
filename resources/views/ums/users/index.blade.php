@extends('ums.layouts.app')

@section('title', 'User Management')

@section('content')

<style>
    /* ================= CARD ================= */
    .page-card {
        border-radius: 14px;
        padding: 26px 28px;
    }

    /* ================= HEADER ================= */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 18px;
    }

    .page-title {
        font-size: 26px;
        font-weight: 800;
    }

    .page-title span {
        font-size: 16px;
        color: #777;
        margin-left: 6px;
    }

    /* ================= FILTER ================= */
    .filter-bar {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .filter-bar input,
    .filter-bar select {
        height: 32px;
        border-radius: 6px;
        border: 1px solid #bdbdbd;
        padding: 0 12px;
        font-size: 13px;
        background: #f0f0f0;
    }

    .filter-bar input {
        width: 190px;
        background: #fff;
    }

    .add-btn {
        background: #003d82;
        color: #fff;
        padding: 7px 14px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        display: flex;
        gap: 6px;
        align-items: center;
        text-decoration: none;
    }

    /* ================= TABLE WRAP ================= */
    .table-wrapper {
        overflow-x: auto;
        margin-top: 16px;
    }

    /* ================= TABLE ================= */
    .user-table {
        width: 100%;
        min-width: 1200px;
        border-collapse: collapse;
    }

    /* HEADER */
    .user-table thead th {
        font-size: 13px;
        font-weight: 700;
        padding: 12px 8px;
        text-align: center;
        border-bottom: 2px solid #000;
    }

    /* ✅ BARIS TABEL PUTIH */
    .user-table tbody tr {
        background: #ffffff;
        border-bottom: 1px solid #e0e0e0;
    }

    .user-table tbody td {
        padding: 14px 8px;
        font-size: 13px;
        text-align: center;
        vertical-align: middle;
    }

    /* ALIGN LEFT */
    .col-name,
    .col-division {
        text-align: left;
    }

    .user-name {
        font-weight: 700;
    }

    .email-muted,
    .sub-muted {
        font-size: 11px;
        color: #777;
    }

    /* ================= ROLE BADGE ================= */
    .role-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    /* ================= ROLE COLOR MAP ================= */
    .role-requester { background:#f0f0f0; }
    .role-inquiry { background:#d5e5ff; }
    .role-evatek { background:#e0f7fa; }
    .role-negotiation { background:#fff3cd; }
    .role-pengadaan { background:#e6e6fa; }
    .role-contract { background:#d1ecf1; }
    .role-pembayaran { background:#ffd5d5; }
    .role-delivery { background:#d4edda; }
    .role-treasury { background:#f8d7da; }
    .role-accounting { background:#ffddb3; }
    .role-qa_inspector { background:#d2fffa; }
    .role-qa_approver { background:#cce5ff; }
    .role-sekdir { background:#feefc6; }
    .role-designer { background:#dfffd6; }
    .role-vendor { background:#e8eaf6; }
    .role-superadmin { background:#cccccc; }
    .role-admin { background:#d5e5ff; }

    /* ================= ROLE GRID (3 PER BARIS) ================= */
    .roles-grid {
        display: grid;
        grid-template-columns: repeat(3, max-content);
        gap: 6px 6px;

        /* CENTER GRID DI DALAM TD */
        justify-content: center;
        justify-items: center;
        margin: 0 auto;
    }

    /* ================= ROLES COLUMN CENTER ================= */
    .col-roles {
        text-align: center;
    }

    /* ================= STATUS ================= */
    .status-active {
        color: #1a9e37;
        font-weight: 700;
    }

    .status-inactive {
        color: #d93025;
        font-weight: 700;
    }

    /* ================= ACTION ================= */
    .col-action {
        text-align: right;
        white-space: nowrap;
    }

    .action-icon {
        font-size: 16px;
        margin-left: 10px;
        cursor: pointer;
    }

    .action-edit { color: #DABA61; }
    .action-active { color: #2E7D32; }
    .action-inactive { color: #C62828; }
    .action-delete { color: #CF5A5A; }
</style>

<div class="page-card">

    <div class="page-header">
        <div class="page-title">
            All Users <span>{{ $users->count() }}</span>
        </div>

        <div class="filter-bar">
            <input type="text" id="searchInput" placeholder="Search User">

            <select id="filterDivision">
                <option value="">All Divisi</option>
                @foreach($users->pluck('division.division_name')->unique()->filter() as $d)
                    <option value="{{ strtolower($d) }}">{{ $d }}</option>
                @endforeach
            </select>

            <select id="filterRole">
                <option value="">All Roles</option>
                @foreach($users->pluck('roles')->flatten()->pluck('role_code')->unique() as $r)
                    <option value="{{ strtolower($r) }}">{{ ucfirst(str_replace('_', ' ', $r)) }}</option>
                @endforeach
            </select>

            <select id="filterStatus">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <a href="{{ route('ums.users.create') }}" class="add-btn">
                <i class="bi bi-plus-lg"></i> Add New User
            </a>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Divisi / Departement</th>
                    <th class="col-roles">Roles</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Last Updated</th>
                    <th>Created</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @foreach($users as $u)
                <tr class="user-row"
                    data-name="{{ strtolower($u->name) }}"
                    data-email="{{ strtolower($u->email) }}"
                    data-division="{{ strtolower($u->division->division_name ?? '') }}"
                    data-role="{{ strtolower($u->roles->pluck('role_code')->implode(',')) }}"
                    data-status="{{ strtolower($u->status) }}">

                    <td class="col-name">
                        <div class="user-name">{{ $u->name }}</div>
                        <div class="email-muted">{{ $u->email }}</div>
                    </td>

                    <td class="col-division">
                        <div>{{ $u->division->division_name ?? '-' }}</div>
                        <div class="sub-muted">{{ $u->division->description ?? '-' }}</div>
                    </td>

                    <td class="col-roles">
                        @if($u->roles->isNotEmpty())
                            <div class="roles-grid">
                                @foreach($u->roles as $role)
                                    <span class="role-badge role-{{ strtolower($role->role_code) }}">
                                        {{ ucfirst(str_replace('_', ' ', $role->role_code)) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="role-badge role-none">No Role</span>
                        @endif
                    </td>

                    <td>
                        <span class="{{ $u->status === 'active' ? 'status-active' : 'status-inactive' }}">
                            ● {{ ucfirst($u->status) }}
                        </span>
                    </td>

                    {{-- ✅ PERBAIKAN: LAST LOGIN --}}
                    <td>
                        {{ $u->last_login_at
                            ? \Carbon\Carbon::parse($u->last_login_at)->format('M d, Y H:i')
                            : '-'
                        }}
                    </td>


                    <td>{{ $u->updated_at->format('M d, Y') }}</td>
                    <td>{{ $u->created_at->format('M d, Y') }}</td>

                    <td class="col-action">
                        <a href="{{ route('ums.users.edit', $u->user_id) }}">
                            <i class="bi bi-pencil action-icon action-edit"></i>
                        </a>

                        <form method="POST"
                            action="{{ route('ums.users.toggleStatus', $u->user_id) }}"
                            style="display:inline">
                            @csrf
                            <button type="submit" style="border:none;background:none;">
                                @if($u->status === 'active')
                                    <i class="bi bi-x-circle action-icon action-inactive"></i>
                                @else
                                    <i class="bi bi-check-circle action-icon action-active"></i>
                                @endif
                            </button>
                        </form>

                        <form method="POST"
                            action="{{ route('ums.users.destroy', $u->user_id) }}"
                            style="display:inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="border:none;background:none;">
                                <i class="bi bi-trash action-icon action-delete"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('searchInput');
    const divisi = document.getElementById('filterDivision');
    const role = document.getElementById('filterRole');
    const status = document.getElementById('filterStatus');
    const rows = document.querySelectorAll('.user-row');

    function filterTable() {
        const s = search.value.toLowerCase();

        rows.forEach(row => {
            const matchText =
                row.dataset.name.includes(s) ||
                row.dataset.email.includes(s);

            const matchDiv = !divisi.value || row.dataset.division === divisi.value;
            const matchRole = !role.value || row.dataset.role === role.value;
            const matchStatus = !status.value || row.dataset.status === status.value;

            row.style.display =
                (matchText && matchDiv && matchRole && matchStatus) ? '' : 'none';
        });
    }

    [search, divisi, role, status].forEach(el => {
        el.addEventListener('input', filterTable);
        el.addEventListener('change', filterTable);
    });
});
</script>

@endsection
