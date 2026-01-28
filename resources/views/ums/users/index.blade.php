@extends('ums.layouts.app')

@section('title', 'User Management')

@section('content')

<style>
.user-wrapper {
    padding: 26px 30px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    margin-top: 20px;
}

.user-title {
    font-size: 22px;
    font-weight: 800;
}

.user-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 18px;
}

.user-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    gap: 12px;
}

.user-search {
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Table */
.user-table {
    width: 100%;
    min-width: 1200px;
    border-collapse: collapse;
}

.user-table th {
    font-size: 13px;
    font-weight: 700;
    padding: 12px 8px;
    border-bottom: 2px solid #ddd;
    text-align: center;
}

.user-table td {
    font-size: 13px;
    padding: 14px 8px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    text-align: center;
}

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

/* Role badges */
.role-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(3, max-content);
    gap: 6px;
    justify-content: center;
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

/* Status */
.status-active {
    color: #1a9e37;
    font-weight: 700;
}

.status-inactive {
    color: #d93025;
    font-weight: 700;
}

/* Actions */
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

<div class="user-wrapper">

    {{-- Title --}}
    <div class="user-title">
        User Management <span class="text-muted" style="font-size:14px;">({{ $users->count() }})</span>
    </div>
    <div class="user-subtitle">
        Manage system users, roles, and access control
    </div>

    {{-- Toolbar --}}
    <div class="user-toolbar">

        <div class="user-search">
            <input type="text" id="searchInput" class="form-control form-control-sm"
                   style="width:190px;" placeholder="Search User">

            <select id="filterDivision" class="form-select form-select-sm">
                <option value="">All Divisi</option>
                @foreach($users->pluck('division.division_name')->unique()->filter() as $d)
                    <option value="{{ strtolower($d) }}">{{ $d }}</option>
                @endforeach
            </select>

            <select id="filterRole" class="form-select form-select-sm">
                <option value="">All Roles</option>
                @foreach($users->pluck('roles')->flatten()->pluck('role_code')->unique() as $r)
                    <option value="{{ strtolower($r) }}">
                        {{ ucfirst(str_replace('_', ' ', $r)) }}
                    </option>
                @endforeach
            </select>

            <select id="filterStatus" class="form-select form-select-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <a href="{{ route('ums.users.create') }}" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg"></i> Add New User
        </a>
    </div>

    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table class="user-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Divisi / Departement</th>
                    <th>Roles</th>
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

                    <td>
                        @if($u->roles->isNotEmpty())
                            <div class="roles-grid">
                                @foreach($u->roles as $role)
                                    <span class="role-badge role-{{ strtolower($role->role_code) }}">
                                        {{ ucfirst(str_replace('_', ' ', $role->role_code)) }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-muted-sm">No Role</span>
                        @endif
                    </td>

                    <td>
                        <span class="{{ $u->status === 'active' ? 'status-active' : 'status-inactive' }}">
                            ● {{ ucfirst($u->status) }}
                        </span>
                    </td>

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
            const matchRole = !role.value || row.dataset.role.includes(role.value);
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
