<?php

use App\Http\Controllers\Hr\PayrollRunController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/hr/payroll/run', [PayrollRunController::class, 'run']);
    Route::get('/hr/payroll/{run}', [PayrollRunController::class, 'show']);
    Route::post('/hr/payroll/{run}/approve', [PayrollRunController::class, 'approve']);
});
