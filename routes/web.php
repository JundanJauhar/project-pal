<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\CaptchaController;
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
use App\Http\Controllers\DetailApprovalController;
use App\Http\Controllers\ListApprovalController;
use App\Http\Controllers\SekdirController;
use App\Http\Controllers\EvatekController;
use App\Http\Controllers\VendorEvatekController;
use App\Http\Controllers\InquiryQuotationController;
use App\Http\Controllers\NegotiationController;
use App\Http\Controllers\PengadaanOcController;
use App\Http\Controllers\PengesahanKontrakController;
use App\Http\Controllers\KontrakController;
use App\Http\Controllers\MaterialDeliveryController;
use App\Http\Controllers\CheckpointTransitionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\JaminanPembayaranController;



Route::get('/', function () {
    return redirect()->route('login');
});

// login
Route::get('/login', [LoginController::class, 'show'])
    ->name('login')
    ->middleware('guest');

Route::post('/login', [LoginController::class, 'authenticate'])
    ->middleware(['guest', 'throttle:5,1']);

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');


//Capthca
Route::get('/captcha', [CaptchaController::class, 'generate'])
    ->name('captcha.generate');
/*
|--------------------------------------------------------------------------
| VENDOR ROUTES (Menggunakan guard 'vendor', HARUS DI LUAR middleware 'auth')
|--------------------------------------------------------------------------
*/
Route::middleware('auth:vendor')->group(function () {
    Route::get('/vendor', [VendorEvatekController::class, 'index'])
        ->name('vendor.index');

    Route::get('/vendor/notifications', [VendorEvatekController::class, 'notifications'])
        ->name('vendor.notifications');
        
    Route::post('/vendor/notifications/{id}/read', [VendorEvatekController::class, 'markAsRead'])
        ->name('vendor.notifications.read');

    Route::get('/vendor/profile', [VendorEvatekController::class, 'profile'])
        ->name('vendor.profile');

    Route::put('/vendor/profile', [VendorEvatekController::class, 'updateProfile'])
        ->name('vendor.profile.update');

    // Vendor Evatek Routes
    Route::get('/vendor/evatek/{evatekId}/review', [VendorEvatekController::class, 'reviewEvatek'])
        ->name('vendor.evatek.review');

    Route::post('/vendor/evatek/save-link', [VendorEvatekController::class, 'saveVendorLink'])
        ->name('vendor.evatek.save-link');

    Route::post('/vendor/evatek/save-log', [VendorEvatekController::class, 'saveLog'])
        ->name('vendor.evatek.save-log');

    // Vendor Contract Review Routes
    Route::get('/vendor/contract-review/{contractReviewId}/review', [VendorEvatekController::class, 'reviewContract'])
        ->name('vendor.contract-review.review');

    Route::post('/vendor/contract-review/save-link', [VendorEvatekController::class, 'saveContractLink'])
        ->name('vendor.contract-review.save-link');

    Route::post('/vendor/contract-review/save-log', [VendorEvatekController::class, 'saveContractLog'])
        ->name('vendor.contract-review.save-log');
});

