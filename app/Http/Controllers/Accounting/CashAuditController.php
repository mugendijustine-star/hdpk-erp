<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\CashAudit;
use App\Services\AccountingService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class CashAuditController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer'],
            'method' => ['nullable', 'string'],
            'channel' => ['nullable', 'string'],
            'difference' => ['required', 'numeric'],
        ]);

        $audit = CashAudit::create([
            ...$validated,
            'status' => 'submitted',
            'submitted_by' => auth()->id(),
        ]);

        AuditLogger::log(
            'Accounting',
            'submit',
            'CashAudit',
            $audit->id,
            null,
            $audit->toArray()
        );

        return response()->json($audit, 201);
    }

    public function approve(Request $request, CashAudit $audit)
    {
        $oldValues = [
            'status' => $audit->status ?? null,
        ];

        if (method_exists($audit, 'update')) {
            $audit->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
            ]);
        } else {
            $audit->status = 'approved';
            $audit->approved_by = auth()->id();
        }

        AccountingService::postCashAudit($audit);

        AuditLogger::log(
            'Accounting',
            'approve',
            'CashAudit',
            $audit->id,
            $oldValues,
            [
                'status' => $audit->status ?? null,
                'approved_by' => $audit->approved_by ?? null,
            ]
        );

        return response()->json($audit);
    }
}
