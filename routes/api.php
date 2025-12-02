<?php

use App\Http\Controllers\Hr\VariableAllowanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hr/allowances/variable', [VariableAllowanceController::class, 'index']);
    Route::post('/hr/allowances/variable', [VariableAllowanceController::class, 'store']);
    Route::post('/hr/allowances/variable/{allowance}/approve', [VariableAllowanceController::class, 'approve']);
});
