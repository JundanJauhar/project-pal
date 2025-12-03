<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UMS\UsersController;
use App\Http\Controllers\UMS\SettingsController;
use App\Http\Controllers\UMS\AuditLogController;
use App\Http\Controllers\UMS\AdminScopeController;
use App\Http\Controllers\UMS\ActivityLogController;

Route::prefix('ums')
    ->middleware(['auth'])
    ->as('ums.') // prefix name "ums."
    ->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DEFAULT UMS DASHBOARD REDIRECT
    |--------------------------------------------------------------------------
    */
    Route::get('/', function () {
        return redirect()->route('ums.users.index');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | USERS
    |--------------------------------------------------------------------------
    */
    Route::resource('users', UsersController::class)
        ->names([
            'index'   => 'users.index',
            'create'  => 'users.create',
            'store'   => 'users.store',
            'edit'    => 'users.edit',
            'update'  => 'users.update',
            'destroy' => 'users.destroy',
        ]);

    // ⭐ FORCE LOGOUT
    Route::post('/users/{id}/force-logout', [UsersController::class, 'forceLogout'])
        ->name('users.forceLogout');

        

    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */
    Route::resource('settings', SettingsController::class)
        ->except(['show'])
        ->names([
            'index'   => 'settings.index',
            'create'  => 'settings.create',
            'store'   => 'settings.store',
            'edit'    => 'settings.edit',
            'update'  => 'settings.update',
            'destroy' => 'settings.destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | ADMIN SCOPES
    |--------------------------------------------------------------------------
    */
    Route::resource('admin-scopes', AdminScopeController::class)
        ->except(['show'])
        ->names([
            'index'   => 'admin_scopes.index',
            'create'  => 'admin_scopes.create',
            'store'   => 'admin_scopes.store',
            'edit'    => 'admin_scopes.edit',
            'update'  => 'admin_scopes.update',
            'destroy' => 'admin_scopes.destroy',
        ]);


    /*
    |--------------------------------------------------------------------------
    | AUDIT LOGS (Login-related logs only)
    |--------------------------------------------------------------------------
    */
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->name('audit_logs.index');

    Route::get('audit-logs/{id}', [AuditLogController::class, 'show'])
        ->name('audit_logs.show');


    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOGS (CRUD/Actions tracking)
    |--------------------------------------------------------------------------
    */
    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity_logs.index');

    Route::get('activity-logs/{id}', [ActivityLogController::class, 'show'])
        ->name('activity_logs.show');
});
