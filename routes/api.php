<?php

use App\Http\Controllers\Reports\SalesReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'trusted.device'])->group(function () {
    Route::get('/reports/sales/daily', [SalesReportController::class, 'dailyJson']);
    Route::get('/reports/sales/daily/pdf', [SalesReportController::class, 'dailyPdf']);
});
