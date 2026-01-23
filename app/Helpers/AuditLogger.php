<?php

namespace App\Helpers;

use App\Models\UMS\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log a new audit record
     *
     * @param string $action
     * @param string|null $table
     * @param int|string|null $targetId
     * @param array|null $details
     * @param int|null $actorUserId Override actor user ID (null for unauthenticated)
     * @return \App\Models\UMS\AuditLog
     */
    public static function log($action, $table = null, $targetId = null, $details = null, $actorUserId = 'auto')
    {
        // normalize details to array
        $payload = is_array($details) ? $details : ( $details ? (array) $details : [] );

        // add IP and user-agent if not present
        if (!isset($payload['ip']) && function_exists('request')) {
            $payload['ip'] = request()->ip();
        }
        if (!isset($payload['ua']) && function_exists('request')) {
            $payload['ua'] = request()->userAgent();
        }

        // determine actor user ID
        // 'auto' = use Auth::id(), null = explicit null, int = explicit user ID
        $userId = $actorUserId === 'auto' ? Auth::id() : $actorUserId;

        // create audit record
        $log = AuditLog::create([
            'actor_user_id' => $userId,
            'action'        => $action,
            'target_table'  => $table,
            'target_id'     => $targetId,
            'details'       => $payload,
            // do not set created_at here — DB should use CURRENT_TIMESTAMP (useCurrent)
        ]);

        return $log;
    }
}
