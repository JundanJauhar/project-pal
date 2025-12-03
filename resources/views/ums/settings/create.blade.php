@extends('ums.layouts.app')

@section('title', 'Tambah Setting')

@section('content')
<h3 class="fw-bold mb-4">Tambah Setting</h3>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <form action="{{ route('ums.settings.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-semibold">Key</label>
                <input type="text" name="key" class="form-control" required value="{{ old('key') }}" autofocus placeholder="contoh: max_login_attempts">
                @error('key')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Value</label>
                <textarea name="value" class="form-control" rows="3">{{ old('value') }}</textarea>
                @error('value')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                @error('description')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-dark">Simpan</button>
            <a href="{{ route('ums.settings.index') }}" class="btn btn-secondary">Kembali</a>

        </form>

    </div>
</div>
@endsection
