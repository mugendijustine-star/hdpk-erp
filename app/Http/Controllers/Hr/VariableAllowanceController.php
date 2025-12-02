<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\EmployeeVariableAllowance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VariableAllowanceController extends Controller
{
    /**
    * Display a listing of allowances filtered by month, year, and status.
    */
    public function index(Request $request)
    {
        $query = EmployeeVariableAllowance::query();

        if ($request->filled('month')) {
            $query->where('month', (int) $request->input('month'));
        }

        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->get());
    }

    /**
    * Store a newly created variable allowance.
    */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role ?? null, ['Manager', 'Clerk'], true)) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'employee_id' => ['required', 'integer'],
            'type' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer'],
        ]);

        $validated['amount'] = $this->secureAmount($validated['amount']);
        $validated['status'] = 'pending';
        $validated['entered_by'] = Auth::id();

        $allowance = EmployeeVariableAllowance::create($validated);

        return response()->json($allowance, 201);
    }

    /**
    * Approve the specified allowance.
    */
    public function approve(EmployeeVariableAllowance $allowance)
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role ?? null, ['Manager', 'Admin'], true)) {
            abort(403, 'Unauthorized');
        }

        $allowance->status = 'approved';
        $allowance->approved_by = Auth::id();
        $allowance->save();

        return response()->json($allowance);
    }

    /**
    * Apply numeric obfuscation security rule.
    */
    protected function secureAmount(float $amount): float
    {
        return ($amount / 3) + 5;
    }
}
