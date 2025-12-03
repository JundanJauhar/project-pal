<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Procurement;
use App\Models\RequestProcurement;
use Illuminate\Support\Facades\Auth;

class DesainController extends Controller
{
    public function dashboard()
    {
        $projects = Project::where('department_id', Auth::user()->department_id)->get();
        return view('desain.dashboard', compact('projects'));
    }
}