Route::redirect('/vendor/dashboard', '/vendor');
Route::redirect('/vendor/evatek', '/vendor');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (User harus login)
| Middleware 'redirect.if.vendor' memastikan vendor tidak bisa akses route ini
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'redirect.if.vendor'])->group(function () {

    // Dashboard (hanya untuk internal user, bukan vendor)
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/division/{divisionId}', [DashboardController::class, 'divisionDashboard'])->name('dashboard.division');
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    Route::get('/dashboard/timeline/{projectId}', [DashboardController::class, 'getProcurementTimeline'])->name('dashboard.timeline');
    Route::get('/dashboard/search', [DashboardController::class, 'search'])->name('dashboard.search');

    // Projects
    Route::get('/projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::post('/projects/upload-review', [ProjectController::class, 'uploadReview'])->name('projects.uploadReview');
    Route::post('/projects/save-review-notes', [ProjectController::class, 'saveReviewNotes'])->name('projects.saveReviewNotes');
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');

    // Procurements
    Route::get('/procurements/search', [ProcurementController::class, 'search'])->name('procurements.search');
    Route::get('/procurements/by-project/{projectId}', [ProcurementController::class, 'byProject'])->name('procurements.by-project');
    Route::get('/procurements/{id}/progress', [ProcurementController::class, 'getProgress'])->name('procurements.progress');
    Route::post('/procurements/{id}/progress', [ProcurementController::class, 'updateProgress'])->name('procurements.update-progress');
    Route::resource('procurements', ProcurementController::class, ['only' => ['index', 'show', 'create', 'store', 'update']]);

    // User list (user division)
    Route::get('/user/list', function () {
        $procurements = \App\Models\Procurement::with(['department', 'requestProcurements.vendor'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.list', compact('procurements'));
    })->name('user.list');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    /*
    |--------------------------------------------------------------------------
    | SUPPLY CHAIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('supply-chain')->name('supply-chain.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [SupplyChainController::class, 'dashboard'])
            ->name('dashboard');

        Route::post('/dashboard/store', [SupplyChainController::class, 'storePengadaan'])
            ->name('dashboard.store');

        Route::get('/projects/{projectId}/review', [SupplyChainController::class, 'reviewProject'])
            ->name('review-project');

        Route::post('/projects/{projectId}/approve', [SupplyChainController::class, 'approveReview'])
            ->name('approve-review');

        Route::get('/material-requests', [SupplyChainController::class, 'materialRequests'])
            ->name('material-requests');

        Route::post('/material-requests/{requestId}', [SupplyChainController::class, 'updateMaterialRequest'])
            ->name('update-material-request');

        Route::get('/vendors', [SupplyChainController::class, 'vendors'])
            ->name('vendors');

        Route::post('/projects/select-vendor/{procurement_id}', [SupplyChainController::class, 'selectVendor'])
            ->name('select-vendor');

        Route::get('/evatek', [EvatekController::class, 'index'])
            ->name('evatek.index');

        Route::get('/evatek/create', [EvatekController::class, 'create'])
            ->name('evatek.create');

        Route::post('/evatek/store', [EvatekController::class, 'store'])
            ->name('evatek.store');

        // ========== INPUT ITEM ROUTES ==========
        Route::get('/project/{projectId}/input-item', [SupplyChainController::class, 'inputItem'])
            ->name('input-item')
            ->where('projectId', '[0-9]+');

        Route::post('/project/{projectId}/input-item', [SupplyChainController::class, 'storeItem'])
            ->name('input-item.store')
            ->where('projectId', '[0-9]+');

        Route::post('/supply-chain/evatek-item/{procurementId}', [SupplyChainController::class, 'storeEvatekItem'])
            ->name('evatek-item.store')
            ->where('projectId', '[0-9]+');

        Route::get('/get-procurement-items/{procurementId}', [SupplyChainController::class, 'getProcurementItems'])
            ->name('get-procurement-items')
            ->where('procurementId', '[0-9]+');

        // ========== VENDOR ROUTES (Supply Chain) ==========
        Route::get('/vendor/kelola', [SupplyChainController::class, 'kelolaVendor'])
            ->name('vendor.kelola');

        Route::post('/vendor/store', [SupplyChainController::class, 'storeVendor'])
            ->name('vendor.store');

        Route::get('/vendor/form', [SupplyChainController::class, 'formVendor'])
            ->name('vendor.form');

        Route::get('/vendor/detail', [SupplyChainController::class, 'detailVendor'])
            ->name('vendor.detail');

        Route::get('/vendor/pilih/{procurement_id}', [SupplyChainController::class, 'pilihVendor'])
            ->name('vendor.pilih');

        Route::put('/vendor/update/{id_vendor}', [SupplyChainController::class, 'updateVendor'])
            ->name('vendor.update');

        Route::post('/vendor/simpan/{procurementId}', [SupplyChainController::class, 'simpanVendor'])
            ->name('vendor.simpan');

        // ========== CONTRACT REVIEW ROUTES ==========
        Route::post('/contract-review/{procurementId}/store', [SupplyChainController::class, 'storeContractReview'])
            ->name('contract-review.store');

        Route::get('/contract-review/show/{contractReviewId}', [SupplyChainController::class, 'showContractReview'])
            ->name('contract-review.show');

        // AJAX routes for contract review detail page
        Route::post('/contract-review/save-link', [SupplyChainController::class, 'saveLink'])
            ->name('contract-review.save-link');

        Route::post('/contract-review/save-log', [SupplyChainController::class, 'saveLog'])
            ->name('contract-review.save-log');

        Route::post('/contract-review/approve', [SupplyChainController::class, 'approve'])
            ->name('contract-review.approve');

        Route::post('/contract-review/reject', [SupplyChainController::class, 'reject'])
            ->name('contract-review.reject');

        Route::post('/contract-review/revisi', [SupplyChainController::class, 'revisi'])
            ->name('contract-review.revisi');
    });

    /*
    |--------------------------------------------------------------------------
    | PAYMENT ROUTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/create/{projectId}', [PaymentController::class, 'create'])->name('create');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        Route::post('/{id}/accounting-verification', [PaymentController::class, 'accountingVerification'])->name('accounting-verification');
        Route::post('/{id}/treasury-verification', [PaymentController::class, 'treasuryVerification'])->name('treasury-verification');
        Route::get('/statistics', [PaymentController::class, 'statistics'])->name('statistics');
    });


    Route::prefix('supply-chain/inquiry-quotation')->group(function () {

        // CREATE (PROCUREMENT BASED)
        Route::post(
            '/procurement/{procurementId}',
            [InquiryQuotationController::class, 'store']
        )->name('inquiry-quotation.store');

        // UPDATE (INQUIRY QUOTATION BASED)
        Route::post(
            '/{inquiryQuotationId}/update',
            [InquiryQuotationController::class, 'update']
        )->name('inquiry-quotation.update');

        // DELETE
        Route::delete(
            '/{inquiryQuotationId}',
            [InquiryQuotationController::class, 'delete']
        )->name('inquiry-quotation.delete');

        // GET BY PROCUREMENT
        Route::get(
            '/procurement/{procurementId}',
            [InquiryQuotationController::class, 'getByProcurement']
        )->name('inquiry-quotation.getByProcurement');
    });


    // ===== NEGOTIATION ROUTES (FIXED) =====
    Route::prefix('supply-chain/negotiation')->group(function () {

        // CREATE (PROCUREMENT BASED)
        Route::post(
            '/procurement/{procurementId}',
            [NegotiationController::class, 'store']
        )->name('negotiation.store');

        // UPDATE (NEGOTIATION BASED)
        Route::post(
            '/{negotiationId}/update',
            [NegotiationController::class, 'update']
        )->name('negotiation.update');

        // DELETE
        Route::delete(
            '/{negotiationId}',
            [NegotiationController::class, 'delete']
        )->name('negotiation.delete');

        // AJAX
        Route::get(
            '/procurement/{procurementId}',
            [NegotiationController::class, 'getByProcurement']
        )->name('negotiation.getByProcurement');
    });

    Route::prefix('supply-chain/material-delivery')->group(function () {

        // CREATE (PROCUREMENT BASED)
        Route::post(
            '/procurement/{procurementId}',
            [MaterialDeliveryController::class, 'store']
        )->name('material-delivery.store');

        // UPDATE (DELIVERY BASED)
        Route::post(
            '/{deliveryId}/update',
            [MaterialDeliveryController::class, 'update']
        )->name('material-delivery.update');

        // DELETE
        Route::delete(
            '/{deliveryId}',
            [MaterialDeliveryController::class, 'delete']
        )->name('material-delivery.delete');

        // GET BY PROCUREMENT
        Route::get(
            '/procurement/{procurementId}',
            [MaterialDeliveryController::class, 'getByProcurement']
        )->name('material-delivery.getByProcurement');
    });

    Route::prefix('supply-chain/pengadaan-oc')->group(function () {

        // CREATE (PROCUREMENT BASED)
        Route::post(
            '/procurement/{procurementId}',
            [PengadaanOcController::class, 'store']
        )->name('pengadaan-oc.store');

        // UPDATE (OC BASED)
        Route::post(
            '/{pengadaanOcId}/update',
            [PengadaanOcController::class, 'update']
        )->name('pengadaan-oc.update');

        // DELETE
        Route::delete(
            '/{pengadaanOcId}',
            [PengadaanOcController::class, 'delete']
        )->name('pengadaan-oc.delete');

        // GET BY PROCUREMENT
        Route::get(
            '/procurement/{procurementId}',
            [PengadaanOcController::class, 'getByProcurement']
        )->name('pengadaan-oc.getByProcurement');
    });

    Route::prefix('supply-chain/pengesahan-kontrak')->group(function () {

        // CREATE (PROCUREMENT BASED)
        Route::post(
            '/procurement/{procurementId}',
            [PengesahanKontrakController::class, 'store']
        )->name('pengesahan-kontrak.store');

        // UPDATE (KONTRAK BASED)
        Route::post(
            '/{pengesahanKontrakId}/update',
            [PengesahanKontrakController::class, 'update']
        )->name('pengesahan-kontrak.update');

        // DELETE
        Route::delete(
            '/{pengesahanKontrakId}',
            [PengesahanKontrakController::class, 'delete']
        )->name('pengesahan-kontrak.delete');

        // GET BY PROCUREMENT
        Route::get(
            '/procurement/{procurementId}',
            [PengesahanKontrakController::class, 'getByProcurement']
        )->name('pengesahan-kontrak.getByProcurement');
    });

    Route::prefix('supply-chain/kontrak')->group(function () {

        // CREATE (PROCUREMENT BASED)
        Route::post(
            '/procurement/{procurementId}',
            [KontrakController::class, 'store']
        )->name('kontrak.store');

        // UPDATE (KONTRAK BASED)
        Route::post(
            '/{kontrakId}/update',
            [KontrakController::class, 'update']
        )->name('kontrak.update');

        // DELETE
        Route::delete(
            '/{kontrakId}',
            [KontrakController::class, 'delete']
        )->name('kontrak.delete');

        // GET BY PROCUREMENT
        Route::get(
            '/procurement/{procurementId}',
            [KontrakController::class, 'getByProcurement']
        )->name('kontrak.getByProcurement');
    });

    Route::prefix('supply-chain/pembayaran')->group(function () {

        // CREATE (PROCUREMENT BASED)
        Route::post(
            '/procurement/{procurementId}',
            [PembayaranController::class, 'store']
        )->name('pembayaran.store');

        // UPDATE (PEMBAYARAN BASED)
        Route::post(
            '/{pembayaranId}/update',
            [PembayaranController::class, 'update']
        )->name('pembayaran.update');

        // DELETE
        Route::delete(
            '/{pembayaranId}',
            [PembayaranController::class, 'delete']
        )->name('pembayaran.delete');

        // GET BY PROCUREMENT (AJAX)
        Route::get(
            '/procurement/{procurementId}',
            [PembayaranController::class, 'getByProcurement']
        )->name('pembayaran.getByProcurement');
    });

    Route::prefix('supply-chain/jaminan-pembayaran')->group(function () {

    // CREATE (PROCUREMENT BASED)
    Route::post(
            '/procurement/{procurementId}',
            [JaminanPembayaranController::class, 'store']
        )->name('jaminan-pembayaran.store');

        // UPDATE (JAMINAN PEMBAYARAN BASED)
        Route::post(
            '/{jaminanPembayaranId}/update',
            [JaminanPembayaranController::class, 'update']
        )->name('jaminan-pembayaran.update');

        // DELETE
        Route::delete(
            '/{jaminanPembayaranId}',
            [JaminanPembayaranController::class, 'delete']
        )->name('jaminan-pembayaran.delete');

        // GET BY PROCUREMENT (AJAX)
        Route::get(
            '/procurement/{procurementId}',
            [JaminanPembayaranController::class, 'getByProcurement']
        )->name('jaminan-pembayaran.getByProcurement');
    });


    /*
    |--------------------------------------------------------------------------
    | QA
    |--------------------------------------------------------------------------
    */
    Route::prefix('inspections')->name('inspections.')->group(function () {
        Route::get('/', [InspectionController::class, 'index'])->name('index');
        Route::get('/{id}', [InspectionController::class, 'show'])->name('show');

        // NCR REPORTS
        Route::get('/ncr', [InspectionController::class, 'ncrReports'])->name('ncr.index');
        Route::get('/ncr/{id}', [InspectionController::class, 'showNcr'])->name('ncr.show');
        Route::put('/ncr/{id}', [InspectionController::class, 'updateNcr'])->name('ncr.update');
        Route::post('/ncr/{id}/verify', [InspectionController::class, 'verifyNcr'])->name('ncr.verify');
    });

    // QA DETAIL APPROVAL
    Route::get('/qa/detail-approval/{procurement_id}', [DetailApprovalController::class, 'show'])->name('qa.detail-approval');
    Route::post('/qa/detail-approval/{procurement_id}/save', [DetailApprovalController::class, 'saveAll'])->name('qa.detail-approval.save');


    Route::post('/checkpoint/transition/{procurementId}', [CheckpointTransitionController::class, 'transition'])
        ->name('checkpoint.transition');


    /*
    |--------------------------------------------------------------------------
    | DESAIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('desain')->name('desain.')->group(function () {

        // Dashboard
        Route::get('/dashboard', [DesainController::class, 'dashboard'])
            ->name('dashboard');

        // Notifikasi
        Route::get('/notifications', [DesainController::class, 'notifications'])
            ->name('notifications');

        // List Project
        Route::get('/list-project', [DesainListProjectController::class, 'list'])
            ->name('list-project');

        // Daftar Pengadaan (Item Listing)
        Route::get('/project/{id}/permintaan', [EvatekController::class, 'daftarPermintaan'])
            ->name('daftar-pengadaan');

        // ========== PENGADAAN FORM ==========
        Route::get('/project/{id}/pengadaan', [DesainListProjectController::class, 'formPengadaan'])
            ->name('permintaan-pengadaan');

        Route::post('/project/{id}/pengadaan/kirim', [DesainListProjectController::class, 'kirimPengadaan'])
            ->name('kirim-pengadaan');

        // ========== EVATEK ROUTES ==========
        Route::get('/evatek/item/{evatekId}', [EvatekController::class, 'review'])
            ->name('review-evatek')
            ->where('evatekId', '[0-9]+');

        // AJAX actions untuk Evatek
        Route::post('/evatek/save-link', [EvatekController::class, 'saveLink'])
            ->name('evatek.save-link');

        Route::post('/evatek/approve', [EvatekController::class, 'approve'])
            ->name('evatek.approve');

        Route::post('/evatek/reject', [EvatekController::class, 'reject'])
            ->name('evatek.reject');

        Route::post('/evatek/revisi', [EvatekController::class, 'revise'])
            ->name('evatek.revisi');

        Route::post('/evatek/save-log', [EvatekController::class, 'saveLog'])
            ->name('evatek.save-log');
    });

    /*
    |--------------------------------------------------------------------------
    | SEKDIREKSI
    |--------------------------------------------------------------------------
    */
    Route::prefix('sekdir')->name('sekdir.')->group(function () {
        Route::get('/approval', [SekdirController::class, 'approval'])->name('approval');
        Route::get('/approvals', [SekdirController::class, 'approvals'])->name('approvals');
        Route::get('/approval-detail/{procurement_id}', [SekdirController::class, 'approvalDetail'])->name('approval-detail');
        Route::post('/approval-detail/{procurement_id}/save', [SekdirController::class, 'approvalDetailSave'])->name('approval-detail.save');
        Route::post('/approval/{projectId}', [SekdirController::class, 'approvalSubmit'])->name('approval.submit');
        Route::get('/dashboard', [SekdirController::class, 'dashboard'])->name('dashboard');
    });

    // ===== DEBUG ROUTES (HARUS DI LUAR MIDDLEWARE AUTH) =====
    Route::get('/debug/vendor-reset', function () {
        $vendors = \App\Models\Vendor::all();

        echo "<h2>Debug: Reset Password Vendor</h2>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
        echo "<tr style='background: #e0e0e0;'><th>ID</th><th>Name</th><th>Email Login (user_vendor)</th><th>Current Password Hash</th><th>Action</th></tr>";

        foreach ($vendors as $vendor) {
            echo "<tr>";
            echo "<td>{$vendor->id_vendor}</td>";
            echo "<td>{$vendor->name_vendor}</td>";
            echo "<td><strong>{$vendor->user_vendor}</strong></td>";
            echo "<td style='font-size:10px; max-width:200px; overflow:hidden;'>{$vendor->password}</td>";
            echo "<td>";
            echo "<form method='POST' action='/debug/vendor-reset/{$vendor->id_vendor}' style='margin:0;'>";
            echo csrf_field();
            echo "<button type='submit' style='padding:5px 10px; background:#4CAF50; color:white; border:none; cursor:pointer;'>Reset to 'password'</button>";
            echo "</form>";
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<hr><h3>Test Login:</h3>";
        echo "<p>Setelah reset, login dengan:</p>";
        echo "<ul>";
        foreach ($vendors as $vendor) {
            echo "<li>Email: <strong>{$vendor->user_vendor}</strong> | Password: <strong>password</strong></li>";
        }
        echo "</ul>";
    });

    Route::post('/debug/vendor-reset/{id}', function ($id) {
        $vendor = \App\Models\Vendor::findOrFail($id);
        $vendor->password = \Illuminate\Support\Facades\Hash::make('password');
        $vendor->save();

        return redirect('/debug/vendor-reset')->with('success', "Password vendor {$vendor->name_vendor} berhasil direset!");
    });

    Route::get('/debug/inspection/{procurement_id}', function ($procurement_id) {
        $procurement = \App\Models\Procurement::with([
            'requestProcurements.items.inspectionReports',
            'procurementProgress.checkpoint'
        ])->findOrFail($procurement_id);

        echo "<h2>Debug: " . $procurement->code_procurement . "</h2>";

        // Items & Inspection
        echo "<h3>1️⃣  Items & Inspection Status</h3>";
        $items = $procurement->requestProcurements->flatMap->items;
        $totalItems = $items->count();

        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #e0e0e0;'><th>Item ID</th><th>Name</th><th>Result</th><th>Inspection Date</th></tr>";

        foreach ($items as $item) {
            $latest = $item->inspectionReports->sortByDesc('inspection_date')->first();
            $result = $latest?->result ?? '<span style="color: red;">NOT INSPECTED</span>';
            $date = $latest?->inspection_date ?? '-';
            echo "<tr>";
            echo "<td>{$item->item_id}</td>";
            echo "<td>{$item->item_name}</td>";
            echo "<td><strong>$result</strong></td>";
            echo "<td>$date</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Statistics
        echo "<h3>2️⃣  Statistics</h3>";
        $latestResults = $items->map(function ($it) {
            $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
            return $latest?->result ?? null;
        });
        $inspectedItems = $latestResults->filter(fn($r) => !is_null($r))->count();
        $passedCount = $latestResults->filter(fn($r) => $r === 'passed')->count();
        $failedCount = $latestResults->filter(fn($r) => $r === 'failed')->count();

        echo "<ul>";
        echo "<li>Total Items: <strong>$totalItems</strong></li>";
        echo "<li>Inspected Items: <strong>$inspectedItems</strong></li>";
        echo "<li>Passed: <strong style='color: green;'>$passedCount</strong></li>";
        echo "<li>Failed: <strong style='color: red;'>$failedCount</strong></li>";
        echo "</ul>";

        // Status
        echo "<h3>3️⃣  Procurement Status</h3>";
        $allPassed = $latestResults->every(fn($r) => $r === 'passed');
        $allFailed = $latestResults->every(fn($r) => $r === 'failed');

        if ($inspectedItems === 0) {
            $statusProc = 'BUTUH (belum inspeksi)';
            $color = 'orange';
        } elseif ($inspectedItems < $totalItems) {
            $statusProc = 'SEDANG (partial inspeksi)';
            $color = 'blue';
        } elseif ($allPassed) {
            $statusProc = 'LOLOS (all passed)';
            $color = 'green';
        } elseif ($allFailed) {
            $statusProc = 'GAGAL (all failed)';
            $color = 'red';
        } else {
            $statusProc = 'SEDANG (mixed results)';
            $color = 'blue';
        }

        echo "<p style='font-size: 18px; font-weight: bold; color: $color;'>Status: $statusProc</p>";

        // Checkpoint Progress
        echo "<h3>4️⃣  Checkpoint Progress</h3>";
        echo "<table border='1' cellpadding='8' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #e0e0e0;'><th>Checkpoint</th><th>Sequence</th><th>Status</th><th>Start Date</th><th>End Date</th><th>Updated</th></tr>";

        foreach ($procurement->procurementProgress as $progress) {
            $statusColor = match ($progress->status) {
                'completed' => '#c8e6c9',
                'in_progress' => '#bbdefb',
                'not_started' => '#f5f5f5',
                default => '#fff9c4'
            };

            echo "<tr style='background: $statusColor;'>";
            echo "<td>" . $progress->checkpoint->point_name . "</td>";
            echo "<td>" . $progress->checkpoint->point_sequence . "</td>";
            echo "<td><strong>" . $progress->status . "</strong></td>";
            echo "<td>" . ($progress->start_date ?? '-') . "</td>";
            echo "<td>" . ($progress->end_date ?? '-') . "</td>";
            echo "<td>" . $progress->updated_at . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Diagnosis
        echo "<h3>5️⃣  Diagnosis</h3>";
        $cp11 = \App\Models\Checkpoint::where('point_name', 'Inspeksi Barang')->first();
        $cp11Progress = $cp11 ? \App\Models\ProcurementProgress::where([
            'procurement_id' => $procurement_id,
            'checkpoint_id' => $cp11->point_id
        ])->first() : null;

        if (!$cp11Progress) {
            echo "<p style='color: red;'>❌ CP11 PROGRESS NOT FOUND</p>";
        } elseif ($inspectedItems === $totalItems && $cp11Progress->status === 'in_progress') {
            echo "<p style='color: red;'>❌ PROBLEM: All items inspected but CP11 still 'in_progress'</p>";
            echo "<p>transitionInspection() was NOT called or FAILED silently.</p>";
            echo "<p><a href='" . route('debug.logs') . "' target='_blank'>View Logs →</a></p>";
        } elseif ($cp11Progress->status === 'completed') {
            echo "<p style='color: green;'>✅ CORRECT: CP11 is completed</p>";
        } else {
            echo "<p style='color: blue;'>ℹ️  CP11 Status: " . $cp11Progress->status . "</p>";
        }
    })->name('debug.inspection');

    /**
     * View recent logs
     */
    Route::get('/debug/logs', function () {
        $logFile = storage_path('logs/laravel.log');
        $logs = file_exists($logFile) ? file_get_contents($logFile) : 'No logs found';

        $lines = explode("\n", $logs);
        $filtered = array_filter($lines, function ($line) {
            return stripos($line, 'checkpoint') !== false ||
                stripos($line, 'inspection') !== false ||
                stripos($line, 'transition') !== false ||
                stripos($line, 'simpanVendor') !== false;
        });

        echo "<h2>Recent Checkpoint/Inspection Logs</h2>";
        echo "<p><a href='" . route('debug.logs') . "'>↻ Refresh</a></p>";
        echo "<pre style='background: #1e1e1e; color: #00ff00; padding: 15px; overflow-x: auto; font-family: monospace; font-size: 12px; border-radius: 5px;'>";
        foreach (array_slice($filtered, -100) as $line) {
            echo htmlspecialchars($line) . "\n";
        }
        echo "</pre>";
    })->name('debug.logs');

    /**
     * Force trigger transition
     */
    Route::post('/debug/force-transition/{procurement_id}', function ($procurement_id) {
        $procurement = \App\Models\Procurement::with('requestProcurements.items.inspectionReports')->findOrFail($procurement_id);

        $items = $procurement->requestProcurements->flatMap->items;
        $latestResults = $items->map(function ($it) {
            $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
            return $latest?->result ?? null;
        });

        $allPassed = $latestResults->every(fn($r) => $r === 'passed');
        $allFailed = $latestResults->every(fn($r) => $r === 'failed');

        if (!$allPassed && !$allFailed) {
            return response()->json(['error' => 'Not all items have consistent inspection result'], 422);
        }

        $statusProc = $allPassed ? 'lolos' : 'gagal';

        \Log::info("🔨 [DEBUG] FORCE TRANSITION - Procurement: {$procurement_id}, Status: {$statusProc}");

        $service = new \App\Services\CheckpointTransitionService($procurement);
        $result = $service->transitionInspection($statusProc);

        \Log::info("🔨 [DEBUG] FORCE TRANSITION RESULT: " . json_encode($result));

        return response()->json([
            'success' => true,
            'message' => 'Transition triggered manually',
            'result' => $result,
            'redirect' => route('inspections.index')
        ]);
    })->name('debug.force-transition');

    // UMS ROUTES
    require __DIR__ . '/ums.php';
}); // End of middleware(['auth']) group