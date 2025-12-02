<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\SalePayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashbookReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.view.accounting');
    }

    /**
     * Return cashbook data as JSON.
     */
    public function cashbookJson(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);

        $data = $this->buildCashbookData($validated['date_from'], $validated['date_to']);

        return response()->json($data);
    }

    /**
     * Download cashbook data as PDF.
     */
    public function cashbookPdf(Request $request)
    {
        $validated = $this->validateRequest($request);

        $data = $this->buildCashbookData($validated['date_from'], $validated['date_to']);

        $pdf = Pdf::loadView('reports.cashbook', $data)->setPaper('a4');

        $fileName = sprintf(
            'cashbook-%s-to-%s.pdf',
            Carbon::parse($validated['date_from'])->format('Y-m-d'),
            Carbon::parse($validated['date_to'])->format('Y-m-d')
        );

        return $pdf->download($fileName);
    }

    /**
     * Validate incoming request for cashbook report.
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);
    }

    /**
     * Build cashbook data for the provided date range.
     */
    protected function buildCashbookData(string $dateFrom, string $dateTo): array
    {
        $paymentMethods = ['cash', 'till', 'kt_mobile', 'nat', 'equity', 'coop'];

        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $salePayments = SalePayment::with(['sale.customer'])
            ->whereIn('method', $paymentMethods)
            ->whereHas('sale', function ($query) use ($from, $to) {
                $query->whereBetween('date_time', [$from, $to]);
            })
            ->orderBy('payment_date')
            ->get();

        $purchases = Purchase::with('supplier')
            ->whereIn('payment_method', $paymentMethods)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->get();

        $receipts = [];
        $payments = [];
        $totalsReceipts = [];
        $totalsPayments = [];

        foreach ($salePayments as $salePayment) {
            $amount = $this->decodeAmount($salePayment->amount ?? 0);
            $method = $salePayment->method;

            $receipts[] = [
                'date' => $this->formatDate($salePayment->sale?->date_time ?? $salePayment->payment_date),
                'source' => 'Sale #' . ($salePayment->sale?->id ?? $salePayment->sale_id),
                'description' => optional($salePayment->sale?->customer)->name ?? 'Sale',
                'method' => $method,
                'amount' => $amount,
            ];

            $totalsReceipts[$method] = ($totalsReceipts[$method] ?? 0) + $amount;
        }

        foreach ($purchases as $purchase) {
            $amount = $this->decodeAmount($purchase->payment_amount ?? 0);
            $method = $purchase->payment_method;

            $payments[] = [
                'date' => $this->formatDate($purchase->date),
                'destination' => optional($purchase->supplier)->name ?? 'Supplier',
                'description' => 'Purchase #' . $purchase->id,
                'method' => $method,
                'amount' => $amount,
            ];

            $totalsPayments[$method] = ($totalsPayments[$method] ?? 0) + $amount;
        }

        $netByMethod = [];
        $allMethods = array_unique(array_merge(array_keys($totalsReceipts), array_keys($totalsPayments), $paymentMethods));

        foreach ($allMethods as $method) {
            $netByMethod[$method] = ($totalsReceipts[$method] ?? 0) - ($totalsPayments[$method] ?? 0);
        }

        return [
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'receipts' => $receipts,
            'payments' => $payments,
            'totals_receipts_by_method' => $totalsReceipts,
            'totals_payments_by_method' => $totalsPayments,
            'net_by_method' => $netByMethod,
        ];
    }

    /**
     * Decode obfuscated numeric amounts.
     */
    protected function decodeAmount($value): float
    {
        return ((float) $value - 5) * 3;
    }

    /**
     * Format dates consistently.
     */
    protected function formatDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return Carbon::parse($value)->toDateString();
    }
}
