<?php

use App\Http\Controllers\Api\productsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\newsController;
use App\Http\Controllers\Api\careersController;
use App\Http\Controllers\Api\DirectoryController;

Route::get('/careers', [careersController::class, 'index']);
Route::get('/news', [newsController::class, 'index']);
Route::get('/products', [productsController::class, 'index']);
Route::get('/distributors', [DirectoryController::class, 'index']);