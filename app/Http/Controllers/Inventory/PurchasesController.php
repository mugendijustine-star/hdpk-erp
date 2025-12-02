<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Purchase;
use App\Models\Inventory\PurchaseLine;
use App\Models\Inventory\StockMovement;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:purchases.create')->only('store');
    }

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

            return [$purchase, $lines];
        });

        AccountingService::postPurchase($purchase);

        return response()->json([
            'message' => 'Purchase recorded successfully.',
            'purchase' => $purchase,
            'lines' => $lines,
        ], 201);
    }
}
