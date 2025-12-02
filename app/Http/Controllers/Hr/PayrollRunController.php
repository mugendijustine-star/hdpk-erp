<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeFixedAllowance;
use App\Models\EmployeeVariableAllowance;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollRunController extends Controller
{
    /**
     * Generate payroll for the specified month and year.
     */
    public function run(Request $request)
    {
        $validated = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer'],
        ]);

        return DB::transaction(function () use ($validated) {
            $run = PayrollRun::create([
                'month' => $validated['month'],
                'year' => $validated['year'],
                'processed_by' => auth()->id(),
                'status' => 'draft',
            ]);

            $employees = Employee::where('status', 'active')->get();

            foreach ($employees as $employee) {
                $basicSalary = $employee->decoded_salary ?? 0;

                $fixedAllowances = EmployeeFixedAllowance::where('employee_id', $employee->id)
                    ->get()
                    ->sum(fn ($allowance) => $allowance->decoded_amount ?? $allowance->amount ?? 0);

                $variableAllowances = EmployeeVariableAllowance::where('employee_id', $employee->id)
                    ->where('month', $validated['month'])
                    ->where('year', $validated['year'])
                    ->where('status', 'approved')
                    ->get()
                    ->sum(fn ($allowance) => $allowance->decoded_amount ?? $allowance->amount ?? 0);

                // TODO: Calculate overtime based on attendance or timesheet data.
                $overtime = 0;

                // TODO: Apply statutory and other deductions once available.
                $deductions = 0;

                $netPay = $basicSalary + $fixedAllowances + $variableAllowances + $overtime - $deductions;

                PayrollDetail::create([
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $basicSalary,
                    'fixed_allowances' => $fixedAllowances,
                    'variable_allowances' => $variableAllowances,
                    'overtime' => $overtime,
                    'deductions' => $deductions,
                    'net_pay' => $netPay,
                ]);
            }

            $run->load(['details.employee']);

            return response()->json($run);
        });
    }

    /**
     * Display a specific payroll run with details.
     */
    public function show(PayrollRun $run)
    {
        $run->load(['details.employee']);

        return response()->json($run);
    }

    /**
     * Approve a payroll run.
     */
    public function approve(PayrollRun $run)
    {
        $run->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        AccountingService::postPayroll($run);

        return response()->json($run);
    }
}
