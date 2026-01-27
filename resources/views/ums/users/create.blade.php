@extends('ums.layouts.app')

@section('title', 'Tambah User')

@section('content')
<style>
.role-multiselect {
    background: #fff;
}

.role-tag {
    background: #e9f2ff;
    color: #0d6efd;
    border: 1px solid #cfe2ff;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.role-tag .remove-tag {
    margin-left: 6px;
    cursor: pointer;
    font-weight: bold;
}
</style>

{{-- PAGE HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Tambah User</h3>
        <p class="text-muted mb-0">
            Buat akun pengguna baru dan atur akses sistemnya
        </p>
    </div>
</div>

<form action="{{ route('ums.users.store') }}" method="POST">
    @csrf

    <div class="row g-4">

        {{-- LEFT COLUMN --}}
        <div class="col-lg-8">

            {{-- USER IDENTITY --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Informasi Pengguna</h6>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text"
                            name="name"
                            class="form-control"
                            placeholder="Contoh: Andi Pratama"
                            value="{{ old('name') }}"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email"
                            name="email"
                            class="form-control"
                            placeholder="user@email.com"
                            value="{{ old('email') }}"
                            required>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password"
                            name="password"
                            class="form-control"
                            placeholder="Minimal 8 karakter"
                            required>
                    </div>
                </div>
            </div>

            {{-- ORGANIZATION STRUCTURE --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Struktur Organisasi</h6>

                    {{-- DIVISION --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Divisi</label>
                        <select name="division_id" id="division_id" class="form-select">
                            <option value="">— Tidak ada divisi —</option>
                            @foreach($divisions as $d)
                                <option value="{{ $d->division_id }}"
                                    @selected(old('division_id') == $d->division_id)>
                                    {{ $d->division_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- DEPARTMENT
                    <div>
                        <label class="form-label fw-semibold">Department</label>
                        <input type="text"
                            name="department"
                            class="form-control"
                            placeholder="Contoh: Procurement, IT Support"
                            value="{{ old('department') }}">
                    </div> --}}
                </div>
            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="col-lg-4">

            {{-- ACCESS CONTROL --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">Akses Sistem</h6>

                    {{-- ROLE MULTISELECT CLEAN UI --}}
                    <div class="mb-5">
                        <label class="form-label fw-semibold">Role</label>

                        <div class="role-multiselect border rounded p-2" id="rolesField" style="min-height: 42px; cursor: pointer;">
                            <div class="d-flex flex-wrap gap-2" id="roles_tags_container">
                                <span class="text-muted" id="roles_placeholder">Klik untuk pilih role</span>
                            </div>
                        </div>

                        <div class="dropdown mt-1">
                            <div class="dropdown-menu w-100 p-2 shadow-sm" id="roles_dropdown_menu" style="max-height: 220px; overflow-y: auto;">
                                <div class="text-muted px-2">Pilih divisi terlebih dahulu</div>
                            </div>
                        </div>

                        {{-- Hidden inputs --}}
                        <div id="roles_hidden_inputs"></div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-dark">
                            Simpan User
                        </button>
                        <a href="{{ route('ums.users.index') }}"
                        class="btn btn-outline-secondary">
                        Kembali
                        </a>
                    </div>

                </div>
            </div>

        </div>

    </div>
</form>
<script>
const rolesField = document.getElementById('rolesField');
const menu = document.getElementById('roles_dropdown_menu');
const tagsContainer = document.getElementById('roles_tags_container');
const hiddenInputs = document.getElementById('roles_hidden_inputs');

rolesField.addEventListener('click', function () {
    menu.classList.toggle('show');
});

document.getElementById('division_id').addEventListener('change', function () {
    const divisionId = this.value;

    menu.innerHTML = '';
    tagsContainer.innerHTML = '<span class="text-muted" id="roles_placeholder">Klik untuk pilih role</span>';
    hiddenInputs.innerHTML = '';

    if (!divisionId) {
        menu.innerHTML = '<div class="text-muted px-2">Pilih divisi terlebih dahulu</div>';
        return;
    }

    const url = "{{ route('ums.divisions.roles', ':id') }}".replace(':id', divisionId);

    fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!Array.isArray(data) || data.length === 0) {
            menu.innerHTML = '<div class="text-muted px-2">Tidak ada role</div>';
            return;
        }

        data.forEach(role => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';

            item.innerHTML = `
                <label class="d-flex align-items-center gap-2 m-0">
                    <input type="checkbox" value="${role.role_id}" data-name="${role.role_name}">
                    <span>${role.role_name}</span>
                </label>
            `;

            menu.appendChild(item);
        });

        bindRoleEvents();
    });
});

function bindRoleEvents() {
    menu.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', function () {
            const roleId = this.value;
            const roleName = this.dataset.name;

            if (this.checked) {
                addRoleTag(roleId, roleName);
            } else {
                removeRoleTag(roleId);
            }

            syncHiddenInputs();
        });
    });
}

function getPlaceholder() {
    return document.getElementById('roles_placeholder');
}

function addRoleTag(roleId, roleName) {
    if (document.getElementById('role-tag-' + roleId)) return;

    // Hapus placeholder jika ada
    const ph = getPlaceholder();
    if (ph) ph.remove();

    const tag = document.createElement('span');
    tag.className = 'role-tag';
    tag.id = 'role-tag-' + roleId;
    tag.innerHTML = `
        ${roleName}
        <span class="remove-tag" data-id="${roleId}">×</span>
    `;

    tagsContainer.appendChild(tag);

    tag.querySelector('.remove-tag').addEventListener('click', function (e) {
        e.stopPropagation();
        const id = this.dataset.id;

        const checkbox = menu.querySelector('input[value="' + id + '"]');
        if (checkbox) checkbox.checked = false;

        removeRoleTag(id);
        syncHiddenInputs();
    });
}

function removeRoleTag(roleId) {
    const tag = document.getElementById('role-tag-' + roleId);
    if (tag) tag.remove();

    // Jika tidak ada role terpilih, tampilkan placeholder
    if (!tagsContainer.querySelector('.role-tag')) {
        tagsContainer.innerHTML = '<span class="text-muted" id="roles_placeholder">Klik untuk pilih role</span>';
    }
}

function syncHiddenInputs() {
    hiddenInputs.innerHTML = '';

    const tags = tagsContainer.querySelectorAll('.role-tag');

    tags.forEach(tag => {
        const id = tag.id.replace('role-tag-', '');

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'roles[]';
        input.value = id;

        hiddenInputs.appendChild(input);
    });
}

// Close dropdown if click outside
document.addEventListener('click', function (e) {
    if (!rolesField.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
});
</script>
@endsection