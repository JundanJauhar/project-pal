@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')

<h3 class="fw-bold mb-4">Tambah User</h3>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <form action="{{ route('ums.users.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama</label>
                <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select class="form-select" name="roles" required>
                    <option value="user">User</option>
                    <option value="qa">QA</option>
                    <option value="desain">Desain</option>
                    <option value="supply_chain">Supply Chain</option>
                    <option value="treasury">Treasury</option>
                    <option value="accounting">Accounting</option>
                    <option value="sekretaris_direksi">Sekretaris Direksi</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button class="btn btn-dark">Simpan</button>
            <a href="{{ route('ums.users.index') }}" class="btn btn-secondary">Kembali</a>

        </form>

    </div>
</div>

@endsection
