<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\EvatekItem;
use App\Models\EvatekRevision;
use App\Helpers\ActivityLogger;

class DesainListProjectController extends Controller
{
    public function list()
    {
        $projects = Project::all();

        ActivityLogger::log(
            module: 'Desain',
            action: 'view_list_project',
            targetId: null,
            details: ['user_id' => Auth::id()]
        );

        return view('desain.list-project', compact('projects'));
    }
}
