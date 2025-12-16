@extends('ums.layouts.app')

@section('title', 'System Settings')

@section('content')

<style>
/* ================= LAYOUT ================= */
.settings-container {
    max-width: 1100px;
    margin-top: 24px;
}

/* ================= HEADER ================= */
.settings-header h3 {
    font-weight: 800;
    letter-spacing: -0.4px;
}

.settings-header p {
    color: #6b7280;
    max-width: 720px;
    font-size: 14px;
}

/* ================= TABS ================= */
.settings-tabs {
    display: flex;
    gap: 18px;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 28px;
}

.settings-tab {
    padding: 12px 4px;
    font-size: 14px;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    border-bottom: 2px solid transparent;
}

.settings-tab.active {
    color: #111827;
    border-bottom-color: #111827;
}

/* ================= PANEL ================= */
.settings-panel {
    display: none;
}

.settings-panel.active {
    display: block;
}

/* ================= CARD ================= */
.settings-card {
    background: #ffffff;
    border-radius: 14px;
    padding: 28px 30px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
    margin-bottom: 28px;
}

.settings-card h6 {
    font-weight: 800;
    margin-bottom: 6px;
}

.settings-desc {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 20px;
}

/* ================= FORM ================= */
label {
    font-weight: 600;
    font-size: 13px;
}

small {
    font-size: 12px;
    color: #6b7280;
}

.readonly {
    background: #f3f4f6;
    cursor: not-allowed;
}

/* ================= DANGER ================= */
.danger-card {
    border: 1px solid #f3caca;
    background: #fff7f7;
}

.danger-card h6 {
    color: #b91c1c;
}

.danger-card .settings-desc {
    color: #b91c1c;
}

/* ================= ACTION ================= */
.settings-action {
    position: sticky;
    bottom: 0;
    background: #f9fafb;
    padding: 16px 0;
}
</style>

<div class="settings-container">

    {{-- HEADER --}}
    <div class="settings-header mb-4">
        <h3 class="mb-1">System Settings</h3>
        <p>
            Kelola konfigurasi global sistem, kebijakan keamanan, sesi pengguna,
            pencatatan log, dan status operasional User Management System.
        </p>
    </div>

    {{-- TABS --}}
    <div class="settings-tabs">
        <div class="settings-tab active" data-tab="general">General & Identity</div>
        <div class="settings-tab" data-tab="security">Security Policy</div>
        <div class="settings-tab" data-tab="session">Session Control</div>
        <div class="settings-tab" data-tab="logging">Logging & Compliance</div>
        <div class="settings-tab text-danger" data-tab="maintenance">System Maintenance</div>
    </div>

    <form method="POST" action="{{ route('ums.settings.update') }}">
        @csrf

        {{-- ================= GENERAL ================= --}}
        <div class="settings-panel active" id="general">
            <div class="settings-card">
                <h6>General & Identity</h6>
                <div class="settings-desc">
                    Informasi dasar dan identitas sistem.
                </div>

                <div class="mb-3">
                    <label>System Name</label>
                    <input type="text" name="system_name" class="form-control"
                           value="{{ $settings['system_name'] ?? 'User Management System' }}">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label>Environment</label>
                        <input type="text" class="form-control readonly"
                               value="Production" disabled>
                    </div>
                    <div class="col-md-6">
                        <label>System Version</label>
                        <input type="text" class="form-control readonly"
                               value="v1.0.0" disabled>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= SECURITY ================= --}}
        <div class="settings-panel" id="security">
            <div class="settings-card">
                <h6>Security Policy</h6>
                <div class="settings-desc">
                    Kebijakan keamanan untuk melindungi akun pengguna.
                </div>

                <div class="mb-3">
                    <label>Maximum Login Attempts</label>
                    <input type="number" min="1" name="max_login_attempts"
                           class="form-control"
                           value="{{ $settings['max_login_attempts'] ?? 5 }}">
                    <small>Akun diblokir sementara jika melewati batas login.</small>
                </div>

                <label>Password Policy</label>
                <input type="text" class="form-control readonly"
                       value="Minimum 8 karakter (default)" disabled>
            </div>
        </div>

        {{-- ================= SESSION ================= --}}
        <div class="settings-panel" id="session">
            <div class="settings-card">
                <h6>Session Control</h6>
                <div class="settings-desc">
                    Pengaturan durasi dan perilaku sesi login.
                </div>

                <label>Session Timeout (minutes)</label>
                <input type="number" min="5" name="session_timeout"
                       class="form-control"
                       value="{{ $settings['session_timeout'] ?? 120 }}">
            </div>
        </div>

        {{-- ================= LOGGING ================= --}}
        <div class="settings-panel" id="logging">
            <div class="settings-card">
                <h6>Logging & Compliance</h6>
                <div class="settings-desc">
                    Retensi log untuk audit dan kepatuhan sistem.
                </div>

                <label>Log Retention (days)</label>
                <input type="number" min="30" name="log_retention_days"
                       class="form-control"
                       value="{{ $settings['log_retention_days'] ?? 365 }}">
            </div>
        </div>

        {{-- ================= MAINTENANCE ================= --}}
        <div class="settings-panel" id="maintenance">
            <div class="settings-card danger-card">
                <h6>System Maintenance</h6>
                <div class="settings-desc">
                    Pengaturan kritikal operasional sistem.
                </div>

                <div class="mb-3">
                    <label>Maintenance Mode</label>
                    <select name="maintenance_mode" class="form-select">
                        <option value="off" {{ ($settings['maintenance_mode'] ?? 'off') === 'off' ? 'selected' : '' }}>
                            OFF — Sistem Aktif
                        </option>
                        <option value="on" {{ ($settings['maintenance_mode'] ?? 'off') === 'on' ? 'selected' : '' }}>
                            ON — Sistem Dalam Perawatan
                        </option>
                    </select>
                </div>

                <label>Maintenance Message</label>
                <textarea name="maintenance_message" rows="3"
                          class="form-control">{{ $settings['maintenance_message'] ?? '' }}</textarea>
            </div>
        </div>

        {{-- ACTION --}}
        <div class="settings-action">
            <button class="btn btn-dark btn-lg w-100">
                Simpan Settings
            </button>
        </div>

    </form>
</div>

<script>
document.querySelectorAll('.settings-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));

        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});
</script>

@endsection
