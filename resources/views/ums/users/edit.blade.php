@extends('ums.layouts.app')

@section('title', 'Edit User')

@section('content')

{{-- PAGE HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Edit User</h3>
        <p class="text-muted mb-0">
            Perbarui informasi akun dan hak akses pengguna
        </p>
    </div>
</div>

<form action="{{ route('ums.users.update', $user->user_id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">

            {{-- USER IDENTITY --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Informasi Pengguna</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $user->name) }}"
                               required>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $user->email) }}"
                               required>
                    </div>
                </div>
            </div>

            {{-- ORGANIZATION STRUCTURE --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Struktur Organisasi</h6>

                    {{-- DIVISION --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Divisi</label>
                        <select name="division_id" class="form-select">
                            <option value="">— Tidak ada divisi —</option>
                            @foreach($divisions as $d)
                                <option value="{{ $d->division_id }}"
                                    @selected($user->division_id == $d->division_id)>
                                    {{ $d->division_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DEPARTMENT --}}
                    <div>
                        <label class="form-label fw-semibold">Department</label>
                        <input type="text"
                               name="department"
                               class="form-control"
                               placeholder="Contoh: Accounting, Procurement"
                               value="{{ old('department', $user->department) }}">
                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">

            {{-- ACCESS CONTROL --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Akses Sistem</h6>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="roles" class="form-select" required>
                            @foreach($roles as $r)
                                <option value="{{ $r }}"
                                    @selected($user->roles === $r)>
                                    {{ ucfirst(str_replace('_',' ', $r)) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Perubahan role akan memengaruhi hak akses pengguna
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-dark">
                            Update User
                        </button>
                        <a href="{{ route('ums.users.index') }}"
                           class="btn btn-outline-secondary">
                            Kembali
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>
</form>

@endsection
