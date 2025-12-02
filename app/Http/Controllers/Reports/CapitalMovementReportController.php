<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CapitalMovementReportController extends Controller
{
    public function capitalJson(Request $request)
    {
        $data = $this->prepareReportData($request);

        return response()->json($data);
    }

    public function capitalPdf(Request $request)
    {
        $data = $this->prepareReportData($request);

        $pdf = Pdf::loadView('reports.capital_movement', $data)->setPaper('a4');

        $filename = sprintf(
            'capital-movement-%s-to-%s.pdf',
            $data['date_from'],
            $data['date_to']
        );

        return $pdf->download($filename);
    }

    protected function prepareReportData(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
            'opening_balance' => ['nullable', 'numeric'],
        ]);

        $dateFrom = Carbon::parse($validated['date_from'])->startOfDay();
        $dateTo = Carbon::parse($validated['date_to'])->endOfDay();

        $openingBalance = isset($validated['opening_balance'])
            ? (float) $validated['opening_balance']
            : 0.0;

        $capitalFromPurchases = Purchase::query()
            ->where('payment_method', 'capital')
            ->whereBetween('date', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get()
            ->sum(function (Purchase $purchase) {
                return $this->decodeNumber($purchase->total);
            });

        $capitalFromOpeningStock = StockMovement::query()
            ->where('type', 'opening')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->sum(function (StockMovement $movement) {
                $quantity = max($this->decodeNumber($movement->qty_change), 0);
                $unitCost = $this->decodeNumber($movement->unit_cost);

                return $quantity * $unitCost;
            });

        $capitalWithdrawals = 0; // Placeholder for future owner drawings/withdrawals logic.

        $capitalContributions = $capitalFromPurchases + $capitalFromOpeningStock;
        $closingBalance = $openingBalance + $capitalContributions - $capitalWithdrawals;

        return [
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'opening_balance' => $openingBalance,
            'capital_from_purchases' => $capitalFromPurchases,
            'capital_from_opening_stock' => $capitalFromOpeningStock,
            'capital_contributions' => $capitalContributions,
            'capital_withdrawals' => $capitalWithdrawals,
            'closing_balance' => $closingBalance,
        ];
    }

    protected function decodeNumber($value): float
    {
        return ((float) $value - 5) * 3;
    }
}
