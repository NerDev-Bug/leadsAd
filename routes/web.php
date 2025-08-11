<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\AccessRegisterController;
use App\Http\Controllers\ArchiveNewsController;

Route::get('/dashboard', function () {
    $productsCount = \App\Models\product::count();
    $newsCount = \App\Models\News::count();
    $careersCount = \App\Models\Career::count();
    return Inertia::render('SubPage/Dashboard', [
        'productsCount' => $productsCount,
        'newsCount' => $newsCount,
        'careersCount' => $careersCount,
    ]);
})->middleware('auth')->name('dashboard');

Route::get('/register', function () {
    return Inertia::render('AccessRegister');
})->name('register');

Route::get('/', function () {
    return Inertia::render('LoginForm');
})->middleware('guest')->name('login');

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

Route::post('/access-registers', [AccessRegisterController::class, 'store'])->name('access-registers.store');
Route::post('/access-register/login', [AccessRegisterController::class, 'login'])->name('access-register.login');

Route::post('/access-logout', [AccessRegisterController::class, 'logout'])->name('access.logout');

// Archive news listing (for modal fetch; supports JSON)
Route::get('/archive-news', [ArchiveNewsController::class, 'index'])->middleware('auth')->name('archive.news.index');
Route::post('/archive-news/{archiveNews}/restore', [ArchiveNewsController::class, 'restore'])->middleware('auth')->name('archive.news.restore');

// require __DIR__.'/auth.php';
