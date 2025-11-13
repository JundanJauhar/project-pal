<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SupplyChainController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\QualityAssuranceController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DesainController;
use App\Models\Project;
use App\Http\Controllers\DesainListProjectController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
})->middleware('guest');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// Authentication routes (will be added by Laravel Breeze/Jetstream)
Route::middleware(['auth'])->group(function () {

    // Dashboard Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/division/{divisionId}', [DashboardController::class, 'divisionDashboard'])->name('dashboard.division');
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/dashboard/timeline/{projectId}', [DashboardController::class, 'getProcurementTimeline'])->name('dashboard.timeline');

    // Project Routes
    Route::get('/projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');

    // User-specific project list (used by 'user' role)
    Route::get('/user/list', function () {
        $projects = Project::with(['ownerDivision', 'contracts'])->orderBy('created_at', 'desc')->paginate(10);
        return view('user.list', compact('projects'));
    })->name('user.list');

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // Supply Chain Routes
    Route::prefix('supply-chain')->name('supply-chain.')->group(function () {
        Route::get('/dashboard', [SupplyChainController::class, 'dashboard'])->name('dashboard');
        Route::get('/projects/{projectId}/review', [SupplyChainController::class, 'reviewProject'])->name('review-project');
        Route::post('/projects/{projectId}/approve', [SupplyChainController::class, 'approveReview'])->name('approve-review');

        Route::get('/material-requests', [SupplyChainController::class, 'materialRequests'])->name('material-requests');
        Route::post('/material-requests/{requestId}', [SupplyChainController::class, 'updateMaterialRequest'])->name('update-material-request');

        Route::get('/vendors', [SupplyChainController::class, 'vendors'])->name('vendors');
        Route::post('/projects/{projectId}/select-vendor', [SupplyChainController::class, 'selectVendor'])->name('select-vendor');

        Route::get('/negotiations', [SupplyChainController::class, 'negotiations'])->name('negotiations');
        Route::post('/projects/{projectId}/negotiation', [SupplyChainController::class, 'createNegotiation'])->name('create-negotiation');
        Route::post('/projects/{projectId}/request-hps-update', [SupplyChainController::class, 'requestHpsUpdate'])->name('request-hps-update');

        Route::get('/material-shipping', [SupplyChainController::class, 'materialShipping'])->name('material-shipping');
        Route::post('/projects/{projectId}/material-arrival', [SupplyChainController::class, 'updateMaterialArrival'])->name('material-arrival');


        Route::get('/vendor/kelola', [SupplyChainController::class, 'kelolaVendor'])->name('vendor.kelola');
        Route::get('/vendor/create', [SupplyChainController::class, 'createVendor'])->name('vendor.create');
        Route::post('/vendor/store', [SupplyChainController::class, 'storeVendor'])->name('vendor.store');
        Route::get('/vendor/pilih', [SupplyChainController::class, 'pilihVendor'])->name('vendor.pilih');
        Route::get('/vendor/detail', [SupplyChainController::class, 'detailVendor'])->name('vendor.detail');



    });


    // Payment Routes (Treasury & Accounting)
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/create/{projectId}', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        Route::get('/statistics', [PaymentController::class, 'statistics'])->name('statistics');

        // Accounting verification
        Route::post('/{id}/accounting-verification', [PaymentController::class, 'accountingVerification'])->name('accounting-verification');

        // Treasury verification
        Route::post('/{id}/treasury-verification', [PaymentController::class, 'treasuryVerification'])->name('treasury-verification');
        Route::post('/projects/{projectId}/open-lc-tt', [PaymentController::class, 'openLcTt'])->name('open-lc-tt');
        Route::post('/projects/{projectId}/open-sekbun', [PaymentController::class, 'openSekbun'])->name('open-sekbun');
    });

    // Inspection Routes (Quality Assurance)
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionController::class, 'index'])->name('index');
        Route::get('/create/{projectId}', [InspectionController::class, 'create'])->name('create');
        Route::post('/', [InspectionController::class, 'store'])->name('store');
        Route::get('/{id}', [InspectionController::class, 'show'])->name('show');
        Route::put('/{id}', [InspectionController::class, 'update'])->name('update');

        // NCR Routes
        Route::get('/ncr', [InspectionController::class, 'ncrReports'])->name('ncr.index');
        Route::get('/ncr/{id}', [InspectionController::class, 'showNcr'])->name('ncr.show');
        Route::put('/ncr/{id}', [InspectionController::class, 'updateNcr'])->name('ncr.update');
        Route::post('/ncr/{id}/verify', [InspectionController::class, 'verifyNcr'])->name('ncr.verify');
    });

    // Desain Routes
    Route::prefix('desain')->name('desain.')->group(function () {
        Route::get('/dashboard', [DesainController::class, 'dashboard'])->name('dashboard');
        Route::get('/input-equipment', [DesainController::class, 'inputEquipment'])->name('input-equipment');
        Route::get('/status-evatek/{projectId}', [DesainController::class, 'statusEvatek'])->name('status-evatek');
    });


Route::get('/desain/list-project', [DesainListProjectController::class, 'list'])
     ->name('desain.list-project');

});
