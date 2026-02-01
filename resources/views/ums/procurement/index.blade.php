@extends('ums.layouts.app')

@section('title', 'Procurement Management')

@section('content')

<style>
.procurement-wrapper {
    padding: 26px 30px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    margin-top: 20px;
}

.procurement-title {
    font-size: 22px;
    font-weight: 800;
}

.procurement-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 18px;
}

.procurement-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    gap: 12px;
}

.procurement-search {
    display: flex;
    align-items: center;
    gap: 8px;
}

.procurement-table {
    width: 100%;
    border-collapse: collapse;
}

.procurement-table th {
    font-size: 13px;
    font-weight: 700;
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

.procurement-table td {
    font-size: 13px;
    padding: 14px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    text-align: center;
}

.code-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 12px;
    background: #f2f2f2;
    border: 1px solid #ddd;
    font-weight: 600;
}

.text-muted-sm {
    font-size: 12px;
    color: #666;
}

.delete-btn {
    background: #c62828;
    border: none;
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
}
</style>

<div class="procurement-wrapper">

    {{-- Project Info --}}
    @if(isset($project))
        <div class="mb-2 text-muted-sm">
            Project:
            <strong>{{ $project->project_code }} - {{ $project->project_name }}</strong>
        </div>
    @endif

    {{-- Title --}}
    <div class="procurement-title">Procurement Management</div>
    <div class="procurement-subtitle">
        Manage and monitor all procurement activities
    </div>

    {{-- Toolbar --}}
    <div class="procurement-toolbar">

        {{-- Left: Back Button --}}
        <div>
            <a href="{{ route('ums.project.index') }}"
               class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Projects
            </a>
        </div>

        {{-- Right: Search --}}
        <form method="GET"
              action="{{ isset($project)
                    ? route('ums.project.procurements', $project->project_id)
                    : route('ums.procurement.index') }}"
              class="procurement-search">

            <div class="input-group input-group-sm" style="width: 220px;">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Search procurement...">
            </div>

            <button type="submit" class="btn btn-sm btn-primary">
                Search
            </button>

            @if(request('search'))
                <a href="{{ isset($project)
                            ? route('ums.project.procurements', $project->project_id)
                            : route('ums.procurement.index') }}"
                   class="btn btn-sm btn-outline-secondary">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Table --}}
    <table class="procurement-table">
        <thead>
            <tr>
                <th style="width:60px;">#</th>
                <th>Kode Project</th>
                <th>Kode Procurement</th>
                <th>Nama Procurement</th>
                <th>Deskripsi</th>
                <th style="width:100px;">Aksi</th>
            </tr>
        </thead>

        <tbody>
        @forelse($procurements as $index => $procurement)
            <tr>
                <td class="text-muted-sm">
                    {{ $index + 1 }}
                </td>

                <td style="font-weight:700;">
                    {{ $procurement->project->project_code ?? '-' }}
                </td>

                <td>
                    <span class="code-badge">
                        {{ $procurement->code_procurement }}
                    </span>
                </td>

                <td>
                    {{ $procurement->name_procurement }}
                </td>

                <td class="text-muted-sm" style="max-width:380px;">
                    {{ \Illuminate\Support\Str::limit($procurement->description, 90) ?? '-' }}
                </td>

                <td>
                    <form action="{{ route('ums.procurement.destroy', $procurement->procurement_id) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Yakin ingin menghapus procurement ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="delete-btn"
                                title="Hapus Procurement">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-muted-sm py-4">
                    Tidak ada data procurement.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

</div>
@endsection
