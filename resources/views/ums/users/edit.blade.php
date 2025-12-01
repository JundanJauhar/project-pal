@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

<h3 class="fw-bold mb-4">Edit User</h3>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <form action="{{ route('ums.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama</label>
                <input type="text" class="form-control" name="name" required
                       value="{{ $user->name }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" class="form-control" name="email" required
                       value="{{ $user->email }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select class="form-select" name="roles" required>
                    @foreach(['user','qa','desain','supply_chain','treasury','accounting','sekretaris_direksi','admin'] as $r)
                        <option value="{{ $r }}" @selected($user->roles === $r)>
                            {{ ucfirst(str_replace('_',' ', $r)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button class="btn btn-dark">Update</button>
            <a href="{{ route('ums.users.index') }}" class="btn btn-secondary">Kembali</a>

        </form>

    </div>
</div>

@endsection
