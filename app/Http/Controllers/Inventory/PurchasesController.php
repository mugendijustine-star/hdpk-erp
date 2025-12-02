<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseLine;
use App\Models\Inventory\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
            'reference' => ['nullable', 'string'],
            'payment_method' => ['required', 'string', 'in:cash,till,bank_nat,bank_equity,bank_coop,creditor_goods,creditor_services,capital'],
            'items' => ['required', 'array'],
            'items.*.product_variant_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'numeric'],
            'items.*.cost' => ['required', 'numeric'],
        ]);

        $items = collect($data['items']);
        $total = $items->reduce(function ($carry, $item) {
            return $carry + ($item['qty'] * $item['cost']);
        }, 0);

        [$purchase, $lines] = DB::transaction(function () use ($data, $items, $total, $request) {
            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'branch_id' => $data['branch_id'] ?? null,
                'date' => $data['date'],
                'reference' => $data['reference'] ?? null,
                'payment_method' => $data['payment_method'],
                'user_id' => $request->user()->id,
                'total' => $total,
            ]);

            $lines = [];

            foreach ($items as $item) {
                $lineTotal = $item['qty'] * $item['cost'];

                $lines[] = PurchaseLine::create([
                    'purchase_id' => $purchase->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'qty' => $item['qty'],
                    'cost' => $item['cost'],
                    'line_total' => $lineTotal,
                ]);

                StockMovement::create([
                    'product_variant_id' => $item['product_variant_id'],
                    'branch_id' => $data['branch_id'] ?? null,
                    'type' => 'purchase',
                    'qty_change' => $item['qty'],
                    'unit_cost' => $item['cost'],
                    'reference' => $data['reference'] ?? (string) $purchase->id,
                    'user_id' => $request->user()->id,
                ]);
            }

            // TODO: Post accounting journal entries.
            // If payment_method = 'capital':
            //   Dr Inventory (total)
            //   Cr Capital – Owner’s Equity (total)
            // Else if payment_method is a bank or cash method:
            //   Dr Inventory
            //   Cr respective Cash/Bank account
            // Else if payment_method is creditor_goods or creditor_services:
            //   Dr Inventory
            //   Cr respective Creditors account

            return [$purchase, $lines];
        });

        return response()->json([
            'message' => 'Purchase recorded successfully.',
            'purchase' => $purchase,
            'lines' => $lines,
        ], 201);
    }
}
