<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UMS\AuditLog;
use App\Helpers\AuditLogger;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // 🔥 CATAT AKSI: user membuka halaman audit logs
        AuditLogger::log(
            action: 'view_audit_logs',
            table: 'audit_logs',
            targetId: null,
            details: [
                'viewer' => Auth::id(),
                'filters' => $request->all(),
            ]
        );

        // Query dasar
        $query = AuditLog::with('actor')->orderBy('id', 'desc');

        // Filter tindakan
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter target table
        if ($request->filled('table')) {
            $query->where('target_table', $request->table);
        }

        // Filter oleh user
        if ($request->filled('actor')) {
            $query->where('actor_user_id', $request->actor);
        }

        $logs = $query->paginate(20);

        return view('ums.audit_logs.index', compact('logs'));
    }

    public function show($id)
    {
        $log = AuditLog::with('actor')->findOrFail($id);

        // 🔥 CATAT AKSI: user melihat detail log tertentu
        AuditLogger::log(
            action: 'view_audit_log_detail',
            table: 'audit_logs',
            targetId: $id,
            details: [
                'viewer' => Auth::id(),
            ]
        );

        return view('ums.audit_logs.show', compact('log'));
    }
}
