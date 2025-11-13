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
}
