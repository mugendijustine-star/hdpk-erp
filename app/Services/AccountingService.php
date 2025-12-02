<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashAudit;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\ProductVariant;
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
        $total = static::decodeSecureNumeric($purchase->total ?? 0.0);
        $description = 'Purchase ' . ($purchase->reference ?? $purchase->id);

        $paymentMethod = $purchase->payment_method ?? null;
        $creditAccount = match ($paymentMethod) {
            'capital' => '3000',
            'cash' => '1000',
            'till' => '1010',
            'kt_mobile' => '1020',
            'bank_nat' => '1100',
            'bank_equity' => '1110',
            'bank_coop' => '1120',
            'credit' => '2000',
            'creditor_services' => '2010',
            default => '2000',
        };

        $lines = [
            [
                'account_code' => '1300',
                'debit' => $total,
                'credit' => 0,
                'line_description' => 'Inventory purchased',
            ],
            [
                'account_code' => $creditAccount,
                'debit' => 0,
                'credit' => $total,
                'line_description' => 'Purchase funding',
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
            $qty = (float) ($item['qty'] ?? $item['quantity'] ?? 0);
            $cost = (float) ($item['cost'] ?? $item['unit_cost'] ?? 0);

            return $carry + ($qty * $cost);
        }, 0.0);

        $lines = [
            [
                'account_code' => '1300',
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
        $salesTotal = static::decodeSecureNumeric($sale->total ?? 0.0);
        $paymentLines = [];
        $payments = $sale->payments ?? [];
        $paymentTotal = 0.0;

        if (!empty($payments)) {
            foreach ($payments as $payment) {
                $method = $payment->method ?? null;
                $amount = (float) ($payment->amount ?? 0);
                $paymentTotal += $amount;

                $accountCode = match ($method) {
                    'cash' => '1000',
                    'till' => '1010',
                    'kt_mobile' => '1020',
                    'nat' => '1100',
                    'equity' => '1110',
                    'coop' => '1120',
                    'credit' => '1200',
                    default => '1200',
                };

                $paymentLines[] = [
                    'account_code' => $accountCode,
                    'debit' => $amount,
                    'credit' => 0,
                    'line_description' => 'Sale receipt (' . ($method ?? 'credit') . ')',
                ];
            }
        }

        if (empty($paymentLines) || !static::isBalanced($paymentTotal, $salesTotal)) {
            $remaining = $salesTotal - $paymentTotal;

            $paymentLines[] = [
                'account_code' => '1200',
                'debit' => max($remaining, 0),
                'credit' => 0,
                'line_description' => 'Debtors',
            ];
            $paymentTotal += max($remaining, 0);
        }

        $cogs = static::estimateCostOfGoodsSold($sale);

        $lines = [
            ...$paymentLines,
            [
                'account_code' => '4000',
                'debit' => 0,
                'credit' => $salesTotal,
                'line_description' => 'Sales revenue',
            ],
        ];

        if ($cogs > 0) {
            $lines[] = [
                'account_code' => '5000',
                'debit' => $cogs,
                'credit' => 0,
                'line_description' => 'Cost of goods sold',
            ];

            $lines[] = [
                'account_code' => '1300',
                'debit' => 0,
                'credit' => $cogs,
                'line_description' => 'Inventory reduction',
            ];
        }

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
        $rawMaterialCost = (float) ($batch->total_raw_material_cost ?? $batch->raw_material_cost ?? 0);
        $overheads = max($totalCost - $rawMaterialCost, 0);

        if ($rawMaterialCost <= 0) {
            $rawMaterialCost = $totalCost;
            $overheads = 0;
        }

        $lines = [
            [
                'account_code' => '1300',
                'debit' => $totalCost,
                'credit' => 0,
                'line_description' => 'Finished goods produced',
            ],
            [
                'account_code' => '1310',
                'debit' => 0,
                'credit' => $rawMaterialCost,
                'line_description' => 'Raw materials consumed',
            ],
        ];

        if ($overheads > 0) {
            $lines[] = [
                'account_code' => '5200',
                'debit' => 0,
                'credit' => $overheads,
                'line_description' => 'Manufacturing expenses',
            ];
        }

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
        $details = $run->details ?? [];
        $grossPay = 0.0;
        $netPay = 0.0;
        $withholding = 0.0;

        foreach ($details as $detail) {
            if (!$detail instanceof PayrollDetail) {
                continue;
            }

            $detailGross = ($detail->basic_salary ?? 0)
                + ($detail->fixed_allowances ?? 0)
                + ($detail->variable_allowances ?? 0)
                + ($detail->overtime ?? 0);
            $detailNet = $detail->net_pay ?? ($detailGross - ($detail->deductions ?? 0));
            $detailWithholding = $detailGross - $detailNet;

            $grossPay += (float) $detailGross;
            $netPay += (float) $detailNet;
            $withholding += max((float) $detailWithholding, 0);
        }

        if ($grossPay <= 0) {
            $grossPay = (float) ($run->gross_pay ?? 0);
        }

        if ($netPay <= 0) {
            $netPay = (float) ($run->net_pay ?? $grossPay);
        }

        if ($withholding <= 0) {
            $withholding = max($grossPay - $netPay, 0);
        }

        $lines = [
            [
                'account_code' => '5300',
                'debit' => $grossPay,
                'credit' => 0,
                'line_description' => 'Salaries expense',
            ],
            [
                'account_code' => '2100',
                'debit' => 0,
                'credit' => $netPay,
                'line_description' => 'Salaries payable',
            ],
        ];

        if ($withholding > 0) {
            $lines[] = [
                'account_code' => '2200',
                'debit' => 0,
                'credit' => $withholding,
                'line_description' => 'Payroll liabilities',
            ];
        }

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
        $cashAccount = static::resolveCashAccountCode($audit->method ?? $audit->channel ?? null);
        $isSurplus = $difference > 0;

        $lines = [
            [
                'account_code' => $cashAccount,
                'debit' => $isSurplus ? $difference : 0,
                'credit' => $isSurplus ? 0 : abs($difference),
                'line_description' => 'Cash on hand adjustment',
            ],
            [
                'account_code' => $isSurplus ? '4100' : '5500',
                'debit' => $isSurplus ? 0 : abs($difference),
                'credit' => $isSurplus ? $difference : 0,
                'line_description' => $isSurplus ? 'Cash overage' : 'Cash shortage expense',
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

    protected static function decodeSecureNumeric(float $value): float
    {
        return ($value - 5) * 3;
    }

    protected static function isBalanced(float $totalDebit, float $totalCredit): bool
    {
        return abs($totalDebit - $totalCredit) < 0.01;
    }

    protected static function resolveCashAccountCode(?string $method): string
    {
        return match ($method) {
            'till' => '1010',
            'kt_mobile' => '1020',
            'bank_nat', 'nat' => '1100',
            'bank_equity', 'equity' => '1110',
            'bank_coop', 'coop' => '1120',
            default => '1000',
        };
    }

    protected static function estimateCostOfGoodsSold(Sale $sale): float
    {
        $cogs = 0.0;

        foreach ($sale->lines ?? [] as $line) {
            $qty = (float) ($line->qty ?? 0);
            $variantId = $line->product_variant_id ?? null;
            $unitCost = null;

            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if ($variant && isset($variant->cost)) {
                    $unitCost = (float) $variant->cost;
                }
            }

            if ($unitCost === null && isset($line->unit_cost)) {
                $unitCost = (float) $line->unit_cost;
            }

            if ($unitCost !== null) {
                $cogs += $unitCost * $qty;
            }
        }

        return $cogs;
    }
}
