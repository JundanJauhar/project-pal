<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UMS\AuditLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // Ambil hanya log login dan login_failed
        $query = AuditLog::with('actor')
            ->whereIn('action', ['login', 'login_failed'])
            ->orderBy('id', 'desc');

        // Filter berdasarkan aksi (optional)
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter berdasarkan user (optional)
        if ($request->filled('actor')) {
            $query->where('actor_user_id', $request->actor);
        }

        $logs = $query->paginate(20);

        return view('ums.audit_logs.index', compact('logs'));
    }

    public function show($id)
    {
        // Ambil log dengan relasi user
        $log = AuditLog::with('actor')->findOrFail($id);

        // Tidak mencatat view detail agar tidak memenuhi tabel
        return view('ums.audit_logs.show', compact('log'));
    }
}
