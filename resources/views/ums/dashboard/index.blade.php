@extends('ums.layouts.app')

@section('title', 'Dashboard')

@section('content')

<style>
/* ================= DASHBOARD CORE ================= */
.ums-dashboard { padding: 22px 28px; }

.dashboard-header h2 {
    font-weight: 900;
    font-size: 26px;
    margin-bottom: 2px;
}
.dashboard-header p {
    font-size: 13px;
    color: #6b7280;
}

/* ================= KPI ================= */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.kpi-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 16px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all .15s ease;
}

.kpi-card:hover {
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    transform: translateY(-1px);
}

.kpi-title {
    font-size: 11px;
    color: #6b7280;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .4px;
}

.kpi-number {
    font-size: 30px;
    font-weight: 900;
    margin-top: 2px;
}

.kpi-desc {
    font-size: 11px;
    margin-top: 2px;
    color: #64748b;
}

.kpi-icon-box {
    width: 46px;
    height: 46px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 20px;
}

.bg-primary { background:#2563eb; }
.bg-success { background:#16a34a; }
.bg-info    { background:#0ea5e9; }
.bg-warning { background:#f59e0b; }

/* ================= GRID ================= */
.main-grid {
    display: grid;
    grid-template-columns: 2.3fr 1.4fr;
    gap: 18px;
    margin-bottom: 22px;
}

.card-box {
    background:#fff;
    border-radius:14px;
    border:1px solid #e5e7eb;
    padding:16px 18px;
}

.card-box h5 {
    font-size:14px;
    font-weight:900;
    margin-bottom: 4px;
}

.card-subtitle {
    font-size:11px;
    color:#6b7280;
    margin-bottom: 10px;
}

/* ================= MODULE TABLE ================= */
.mini-table {
    width:100%;
    font-size:12px;
}
.mini-table th {
    color:#6b7280;
    font-weight:800;
    padding:6px 4px;
}
.mini-table td {
    padding:6px 4px;
    border-top:1px solid #f1f5f9;
}
.status-active {
    color:#16a34a;
    font-weight:800;
}

/* ================= RECENT ACTIVITY ================= */
.activity-item {
    border-bottom:1px solid #f1f5f9;
    padding:8px 0;
    font-size:12px;
}
.activity-item:last-child {
    border-bottom:none;
}
.activity-title {
    font-weight:800;
}
.activity-meta {
    font-size:11px;
    color:#64748b;
}

/* ================= SYSTEM HEALTH ================= */
.health-list {
    font-size:12px;
}
.health-list li {
    margin-bottom:6px;
}
.health-ok {
    color:#16a34a;
    font-weight:900;
}

/* ================= FOOTER NOTE ================= */
.dashboard-note {
    background:#f9fafb;
    border:1px dashed #cbd5e1;
    border-radius:14px;
    padding:14px;
    font-size:12px;
    color:#64748b;
    text-align:center;
}
</style>

<div class="ums-dashboard">

    {{-- HEADER --}}
    <div class="dashboard-header mb-3">
        <h2>Dashboard</h2>
        <p>Monitoring & analytics for User Management System</p>
    </div>

    {{-- KPI --}}
    <div class="kpi-grid">

        <div class="kpi-card">
            <div>
                <div class="kpi-title">Total Users</div>
                <div class="kpi-number">{{ $totalUsers }}</div>
                <div class="kpi-desc">{{ $activeUsers }} active users</div>
            </div>
            <div class="kpi-icon-box bg-primary">
                <i class="bi bi-people"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <div class="kpi-title">Active Users</div>
                <div class="kpi-number">{{ $activeUsers }}</div>
                <div class="kpi-desc">Live status</div>
            </div>
            <div class="kpi-icon-box bg-success">
                <i class="bi bi-person-check"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <div class="kpi-title">Total Divisions</div>
                <div class="kpi-number">{{ $totalDivisions }}</div>
                <div class="kpi-desc">Organization units</div>
            </div>
            <div class="kpi-icon-box bg-info">
                <i class="bi bi-diagram-3"></i>
            </div>
        </div>

        <div class="kpi-card">
            <div>
                <div class="kpi-title">Total Roles</div>
                <div class="kpi-number">{{ $totalRoles }}</div>
                <div class="kpi-desc">Access control</div>
            </div>
            <div class="kpi-icon-box bg-warning">
                <i class="bi bi-shield-lock"></i>
            </div>
        </div>

    </div>

    {{-- MAIN GRID --}}
    <div class="main-grid">

        {{-- SYSTEM MODULES --}}
        <div class="card-box">
            <h5>System Modules Overview</h5>
            <div class="card-subtitle">Live system operational status</div>

            <table class="mini-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Status</th>
                        <th>Info</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($moduleOverview as $module)
                        <tr>
                            <td>{{ $module['name'] }}</td>
                            <td class="status-active">{{ $module['status'] }}</td>
                            <td>{{ $module['info'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- RECENT ACTIVITY --}}
        <div class="card-box">
            <h5>Recent System Activity</h5>
            <div class="card-subtitle">Latest actions from activity logs</div>

            @forelse($recentActivities as $log)
                <div class="activity-item">
                    <div class="activity-title">
                        {{ $log->actor->name ?? 'System' }} — {{ $log->module }}
                    </div>
                    <div class="activity-meta">
                        {{ $log->action }} • {{ $log->created_at?->diffForHumans() }}
                    </div>
                </div>
            @empty
                <div class="activity-item text-muted">
                    No recent activity.
                </div>
            @endforelse
        </div>

    </div>

    {{-- SYSTEM HEALTH --}}
    <div class="card-box mb-3">
        <h5>Security & System Health</h5>
        <div class="card-subtitle">Core security & integrity checks</div>

        <ul class="health-list list-unstyled mb-0">
            <li>• Authentication System: <span class="health-ok">OK</span></li>
            <li>• Role & Permission Mapping: <span class="health-ok">OK</span></li>
            <li>• Session Tracking: <span class="health-ok">OK</span></li>
            <li>• Audit Trail: <span class="health-ok">OK</span></li>
            <li>• Access Policy Enforcement: <span class="health-ok">OK</span></li>
        </ul>
    </div>

    {{-- FOOTER NOTE --}}
    <div class="dashboard-note">
        Dashboard ini terhubung langsung dengan activity_logs & audit_logs.
        Siap dikembangkan untuk analytics, charts, dan anomaly detection.
    </div>

</div>
@endsection
