<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UMS\UsersController;
use App\Http\Controllers\UMS\SettingsController;
use App\Http\Controllers\UMS\AuditLogController;
use App\Http\Controllers\UMS\AdminScopeController;

Route::prefix('ums')
    ->middleware(['auth'])
    ->as('ums.')              // 👉 PREFIX NAMA ROUTE
    ->group(function () {

    // Dashboard (opsional)
    Route::get('/', function () {
        return redirect()->route('ums.users.index');
    })->name('dashboard');

    // USERS
    Route::resource('users', UsersController::class)
        ->names([
            'index'   => 'users.index',
            'create'  => 'users.create',
            'store'   => 'users.store',
            'edit'    => 'users.edit',
            'update'  => 'users.update',
            'destroy' => 'users.destroy',
        ]);

    // SETTINGS
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

    // ADMIN SCOPES
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

    // AUDIT LOGS
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->name('audit_logs.index');

    Route::get('audit-logs/{id}', [AuditLogController::class, 'show'])
        ->name('audit_logs.show');
});
