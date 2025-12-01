@extends('ums.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between mb-4">
        <h3 class="fw-bold">Settings</h3>
        <a href="{{ route('ums.settings.create') }}" class="btn btn-dark">
            <i class="bi bi-plus-circle"></i> Tambah Setting
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Key</th>
                        <th>Value</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($settings as $s)
                    <tr>
                        <td>{{ $s->key }}</td>
                        <td>{{ $s->value }}</td>
                        <td>{{ $s->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('ums.settings.edit', $s->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('ums.settings.destroy', $s->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Hapus setting ini?')" class="btn btn-sm btn-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection
