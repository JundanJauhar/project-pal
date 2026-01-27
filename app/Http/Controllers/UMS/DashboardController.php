<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Division;
use App\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsers     = User::count();
        $totalDivisions = Division::count();
        $totalRoles     = Role::count();

        $activeUsers = User::where('status', 'active')->count();

        return view('ums.dashboard.index', compact(
            'totalUsers',
            'totalDivisions',
            'totalRoles',
            'activeUsers'
        ));
    }
}
