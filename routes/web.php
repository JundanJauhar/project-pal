<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Controllers
use App\Http\Controllers\Auth\LoginController;
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


// Redirect root → login
Route::get('/', fn() => redirect()->route('login'));

Route::get('/login', fn() => view('auth.login'))
    ->name('login')
    ->middleware('guest');

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        // Redirect berdasarkan role
            if (Auth::user()->roles === 'superadmin') {
                return redirect()->route('ums.users.index'); // langsung ke UMS
            }

            return redirect()->route('dashboard');


        // Jika vendor, arahkan ke halaman vendor
        if (Auth::user()->roles === 'vendor') {
            return redirect()->route('vendor.index');
        }

        return redirect()->route('dashboard');
    }

    return back()->withErrors([
        'email' => 'Email atau password salah.',
    ]);
})->middleware('guest');

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (User harus login)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Dashboard
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
        Route::get('/dashboard', [SupplyChainController::class, 'dashboard'])->name('dashboard');
        Route::post('/dashboard/store', [SupplyChainController::class, 'storePengadaan'])->name('dashboard.store');
        Route::get('/projects/{projectId}/review', [SupplyChainController::class, 'reviewProject'])->name('review-project');
        Route::post('/projects/{projectId}/approve', [SupplyChainController::class, 'approveReview'])->name('approve-review');
        Route::get('/material-requests', [SupplyChainController::class, 'materialRequests'])->name('material-requests');
        Route::post('/material-requests/{requestId}', [SupplyChainController::class, 'updateMaterialRequest'])->name('update-material-request');
        Route::get('/vendors', [SupplyChainController::class, 'vendors'])->name('vendors');
        Route::post('/projects/select-vendor/{procurement_id}', [SupplyChainController::class, 'selectVendor'])->name('select-vendor');
        Route::get('/negotiations', [SupplyChainController::class, 'negotiations'])->name('negotiations');
        Route::post('/projects/{projectId}/negotiation', [SupplyChainController::class, 'createNegotiation'])->name('create-negotiation');
        Route::get('/material-shipping', [SupplyChainController::class, 'materialShipping'])->name('material-shipping');
        Route::post('/projects/{projectId}/material-arrival', [SupplyChainController::class, 'updateMaterialArrival'])->name('material-arrival');
        Route::get('/evatek', [EvatekController::class, 'index'])->name('evatek.index');
        Route::get('/evatek/create', [EvatekController::class, 'create'])->name('evatek.create');
        Route::post('/evatek/store', [EvatekController::class, 'store'])->name('evatek.store');
        
        // VENDOR ROUTES - Proper route ordering
        Route::get('/vendor/kelola', [SupplyChainController::class, 'kelolaVendor'])->name('vendor.kelola');
        Route::post('/vendor/store', [SupplyChainController::class, 'storeVendor'])->name('vendor.store');
        Route::get('/vendor/form', [SupplyChainController::class, 'formVendor'])->name('vendor.form');
        Route::get('/vendor/detail', [SupplyChainController::class, 'detailVendor'])->name('vendor.detail');
        Route::get('/vendor/pilih/{procurement_id}', [SupplyChainController::class, 'pilihVendor'])->name('vendor.pilih');
        Route::put('/vendor/update/{id_vendor}', [SupplyChainController::class, 'updateVendor'])->name('vendor.update');
        Route::post('/vendor/simpan/{procurementId}', [SupplyChainController::class, 'simpanVendor'])->name('vendor.simpan');
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

    /*
    |--------------------------------------------------------------------------
    | QA
    |--------------------------------------------------------------------------
    */
    Route::prefix('inspections')->name('inspections.')->group(function () {
        // ===== ROUTES QA INSPECTION =====
        Route::get('/', [InspectionController::class, 'index'])->name('index');
        Route::get('/{id}', [InspectionController::class, 'show'])->name('show');
        
        // ===== ROUTES NCR REPORTS =====
        Route::get('/ncr', [InspectionController::class, 'ncrReports'])->name('ncr.index');
        Route::get('/ncr/{id}', [InspectionController::class, 'showNcr'])->name('ncr.show');
        Route::put('/ncr/{id}', [InspectionController::class, 'updateNcr'])->name('ncr.update');
        Route::post('/ncr/{id}/verify', [InspectionController::class, 'verifyNcr'])->name('ncr.verify');
    });

    // ===== ROUTES QA DETAIL APPROVAL =====
    Route::get('/qa/detail-approval/{procurement_id}', [DetailApprovalController::class, 'show'])->name('qa.detail-approval');
    Route::post('/qa/detail-approval/{procurement_id}/save', [DetailApprovalController::class, 'saveAll'])->name('qa.detail-approval.save');

    /*
    |--------------------------------------------------------------------------
    | DESAIN
    |--------------------------------------------------------------------------
    */
    Route::prefix('desain')->name('desain.')->group(function () {
        Route::get('/dashboard', [DesainController::class, 'dashboard'])->name('dashboard');
        Route::get('/list-project', [DesainListProjectController::class, 'list'])->name('list-project');
        Route::get('/project/{id}/permintaan', [DesainListProjectController::class, 'daftarPengadaan'])->name('daftar-pengadaan');
        Route::get('/project/{id}/pengadaan', [DesainListProjectController::class, 'formPengadaan'])->name('permintaan-pengadaan');
        Route::post('/project/{id}/pengadaan/kirim', [DesainListProjectController::class, 'kirimPengadaan'])->name('kirim-pengadaan');
        Route::get('/evatek/{request_id}', [DesainListProjectController::class, 'reviewEvatek'])->name('review-evatek');
        Route::get('/project/{projectId}/input-item', [DesainController::class, 'inputItem'])->name('input-item');
        Route::post('/project/{projectId}/input-item/store', [DesainController::class, 'storeItem'])->name('input-item.store');
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
    });

