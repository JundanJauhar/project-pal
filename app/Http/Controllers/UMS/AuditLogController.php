<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UMS\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('actor')
            ->whereIn('action', ['login', 'login_failed'])
            ->orderByDesc('created_at');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('actor')) {
            $query->where('actor_user_id', $request->actor);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('ums.audit_logs.index', compact('logs'));
    }

    public function show($id)
    {
        $log = AuditLog::with('actor')->findOrFail($id);

        return view('ums.audit_logs.show', compact('log'));
    }
}
