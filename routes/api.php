<?php

use App\Http\Controllers\Reports\CapitalMovementReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports/capital', [CapitalMovementReportController::class, 'capitalJson']);
    Route::get('/reports/capital/pdf', [CapitalMovementReportController::class, 'capitalPdf']);
use App\Http\Controllers\Reports\CashbookReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports/cashbook', [CashbookReportController::class, 'cashbookJson']);
    Route::get('/reports/cashbook/pdf', [CashbookReportController::class, 'cashbookPdf']);
use App\Http\Controllers\Reports\SalesReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'trusted.device'])->group(function () {
    Route::get('/reports/sales/daily', [SalesReportController::class, 'dailyJson']);
    Route::get('/reports/sales/daily/pdf', [SalesReportController::class, 'dailyPdf']);
use App\Http\Controllers\Hr\PayrollRunController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/hr/payroll/run', [PayrollRunController::class, 'run']);
    Route::get('/hr/payroll/{run}', [PayrollRunController::class, 'show']);
    Route::post('/hr/payroll/{run}/approve', [PayrollRunController::class, 'approve']);
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
