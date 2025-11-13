<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class DesainListProjectController extends Controller
{
    public function list()
    {
        $projects = Project::all();

        return view('desain.list-project', compact('projects'));
    }

    public function daftarPermintaan($id)
    {
        $project = Project::with('requests')->findOrFail($id);

        return view('desain.daftar-permintaan', compact('project'));
    }
}
