<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\AccessRegisterController;
use App\Http\Controllers\ArchiveNewsController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\UserSessionController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\AdminUserController;

Route::get('/dashboard', function () {
    $productsCount = \App\Models\product::count();
    $newsCount = \App\Models\News::count();
    $careersCount = \App\Models\Career::count();
    $directoriesCount = \App\Models\Directory::count();
    return Inertia::render('SubPage/Dashboard', [
        'productsCount' => $productsCount,
        'newsCount' => $newsCount,
        'careersCount' => $careersCount,
        'directoriesCount' => $directoriesCount,
    ]);
})->middleware(['auth', 'role:dashboard'])->name('dashboard');

// Route::get('/register', function () {
//     return Inertia::render('AccessRegister');
// })->name('register');

Route::get('/', function () {
    return Inertia::render('LoginForm');
})->middleware('guest')->name('login');

Route::fallback(function () {
    return Inertia::render('Errors/NotFound');
});

// Products
Route::middleware(['auth', 'role:products'])->group(function () {
    Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products');
    Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store']);
    Route::put('/products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');
});

// News
Route::middleware(['auth', 'role:news'])->group(function () {
    Route::get('/news', [\App\Http\Controllers\NewsController::class, 'index'])->name('news');
    Route::post('/news', [\App\Http\Controllers\NewsController::class, 'store']);
    Route::put('/news/{news}', [\App\Http\Controllers\NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}', [\App\Http\Controllers\NewsController::class, 'destroy'])->name('news.destroy');
});

// Careers
Route::middleware(['auth', 'role:careers'])->group(function () {
    Route::get('/careers', [CareerController::class, 'index'])->name('careers');
    Route::post('/careers', [CareerController::class, 'store']);
    Route::put('/careers/{career}', [CareerController::class, 'update'])->name('careers.update');
    Route::delete('/careers/{career}', [CareerController::class, 'destroy'])->name('careers.destroy');
});

// Directories
Route::middleware(['auth', 'role:directories'])->group(function () {
    Route::get('/directories', [DirectoryController::class, 'index'])->name('directories');
    Route::post('/directories', [DirectoryController::class, 'store']);
    Route::post('/directories/import', [DirectoryController::class, 'import'])->name('directories.import');
    Route::put('/directories/{directory}', [DirectoryController::class, 'update'])->name('directories.update');
    Route::delete('/directories/{directory}', [DirectoryController::class, 'destroy'])->name('directories.destroy');
});

// Public self-registration disabled — create users via seeder / admin only.
// Route::post('/access-registers', [AccessRegisterController::class, 'store'])->name('access-registers.store');
Route::post('/access-register/login', [AccessRegisterController::class, 'login'])
    ->middleware('throttle:10,1')
    ->name('access-register.login');

Route::post('/access-logout', [AccessRegisterController::class, 'logout'])->name('access.logout');
Route::put('/access-register/password', [AccessRegisterController::class, 'updatePassword'])
    ->middleware('auth')
    ->name('access-register.password.update');

Route::middleware('auth')->group(function () {
    Route::get('/user-sessions', [UserSessionController::class, 'index'])->name('user-sessions.index');
    Route::delete('/user-sessions/{userSession}', [UserSessionController::class, 'destroy'])->name('user-sessions.destroy');
    Route::post('/user-sessions/logout-all', [UserSessionController::class, 'logoutAll'])->name('user-sessions.logout-all');
});

// Archive news (Settings > News Archive)
Route::middleware(['auth', 'role:archive'])->group(function () {
    Route::get('/archive-news', [ArchiveNewsController::class, 'index'])->name('archive.news.index');
    Route::put('/archive-news/{archiveNews}', [ArchiveNewsController::class, 'update'])->name('archive.news.update');
    Route::post('/archive-news/{archiveNews}/restore', [ArchiveNewsController::class, 'restore'])->name('archive.news.restore');
    Route::delete('/archive-news/{archiveNews}', [ArchiveNewsController::class, 'destroy'])->name('archive.news.destroy');
});

Route::get('/settings', function () {
    return Inertia::render('SubPage/Settings');
})->middleware(['auth', 'role:settings'])->name('settings');

// Backups (Settings > Backup) — administrator only
Route::middleware(['auth', 'role:backup'])->group(function () {
    Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
    Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
    Route::get('/backups/{filename}/download', [BackupController::class, 'download'])
        ->where('filename', 'backup_[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}\.sql')
        ->name('backups.download');
    Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])
        ->where('filename', 'backup_[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}\.sql')
        ->name('backups.destroy');
});

// Admin users management (Settings > Users) — administrator only
Route::middleware(['auth', 'role:users'])->group(function () {
    Route::get('/admin-users', [AdminUserController::class, 'index'])->name('admin-users.index');
    Route::get('/admin-users/{accessRegister}', [AdminUserController::class, 'show'])->name('admin-users.show');
    Route::delete('/admin-users/{accessRegister}', [AdminUserController::class, 'destroy'])->name('admin-users.destroy');
});

// require __DIR__.'/auth.php';
