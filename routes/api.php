<?php

use App\Http\Controllers\Hr\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hr/attendance', [AttendanceController::class, 'index']);
    Route::post('/hr/attendance', [AttendanceController::class, 'store']);
});
