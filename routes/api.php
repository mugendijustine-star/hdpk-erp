<?php

use App\Http\Controllers\Inventory\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'trusted.device'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{variant}', [ProductController::class, 'show']);
});
