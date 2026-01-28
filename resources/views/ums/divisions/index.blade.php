@extends('ums.layouts.app')

@section('title', 'Divisi Management')

@section('content')
<div class="page-card">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Divisi Management</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDivisionModal">
            + Tambah Divisi
        </button>
    </div>

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

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="40">#</th>
                <th>Division</th>
                <th>Description</th>
                <th width="110">Total Roles</th>
                <th>Roles List</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($divisions as $i => $division)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td class="fw-semibold">{{ $division->division_name }}</td>
                <td class="text-muted">{{ $division->description }}</td>
                <td>
                    <span class="badge rounded-pill bg-info">
                        {{ $division->roles_count }}
                    </span>
                </td>
                <td>
                    <div class="d-flex flex-wrap gap-1">
                        @forelse($division->roles as $role)
                            <span class="badge rounded-pill bg-secondary">
                                {{ $role->role_name }}
                            </span>
                        @empty
                            <span class="text-muted small">No roles</span>
                        @endforelse
                    </div>
                </td>
                <td>
                    <button class="btn btn-sm btn-outline-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#editDivisionModal{{ $division->division_id }}">
                        Edit
                    </button>

                    <form action="{{ route('ums.divisions.destroy', $division->division_id) }}"
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Yakin hapus divisi ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>

            {{-- ================= EDIT MODAL ================= --}}
            <div class="modal fade" id="editDivisionModal{{ $division->division_id }}" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">

                        {{-- Update Division Form --}}
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

                                {{-- Division Info --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Division Name</label>
                                        <input type="text" class="form-control" value="{{ $division->division_name }}" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Description</label>
                                        <input type="text" class="form-control" value="{{ $division->description }}" readonly>
                                    </div>
                                </div>
                                <hr>

                                {{-- Roles List --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold mb-2">
                                        Roles in this Division
                                    </label>

                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @forelse($division->roles as $role)
                                            <div class="d-flex align-items-center gap-1">
                                                <span class="badge rounded-pill bg-dark">
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
                                            <span class="text-muted small">No roles yet</span>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- Add Role --}}
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

                                {{-- Multi Roles --}}
                                <label class="form-label fw-semibold">Roles List</label>

                                <div id="add-roles-wrapper">
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="text" name="roles[]" class="form-control" placeholder="e.g. Finance Approval">
                                        <button type="button" class="btn btn-outline-secondary" onclick="addRoleInput()">
                                            +
                                        </button>
                                    </div>
                                </div>

                                <small class="text-muted">
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
