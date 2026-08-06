<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\AccessRegisterController;
use App\Http\Controllers\ArchiveNewsController;
use App\Http\Controllers\DirectoryController;
use App\Http\Controllers\UserSessionController;
use App\Http\Controllers\BackupController;

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
})->middleware('auth')->name('dashboard');

Route::get('/register', function () {
    return Inertia::render('AccessRegister');
})->name('register');

Route::get('/', function () {
    return Inertia::render('LoginForm');
})->middleware('guest')->name('login');

Route::fallback(function () {
    return Inertia::render('Errors/NotFound');
});

//Products functions
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->middleware('auth')->name('products');
Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store'])->middleware('auth');
Route::put('/products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->middleware('auth')->name('products.update');
Route::delete('/products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->middleware('auth')->name('products.destroy');

//News functions
Route::get('/news', [\App\Http\Controllers\NewsController::class, 'index'])->middleware('auth')->name('news');
Route::post('/news', [\App\Http\Controllers\NewsController::class, 'store'])->middleware('auth');
Route::put('/news/{news}', [\App\Http\Controllers\NewsController::class, 'update'])->middleware('auth')->name('news.update');
Route::delete('/news/{news}', [\App\Http\Controllers\NewsController::class, 'destroy'])->middleware('auth')->name('news.destroy');

//Careers functions
Route::get('/careers', [CareerController::class, 'index'])->middleware('auth')->name('careers');
Route::post('/careers', [CareerController::class, 'store'])->middleware('auth');
Route::put('/careers/{career}', [CareerController::class, 'update'])->middleware('auth')->name('careers.update');
Route::delete('/careers/{career}', [CareerController::class, 'destroy'])->middleware('auth')->name('careers.destroy');

// Directories functions
Route::get('/directories', [DirectoryController::class, 'index'])->middleware('auth')->name('directories');
Route::post('/directories', [DirectoryController::class, 'store'])->middleware('auth');
Route::post('/directories/import', [DirectoryController::class, 'import'])->middleware('auth')->name('directories.import');
Route::put('/directories/{directory}', [DirectoryController::class, 'update'])->middleware('auth')->name('directories.update');
Route::delete('/directories/{directory}', [DirectoryController::class, 'destroy'])->middleware('auth')->name('directories.destroy');

Route::post('/access-registers', [AccessRegisterController::class, 'store'])->name('access-registers.store');
Route::post('/access-register/login', [AccessRegisterController::class, 'login'])->name('access-register.login');

Route::post('/access-logout', [AccessRegisterController::class, 'logout'])->name('access.logout');
Route::put('/access-register/password', [AccessRegisterController::class, 'updatePassword'])
    ->middleware('auth')
    ->name('access-register.password.update');

Route::get('/user-sessions', [UserSessionController::class, 'index'])
    ->middleware('auth')
    ->name('user-sessions.index');
Route::delete('/user-sessions/{userSession}', [UserSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('user-sessions.destroy');
Route::post('/user-sessions/logout-all', [UserSessionController::class, 'logoutAll'])
    ->middleware('auth')
    ->name('user-sessions.logout-all');

// Archive news listing (for modal fetch; supports JSON)
Route::get('/archive-news', [ArchiveNewsController::class, 'index'])->middleware('auth')->name('archive.news.index');
Route::put('/archive-news/{archiveNews}', [ArchiveNewsController::class, 'update'])->middleware('auth')->name('archive.news.update');
Route::post('/archive-news/{archiveNews}/restore', [ArchiveNewsController::class, 'restore'])->middleware('auth')->name('archive.news.restore');
Route::delete('/archive-news/{archiveNews}', [ArchiveNewsController::class, 'destroy'])->middleware('auth')->name('archive.news.destroy');


Route::get('/settings', function () {
    return Inertia::render('SubPage/Settings');
})->middleware('auth')->name('settings');

Route::get('/backups', [BackupController::class, 'index'])->middleware('auth')->name('backups.index');
Route::post('/backups', [BackupController::class, 'store'])->middleware('auth')->name('backups.store');
Route::get('/backups/{filename}/download', [BackupController::class, 'download'])
    ->middleware('auth')
    ->where('filename', 'backup_[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}\.sql')
    ->name('backups.download');
Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])
    ->middleware('auth')
    ->where('filename', 'backup_[0-9]{4}_[0-9]{2}_[0-9]{2}_[0-9]{6}\.sql')
    ->name('backups.destroy');

// require __DIR__.'/auth.php';
