<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class SaleController extends Controller
{
    /**
     * Download a delivery note PDF for the given sale.
     */
    public function printDeliveryNote(Sale $sale)
    {
        $sale->load(['customer', 'lines']);

        $pdf = Pdf::loadView('documents.delivery_note', [
            'sale' => $sale,
        ]);

        return $pdf->download('delivery-note-' . $sale->id . '.pdf');
use App\Models\SaleLine;
use App\Models\SalePayment;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => ['nullable', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', 'string', Rule::in(['cash', 'credit', 'till', 'nat', 'equity', 'coop', 'kt_mobile'])],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        $total = collect($data['items'])->reduce(function ($carry, $item) {
            return $carry + ($item['qty'] * $item['unit_price']);
        }, 0);

        $paymentsTotal = collect($data['payments'])->reduce(function ($carry, $payment) {
            return $carry + $payment['amount'];
        }, 0);

        if (abs($paymentsTotal - $total) > 0.01) {
            return response()->json([
                'message' => 'Payment total does not match sale total.',
                'total' => $total,
                'payments_total' => $paymentsTotal,
            ], 422);
        }

        $sale = DB::transaction(function () use ($data, $total) {
            $sale = Sale::create([
                'branch_id' => $data['branch_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'date_time' => now()->toDateTimeString(),
                'user_id' => auth()->id(),
                'status' => 'completed',
                'total' => $total,
            ]);

            foreach ($data['items'] as $item) {
                $lineTotal = $item['qty'] * $item['unit_price'];
                $saleLine = SaleLine::create([
                    'sale_id' => $sale->id,
                    'product_variant_id' => $item['product_variant_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                ]);
                $sale->lines[] = $saleLine;

                StockMovement::create([
                    'product_variant_id' => $item['product_variant_id'],
                    'branch_id' => $data['branch_id'] ?? null,
                    'type' => 'sale',
                    'qty_change' => -1 * $item['qty'],
                    'unit_cost' => null,
                    'reference' => 'SALE-' . $sale->id,
                    'user_id' => auth()->id(),
                ]);
            }

            foreach ($data['payments'] as $payment) {
                $salePayment = SalePayment::create([
                    'sale_id' => $sale->id,
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                ]);
                $sale->payments[] = $salePayment;
            }

            // Dr Debtors / Cash / Bank (per payment method)
            // Cr Sales
            // Dr Cost of Sales, Cr Inventory (when COGS is implemented)

            return $sale->load(['lines', 'payments']);
        });

        return response()->json($sale);
    }
}
