<?php

/**
 * Web Routes
 * 
 * File ini mendefinisikan semua route untuk aplikasi web
 * Routes dikelompokkan berdasarkan fitur dan dilindungi dengan middleware auth
 * 
 * @package Routes
 * @author PT PAL Indonesia
 */

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
use App\Http\Controllers\ProcurementController;

// ============================================================================
// PUBLIC ROUTES (Tidak memerlukan authentication)
// ============================================================================

/**
 * Route untuk halaman root
 * Redirect ke halaman login jika user belum login
 */
Route::get('/', function () {
    return redirect()->route('login');
});

// ============================================================================
// AUTHENTICATION ROUTES (Login & Logout)
// ============================================================================

/**
 * Route untuk menampilkan halaman login
 * Hanya bisa diakses oleh guest (user yang belum login)
 */
Route::get('/login', function() {
    return view('auth.login');
})->name('login')->middleware('guest');

/**
 * Route untuk memproses login
 * Validasi email dan password, kemudian redirect ke dashboard jika berhasil
 */
Route::post('/login', function(\Illuminate\Http\Request $request) {
    // Validasi input email dan password
    $credentials = $request->validate([
        'email' => 'required|email', // Email wajib diisi dan harus format email valid
        'password' => 'required', // Password wajib diisi
    ]);

    // Mencoba login dengan credentials yang diberikan
    // Parameter kedua adalah remember me (checkbox)
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        // Regenerate session untuk keamanan (mencegah session fixation attack)
        $request->session()->regenerate();
        // Redirect ke halaman yang dimaksud user, atau dashboard jika tidak ada
        return redirect()->intended(route('dashboard'));
    }

    // Jika login gagal, kembali ke halaman login dengan error message
    return back()->withErrors([
        'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email'); // Hanya kembalikan input email (untuk UX yang lebih baik)
})->middleware('guest'); // Hanya bisa diakses oleh guest

/**
 * Route untuk logout
 * Menghapus session dan redirect ke halaman login
 */
Route::post('/logout', function(\Illuminate\Http\Request $request) {
    // Logout user
    Auth::logout();
    // Invalidate session (hapus semua data session)
    $request->session()->invalidate();
    // Regenerate CSRF token untuk keamanan
    $request->session()->regenerateToken();
    // Redirect ke halaman login
    return redirect()->route('login');
})->name('logout');

// ============================================================================
// PROTECTED ROUTES (Memerlukan authentication)
// ============================================================================

/**
 * Semua route di dalam group ini memerlukan authentication
 * User harus login terlebih dahulu untuk mengakses route-route ini
 */
