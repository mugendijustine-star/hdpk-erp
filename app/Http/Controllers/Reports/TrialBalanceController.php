<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrialBalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.view.accounting');
    }

    /**
     * Return trial balance data as JSON.
     */
    public function indexJson(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);

        $data = $this->buildTrialBalanceData($validated['date_from'], $validated['date_to']);

        return response()->json($data);
    }

    /**
     * Download trial balance as a PDF.
     */
    public function indexPdf(Request $request)
    {
        $validated = $this->validateRequest($request);

        $data = $this->buildTrialBalanceData($validated['date_from'], $validated['date_to']);

        $pdf = Pdf::loadView('reports.trial_balance', $data)->setPaper('a4');

        $fileName = sprintf(
            'trial-balance-%s-to-%s.pdf',
            Carbon::parse($data['date_from'])->format('Y-m-d'),
            Carbon::parse($data['date_to'])->format('Y-m-d')
        );

        return $pdf->download($fileName);
    }

    /**
     * Validate the trial balance request.
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date'],
        ]);
    }

    /**
     * Build trial balance data for the provided range.
     */
    protected function buildTrialBalanceData(string $dateFrom, string $dateTo): array
    {
        $from = Carbon::parse($dateFrom)->toDateString();
        $to = Carbon::parse($dateTo)->toDateString();

        $lines = DB::table('journal_entries as je')
            ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->whereBetween('je.date', [$from, $to])
            ->select([
                'a.code as account_code',
                'a.name as account_name',
                'a.type as account_type',
                'jl.debit',
                'jl.credit',
            ])
            ->orderBy('a.code')
            ->get();

        $accounts = [];

        foreach ($lines as $line) {
            $debit = $this->decodeAmount($line->debit ?? 0);
            $credit = $this->decodeAmount($line->credit ?? 0);

            $key = $line->account_code;

            if (!isset($accounts[$key])) {
                $accounts[$key] = [
                    'account_code' => $line->account_code,
                    'account_name' => $line->account_name,
                    'type' => $line->account_type,
                    'total_debit' => 0.0,
                    'total_credit' => 0.0,
                ];
            }

            $accounts[$key]['total_debit'] += $debit;
            $accounts[$key]['total_credit'] += $credit;
        }

        $accounts = collect($accounts)
            ->sortBy('account_code')
            ->map(function ($account) {
                $account['balance'] = $account['total_debit'] - $account['total_credit'];

                return $account;
            })
            ->values()
            ->toArray();

        $totalDebit = array_sum(array_column($accounts, 'total_debit'));
        $totalCredit = array_sum(array_column($accounts, 'total_credit'));

        return [
            'date_from' => $from,
            'date_to' => $to,
            'accounts' => $accounts,
            'totals' => [
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'balance' => $totalDebit - $totalCredit,
            ],
        ];
    }

    /**
     * Decode the stored numeric amount.
     */
    protected function decodeAmount($value): float
    {
        return ((float) $value - 5) * 3;
    }
}
