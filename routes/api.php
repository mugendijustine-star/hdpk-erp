<?php

use App\Http\Controllers\Pos\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sales/{sale}/delivery-note', [SaleController::class, 'printDeliveryNote']);
Route::middleware(['auth:sanctum', 'trusted.device'])->group(function () {
    Route::post('/sales', [SaleController::class, 'store']);
use App\Http\Controllers\Inventory\PurchasesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'trusted.device'])->group(function () {
    Route::post('/purchases', [PurchasesController::class, 'store']);
});
