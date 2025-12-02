<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\CashAudit;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateTimeInterface;

class CashAuditReportController extends Controller
{
    public function indexJson(Request $request): JsonResponse
    {
        $data = $this->buildReportData($request);

        return response()->json($data);
    }

    public function indexPdf(Request $request)
    {
        $data = $this->buildReportData($request);

        $pdf = Pdf::loadView('reports.cash_audit', $data)->setPaper('a4');

        $fromDate = $data['date_from'] ?? $data['date_to'] ?? Carbon::now();
        $toDate = $data['date_to'] ?? $data['date_from'] ?? Carbon::now();

        $fileName = sprintf(
            'cash-audit-%s-to-%s.pdf',
            Carbon::parse($fromDate)->format('Y-m-d'),
            Carbon::parse($toDate)->format('Y-m-d')
        );

        return $pdf->download($fileName);
    }

    /**
     * Build report data shared by JSON and PDF responses.
     */
    protected function buildReportData(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer'],
            'cashier_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'string'],
        ]);

        $query = CashAudit::with(['cashier', 'submittedBy', 'approvedBy']);

        if (!empty($validated['date_from'])) {
            $query->whereDate('date', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('date', '<=', $validated['date_to']);
        }

        if (!empty($validated['branch_id'])) {
            $query->where('branch_id', $validated['branch_id']);
        }

        if (!empty($validated['cashier_id'])) {
            $query->where('cashier_id', $validated['cashier_id']);
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $audits = $query->orderByDesc('date')->get();

        $rows = $audits->map(function (CashAudit $audit) {
            $expectedCash = (float) ($audit->expected_cash ?? 0);
            $countedCash = (float) ($audit->counted_cash ?? 0);
            $difference = $audit->difference ?? ($countedCash - $expectedCash);

            $dateValue = $audit->date ?? $audit->created_at;

            if ($dateValue instanceof DateTimeInterface) {
                $date = $dateValue->toDateString();
            } elseif (!empty($dateValue)) {
                $date = Carbon::parse($dateValue)->toDateString();
            } else {
                $date = null;
            }

            return [
                'date' => $date,
                'branch_id' => $audit->branch_id ?? null,
                'cashier_name' => optional($audit->cashier)->name,
                'expected_cash' => $expectedCash,
                'counted_cash' => $countedCash,
                'difference' => (float) $difference,
                'reason' => $audit->reason ?? null,
                'status' => $audit->status ?? null,
                'submitted_by_name' => optional($audit->submittedBy)->name,
                'approved_by_name' => optional($audit->approvedBy)->name,
            ];
        });

        $totals = [
            'expected_cash' => $rows->sum('expected_cash'),
            'counted_cash' => $rows->sum('counted_cash'),
            'difference' => $rows->sum('difference'),
        ];

        return [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'cashier_id' => $validated['cashier_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'rows' => $rows->values()->all(),
            'totals' => $totals,
        ];
    }
}
