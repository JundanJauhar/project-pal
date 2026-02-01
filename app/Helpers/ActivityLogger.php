<?php

namespace App\Helpers;

use App\Models\UMS\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($module, $action, $targetId = null, $details = [])
    {
        $payload = is_array($details) ? $details : [];

        if (!isset($payload['ip']) && request()) {
            $payload['ip'] = request()->ip();
        }

        if (!isset($payload['ua']) && request()) {
            $payload['ua'] = request()->userAgent();
        }

        return ActivityLog::create([
            'actor_user_id' => Auth::id(),
            'module' => $module,
            'action' => $action,
            'target_id' => $targetId,
            'details' => $payload,
        ]);
    }
}
