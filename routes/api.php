<?php

use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\PayrollRunController;
use App\Http\Controllers\Hr\VariableAllowanceController;
use App\Http\Controllers\Inventory\PurchasesController;
use App\Http\Controllers\Accounting\CashAuditController;
use App\Http\Controllers\Manufacturing\ProductionController;
use App\Http\Controllers\Pos\SaleController;
use App\Http\Controllers\Reports\CapitalMovementReportController;
use App\Http\Controllers\Reports\CashbookReportController;
use App\Http\Controllers\Reports\BalanceSheetController;
use App\Http\Controllers\Reports\ProfitLossController;
use App\Http\Controllers\Reports\SalesReportController;
use App\Http\Controllers\Reports\TrialBalanceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reports/capital', [CapitalMovementReportController::class, 'capitalJson']);
    Route::get('/reports/capital/pdf', [CapitalMovementReportController::class, 'capitalPdf']);

    Route::get('/reports/cashbook', [CashbookReportController::class, 'cashbookJson']);
    Route::get('/reports/cashbook/pdf', [CashbookReportController::class, 'cashbookPdf']);

    Route::get('/reports/balance-sheet', [BalanceSheetController::class, 'indexJson']);
    Route::get('/reports/balance-sheet/pdf', [BalanceSheetController::class, 'indexPdf']);
    Route::get('/reports/profit-loss', [ProfitLossController::class, 'indexJson']);
    Route::get('/reports/profit-loss/pdf', [ProfitLossController::class, 'indexPdf']);

    Route::get('/reports/trial-balance', [TrialBalanceController::class, 'indexJson']);
    Route::get('/reports/trial-balance/pdf', [TrialBalanceController::class, 'indexPdf']);

    Route::get('/accounting/cash-audits', [CashAuditController::class, 'index']);
    Route::post('/accounting/cash-audits', [CashAuditController::class, 'store']);
    Route::post('/accounting/cash-audits/{audit}/approve', [CashAuditController::class, 'approve']);

    Route::post('/hr/payroll/run', [PayrollRunController::class, 'run']);
    Route::get('/hr/payroll/{run}', [PayrollRunController::class, 'show']);
    Route::post('/hr/payroll/{run}/approve', [PayrollRunController::class, 'approve']);

    Route::get('/hr/attendance', [AttendanceController::class, 'index']);
    Route::post('/hr/attendance', [AttendanceController::class, 'store']);

    Route::get('/hr/allowances/variable', [VariableAllowanceController::class, 'index']);
    Route::post('/hr/allowances/variable', [VariableAllowanceController::class, 'store']);
    Route::post('/hr/allowances/variable/{allowance}/approve', [VariableAllowanceController::class, 'approve']);

    Route::get('/sales/{sale}/delivery-note', [SaleController::class, 'printDeliveryNote']);
});

Route::middleware(['auth:sanctum', 'trusted.device'])->group(function () {
    Route::get('/reports/sales/daily', [SalesReportController::class, 'dailyJson']);
    Route::get('/reports/sales/daily/pdf', [SalesReportController::class, 'dailyPdf']);

    Route::post('/sales', [SaleController::class, 'store']);

    Route::post('/purchases', [PurchasesController::class, 'store']);
});

Route::post('/production-batches', [ProductionController::class, 'store']);
