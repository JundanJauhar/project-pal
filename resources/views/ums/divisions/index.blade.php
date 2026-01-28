@extends('ums.layouts.app')

@section('title', 'Divisi Management')

@section('content')

<style>
.division-wrapper {
    padding: 26px 30px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    margin-top: 20px;
}

.division-title {
    font-size: 22px;
    font-weight: 800;
}

.division-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 18px;
}

.division-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    gap: 12px;
}

.division-table {
    width: 100%;
    border-collapse: collapse;
}

.division-table th {
    font-size: 13px;
    font-weight: 700;
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}

.division-table td {
    font-size: 13px;
    padding: 14px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.role-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 12px;
    background: #f2f2f2;
    border: 1px solid #ddd;
    font-weight: 600;
}

.count-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    background: #0dcaf0;
    color: #fff;
    font-weight: 700;
}

.text-muted-sm {
    font-size: 12px;
    color: #666;
}

.edit-btn {
    background: #ffb300;
    border: none;
    color: #000;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
}

.delete-btn {
    background: #c62828;
    border: none;
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
}
</style>

<div class="division-wrapper">

    {{-- Title --}}
    <div class="division-title">Divisi Management</div>
    <div class="division-subtitle">
        Manage division structure and roles configuration
    </div>

    {{-- Toolbar --}}
    <div class="division-toolbar">
        <div></div>

        <button class="btn btn-sm btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#addDivisionModal">
            + Tambah Divisi
        </button>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Table --}}
    <table class="division-table">
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th>Division</th>
                <th>Description</th>
                <th style="width:120px;">Total Roles</th>
                <th>Roles List</th>
                <th style="width:140px; text-align:center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @foreach($divisions as $i => $division)
            <tr>
                <td class="text-muted-sm">
                    {{ $i + 1 }}
                </td>

                <td style="font-weight:700;">
                    {{ $division->division_name }}
                </td>

                <td class="text-muted-sm">
                    {{ $division->description }}
                </td>

                <td>
                    <span class="count-badge">
                        {{ $division->roles_count }}
                    </span>
                </td>

                <td>
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        @forelse($division->roles as $role)
                            <span class="role-badge">
                                {{ $role->role_name }}
                            </span>
                        @empty
                            <span class="text-muted-sm">No roles</span>
                        @endforelse
                    </div>
                </td>

                <td style="text-align:center;">
                    <button class="edit-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#editDivisionModal{{ $division->division_id }}">
                        Edit
                    </button>

                    <form action="{{ route('ums.divisions.destroy', $division->division_id) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Yakin hapus divisi ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="delete-btn">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>

            {{-- ================= EDIT MODAL ================= --}}
            <div class="modal fade" id="editDivisionModal{{ $division->division_id }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">

                        <form method="POST"
                              action="{{ route('ums.divisions.update', $division->division_id) }}">
                            @csrf
                            @method('PUT')

                            <div class="modal-header bg-light">
                                <h5 class="modal-title">
                                    Edit Division — {{ $division->division_name }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Division Name</label>
                                        <input type="text" class="form-control"
                                               value="{{ $division->division_name }}" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Description</label>
                                        <input type="text" class="form-control"
                                               value="{{ $division->description }}" readonly>
                                    </div>
                                </div>

                                <hr>

                                <label class="form-label fw-semibold mb-2">
                                    Roles in this Division
                                </label>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    @forelse($division->roles as $role)
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="role-badge">
                                                {{ $role->role_name }}
                                            </span>

                                            <form action="{{ route('ums.divisions.roles.destroy', $role->role_id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Hapus role ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger px-2 py-0">
                                                    &times;
                                                </button>
                                            </form>
                                        </div>
                                    @empty
                                        <span class="text-muted-sm">No roles yet</span>
                                    @endforelse
                                </div>

                                <div class="card border-0 bg-light p-3">
                                    <label class="form-label fw-semibold mb-2">
                                        Add New Role
                                    </label>

                                    <form method="POST"
                                          action="{{ route('ums.divisions.roles.store', $division->division_id) }}"
                                          class="d-flex gap-2">
                                        @csrf
                                        <input type="text"
                                               name="role_name"
                                               class="form-control"
                                               placeholder="e.g. Finance Approval"
                                               required>

                                        <button class="btn btn-outline-primary">
                                            + Add Role
                                        </button>
                                    </form>
                                </div>

                            </div>

                            <div class="modal-footer bg-light">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button class="btn btn-primary">
                                    Save Changes
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            {{-- =============== END EDIT MODAL =============== --}}

        @endforeach
        </tbody>
    </table>

</div>

{{-- ================= ADD DIVISION MODAL ================= --}}
<div class="modal fade" id="addDivisionModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('ums.divisions.store') }}">
            @csrf
            <div class="modal-content">

                <div class="modal-header bg-light">
                    <h5 class="modal-title">Tambah Division</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Division Name</label>
                            <input type="text" name="division_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control">
                        </div>
                    </div>

                    <hr>

                    <label class="form-label fw-semibold">Roles List</label>

                    <div id="add-roles-wrapper">
                        <div class="d-flex gap-2 mb-2">
                            <input type="text" name="roles[]" class="form-control"
                                   placeholder="e.g. Finance Approval">
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="addRoleInput()">
                                +
                            </button>
                        </div>
                    </div>

                    <small class="text-muted-sm">
                        Total Roles akan dihitung otomatis setelah disimpan.
                    </small>
                </div>

                <div class="modal-footer bg-light">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save Division</button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function addRoleInput() {
    const wrapper = document.getElementById('add-roles-wrapper');

    const div = document.createElement('div');
    div.classList.add('d-flex','gap-2','mb-2');

    div.innerHTML = `
        <input type="text" name="roles[]" class="form-control" placeholder="e.g. Additional Role">
        <button type="button" class="btn btn-outline-danger" onclick="this.parentElement.remove()">×</button>
    `;

    wrapper.appendChild(div);
}
</script>
@endpush

@endsection
