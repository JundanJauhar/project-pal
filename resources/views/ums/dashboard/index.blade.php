@extends('ums.layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="page-card mb-4">
    <h4 class="mb-3">UMS Dashboard</h4>
    <p class="text-muted">Ringkasan sistem User Management System</p>
</div>

<div class="row g-4">

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Users</h6>
                        <h3>{{ $totalUsers }}</h3>
                    </div>
                    <i class="bi bi-people fs-1 text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Divisions</h6>
                        <h3>{{ $totalDivisions }}</h3>
                    </div>
                    <i class="bi bi-diagram-3 fs-1 text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Total Roles</h6>
                        <h3>{{ $totalRoles }}</h3>
                    </div>
                    <i class="bi bi-shield-lock fs-1 text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6>Active Users</h6>
                        <h3>{{ $activeUsers }}</h3>
                    </div>
                    <i class="bi bi-person-check fs-1 text-info"></i>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
