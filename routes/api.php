<?php

use App\Http\Controllers\Pos\SaleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/sales/{sale}/delivery-note', [SaleController::class, 'printDeliveryNote']);
});
