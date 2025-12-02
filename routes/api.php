<?php

use App\Http\Controllers\Reports\CapitalMovementReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports/capital', [CapitalMovementReportController::class, 'capitalJson']);
    Route::get('/reports/capital/pdf', [CapitalMovementReportController::class, 'capitalPdf']);
});
