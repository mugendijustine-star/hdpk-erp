<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpeningStockController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'numeric', 'min:0'],
            'items.*.cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['items'] as $item) {
                StockMovement::create([
                    'product_variant_id' => $item['product_variant_id'],
                    'branch_id' => $data['branch_id'] ?? null,
                    'type' => 'opening',
                    'qty_change' => $item['qty'],
                    'unit_cost' => $item['cost'],
                    'reference' => 'OPENING-STOCK',
                    'user_id' => auth()->id(),
                ]);
            }
        });

        AccountingService::postOpeningStock($data['items'], $data['date'], $data['branch_id'] ?? null);

        return response()->json([
            'message' => 'Opening stock recorded successfully.',
            'items' => $data['items'],
        ], 201);
    }
}
