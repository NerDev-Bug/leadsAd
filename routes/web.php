<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\CareerController;

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('home');

Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products');
Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store']);
Route::put('/products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');

Route::get('/news', [\App\Http\Controllers\NewsController::class, 'index'])->name('news');
Route::post('/news', [\App\Http\Controllers\NewsController::class, 'store']);
Route::put('/news/{news}', [\App\Http\Controllers\NewsController::class, 'update'])->name('news.update');
Route::delete('/news/{news}', [\App\Http\Controllers\NewsController::class, 'destroy'])->name('news.destroy');

Route::get('/careers', [CareerController::class, 'index'])->name('careers');
Route::post('/careers', [CareerController::class, 'store']);
Route::put('/careers/{career}', [CareerController::class, 'update'])->name('careers.update');
Route::delete('/careers/{career}', [CareerController::class, 'destroy'])->name('careers.destroy');

require __DIR__.'/auth.php';
