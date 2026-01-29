@extends('layouts.app')

@section('content')
<div class="container" style="max-width:420px;margin-top:80px;">
    <h4>Lupa Password</h4>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   required>

            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button class="btn btn-primary w-100">
            Kirim Link Reset Password
        </button>
    </form>
</div>
@endsection
