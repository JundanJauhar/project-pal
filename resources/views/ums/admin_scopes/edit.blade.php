@extends('ums.layouts.app')

@section('title', 'Edit Scope')

@section('content')

<h3 class="fw-bold mb-4">Edit Admin Scope</h3>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">

        <form action="{{ route('ums.admin_scopes.update', $scope->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Scope</label>
                <input type="text" name="name" class="form-control" required value="{{ $scope->name }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="description" class="form-control" rows="3">{{ $scope->description }}</textarea>
            </div>

            <button class="btn btn-dark">Update</button>
            <a href="{{ route('ums.admin_scopes.index') }}" class="btn btn-secondary">Kembali</a>

        </form>

    </div>
</div>

@endsection
