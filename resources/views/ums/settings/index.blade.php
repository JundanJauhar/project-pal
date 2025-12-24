@extends('ums.layouts.app')

@section('title', 'System Settings')

@section('content')

<style>
/* =========================================================
   ROOT & LAYOUT
========================================================= */
:root {
    --bg-main: #f5f7fb;
    --card-bg: #ffffff;
    --border-soft: #e5e7eb;
    --text-muted: #6b7280;
    --text-dark: #111827;
    --primary-dark: #111827;
    --danger: #b91c1c;
}

body {
    background: var(--bg-main);
}

.settings-wrapper {
    width: 100%;
    padding: 32px 40px 120px;
}

/* =========================================================
   HEADER
========================================================= */
.settings-header h3 {
    font-weight: 800;
    letter-spacing: -0.4px;
}

.settings-header p {
    max-width: 760px;
    color: var(--text-muted);
    font-size: 14px;
    line-height: 1.6;
}

/* =========================================================
   TABS (SCALABLE)
========================================================= */
.settings-tabs {
    display: flex;
    gap: 6px;
    background: #ffffff;
    padding: 6px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    margin-bottom: 28px;
    overflow-x: auto;
}

.settings-tab {
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-muted);
    border-radius: 10px;
    cursor: pointer;
    white-space: nowrap;
    transition: all .2s ease;
}

.settings-tab:hover {
    background: #f3f4f6;
}

.settings-tab.active {
    background: var(--primary-dark);
    color: #ffffff;
}

.settings-tab.text-danger.active {
    background: var(--danger);
}

/* =========================================================
   PANELS
========================================================= */
.settings-panel {
    display: none;
}

.settings-panel.active {
    display: block;
}

/* =========================================================
   CARD
========================================================= */
.settings-card {
    background: var(--card-bg);
    border-radius: 18px;
    padding: 32px;
    box-shadow: 0 10px 28px rgba(0,0,0,0.06);
    margin-bottom: 28px;
}

.settings-card h6 {
    font-weight: 800;
    font-size: 15px;
}

.settings-desc {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 24px;
}

/* =========================================================
   FORM
========================================================= */
label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 6px;
}

small {
    font-size: 12px;
    color: var(--text-muted);
}

.readonly {
    background: #f3f4f6;
    cursor: not-allowed;
}

/* =========================================================
   GRID
========================================================= */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

/* =========================================================
   DANGER
========================================================= */
.danger-card {
    border: 1px solid #f3caca;
    background: #fff7f7;
}

.danger-card h6,
.danger-card .settings-desc {
    color: var(--danger);
}

/* =========================================================
   ACTION BAR
========================================================= */
.settings-action {
    position: fixed;
    left: 0;
    right: 0;
    bottom: 0;
    background: #ffffff;
    border-top: 1px solid var(--border-soft);
    padding: 16px 40px;
    z-index: 20;
}

.settings-action button {
    max-width: 420px;
}

/* =========================================================
   RESPONSIVE
========================================================= */
@media (max-width: 768px) {
    .settings-wrapper {
        padding: 24px 20px 140px;
    }
}
</style>

<div class="settings-wrapper">

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

        {{-- GENERAL --}}
        <div class="settings-panel active" id="general">
            <div class="settings-card">
                <h6>General & Identity</h6>
                <div class="settings-desc">Informasi dasar dan identitas sistem.</div>

                <div class="settings-grid">
                    <div>
                        <label>System Name</label>
                        <input type="text" name="system_name" class="form-control"
                               value="{{ $settings['system_name'] ?? 'User Management System' }}">
                    </div>

                    <div>
                        <label>Environment</label>
                        <input type="text" class="form-control readonly" value="Production" disabled>
                    </div>

                    <div>
                        <label>System Version</label>
                        <input type="text" class="form-control readonly" value="v1.0.0" disabled>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECURITY --}}
        <div class="settings-panel" id="security">
            <div class="settings-card">
                <h6>Security Policy</h6>
                <div class="settings-desc">Kebijakan keamanan akun pengguna.</div>

                <div class="settings-grid">
                    <div>
                        <label>Maximum Login Attempts</label>
                        <input type="number" min="1" name="max_login_attempts"
                               class="form-control"
                               value="{{ $settings['max_login_attempts'] ?? 5 }}">
                        <small>Akun akan diblokir sementara jika gagal login berulang.</small>
                    </div>

                    <div>
                        <label>Password Policy</label>
                        <input type="text" class="form-control readonly"
                               value="Minimum 8 karakter (default)" disabled>
                    </div>
                </div>
            </div>
        </div>

        {{-- SESSION --}}
        <div class="settings-panel" id="session">
            <div class="settings-card">
                <h6>Session Control</h6>
                <div class="settings-desc">Durasi dan perilaku sesi login.</div>

                <label>Session Timeout (minutes)</label>
                <input type="number" min="5" name="session_timeout"
                       class="form-control"
                       value="{{ $settings['session_timeout'] ?? 120 }}">
            </div>
        </div>

        {{-- LOGGING --}}
        <div class="settings-panel" id="logging">
            <div class="settings-card">
                <h6>Logging & Compliance</h6>
                <div class="settings-desc">Retensi log audit dan kepatuhan.</div>

                <label>Log Retention (days)</label>
                <input type="number" min="30" name="log_retention_days"
                       class="form-control"
                       value="{{ $settings['log_retention_days'] ?? 365 }}">
            </div>
        </div>

        {{-- MAINTENANCE --}}
        <div class="settings-panel" id="maintenance">
            <div class="settings-card danger-card">
                <h6>System Maintenance</h6>
                <div class="settings-desc">Pengaturan kritikal operasional sistem.</div>

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
        <div class="settings-action d-flex justify-content-center">
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
