<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = Carbon::now();

        $accounts = [
            ['code' => '1000', 'name' => 'Cash on Hand', 'type' => 'Asset', 'is_system' => true],
            ['code' => '1010', 'name' => 'Till', 'type' => 'Asset', 'is_system' => true],
            ['code' => '1020', 'name' => 'KT Mobile', 'type' => 'Asset', 'is_system' => true],
            ['code' => '1100', 'name' => 'Bank – Nat', 'type' => 'Asset', 'is_system' => true],
            ['code' => '1110', 'name' => 'Bank – Equity', 'type' => 'Asset', 'is_system' => true],
            ['code' => '1120', 'name' => 'Bank – Co-op', 'type' => 'Asset', 'is_system' => true],
            ['code' => '1200', 'name' => 'Accounts Receivable (Debtors)', 'type' => 'Asset', 'is_system' => true],
            ['code' => '1300', 'name' => 'Inventory – Finished Goods', 'type' => 'Asset', 'is_system' => true],
            ['code' => '1310', 'name' => 'Inventory – Raw Materials', 'type' => 'Asset', 'is_system' => true],
            ['code' => '2000', 'name' => 'Creditors – Goods', 'type' => 'Liability', 'is_system' => true],
            ['code' => '2010', 'name' => 'Creditors – Services', 'type' => 'Liability', 'is_system' => true],
            ['code' => '2100', 'name' => 'Salaries Payable', 'type' => 'Liability', 'is_system' => true],
            ['code' => '3000', 'name' => "Capital – Owner's Equity", 'type' => 'Equity', 'is_system' => true],
            ['code' => '4000', 'name' => 'Sales', 'type' => 'Income', 'is_system' => true],
            ['code' => '4100', 'name' => 'Other Income (for cash overages, etc.)', 'type' => 'Income', 'is_system' => true],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'Expense', 'is_system' => true],
            ['code' => '5100', 'name' => 'Stock Loss & Damage Expense', 'type' => 'Expense', 'is_system' => true],
            ['code' => '5200', 'name' => 'Manufacturing Expenses', 'type' => 'Expense', 'is_system' => true],
            ['code' => '5300', 'name' => 'Salaries Expense', 'type' => 'Expense', 'is_system' => true],
            ['code' => '5400', 'name' => 'Utility Expenses (electricity, internet, tea, etc.)', 'type' => 'Expense', 'is_system' => true],
            ['code' => '5500', 'name' => 'Cash Shortage Expense', 'type' => 'Expense', 'is_system' => true],
        ];

        foreach ($accounts as $account) {
            DB::table('accounts')->updateOrInsert(
                ['code' => $account['code']],
                array_merge($account, [
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ])
            );
        }
    }
}
