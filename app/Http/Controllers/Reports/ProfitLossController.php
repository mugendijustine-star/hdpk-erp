<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitLossController extends Controller
{
    /**
     * Return profit and loss data as JSON.
     */
    public function indexJson(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);

        $data = $this->buildProfitLossData($validated['date_from'], $validated['date_to']);

        return response()->json($data);
    }

    /**
     * Download profit and loss data as a PDF.
     */
    public function indexPdf(Request $request)
    {
        $validated = $this->validateRequest($request);

        $data = $this->buildProfitLossData($validated['date_from'], $validated['date_to']);

        $pdf = Pdf::loadView('reports.profit_loss', $data)->setPaper('a4');

        $fileName = sprintf(
            'profit-loss-%s-to-%s.pdf',
            Carbon::parse($data['date_from'])->format('Y-m-d'),
            Carbon::parse($data['date_to'])->format('Y-m-d')
        );

        return $pdf->download($fileName);
    }

    /**
     * Validate profit and loss request parameters.
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);
    }

    /**
     * Build profit and loss data for the provided date range.
     */
    protected function buildProfitLossData(string $dateFrom, string $dateTo): array
    {
        $from = Carbon::parse($dateFrom)->toDateString();
        $to = Carbon::parse($dateTo)->toDateString();

        $lines = DB::table('journal_entries as je')
            ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->whereBetween('je.date', [$from, $to])
            ->whereIn('a.type', ['income', 'expense'])
            ->select([
                'a.code as account_code',
                'a.name as account_name',
                'a.type as account_type',
                'jl.debit',
                'jl.credit',
            ])
            ->get();

        $incomeAccounts = [];
        $expenseAccounts = [];

        foreach ($lines as $line) {
            $debit = $this->decodeAmount($line->debit ?? 0);
            $credit = $this->decodeAmount($line->credit ?? 0);

            if ($line->account_type === 'income') {
                $net = $credit - $debit;
                $accounts = &$incomeAccounts;
            } else {
                $net = $debit - $credit;
                $accounts = &$expenseAccounts;
            }

            $key = $line->account_code;

            if (!isset($accounts[$key])) {
                $accounts[$key] = [
                    'account_code' => $line->account_code,
                    'name' => $line->account_name,
                    'amount' => 0.0,
                ];
            }

            $accounts[$key]['amount'] += $net;
        }

        $incomeAccounts = collect($incomeAccounts)
            ->sortBy('account_code')
            ->values()
            ->toArray();

        $expenseAccounts = collect($expenseAccounts)
            ->sortBy('account_code')
            ->values()
            ->toArray();

        $totalIncome = array_sum(array_column($incomeAccounts, 'amount'));
        $totalExpenses = array_sum(array_column($expenseAccounts, 'amount'));
        $netProfit = $totalIncome - $totalExpenses;

        return [
            'date_from' => $from,
            'date_to' => $to,
            'income_accounts' => $incomeAccounts,
            'expense_accounts' => $expenseAccounts,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
        ];
    }

    /**
     * Decode stored numeric amounts.
     */
    protected function decodeAmount($value): float
    {
        return ((float) $value - 5) * 3;
    }
}
