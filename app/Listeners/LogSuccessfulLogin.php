<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Helpers\AuditLogger;

class LogSuccessfulLogin
{
    /**
     * Handle the event.
     */
    public function handle(Login $event)
    {
        // Pastikan struktur user Anda memakai user_id
        $userId = $event->user->user_id ?? $event->user->id;

        AuditLogger::log(
            action: 'login',
            table: 'users',
            targetId: $userId,
            details: [
                'email' => $event->user->email,
                'ip'    => request()->ip(),
                'ua'    => request()->userAgent(),
            ]
        );
    }
}
