@extends('ums.layouts.app')

@section('title', 'Admin Scopes')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between mb-4">
        <h3 class="fw-bold">Admin Scopes</h3>
        <a href="{{ route('ums.admin_scopes.create') }}" class="btn btn-dark">
            <i class="bi bi-plus-circle"></i> Tambah Scope
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Keterangan</th>
                        <th>Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($scopes as $s)
                    <tr>
                        <td>{{ $s->name }}</td>
                        <td>{{ $s->description }}</td>
                        <td>{{ $s->created_at->format('d M Y') }}</td>
                        <td class="text-end">

                            <a href="{{ route('ums.admin_scopes.edit', $s->id) }}"
                               class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>

                            <form action="{{ route('ums.admin_scopes.destroy', $s->id) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Hapus scope ini?')"
                                        class="btn btn-sm btn-danger">
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
