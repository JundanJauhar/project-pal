@extends('ums.layouts.app')

@section('title', 'Tambah User')

@section('content')

<!-- Bootstrap Icons -->
<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
/* ===== CLEAN ADMIN STYLE ===== */
.page-subtitle {
    color: #6c757d;
    font-size: 14px;
}

.card-admin {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.card-admin .card-header {
    background: transparent;
    border-bottom: 1px solid #f1f3f5;
    font-weight: 600;
    padding: 16px 20px;
}

.card-admin .card-body {
    padding: 20px;
}

.role-multiselect {
    background: #fff;
}

.role-tag {
    background: #eef4ff;
    color: #0d6efd;
    border: 1px solid #d6e4ff;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
}

.role-tag .remove-tag {
    margin-left: 6px;
    cursor: pointer;
    font-weight: bold;
}

.password-toggle {
    cursor: pointer;
}
</style>

{{-- PAGE HEADER --}}
<div class="mb-4">
    <h3 class="fw-bold mb-1">Tambah User</h3>
    <div class="page-subtitle">
        Buat akun pengguna baru dan atur akses sistemnya
    </div>
</div>

<form action="{{ route('ums.users.store') }}" method="POST">
@csrf

<div class="row g-4">

{{-- ================= LEFT COLUMN ================= --}}
<div class="col-lg-8">

{{-- USER INFO --}}
<div class="card card-admin mb-4">
<div class="card-header">
    Informasi Pengguna
</div>

<div class="card-body">

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

<div class="input-group">
<input type="password"
       name="password"
       id="password"
       class="form-control"
       placeholder="Minimal 8 karakter"
       required>

<button type="button"
        class="btn btn-outline-secondary password-toggle"
        onclick="togglePassword()">
    <i id="togglePasswordIcon" class="bi bi-eye-slash"></i>
</button>
</div>

</div>

</div>
</div>

{{-- ORGANIZATION STRUCTURE --}}
<div class="card card-admin">
<div class="card-header">
    Struktur Organisasi
</div>

<div class="card-body">

<div>
<label class="form-label fw-semibold">Divisi</label>

<select name="division_id"
        id="division_id"
        class="form-select">

<option value="">— Tidak ada divisi —</option>

@foreach($divisions as $d)
<option value="{{ $d->division_id }}"
    @selected(old('division_id') == $d->division_id)>
    {{ $d->division_name }}
</option>
@endforeach

</select>
</div>

</div>
</div>

</div>

{{-- ================= RIGHT COLUMN ================= --}}
<div class="col-lg-4">

<div class="card card-admin">
<div class="card-header">
    Akses Sistem
</div>

<div class="card-body">

<div class="mb-4">
<label class="form-label fw-semibold">Role</label>

<div class="role-multiselect border rounded p-2"
     id="rolesField"
     style="min-height: 42px; cursor:pointer;">

<div class="d-flex flex-wrap gap-2"
     id="roles_tags_container">
<span class="text-muted"
      id="roles_placeholder">
Klik untuk pilih role
</span>
</div>

</div>

<div class="dropdown mt-1">
<div class="dropdown-menu w-100 p-2 shadow-sm"
     id="roles_dropdown_menu"
     style="max-height:220px;overflow-y:auto;">
<div class="text-muted px-2">
Pilih divisi terlebih dahulu
</div>
</div>
</div>

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
/* ===== PASSWORD TOGGLE ===== */
function togglePassword() {
    const password = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');

    if (password.type === 'password') {
        password.type = 'text';
        icon.className = 'bi bi-eye';
    } else {
        password.type = 'password';
        icon.className = 'bi bi-eye-slash';
    }
}

/* ===== ROLE MULTISELECT ===== */
const rolesField = document.getElementById('rolesField');
const menu = document.getElementById('roles_dropdown_menu');
const tagsContainer = document.getElementById('roles_tags_container');
const hiddenInputs = document.getElementById('roles_hidden_inputs');

rolesField.addEventListener('click', () => {
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

    const url = "{{ route('ums.divisions.roles', ':id') }}"
                .replace(':id', divisionId);

    fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.length) {
            menu.innerHTML = '<div class="text-muted px-2">Tidak ada role</div>';
            return;
        }

        data.forEach(role => {
            const item = document.createElement('div');
            item.className = 'dropdown-item';

            item.innerHTML = `
                <label class="d-flex gap-2">
                    <input type="checkbox"
                           value="${role.role_id}"
                           data-name="${role.role_name}">
                    ${role.role_name}
                </label>
            `;

            menu.appendChild(item);
        });

        bindRoleEvents();
    });
});

function bindRoleEvents() {
    menu.querySelectorAll('input').forEach(cb => {
        cb.addEventListener('change', function () {
            this.checked
                ? addRoleTag(this.value, this.dataset.name)
                : removeRoleTag(this.value);

            syncHiddenInputs();
        });
    });
}

function addRoleTag(id, name) {
    if (document.getElementById('role-tag-' + id)) return;

    const ph = document.getElementById('roles_placeholder');
    if (ph) ph.remove();

    const tag = document.createElement('span');
    tag.className = 'role-tag';
    tag.id = 'role-tag-' + id;
    tag.innerHTML = `${name} <span class="remove-tag" data-id="${id}">×</span>`;

    tagsContainer.appendChild(tag);

    tag.querySelector('.remove-tag').onclick = e => {
        e.stopPropagation();
        menu.querySelector(`input[value="${id}"]`).checked = false;
        removeRoleTag(id);
        syncHiddenInputs();
    };
}

function removeRoleTag(id) {
    const tag = document.getElementById('role-tag-' + id);
    if (tag) tag.remove();

    if (!tagsContainer.querySelector('.role-tag')) {
        tagsContainer.innerHTML =
        '<span class="text-muted" id="roles_placeholder">Klik untuk pilih role</span>';
    }
}

function syncHiddenInputs() {
    hiddenInputs.innerHTML = '';
    tagsContainer.querySelectorAll('.role-tag').forEach(tag => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'roles[]';
        input.value = tag.id.replace('role-tag-', '');
        hiddenInputs.appendChild(input);
    });
}

document.addEventListener('click', e => {
    if (!rolesField.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('show');
    }
});
</script>

@endsection
