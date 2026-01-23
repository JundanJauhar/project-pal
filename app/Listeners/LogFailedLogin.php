<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use App\Helpers\AuditLogger;

class LogFailedLogin
{
    /**
     * Handle failed login attempt.
     */
    public function handle(Failed $event)
    {
        AuditLogger::log(
            action: 'login_failed',
            table: 'users',
            targetId: null,
            details: [
                'email' => $event->credentials['email'] ?? null,
                'status' => 'failed',
                'ip'    => request()->ip(),
                'ua'    => request()->userAgent(),
            ],
            actorUserId: null  // Failed login = no authenticated user
        );
    }
}
