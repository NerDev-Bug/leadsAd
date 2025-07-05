<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::post('/products', [\App\Http\Controllers\ProductController::class, 'store']);
Route::put('/products/{product}', [\App\Http\Controllers\ProductController::class, 'update'])->name('products.update');
Route::delete('/products/{product}', [\App\Http\Controllers\ProductController::class, 'destroy'])->name('products.destroy');

Route::get('/', function () {
    return Inertia::render('Dashboard');
})->name('home');

Route::get('/news', function () {
    return Inertia::render('News');
})->name('news');

Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index'])->name('products');


require __DIR__.'/auth.php';
