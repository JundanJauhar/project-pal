@extends('layouts.app')

@section('title', 'Edit Setting')

@section('content')
<h3 class="fw-bold mb-4">Edit Setting</h3>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <form action="{{ route('ums.settings.update', $setting->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Key</label>
                <input type="text" readonly class="form-control" value="{{ $setting->key }}">
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Value</label>
                <textarea name="value" class="form-control" rows="4">{{ $setting->value }}</textarea>
            </div>

            <button class="btn btn-dark">Update</button>
            <a href="{{ route('ums.settings.index') }}" class="btn btn-secondary">Kembali</a>

        </form>

    </div>
</div>
@endsection
