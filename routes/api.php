<?php

use App\Http\Controllers\Reports\CashbookReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports/cashbook', [CashbookReportController::class, 'cashbookJson']);
    Route::get('/reports/cashbook/pdf', [CashbookReportController::class, 'cashbookPdf']);
});
