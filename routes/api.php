<?php

use App\Http\Controllers\Hr\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hr/attendance', [AttendanceController::class, 'index']);
    Route::post('/hr/attendance', [AttendanceController::class, 'store']);
use App\Http\Controllers\Hr\VariableAllowanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/hr/allowances/variable', [VariableAllowanceController::class, 'index']);
    Route::post('/hr/allowances/variable', [VariableAllowanceController::class, 'store']);
    Route::post('/hr/allowances/variable/{allowance}/approve', [VariableAllowanceController::class, 'approve']);
use App\Http\Controllers\Manufacturing\ProductionController;
use Illuminate\Support\Facades\Route;

Route::post('/production-batches', [ProductionController::class, 'store']);
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
