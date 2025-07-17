<?php

use App\Http\Controllers\Api\productsController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [productsController::class, 'index']);
