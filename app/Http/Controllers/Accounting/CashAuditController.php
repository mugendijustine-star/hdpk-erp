<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\CashAudit;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = CashAudit::query();

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->input('to'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('cashier_id')) {
            $query->where('cashier_id', $request->integer('cashier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'nullable|integer',
            'date' => 'required|date',
            'cashier_id' => 'required|integer',
            'counted_cash' => 'required|numeric',
            'reason' => 'nullable|string',
        ]);

        $expectedCash = SalePayment::query()
            ->whereIn('method', ['cash', 'till', 'kt_mobile', 'nat', 'equity', 'coop'])
            ->whereHas('sale', function ($query) use ($data) {
                $query->whereDate('date_time', $data['date']);

                if (!empty($data['branch_id'])) {
                    $query->where('branch_id', $data['branch_id']);
                }
            })
            ->get()
            ->sum(fn (SalePayment $payment) => $payment->amount ?? 0);

        $difference = $data['counted_cash'] - $expectedCash;

        $audit = CashAudit::create([
            'branch_id' => $data['branch_id'] ?? null,
            'date' => $data['date'],
            'cashier_id' => $data['cashier_id'],
            'expected_cash' => $expectedCash,
            'counted_cash' => $data['counted_cash'],
            'difference' => $difference,
            'reason' => $data['reason'] ?? null,
            'status' => 'submitted',
            'submitted_by' => Auth::id(),
        ]);

        return response()->json($audit, 201);
    }

    public function approve(CashAudit $audit, Request $request)
    {
        $data = $request->validate([
            'reason' => 'nullable|string',
        ]);

        $audit->reason = $data['reason'] ?? $audit->reason;
        $audit->status = 'approved';
        $audit->approved_by = Auth::id();

        // TODO: Post accounting journals for shortages/overages.
        // if ($audit->difference < 0) {
        //     Dr Cash Shortage Expense, Cr Cash/Cash Control
        // } elseif ($audit->difference > 0) {
        //     Dr Cash/Cash Control, Cr Cash Overage / Misc Income
        // }

        $audit->save();

        return response()->json($audit);
    }
}
