<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProcurementController;
use App\Http\Controllers\SupplyChainController;
use App\Http\Controllers\TreasuryController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\QualityAssuranceController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DesainController;
use App\Http\Controllers\DesainListProjectController;
use App\Http\Controllers\ListApprovalController;
use App\Models\Project;

// ============= PUBLIC ROUTES =============
Route::get('/', function () {
    return redirect()->route('login');
});

// ============= AUTH ROUTES =============
Route::get('/login', function () {
    return view('auth.login');
})->name('login')->middleware('guest');

Route::post('/login', function (Request $request) {

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

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// ============= PROTECTED ROUTES =============
Route::middleware(['auth'])->group(function () {

    // ------ Dashboard ------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/division/{divisionId}', [DashboardController::class, 'divisionDashboard'])->name('dashboard.division');
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/dashboard/timeline/{projectId}', [DashboardController::class, 'getProcurementTimeline'])->name('dashboard.timeline');
    Route::get('/procurements/search', [DashboardController::class, 'search'])->name('procurements.search');
    Route::get('/dashboard/search', [DashboardController::class, 'search'])->name('dashboard.search');
    // Project Routes
    Route::get('/projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::post('/projects/upload-review', [ProjectController::class, 'uploadReview'])->name('projects.uploadReview');
    Route::post('/projects/save-review-notes', [ProjectController::class, 'saveReviewNotes'])->name('projects.saveReviewNotes');
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');

    // ------ Procurements ------
    Route::get('/procurements/search', [ProcurementController::class, 'search'])->name('procurements.search');
    Route::get('/procurements/by-project/{projectId}', [ProcurementController::class, 'byProject'])->name('procurements.by-project');
    Route::get('/procurements/{id}/progress', [ProcurementController::class, 'getProgress'])->name('procurements.progress');
    Route::post('/procurements/{id}/progress', [ProcurementController::class, 'updateProgress'])->name('procurements.update-progress');
    Route::resource('procurements', ProcurementController::class, ['only' => ['index', 'show', 'create', 'store', 'update']]);

    // ------ User Procurement ------
    Route::get('/user/list', function () {
        $procurements = \App\Models\Procurement::with(['department', 'requestProcurements.vendor'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.list', compact('procurements'));
    })->name('user.list');

    // ------ Notifications ------
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // ------ Supply Chain ------
    Route::prefix('supply-chain')->name('supply-chain.')->group(function () {
        Route::get('/dashboard', [SupplyChainController::class, 'dashboard'])->name('dashboard');
        Route::post('/dashboard/store', [SupplyChainController::class, 'storePengadaan'])->name('dashboard.store');

        Route::get('/projects/{projectId}/review', [SupplyChainController::class, 'reviewProject'])->name('review-project');
        Route::post('/projects/{projectId}/approve', [SupplyChainController::class, 'approveReview'])->name('approve-review');
        Route::post('/projects/upload-review', [SupplyChainController::class, 'uploadReview'])->name('upload-review');

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
        Route::get('/vendor/form', [SupplyChainController::class, 'formVendor'])->name('vendor.form');
        Route::post('/vendor/store', [SupplyChainController::class, 'storeVendor'])->name('vendor.store');
        Route::get('/vendor/pilih', [SupplyChainController::class, 'pilihVendor'])->name('vendor.pilih');
        Route::get('/vendor/detail', [SupplyChainController::class, 'detailVendor'])->name('vendor.detail');
        Route::put('/vendor/update/{id_vendor}', [SupplyChainController::class, 'updateVendor'])->name('vendor.update');
    });

    // ------ Payments ------
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/create/{projectId}', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        Route::get('/statistics', [PaymentController::class, 'statistics'])->name('statistics');

        Route::post('/{id}/accounting-verification', [PaymentController::class, 'accountingVerification'])->name('accounting-verification');
        Route::post('/{id}/treasury-verification', [PaymentController::class, 'treasuryVerification'])->name('treasury-verification');

        Route::post('/projects/{projectId}/open-lc-tt', [PaymentController::class, 'openLcTt'])->name('open-lc-tt');
        Route::post('/projects/{projectId}/open-sekbun', [PaymentController::class, 'openSekbun'])->name('open-sekbun');
    });

    // ------ QA INSPECTIONS ------
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionController::class, 'index'])->name('index');
        Route::get('/ncr', [InspectionController::class, 'ncrReports'])->name('ncr.index');
        Route::get('/ncr/{id}', [InspectionController::class, 'showNcr'])->name('ncr.show');
        Route::put('/ncr/{id}', [InspectionController::class, 'updateNcr'])->name('ncr.update');
        Route::post('/ncr/{id}/verify', [InspectionController::class, 'verifyNcr'])->name('ncr.verify');
    });

    // ------ QA LIST APPROVAL ------
    Route::get('/qa/list-approval', [ListApprovalController::class, 'index'])
        ->name('qa.list-approval');


    // ------ Desain ------
    Route::prefix('desain')->name('desain.')->group(function () {
        Route::get('/dashboard', [DesainController::class, 'dashboard'])->name('dashboard');
        Route::get('/list-project', [DesainListProjectController::class, 'list'])->name('list-project');
        Route::get('/project/{id}/permintaan', [DesainListProjectController::class, 'daftarPermintaan'])->name('daftar-permintaan');
        Route::get('/project/{id}/pengadaan', [DesainListProjectController::class, 'formPengadaan'])->name('permintaan-pengadaan');
        Route::post('/project/{id}/pengadaan/kirim', [DesainListProjectController::class, 'kirimPengadaan'])->name('kirim-pengadaan');
        Route::get('/evatek/{request_id}', [DesainListProjectController::class, 'reviewEvatek'])->name('review-evatek');
    });

    Route::get('/desain/list-project', [DesainListProjectController::class, 'list'])->name('desain.list-project');
    Route::get('/desain/project/{id}/permintaan', [DesainListProjectController::class, 'daftarPengadaan'])->name('desain.daftar-pengadaan');
    Route::get('/desain/evatek/{request_id}', [DesainListProjectController::class, 'reviewEvatek'])->name('desain.review-evatek');

});


    // Desain Routes
    Route::prefix('desain')->name('desain.')->group(function () {
    Route::get('/dashboard', [DesainController::class, 'dashboard'])->name('dashboard');
    Route::get('/list-project', [DesainListProjectController::class, 'list'])->name('list-project');
    Route::get('/project/{id}/permintaan', [DesainListProjectController::class, 'daftarPermintaan'])->name('daftar-permintaan');
    Route::get('/project/{id}/dashboard', [DesainListProjectController::class, 'formPengadaan'])->name('permintaan-pengadaan');
    Route::post('/project/{id}/dasboard/kirim', [DesainListProjectController::class, 'kirimPengadaan'])->name('kirim-pengadaan');
    Route::get('/evatek/{request_id}', [DesainListProjectController::class, 'reviewEvatek'])->name('review-evatek');
    });

    Route::get('/desain/list-project', [DesainListProjectController::class, 'list'])
        ->name('desain.list-project');

    Route::get('/desain/project/{id}/permintaan', [DesainListProjectController::class, 'daftarPengadaan'])
    ->name('desain.daftar-pengadaan');

    Route::get('/desain/evatek/{request_id}', [App\Http\Controllers\DesainListProjectController::class, 'reviewEvatek'])
    ->name('desain.review-evatek');
