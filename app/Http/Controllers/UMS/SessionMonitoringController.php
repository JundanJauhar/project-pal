<?php

namespace App\Http\Controllers\UMS;

use App\Http\Controllers\Controller;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionMonitoringController extends Controller
{
    /**
     * Display active sessions
     */
    public function index()
    {
        $sessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.user_id')
            ->select(
                'sessions.id',
                'sessions.user_id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'users.name',
                'users.email'
            )
            ->orderByDesc('sessions.last_activity')
            ->paginate(15);

        return view('ums.session_monitoring.index', compact('sessions'));
    }

    /**
     * Force logout SINGLE session
     */
    public function forceLogoutSession(string $sessionId)
    {
        $session = DB::table('sessions')->where('id', $sessionId)->first();

        if (!$session) {
            return back()->with('error', 'Session tidak ditemukan.');
        }

        // ❌ Tidak boleh logout session sendiri
        if ((int)$session->user_id === (int)Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghentikan session Anda sendiri.');
        }

        DB::table('sessions')->where('id', $sessionId)->delete();

        ActivityLogger::log(
            module: 'Session Monitoring',
            action: 'force_logout_session',
            targetId: $session->user_id,
            details: [
                'session_id' => $sessionId,
                'forced_by'  => Auth::id(),
            ]
        );

        return back()->with('success', 'Session berhasil dihentikan.');
    }

    /**
     * Force logout ALL sessions by user (emergency only)
     */
    public function forceLogoutByUser(int $userId)
    {
        if ($userId === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat logout semua session Anda sendiri.');
        }

        DB::table('sessions')->where('user_id', $userId)->delete();

        ActivityLogger::log(
            module: 'Session Monitoring',
            action: 'force_logout_all_sessions',
            targetId: $userId,
            details: [
                'forced_by' => Auth::id(),
            ]
        );

        return back()->with('success', 'Semua session user berhasil dihentikan.');
    }
}