Route::middleware(['auth'])->group(function () {

    // halaman utama vendor
    Route::get('/vendor', [VendorEvatekController::class, 'index'])
        ->name('vendor.index');

    // kompatibilitas
    Route::redirect('/vendor/dashboard', '/vendor');
    Route::redirect('/vendor/evatek', '/vendor');
});


    // ============ DEBUG ROUTES ============
    
    /**
     * Debug inspection status
     * GET /debug/inspection/{procurement_id}
     */
    Route::get('/debug/inspection/{procurement_id}', function($procurement_id) {
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
        $inspectedItems = $latestResults->filter(fn ($r) => !is_null($r))->count();
        $passedCount = $latestResults->filter(fn ($r) => $r === 'passed')->count();
        $failedCount = $latestResults->filter(fn ($r) => $r === 'failed')->count();
        
        echo "<ul>";
        echo "<li>Total Items: <strong>$totalItems</strong></li>";
        echo "<li>Inspected Items: <strong>$inspectedItems</strong></li>";
        echo "<li>Passed: <strong style='color: green;'>$passedCount</strong></li>";
        echo "<li>Failed: <strong style='color: red;'>$failedCount</strong></li>";
        echo "</ul>";

        // Status
        echo "<h3>3️⃣  Procurement Status</h3>";
        $allPassed = $latestResults->every(fn ($r) => $r === 'passed');
        $allFailed = $latestResults->every(fn ($r) => $r === 'failed');
        
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
            $statusColor = match($progress->status) {
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
     * GET /debug/logs
     */
    Route::get('/debug/logs', function() {
        $logFile = storage_path('logs/laravel.log');
        $logs = file_exists($logFile) ? file_get_contents($logFile) : 'No logs found';
        
        $lines = explode("\n", $logs);
        $filtered = array_filter($lines, function($line) {
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
     * POST /debug/force-transition/{procurement_id}
     */
    Route::post('/debug/force-transition/{procurement_id}', function($procurement_id) {
        $procurement = \App\Models\Procurement::with('requestProcurements.items.inspectionReports')->findOrFail($procurement_id);
        
        $items = $procurement->requestProcurements->flatMap->items;
        $latestResults = $items->map(function ($it) {
            $latest = $it->inspectionReports->sortByDesc('inspection_date')->first();
            return $latest?->result ?? null;
        });
        
        $allPassed = $latestResults->every(fn ($r) => $r === 'passed');
        $allFailed = $latestResults->every(fn ($r) => $r === 'failed');
        
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

    // ============ END DEBUG ROUTES ============

    // -------------------------------------------------------------
    //  UMS ROUTES (DILETAKKAN DI LUAR, tetapi tetap pakai middleware auth)
    // -------------------------------------------------------------
    require __DIR__.'/ums.php';
});
