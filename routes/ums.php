<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UMS\UsersController;
use App\Http\Controllers\UMS\AuditLogController;
use App\Http\Controllers\UMS\ActivityLogController;
use App\Http\Controllers\UMS\SessionMonitoringController;
use App\Http\Controllers\UMS\SystemSettingController;
use App\Http\Controllers\UMS\AdminScopeController;
use App\Http\Controllers\UMS\DivisionManagementController;
use App\Http\Controllers\UMS\DashboardController; 

Route::prefix('ums')
    ->middleware(['auth'])
    ->as('ums.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | HEALTH CHECK (OPTIONAL)
        |--------------------------------------------------------------------------
        */
        Route::get('divisions-test', function () {
            return 'UMS ROUTE FILE ACTIVE';
        })->name('health');

        /*
        |--------------------------------------------------------------------------
        | DEFAULT UMS DASHBOARD (FIXED)
        |--------------------------------------------------------------------------
        */
        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
        */
        Route::resource('users', UsersController::class)
            ->except(['show'])
            ->names([
                'index'   => 'users.index',
                'create'  => 'users.create',
                'store'   => 'users.store',
                'edit'    => 'users.edit',
                'update'  => 'users.update',
                'destroy' => 'users.destroy',
            ]);

        // Toggle active / inactive
        Route::post(
            'users/{user}/toggle-status',
            [UsersController::class, 'toggleStatus']
        )->name('users.toggleStatus');

        /*
        |--------------------------------------------------------------------------
        | AJAX - ROLES BY DIVISION
        |--------------------------------------------------------------------------
        */
        Route::get(
            'divisions/{division}/roles',
            [UsersController::class, 'getRolesByDivision']
        )->name('divisions.roles');

        /*
        |--------------------------------------------------------------------------
        | DIVISION MANAGEMENT
        |--------------------------------------------------------------------------
        */
        Route::prefix('divisions')->name('divisions.')->group(function () {
            Route::get('/', [DivisionManagementController::class, 'index'])->name('index');
            Route::post('/', [DivisionManagementController::class, 'store'])->name('store');
            Route::put('/{id}', [DivisionManagementController::class, 'update'])->name('update');
            Route::delete('/{id}', [DivisionManagementController::class, 'destroy'])->name('destroy');
        });

        /*
        |--------------------------------------------------------------------------
        | SESSION MONITORING
        |--------------------------------------------------------------------------
        */
        Route::get(
            'sessions',
            [SessionMonitoringController::class, 'index']
        )->name('sessions.index');

        Route::post(
            'sessions/{sessionId}/force-logout',
            [SessionMonitoringController::class, 'forceLogoutSession']
        )->name('sessions.forceLogoutSession');

        Route::post(
            'sessions/force-logout-user/{userId}',
            [SessionMonitoringController::class, 'forceLogoutByUser']
        )->name('sessions.forceLogoutUser');

        /*
        |--------------------------------------------------------------------------
        | SYSTEM SETTINGS
        |--------------------------------------------------------------------------
        */
        Route::get(
            'settings',
            [SystemSettingController::class, 'index']
        )->name('settings.index');

        Route::post(
            'settings',
            [SystemSettingController::class, 'update']
        )->name('settings.update');

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
        | AUDIT LOGS
        |--------------------------------------------------------------------------
        */
        Route::get(
            'audit-logs',
            [AuditLogController::class, 'index']
        )->name('audit_logs.index');

        Route::get(
            'audit-logs/{id}',
            [AuditLogController::class, 'show']
        )->name('audit_logs.show');

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOGS
        |--------------------------------------------------------------------------
        */
        Route::get(
            'activity-logs',
            [ActivityLogController::class, 'index']
        )->name('activity_logs.index');

        Route::get(
            'activity-logs/{id}',
            [ActivityLogController::class, 'show']
        )->name('activity_logs.show');

    });
