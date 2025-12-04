<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevent lazy loading in production to catch N+1 queries
        Model::preventLazyLoading(! app()->isProduction());

        // Use Bootstrap pagination
        Paginator::useBootstrap();
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');

        /*
        |--------------------------------------------------------------------------
        | LOGIN EVENT LISTENER (FINAL)
        |--------------------------------------------------------------------------
        | Tidak menggunakan EventServiceProvider karena project kamu tidak
        | punya provider tersebut. Jadi listener ditaruh langsung di sini.
        |--------------------------------------------------------------------------
        */

        // 🔵 Log Login Success
        Event::listen(Login::class, function ($event) {
            \App\Helpers\AuditLogger::log(
                action: 'login',
                table: 'users',
                targetId: $event->user->user_id ?? null,
                details: [
                    'email'  => $event->user->email ?? null,
                    'status' => 'success',
                ]
            );
        });

        // 🔴 Log Login Failed
        Event::listen(Failed::class, function ($event) {
            \App\Helpers\AuditLogger::log(
                action: 'login_failed',
                table: 'users',
                targetId: null,
                details: [
                    'email'  => $event->credentials['email'] ?? null,
                    'status' => 'failed',
                ]
            );
        });
    }
}
