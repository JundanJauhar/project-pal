@extends('ums.layouts.app')

@section('title', 'Project Management')

@section('content')

<style>
.project-wrapper {
    padding: 26px 30px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    margin-top: 20px;
}

.project-title {
    font-size: 22px;
    font-weight: 800;
}

.project-subtitle {
    font-size: 13px;
    color: #666;
    margin-bottom: 18px;
}

.project-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    gap: 12px;
}

.project-table {
    width: 100%;
    border-collapse: collapse;
}

.project-table th {
    font-size: 13px;
    font-weight: 700;
    padding: 12px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}

.project-table td {
    font-size: 13px;
    padding: 14px 10px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
    text-align: center;
}

.count-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    background: #0dcaf0;
    color: #fff;
    font-weight: 700;
}

.text-muted-sm {
    font-size: 12px;
    color: #666;
}

.view-btn {
    background: #1e88e5;
    border: none;
    color: #fff;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    margin-right: 6px;
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

<div class="project-wrapper">

    {{-- Title --}}
    <div class="project-title">Project Management</div>
    <div class="project-subtitle">
        Manage projects and related procurements
    </div>

    {{-- Toolbar --}}
    <div class="project-toolbar">
        <form method="GET" style="max-width:240px;">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   class="form-control form-control-sm"
                   placeholder="Search project...">
        </form>

        <button class="btn btn-sm btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#addProjectModal">
            + Add Project
        </button>
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
    <table class="project-table">
        <thead>
            <tr>
                <th style="width:60px;">#</th>
                <th>Kode Project</th>
                <th>Nama Project</th>
                <th>Deskripsi Project</th> {{-- ✅ NEW --}}
                <th style="width:160px;">Jumlah Procurement</th>
                <th style="width:160px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($projects as $i => $project)
            <tr>
                <td class="text-muted-sm">
                    {{ $i + 1 }}
                </td>

                <td style="font-weight:700;">
                    {{ $project->project_code }}
                </td>

                <td>
                    {{ $project->project_name }}
                </td>

                {{-- ✅ DESCRIPTION COLUMN --}}
                <td class="text-muted-sm" style="max-width:320px;">
                    {{ \Illuminate\Support\Str::limit($project->description, 80) ?? '-' }}
                </td>

                <td>
                    <span class="count-badge">
                        {{ $project->procurements_count }}
                    </span>
                </td>

                <td>
                    {{-- View --}}
                    <a href="{{ route('ums.project.procurements', $project->project_id) }}"
                       class="view-btn"
                       title="Lihat Procurement">
                        <i class="bi bi-eye"></i>
                    </a>

                    {{-- Delete --}}
                    <form action="{{ route('ums.project.destroy', $project->project_id) }}"
                          method="POST"
                          class="d-inline"
                          onsubmit="return confirm('Yakin hapus project ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="delete-btn" title="Hapus Project">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-muted-sm py-4">
                    Tidak ada data project.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>

</div>

{{-- ================= ADD PROJECT MODAL ================= --}}
<div class="modal fade" id="addProjectModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ route('ums.project.store') }}">
            @csrf
            <div class="modal-content">

                <div class="modal-header bg-light">
                    <h5 class="modal-title">Tambah Project</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kode Project</label>
                            <input type="text" name="project_code" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Project</label>
                            <input type="text" name="project_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button class="btn btn-primary">
                        Save Project
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
