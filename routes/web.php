<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- CONTROLLERS: PUBLIC ---
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PostController as PublicPostController;

// --- CONTROLLERS: APP (CORE) ---
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\Web\SettingController;

// --- CONTROLLERS: FINANCE (MODUL KEUANGAN) ---
use App\Http\Controllers\App\Finance\TransactionController;
use App\Http\Controllers\App\Finance\AccountController;
use App\Http\Controllers\App\Finance\OpeningBalanceController;
use App\Http\Controllers\App\Finance\ClosingPeriodController;
use App\Http\Controllers\App\Finance\ReportController;

// --- CONTROLLERS: ASSETS & MEMBERS ---
use App\Http\Controllers\App\Assets\AssetController;
use App\Http\Controllers\App\Members\MemberController;

// --- CONTROLLERS: ADMIN & REFERENCE ---
use App\Http\Controllers\App\Reference\UserController;
use App\Http\Controllers\App\Reference\OrganizationController;
use App\Http\Controllers\App\Web\PostController as AdminPostController;

// --- CONTROLLERS: ADDITIONAL (Arsip, Peta & Pusat Pelaporan) ---
use App\Http\Controllers\App\Archives\ArchiveController;
use App\Http\Controllers\App\Maps\MapController;
use App\Http\Controllers\App\Reports\ReportController as CentralReportController;


/*
|--------------------------------------------------------------------------
| 1. ZONA PUBLIK (LANDING PAGE & PROFILE)
|--------------------------------------------------------------------------
*/
Route::name('public.')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/profil', [PageController::class, 'profile'])->name('profile');
    Route::get('/struktur', [PageController::class, 'structure'])->name('structure');
    
    // Berita Publik
    Route::resource('news', PublicPostController::class);
    // Route::get('/news', [PublicPostController::class, 'index'])->name('posts.index');
    // Route::get('/news/{slug}', [PublicPostController::class, 'show'])->name('posts.show');
});


/*
|--------------------------------------------------------------------------
| 2. ZONA APLIKASI (DASHBOARD & MODUL)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // =========================================================================
    // MODUL UTAMA: DASHBOARD
    // =========================================================================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // =========================================================================
    // MODUL 1: KEUANGAN (FINANCE)
    // =========================================================================
    Route::prefix('finance')->name('finance.')->group(function () {
        
        // A. Transaksi & Akun
        Route::resource('accounts', AccountController::class);
        Route::resource('transactions', TransactionController::class);

        // B. Setup Saldo Awal
        Route::get('opening-balances', [OpeningBalanceController::class, 'index'])->name('opening-balances.index');
        Route::get('opening-balances/create', [OpeningBalanceController::class, 'create'])->name('opening-balances.create');
        Route::post('opening-balances', [OpeningBalanceController::class, 'store'])->name('opening-balances.store');

        // C. Tutup Buku
        Route::get('closing-periods', [ClosingPeriodController::class, 'index'])->name('closing-periods.index');
        Route::post('closing-periods', [ClosingPeriodController::class, 'store'])->name('closing-periods.store');
        Route::delete('closing-periods/{id}', [ClosingPeriodController::class, 'destroy'])->name('closing-periods.destroy');

        // D. Laporan
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::get('reports/income-statement', [ReportController::class, 'incomeStatement'])->name('reports.income-statement');
    });


    // =========================================================================
    // MODUL 2: ASET & INVENTARIS
    // =========================================================================
    Route::resource('assets', AssetController::class);


    // =========================================================================
    // MODUL 3: KEANGGOTAAN (MEMBERS)
    // =========================================================================
    // Custom Routes untuk Import & Utility (Ditempatkan SEBELUM resource)
    Route::controller(MemberController::class)->prefix('members')->name('members.')->group(function () {
        // Import Wizard & Template
        Route::get('template-download', 'downloadTemplate')->name('download_template');
        Route::get('import-wizard', 'importWizard')->name('import_wizard');
        Route::post('import/parse', 'parseImport')->name('import.parse');
        Route::post('import/execute', 'executeImport')->name('import.execute');
        
        // Member Actions (Generate Akun & Update History)
        Route::post('{member}/generate-account', 'generateAccount')->name('generate_account');
        Route::post('{member}/history', 'updateHistory')->name('update_history');
    });

    // Resource Route Utama (CRUD)
    Route::resource('members', MemberController::class);


    // =========================================================================
    // MODUL 4: E-ARSIP & PERSURATAN
    // =========================================================================
    Route::resource('archives', ArchiveController::class);


    // =========================================================================
    // MODUL 5: PETA DAKWAH (GIS)
    // =========================================================================
    Route::get('maps', [MapController::class, 'index'])->name('maps.index');


    // =========================================================================
    // MODUL 6: PUBLIKASI WEB (CMS)
    // =========================================================================
    Route::resource('posts', AdminPostController::class);
    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');


    // =========================================================================
    // MODUL 7: ADMINISTRASI SISTEM (SUPER ADMIN)
    // =========================================================================
    Route::prefix('admin')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('organizations', OrganizationController::class);
        
        // Manajemen Struktur & Wilayah
        Route::get('organizations/{organization}/structure', [OrganizationController::class, 'structure'])->name('organizations.structure');
        Route::post('organizations/{organization}/structure', [OrganizationController::class, 'updateStructure'])->name('organizations.structure.update');
        Route::get('organizations/{organization}/territory', [OrganizationController::class, 'territory'])->name('organizations.territory');
        Route::post('organizations/{organization}/territory', [OrganizationController::class, 'updateTerritory'])->name('organizations.territory.update');
    });

    // =========================================================================
    // MODUL 8: PUSAT PELAPORAN TERPADU (SUPER ADMIN)
    // =========================================================================
    Route::prefix('admin')->group(function () {
        Route::resource('reports', CentralReportController::class);
    });

    // =========================================================================
    // MODUL 9: PROFIL PRIBADI (ME)
    // =========================================================================
    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/me', 'myProfile')->name('me');
        Route::get('/account', 'accountSettings')->name('account');
        Route::put('/account', 'updateAccount')->name('update');
        Route::get('/messages', 'messages')->name('messages');
        Route::get('/logs', 'activityLogs')->name('logs');
    });

    // Helper: Kirim Pesan Internal
    Route::post('/messages/send', function (Illuminate\Http\Request $request) {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string'
        ]);

        \App\Models\Message::create([
            'user_id' => $request->receiver_id,
            'sender_id' => auth()->id(),
            'sender_name' => auth()->user()->name,
            'subject' => 'Percakapan Pribadi',
            'body' => $request->body,
            'category' => 'CHAT',
            'is_read' => false
        ]);

        return back()->with('success', 'Pesan terkirim.');
    })->name('messages.store');

});

/*
|--------------------------------------------------------------------------
| 3. AUTHENTICATION
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';