<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- CONTROLLERS: PUBLIC ---
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\PostController as PublicPostController;

// --- CONTROLLERS: APP (ADMIN/DASHBOARD) ---
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\Members\MemberController;
use App\Http\Controllers\App\Finance\TransactionController;
use App\Http\Controllers\App\Finance\AccountController;
use App\Http\Controllers\App\Assets\AssetController;
use App\Http\Controllers\App\Reference\UserController;
use App\Http\Controllers\App\Reference\OrganizationController;
use App\Http\Controllers\App\Web\PostController as AdminPostController;
use App\Http\Controllers\App\Web\SettingController;

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
    Route::controller(PublicPostController::class)->group(function() {
        Route::get('/berita', 'index')->name('news.index');
        Route::get('/berita/{slug}', 'show')->name('news.show');
    });
});

/*
|--------------------------------------------------------------------------
| 2. ZONA DASHBOARD (SISTEM INFORMASI MANAJEMEN)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // =========================================================================
    // MODUL 1: MANAJEMEN ANGGOTA (MEMBERS)
    // =========================================================================
    Route::prefix('members')->name('members.')->controller(MemberController::class)->group(function () {
        // Import Wizard & Logic
        Route::get('/template-download', 'downloadTemplate')->name('download_template');
        Route::get('/import-wizard', 'importWizard')->name('import_wizard');
        Route::post('/import/parse', 'parseImport')->name('import.parse');
        Route::post('/import/execute', 'executeImport')->name('import.execute');
        
        // Actions
        Route::post('/{member}/generate-account', 'generateAccount')->name('generate_account');
        Route::post('/{member}/history', 'updateHistory')->name('update_history'); // Pastikan method ini ada di controller
    });
    // Resource Route (Index, Create, Store, Show, Edit, Update, Destroy)
    Route::resource('members', MemberController::class);


    // =========================================================================
    // MODUL 2: MANAJEMEN ORGANISASI (ORGANIZATIONS)
    // =========================================================================
    Route::prefix('organizations')->name('organizations.')->controller(OrganizationController::class)->group(function () {
        // Sub-resource: Struktur
        Route::get('/{organization}/structure', 'editStructure')->name('structure.edit');
        Route::post('/{organization}/structure', 'storeStructure')->name('structure.store');
        Route::delete('/structure/{structure}', 'destroyStructure')->name('structure.destroy');

        // Sub-resource: Wilayah (Territory)
        Route::get('/{organization}/territory', 'editTerritory')->name('territory.edit');
        Route::post('/{organization}/territory', 'storeTerritory')->name('territory.store');
        Route::delete('/territory/{territory}', 'destroyTerritory')->name('territory.destroy');
    });
    Route::resource('organizations', OrganizationController::class);


    // =========================================================================
    // MODUL 3: MANAJEMEN USER & AKSES (USERS)
    // =========================================================================
    Route::prefix('users')->name('users.')->controller(UserController::class)->group(function () {
        Route::post('/{user}/link-member', 'linkMember')->name('link');
        Route::post('/{user}/unlink-member', 'unlinkMember')->name('unlink');
        Route::patch('/{user}/toggle-status', 'toggleStatus')->name('toggle_status');
    });
    Route::resource('users', UserController::class);


    // =========================================================================
    // MODUL 4: KEUANGAN & ASET
    // =========================================================================
    // Transaksi (Sudah ada)
    Route::resource('transactions', TransactionController::class);
    Route::post('transactions/accounts', [TransactionController::class, 'storeAccount'])->name('accounts.quick_store');

    // MANAJEMEN AKUN (BARU)
    Route::resource('finance-accounts', AccountController::class);

    Route::resource('assets', AssetController::class);


    // =========================================================================
    // MODUL 5: MANAJEMEN WEB (BERITA & SETTING)
    // =========================================================================
    Route::resource('posts', AdminPostController::class); // Admin Posts
    Route::delete('/posts/attachment/{id}', [AdminPostController::class, 'destroyAttachment'])
        ->name('posts.attachment.destroy');
    Route::delete('/posts/gallery/{id}', [AdminPostController::class, 'destroyGallery'])
        ->name('posts.gallery.destroy');
    
    Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function() {
        Route::get('/', 'edit')->name('edit');
        Route::patch('/', 'update')->name('update');
    });
    


    // =========================================================================
    // MODUL 6: PROFIL PRIBADI (ME)
    // =========================================================================
    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/me', 'myProfile')->name('me');
        Route::get('/account', 'accountSettings')->name('account');
        Route::put('/account', 'updateAccount')->name('update');
        Route::get('/messages', 'messages')->name('messages');
        Route::get('/logs', 'activityLogs')->name('logs');
    });

    // Helper: Send Message (Closure dipindahkan ke Controller idealnya, tapi dibiarkan disini dulu)
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

        return back();
    })->name('messages.store');

});

/*
|--------------------------------------------------------------------------
| 3. AUTHENTICATION
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';