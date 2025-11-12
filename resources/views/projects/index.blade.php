@extends('layouts.app')

@section('title', 'Projects - PT PAL Indonesia')

@section('content')

<style>
    .page-shell {
        min-height: 100%;
        padding: 40px 0 60px;
        background: linear-gradient(180deg, #f4f4f4 0%, #f8f8f8 40%, #ebebeb 100%);
    }
    .projects-wrap {
        background: #EBEBEB;
        padding: 32px 44px;
        border-radius: 20px;
        box-shadow:
            0 22px 45px rgba(0, 0, 0, 0.12),
            0 12px 20px rgba(0, 0, 0, 0.08);
    }
    .projects-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 32px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .projects-title {
        font-weight: 800;
        font-size: 32px;
        margin: 0;
        color: #202020;
    }
    .projects-search {
        max-width: 520px;
        margin: 0;
        position: relative;
        width: 100%;
    }
    .projects-search input {
        border-radius: 24px;
        padding: 10px 42px 10px 18px;
        border: 1px solid #d3d3d3;
        width: 100%;
        outline: none;
        background: #f2f2f2;
        box-shadow: inset 0 3px 6px rgba(0,0,0,0.12);
    }
    .projects-search .bi-search {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c6c6c;
    }

    /* Table styles */
    .table-clean { width: 100%; border-collapse: collapse; }
    .table-clean thead th {
        font-weight: 700;
        color: #1f1f1f;
        padding: 18px 12px;
        text-align: center;
        font-size: 17px;
        border-bottom: 2.2px solid #1f1f1f;
    }
    .table-clean thead th:nth-child(1),
    .table-clean tbody td:nth-child(1) { width: 19%; text-align: left; }
    .table-clean thead th:nth-child(2),
    .table-clean tbody td:nth-child(2) { width: 32%; text-align: left; }
    .table-clean thead th:nth-child(3),
    .table-clean tbody td:nth-child(3) { width: 18%; }
    .table-clean thead th:nth-child(4),
    .table-clean tbody td:nth-child(4),
    .table-clean thead th:nth-child(5),
    .table-clean tbody td:nth-child(5),
    .table-clean thead th:nth-child(6),
    .table-clean tbody td:nth-child(6) { width: 10%; }
    .table-clean tbody tr { border-bottom: 1.6px solid #cecece; }
    .table-clean tbody td {
        padding: 24px 12px;
        vertical-align: middle;
        font-size: 16px;
        text-align: center;
        color: #2f2f2f;
    }
    .table-clean tbody td:first-child,
    .table-clean tbody td:nth-child(2) {
        text-align: left;
    }
    .row-divider {
        height: 1px;
        background: #d8d8d8;
    }
</style>

<div class="page-shell">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="projects-wrap">
                <div class="projects-header">
                    <h2 class="projects-title">Daftar Project</h2>
                    <div class="projects-search">
                        <input type="text" id="search-projects" placeholder="Cari project...">
                        <i class="bi bi-search"></i>
                    </div>
                </div>
                <div class="row-divider mb-3"></div>

                <div class="table-responsive">
                    <table class="table-clean" id="projects-table">
                        <thead>
                            <tr>
                                <th>Nama Project</th>
                                <th>Deskripsi Pengadaan</th>
                                <th>Department</th>
                                <th>Tanggal Pengadaan</th>
                                <th>Tanggal Target</th>
                                <th>Tanggal Keluar</th>
                            </tr>
                        </thead>
                        <tbody id="projects-tbody">
                            @forelse($projects as $project)
                            <tr>
                                <td><strong>{{ $project->name_project }}</strong></td>
                                <td>{{ Str::limit($project->description ?? '-', 90) }}</td>
                                <td>{{ $project->ownerDivision->nama_divisi ?? '-' }}</td>
                                <td>{{ optional($project->start_date)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ optional($project->end_date)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ optional($project->exit_date ?? null)->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                                    <p class="text-muted mt-2">Tidak ada data project</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if(method_exists($projects, 'links'))
                <div class="mt-3">
                    <div id="projects-pagination">
                        {{ $projects->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const search = document.getElementById('search-projects');
        const rows = Array.from(document.querySelectorAll('#projects-tbody tr'));
        if (!search) return;
        search.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            rows.forEach(tr => {
                const text = tr.innerText.toLowerCase();
                tr.style.display = text.includes(q) ? '' : 'none';
            });
        });
    });
</script>
@endpush
