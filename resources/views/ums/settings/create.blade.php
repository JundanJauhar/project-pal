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
                <input type="text" name="key" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Value</label>
                <textarea name="value" class="form-control" rows="4"></textarea>
            </div>

            <button class="btn btn-dark">Simpan</button>
            <a href="{{ route('ums.settings.index') }}" class="btn btn-secondary">Kembali</a>

        </form>

    </div>
</div>
@endsection
