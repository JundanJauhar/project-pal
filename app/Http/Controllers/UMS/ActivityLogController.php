<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UMS\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('actor')->orderBy('id', 'desc');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('actor')) {
            $query->where('actor_user_id', $request->actor);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(20);

        return view('ums.activity_logs.index', compact('logs'));
    }

    public function show($id)
    {
        $log = ActivityLog::with('actor')->findOrFail($id);
        return view('ums.activity_logs.show', compact('log'));
    }
}
