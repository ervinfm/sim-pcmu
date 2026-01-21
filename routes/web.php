<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- CONTROLLERS: PUBLIC ---
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PostController as PublicPostController;
use App\Http\Controllers\Public\AssetController as PublicAssetController;

// --- CONTROLLERS: APP (CORE) ---
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\Web\SettingController;

// --- CONTROLLERS: FINANCE (MODUL KEUANGAN) ---
use App\Http\Controllers\App\Finance\TransactionController;
use App\Http\Controllers\App\Finance\AccountController;
use App\Http\Controllers\App\Finance\LedgerController;
use App\Http\Controllers\App\Finance\OpeningBalanceController;
use App\Http\Controllers\App\Finance\ClosingPeriodController;
use App\Http\Controllers\App\Finance\BudgetController;

// --- CONTROLLERS: ASSETS & MEMBERS ---
use App\Http\Controllers\App\Assets\AssetController;
use App\Http\Controllers\App\Assets\AssetReferenceController;
use App\Http\Controllers\App\Assets\AssetLoanController;
use App\Http\Controllers\App\Members\MemberController;

// --- CONTROLLERS: ADMIN & REFERENCE ---
use App\Http\Controllers\App\Reference\UserController;
use App\Http\Controllers\App\Reference\OrganizationController;
use App\Http\Controllers\App\Web\PostController as AdminPostController;

