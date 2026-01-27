@extends('ums.layouts.app')

@section('title', 'Divisi Management')

@section('content')
<div class="page-card">

    <div class="d-flex justify-content-between mb-3">
        <h4>Divisi Management</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDivisionModal">
            + Tambah Divisi
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <table class="table table-bordered align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Division</th>
                <th>Description</th>
                <th>Total Roles</th>
                <th>Roles List</th>
                <th width="140">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($divisions as $i => $division)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $division->division_name }}</td>
                <td>{{ $division->description }}</td>
                <td>
                    <span class="badge bg-info">
                        {{ $division->roles_count }}
                    </span>
                </td>
                <td>
                    @forelse($division->roles as $role)
                        <span class="badge bg-secondary mb-1">
                            {{ $role->role_name }}
                        </span>
                    @empty
                        <span class="text-muted">-</span>
                    @endforelse
                </td>
                <td>
                    <button class="btn btn-sm btn-warning"
                        data-bs-toggle="modal"
                        data-bs-target="#editDivisionModal{{ $division->division_id }}">
                        Edit
                    </button>

                    <form action="{{ route('ums.divisions.destroy', $division->division_id) }}"
                          method="POST" class="d-inline"
                          onsubmit="return confirm('Yakin hapus divisi ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>

            {{-- EDIT MODAL --}}
            <div class="modal fade" id="editDivisionModal{{ $division->division_id }}">
                <div class="modal-dialog">
                    <form method="POST"
                          action="{{ route('ums.divisions.update', $division->division_id) }}">
                        @csrf
                        @method('PUT')

                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Edit Division</h5>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label>Division Name</label>
                                    <input type="text" name="division_name"
                                           class="form-control"
                                           value="{{ $division->division_name }}" required>
                                </div>

                                <div class="mb-3">
                                    <label>Description</label>
                                    <textarea name="description"
                                              class="form-control">{{ $division->description }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary">Update</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @endforeach
        </tbody>
    </table>
</div>

{{-- ADD MODAL --}}
<div class="modal fade" id="addDivisionModal">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('ums.divisions.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5>Tambah Division</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Division Name</label>
                        <input type="text" name="division_name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