Route::middleware(['auth'])->group(function () {

    // ========================================================================
    // DASHBOARD ROUTES
    // ========================================================================
    
    /**
     * Route untuk halaman dashboard utama
     * Menampilkan overview statistik dan proyek terbaru
     */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    /**
     * Route untuk dashboard divisi tertentu
     * Menampilkan dashboard khusus untuk divisi yang dipilih
     */
    Route::get('/dashboard/division/{divisionId}', [DashboardController::class, 'divisionDashboard'])->name('dashboard.division');
    
    /**
     * Route untuk mendapatkan statistik proyek (API endpoint)
     * Mengembalikan data statistik dalam format JSON
     */
    Route::get('/dashboard/statistics', [DashboardController::class, 'getStatistics'])->name('dashboard.statistics');
    
    /**
     * Route untuk mendapatkan timeline progress pengadaan (API endpoint)
     * Mengembalikan data timeline dalam format JSON
     */
    Route::get('/dashboard/timeline/{projectId}', [DashboardController::class, 'getProcurementTimeline'])->name('dashboard.timeline');

    // ========================================================================
    // PROJECT ROUTES
    // ========================================================================
    
    /**
     * Route untuk search proyek (API endpoint)
     * Digunakan untuk AJAX search di halaman daftar proyek
     */
    Route::get('/projects/search', [ProjectController::class, 'search'])->name('projects.search');
    
    /**
     * Resource route untuk CRUD proyek
     * Membuat route berikut secara otomatis:
     * - GET /projects (index) - Daftar proyek
     * - GET /projects/create (create) - Form create proyek
     * - POST /projects (store) - Simpan proyek baru
     * - GET /projects/{id} (show) - Detail proyek
     * - GET /projects/{id}/edit (edit) - Form edit proyek
     * - PUT/PATCH /projects/{id} (update) - Update proyek
     * - DELETE /projects/{id} (destroy) - Hapus proyek
     */
    Route::resource('projects', ProjectController::class);
    
    /**
     * Route untuk update status proyek
     * Digunakan untuk mengubah status proyek melalui AJAX
     */
    Route::post('/projects/{id}/status', [ProjectController::class, 'updateStatus'])->name('projects.update-status');

    // ========================================================================
    // PROCUREMENT ROUTES (Untuk User Desain)
    // ========================================================================
    
    /**
     * Route group untuk procurement
     * Semua route di dalam group ini memiliki prefix '/procurements' dan name prefix 'procurements.'
     */
    Route::prefix('procurements')->name('procurements.')->group(function () {
        /**
         * Route untuk menampilkan form create procurement
         * User Centered Design: Form disederhanakan untuk user desain
         */
        Route::get('/create', [ProcurementController::class, 'create'])->name('create');
        
        /**
         * Route untuk menyimpan procurement baru
         * Memproses form dan menyimpan data ke database
         */
        Route::post('/', [ProcurementController::class, 'store'])->name('store');
    });

    // ========================================================================
    // NOTIFICATION ROUTES
    // ========================================================================
    
    /**
     * Route untuk menampilkan daftar notifikasi
     */
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    
    /**
     * Route untuk mendapatkan jumlah notifikasi yang belum dibaca (API endpoint)
     * Digunakan untuk menampilkan badge di navbar
     */
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    
    /**
     * Route untuk menandai notifikasi sebagai sudah dibaca
     */
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    
    /**
     * Route untuk menandai semua notifikasi sebagai sudah dibaca
     */
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    // ========================================================================
    // SUPPLY CHAIN ROUTES
    // ========================================================================
    
    /**
     * Route group untuk Supply Chain
     * Semua route di dalam group ini memiliki prefix '/supply-chain' dan name prefix 'supply-chain.'
     */
    Route::prefix('supply-chain')->name('supply-chain.')->group(function () {
        /**
         * Route untuk dashboard Supply Chain
         * Menampilkan overview khusus untuk Supply Chain team
         */
        Route::get('/dashboard', [SupplyChainController::class, 'dashboard'])->name('dashboard');
        
        /**
         * Route untuk review proyek
         * Menampilkan form review untuk Supply Chain
         */
        Route::get('/projects/{projectId}/review', [SupplyChainController::class, 'reviewProject'])->name('review-project');
        
        /**
         * Route untuk approve review proyek
         * Memproses persetujuan review dari Supply Chain
         */
        Route::post('/projects/{projectId}/approve', [SupplyChainController::class, 'approveReview'])->name('approve-review');

        /**
         * Route untuk menampilkan daftar material requests
         */
        Route::get('/material-requests', [SupplyChainController::class, 'materialRequests'])->name('material-requests');
        
        /**
         * Route untuk update material request
         */
        Route::post('/material-requests/{requestId}', [SupplyChainController::class, 'updateMaterialRequest'])->name('update-material-request');

        /**
         * Route untuk menampilkan daftar vendors
         */
        Route::get('/vendors', [SupplyChainController::class, 'vendors'])->name('vendors');
        
        /**
         * Route untuk memilih vendor untuk proyek
         */
        Route::post('/projects/{projectId}/select-vendor', [SupplyChainController::class, 'selectVendor'])->name('select-vendor');

        /**
         * Route untuk menampilkan daftar negotiations
         */
        Route::get('/negotiations', [SupplyChainController::class, 'negotiations'])->name('negotiations');
        
        /**
         * Route untuk membuat negotiation baru
         */
        Route::post('/projects/{projectId}/negotiation', [SupplyChainController::class, 'createNegotiation'])->name('create-negotiation');
        
        /**
         * Route untuk request update HPS (Harga Perkiraan Sendiri)
         * Digunakan ketika penawaran vendor melebihi HPS
         */
        Route::post('/projects/{projectId}/request-hps-update', [SupplyChainController::class, 'requestHpsUpdate'])->name('request-hps-update');

        /**
         * Route untuk menampilkan daftar material shipping
         */
        Route::get('/material-shipping', [SupplyChainController::class, 'materialShipping'])->name('material-shipping');
        
        /**
         * Route untuk update status material arrival
         */
        Route::post('/projects/{projectId}/material-arrival', [SupplyChainController::class, 'updateMaterialArrival'])->name('material-arrival');
    });

    // ========================================================================
    // PAYMENT ROUTES (Untuk Treasury & Accounting)
    // ========================================================================
    
    /**
     * Route group untuk Payment
     * Semua route di dalam group ini memiliki prefix '/payments' dan name prefix 'payments.'
     */
    Route::prefix('payments')->name('payments.')->group(function () {
        /**
         * Route untuk menampilkan daftar payments
         */
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        
        /**
         * Route untuk menampilkan form create payment
         */
        Route::get('/create/{projectId}', [PaymentController::class, 'create'])->name('create');
        
        /**
         * Route untuk menyimpan payment baru
         */
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        
        /**
         * Route untuk menampilkan detail payment
         */
        Route::get('/{id}', [PaymentController::class, 'show'])->name('show');
        
        /**
         * Route untuk mendapatkan statistik payment (API endpoint)
         */
        Route::get('/statistics', [PaymentController::class, 'statistics'])->name('statistics');

        /**
         * Route untuk verifikasi payment oleh Accounting
         */
        Route::post('/{id}/accounting-verification', [PaymentController::class, 'accountingVerification'])->name('accounting-verification');

        /**
         * Route untuk verifikasi payment oleh Treasury
         */
        Route::post('/{id}/treasury-verification', [PaymentController::class, 'treasuryVerification'])->name('treasury-verification');
        
        /**
         * Route untuk membuka LC/TT (Letter of Credit/Telegraphic Transfer)
         */
        Route::post('/projects/{projectId}/open-lc-tt', [PaymentController::class, 'openLcTt'])->name('open-lc-tt');
        
        /**
         * Route untuk membuka Sekbun (Surat Edaran Bank Indonesia)
         */
        Route::post('/projects/{projectId}/open-sekbun', [PaymentController::class, 'openSekbun'])->name('open-sekbun');
    });

    // ========================================================================
    // INSPECTION ROUTES (Untuk Quality Assurance)
    // ========================================================================
    
    /**
     * Route group untuk Inspection
     * Semua route di dalam group ini memiliki prefix '/inspections' dan name prefix 'inspections.'
     */
    Route::prefix('inspections')->name('inspections.')->group(function () {
        /**
         * Route untuk menampilkan daftar inspections
         */
        Route::get('/', [InspectionController::class, 'index'])->name('index');
        
        /**
         * Route untuk menampilkan form create inspection
         */
        Route::get('/create/{projectId}', [InspectionController::class, 'create'])->name('create');
        
        /**
         * Route untuk menyimpan inspection baru
         */
        Route::post('/', [InspectionController::class, 'store'])->name('store');
        
        /**
         * Route untuk menampilkan detail inspection
         */
        Route::get('/{id}', [InspectionController::class, 'show'])->name('show');
        
        /**
         * Route untuk update inspection
         */
        Route::put('/{id}', [InspectionController::class, 'update'])->name('update');

        // ====================================================================
        // NCR (Non-Conformance Report) ROUTES
        // ====================================================================
        
        /**
         * Route untuk menampilkan daftar NCR reports
         */
        Route::get('/ncr', [InspectionController::class, 'ncrReports'])->name('ncr.index');
        
        /**
         * Route untuk menampilkan detail NCR report
         */
        Route::get('/ncr/{id}', [InspectionController::class, 'showNcr'])->name('ncr.show');
        
        /**
         * Route untuk update NCR report
         */
        Route::put('/ncr/{id}', [InspectionController::class, 'updateNcr'])->name('ncr.update');
        
        /**
         * Route untuk verify NCR report
         */
        Route::post('/ncr/{id}/verify', [InspectionController::class, 'verifyNcr'])->name('ncr.verify');
    });
});