// --- CONTROLLERS: ADDITIONAL (Arsip, Peta & Pusat Pelaporan) ---
use App\Http\Controllers\App\Archives\ArchiveController;
use App\Http\Controllers\App\Archives\ArchiveDispositionController;
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
    Route::get('/inventaris/{id}', [PublicAssetController::class, 'show'])->name('assets.show');
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
    Route::post('/keep-alive', function () {
        // Laravel otomatis memperpanjang sesi saat request ini masuk
        return response()->noContent();
    })->name('keep-alive');


    // =========================================================================
    // MODUL 1: KEUANGAN (FINANCE)
    // =========================================================================
    Route::middleware(['auth'])->prefix('finance')->name('finance.')->group(function () {
    
        // 1. TRANSAKSI & AKUN (Operasional Harian)
        Route::get('accounts/generate-code', [AccountController::class, 'generateCode'])
            ->name('accounts.generate-code');
        Route::resource('accounts', AccountController::class);

        Route::resource('transactions', TransactionController::class);

        // 2. BUKU BESAR (Pengganti Laporan)
        Route::get('ledgers', [LedgerController::class, 'index'])->name('ledgers.index');

        // 3. ANGGARAN / RAPB
        Route::resource('budgets', BudgetController::class);

        // 4. SETUP SALDO AWAL
        Route::get('opening-balances', [OpeningBalanceController::class, 'index'])->name('opening-balances.index');
        Route::get('opening-balances/create', [OpeningBalanceController::class, 'create'])->name('opening-balances.create');
        Route::post('opening-balances', [OpeningBalanceController::class, 'store'])->name('opening-balances.store');

        // 5. TUTUP BUKU (Closing Period)
        Route::get('closing-periods', [ClosingPeriodController::class, 'index'])->name('closing-periods.index');
        Route::post('closing-periods', [ClosingPeriodController::class, 'store'])->name('closing-periods.store');
        Route::delete('closing-periods/{id}', [ClosingPeriodController::class, 'destroy'])->name('closing-periods.destroy');

    });


    // =========================================================================
    // MODUL 2: ASET & INVENTARIS
    // =========================================================================
    Route::prefix('assets')->name('assets.')->group(function () {

        // A. MASTER DATA (Referensi: Satuan & Lokasi)
        Route::controller(AssetReferenceController::class)
            ->prefix('references')
            ->name('references.')
            ->group(function() {
                Route::get('/', 'index')->name('index');
                Route::post('/units', 'storeUnit')->name('units.store');
                Route::put('/units/{unit}', 'updateUnit')->name('units.update');
                Route::delete('/units/{unit}', 'destroyUnit')->name('units.destroy');
                Route::post('/locations', 'storeLocation')->name('locations.store');
                Route::put('/locations/{location}', 'updateLocation')->name('locations.update');
                Route::delete('/locations/{location}', 'destroyLocation')->name('locations.destroy');
        });

        // B. SIRKULASI (Peminjaman Aset)
        Route::controller(AssetLoanController::class)
            ->prefix('loans')
            ->name('loans.')
            ->group(function() {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{assetLoan}', 'show')->name('show');
                Route::delete('/{assetLoan}', 'destroy')->name('destroy'); 
                Route::post('/{assetLoan}/checkout', 'checkout')->name('checkout');
                Route::put('/{assetLoan}/checkin', 'checkin')->name('checkin');
                Route::post('/{assetLoan}/reject', 'reject')->name('reject');
                Route::patch('/{assetLoan}/status','changeStatus')->name('change-status');
        });

        // C. FITUR KHUSUS ASET
        Route::get('/{asset}/print-label', [AssetController::class, 'printLabel'])->name('print-label');
        Route::post('/print-batch', [AssetController::class, 'printBatch'])->name('print-batch');

    });

    // D. RESOURCE UTAMA ASET (CRUD)
    Route::resource('assets', AssetController::class);


    // =========================================================================
    // MODUL 3: KEANGGOTAAN (MEMBERS)
    // =========================================================================
    Route::controller(MemberController::class)->prefix('members')->name('members.')->group(function () {
        Route::get('template-download', 'downloadTemplate')->name('download_template');
        Route::get('import-wizard', 'importWizard')->name('import_wizard');
        Route::post('import/parse', 'parseImport')->name('import.parse');
        Route::post('import/execute', 'executeImport')->name('import.execute');
        Route::post('{member}/generate-account', 'generateAccount')->name('generate_account');
        Route::post('{member}/history', 'updateHistory')->name('update_history');
    });

    Route::resource('members', MemberController::class);


    // =========================================================================
    // MODUL 4: E-ARSIP & PERSURATAN
    // =========================================================================
    
    // A. Custom Routes untuk Arsip (Download & Preview)
    Route::get('archives/{archive}/download', [ArchiveController::class, 'download'])->name('archives.download');
    Route::get('archives/{archive}/preview', [ArchiveController::class, 'preview'])->name('archives.preview');
    
    // B. Resource Utama Arsip (CRUD)
    Route::resource('archives', ArchiveController::class);

    // C. Routes Disposisi (Alur Surat)
    Route::controller(ArchiveDispositionController::class)->group(function () {
        // 1. Inbox Disposisi (Surat Masuk Pegawai)
        Route::get('dispositions/inbox', 'index')->name('dispositions.index');
        
        // 2. Kirim Disposisi (Dari Halaman Detail Arsip)
        Route::get('archives/{archive}/disposition', 'create')->name('dispositions.create');
        Route::post('archives/{archive}/disposition', 'store')->name('dispositions.store');
        
        // 3. Aksi Disposisi (Selesaikan / Tandai Baca)
        Route::patch('dispositions/{disposition}/complete', 'update')->name('dispositions.update');
        Route::post('dispositions/{disposition}/read', 'markAsRead')->name('dispositions.read');
    });


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
        Route::get('organizations/{organization}/structure', [OrganizationController::class, 'editStructure'])
            ->name('organizations.structure');
        Route::post('organizations/{organization}/structure', [OrganizationController::class, 'storeStructure'])
            ->name('organizations.structure.store');
        Route::delete('organizations/structure/{id}', [OrganizationController::class, 'destroyStructure'])
            ->name('organizations.structure.destroy');
        Route::get('organizations/{organization}/territory', [OrganizationController::class, 'editTerritory'])
            ->name('organizations.territory');
        Route::post('organizations/{organization}/territory', [OrganizationController::class, 'storeTerritory'])
            ->name('organizations.territory.store');
        Route::delete('organizations/territory/{id}', [OrganizationController::class, 'destroyTerritory'])
            ->name('organizations.territory.destroy');    
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

require __DIR__.'/auth.php';