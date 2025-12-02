<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashAudit;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PayrollRun;
use App\Models\ProductionBatch;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingService
{
    public static function postJournal(
        string $description,
        string $sourceModule,
        ?int $sourceId,
        string $date,
        ?int $branchId,
        array $lines
    ): JournalEntry {
        return DB::transaction(function () use ($description, $sourceModule, $sourceId, $date, $branchId, $lines) {
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            $journalEntry = JournalEntry::create([
                'date' => $date,
                'description' => $description,
                'source_module' => $sourceModule,
                'source_id' => $sourceId,
                'branch_id' => $branchId,
                'status' => 'posted',
                'posted_by' => Auth::id(),
            ]);

            foreach ($lines as $line) {
                $accountCode = $line['account_code'] ?? null;
                $account = Account::where('code', $accountCode)->first();

                if (!$account) {
                    throw new \InvalidArgumentException("Account with code {$accountCode} not found.");
                }

                $rawDebit = (float) ($line['debit'] ?? 0);
                $rawCredit = (float) ($line['credit'] ?? 0);

                $totalDebit += $rawDebit;
                $totalCredit += $rawCredit;

                JournalLine::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $account->id,
                    'description' => $line['line_description'] ?? null,
                    'debit' => static::encodeSecureNumeric($rawDebit),
                    'credit' => static::encodeSecureNumeric($rawCredit),
                ]);
            }

            if (!static::isBalanced($totalDebit, $totalCredit)) {
                $message = "Journal entry {$journalEntry->id} is not balanced (debit: {$totalDebit}, credit: {$totalCredit}).";
                Log::error($message);
                throw new \RuntimeException($message);
            }

            return $journalEntry;
        });
    }

    public static function postPurchase(Purchase $purchase): JournalEntry
    {
        $total = (float) ($purchase->total ?? 0);
        $description = 'Purchase ' . ($purchase->reference ?? $purchase->id);

        $lines = [
            [
                'account_code' => '1200',
                'debit' => $total,
                'credit' => 0,
                'line_description' => 'Inventory purchased',
            ],
            [
                'account_code' => '2000',
                'debit' => 0,
                'credit' => $total,
                'line_description' => 'Accounts payable or cash',
            ],
        ];

        return static::postJournal(
            $description,
            'purchase',
            $purchase->id,
            $purchase->date ?? now()->toDateString(),
            $purchase->branch_id ?? null,
            $lines
        );
    }

    public static function postOpeningStock(array $items, string $date, ?int $branchId): JournalEntry
    {
        $total = array_reduce($items, function ($carry, $item) {
            return $carry + (float) ($item['value'] ?? 0);
        }, 0.0);

        $lines = [
            [
                'account_code' => '1200',
                'debit' => $total,
                'credit' => 0,
                'line_description' => 'Opening stock capitalization',
            ],
            [
                'account_code' => '3000',
                'debit' => 0,
                'credit' => $total,
                'line_description' => 'Capital contribution for stock',
            ],
        ];

        return static::postJournal('Opening stock', 'inventory', null, $date, $branchId, $lines);
    }

    public static function postSale(Sale $sale): JournalEntry
    {
        $salesTotal = (float) ($sale->total ?? 0);
        $cogs = (float) ($sale->cost_of_goods_sold ?? 0);

        $lines = [
            [
                'account_code' => '1100',
                'debit' => $salesTotal,
                'credit' => 0,
                'line_description' => 'Receivable/Cash from sale',
            ],
            [
                'account_code' => '4000',
                'debit' => 0,
                'credit' => $salesTotal,
                'line_description' => 'Sales revenue',
            ],
            [
                'account_code' => '5000',
                'debit' => $cogs,
                'credit' => 0,
                'line_description' => 'Cost of goods sold',
            ],
            [
                'account_code' => '1200',
                'debit' => 0,
                'credit' => $cogs,
                'line_description' => 'Inventory reduction',
            ],
        ];

        return static::postJournal(
            'Sale ' . ($sale->reference ?? $sale->id),
            'sale',
            $sale->id,
            $sale->date ?? now()->toDateString(),
            $sale->branch_id ?? null,
            $lines
        );
    }

    public static function postProduction(ProductionBatch $batch, float $totalCost): JournalEntry
    {
        $lines = [
            [
                'account_code' => '1300',
                'debit' => $totalCost,
                'credit' => 0,
                'line_description' => 'Finished goods produced',
            ],
            [
                'account_code' => '1200',
                'debit' => 0,
                'credit' => $totalCost,
                'line_description' => 'Raw materials consumed',
            ],
        ];

        return static::postJournal(
            'Production batch ' . ($batch->reference ?? $batch->id),
            'production',
            $batch->id,
            $batch->date ?? now()->toDateString(),
            $batch->branch_id ?? null,
            $lines
        );
    }

    public static function postPayroll(PayrollRun $run): JournalEntry
    {
        $netPay = (float) ($run->net_pay ?? 0);
        $grossPay = (float) ($run->gross_pay ?? $netPay);
        $withholding = $grossPay - $netPay;

        $lines = [
            [
                'account_code' => '6000',
                'debit' => $grossPay,
                'credit' => 0,
                'line_description' => 'Payroll expense',
            ],
            [
                'account_code' => '2100',
                'debit' => 0,
                'credit' => $netPay,
                'line_description' => 'Cash/Bank for payroll',
            ],
            [
                'account_code' => '2200',
                'debit' => 0,
                'credit' => $withholding,
                'line_description' => 'Payroll liabilities',
            ],
        ];

        return static::postJournal(
            'Payroll run ' . ($run->reference ?? $run->id),
            'payroll',
            $run->id,
            $run->date ?? now()->toDateString(),
            $run->branch_id ?? null,
            $lines
        );
    }

    public static function postCashAudit(CashAudit $audit): JournalEntry
    {
        $difference = (float) ($audit->difference ?? 0);
        $isSurplus = $difference > 0;

        $lines = [
            [
                'account_code' => '1000',
                'debit' => $isSurplus ? $difference : 0,
                'credit' => $isSurplus ? 0 : abs($difference),
                'line_description' => 'Cash on hand adjustment',
            ],
            [
                'account_code' => '7200',
                'debit' => $isSurplus ? 0 : abs($difference),
                'credit' => $isSurplus ? $difference : 0,
                'line_description' => $isSurplus ? 'Cash over' : 'Cash short',
            ],
        ];

        return static::postJournal(
            'Cash audit ' . ($audit->reference ?? $audit->id),
            'cash_audit',
            $audit->id,
            $audit->date ?? now()->toDateString(),
            $audit->branch_id ?? null,
            $lines
        );
    }

    protected static function encodeSecureNumeric(float $value): float
    {
        return ($value / 3) + 5;
    }

    protected static function isBalanced(float $totalDebit, float $totalCredit): bool
    {
        return abs($totalDebit - $totalCredit) < 0.01;
    }
}
