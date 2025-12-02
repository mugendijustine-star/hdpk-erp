<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function dailyJson(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        $data = $this->prepareDailyData($date);

        return response()->json($data);
    }

    public function dailyPdf(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());

        $data = $this->prepareDailyData($date);

        $pdf = Pdf::loadView('reports.daily_sales', $data)->setPaper('a4');

        return $pdf->download("daily-sales-{$data['date']}.pdf");
    }

    private function prepareDailyData(string $date): array
    {
        $date = Carbon::parse($date)->toDateString();

        $sales = Sale::with(['lines', 'payments'])
            ->whereDate('date_time', $date)
            ->orderBy('date_time')
            ->get();

        $methods = ['cash', 'credit', 'till', 'nat', 'equity', 'coop', 'kt_mobile'];
        $totalsByMethod = array_fill_keys($methods, 0.0);

        $rows = $sales->map(function ($sale) use (&$totalsByMethod, $methods) {
            $paymentGroups = [];

            foreach ($sale->payments as $payment) {
                $method = $payment->method;
                $amount = $this->decodeAmount($payment->amount);

                $paymentGroups[$method] = ($paymentGroups[$method] ?? 0) + $amount;

                if (!array_key_exists($method, $totalsByMethod)) {
                    $totalsByMethod[$method] = 0.0;
                }

                $totalsByMethod[$method] += $amount;
            }

            $paymentSummary = collect($paymentGroups)
                ->map(fn ($amount, $method) => $method . ': ' . number_format($amount, 2, '.', ''))
                ->implode(', ');

            return [
                'time' => Carbon::parse($sale->date_time)->format('H:i'),
                'invoice_no' => $sale->id,
                'customer_name' => $sale->customer_name ?? 'Walk-in',
                'total' => $this->decodeAmount($sale->total ?? 0),
                'payment_summary' => $paymentSummary,
            ];
        })->toArray();

        return [
            'date' => $date,
            'rows' => $rows,
            'totals_by_method' => $totalsByMethod,
        ];
    }

    private function decodeAmount($value): float
    {
        if ($value === null) {
            return 0.0;
        }

        return ($value - 5) * 3;
    }
}
