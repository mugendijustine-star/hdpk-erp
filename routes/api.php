<?php

use App\Http\Controllers\Inventory\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'trusted.device'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{variant}', [ProductController::class, 'show']);
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class);

Route::middleware(['auth:sanctum', 'trusted.device'])->group(function () {
    // protected API routes
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function ($request) {
    return $request->user();
});
