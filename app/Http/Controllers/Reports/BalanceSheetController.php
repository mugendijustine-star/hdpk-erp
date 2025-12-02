<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceSheetController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:reports.view.accounting');
    }

    /**
     * Return balance sheet data as JSON.
     */
    public function indexJson(Request $request): JsonResponse
    {
        $validated = $this->validateRequest($request);

        $data = $this->buildBalanceSheetData($validated['as_at']);

        return response()->json($data);
    }

    /**
     * Download balance sheet as a PDF.
     */
    public function indexPdf(Request $request)
    {
        $validated = $this->validateRequest($request);

        $data = $this->buildBalanceSheetData($validated['as_at']);

        $pdf = Pdf::loadView('reports.balance_sheet', $data)->setPaper('a4');

        $fileName = sprintf(
            'balance-sheet-%s.pdf',
            Carbon::parse($data['as_at'])->format('Y-m-d')
        );

        return $pdf->download($fileName);
    }

    /**
     * Validate incoming request for balance sheet.
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'as_at' => ['required', 'date'],
        ]);
    }

    /**
     * Build balance sheet data for the provided date.
     */
    protected function buildBalanceSheetData(string $asAt): array
    {
        $asAtDate = Carbon::parse($asAt)->toDateString();

        $lines = DB::table('journal_entries as je')
            ->join('journal_lines as jl', 'je.id', '=', 'jl.journal_entry_id')
            ->join('accounts as a', 'jl.account_id', '=', 'a.id')
            ->whereDate('je.date', '<=', $asAtDate)
            ->select([
                'a.id as account_id',
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
            $key = $line->account_id;

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
            ->map(function ($account) {
                $debit = $account['total_debit'];
                $credit = $account['total_credit'];

                $balance = in_array($account['type'], ['asset', 'expense'], true)
                    ? $debit - $credit
                    : $credit - $debit;

                $account['balance'] = $balance;

                return $account;
            })
            ->sortBy('account_code')
            ->values()
            ->toArray();

        $assets = array_values(array_filter($accounts, function ($account) {
            return $account['type'] === 'asset' && $account['balance'] != 0;
        }));

        $liabilities = array_values(array_filter($accounts, function ($account) {
            return $account['type'] === 'liability';
        }));

        $equity = array_values(array_filter($accounts, function ($account) {
            return $account['type'] === 'equity';
        }));

        $totalAssets = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity = array_sum(array_column($equity, 'balance'));
        $liabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return [
            'as_at' => $asAtDate,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totals' => [
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'liabilities_and_equity' => $liabilitiesAndEquity,
                'is_balanced' => abs($totalAssets - $liabilitiesAndEquity) < 0.01,
            ],
        ];
    }

    /**
     * Decode obfuscated numeric amounts.
     */
    protected function decodeAmount($value): float
    {
        return ((float) $value - 5) * 3;
    }
}
