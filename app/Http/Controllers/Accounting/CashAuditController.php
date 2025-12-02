<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\CashAudit;
use App\Services\AccountingService;
use Illuminate\Http\Request;

class CashAuditController extends Controller
{
    public function approve(Request $request, CashAudit $audit)
    {
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

        return response()->json($audit);
    }
}
