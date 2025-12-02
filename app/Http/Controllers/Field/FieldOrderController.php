<?php

namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use App\Models\FieldOrder;
use App\Models\FieldOrderLine;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleLine;
use App\Models\SalePayment;
use App\Models\SalesRep;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FieldOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = FieldOrder::query()->with(['salesRep', 'customer', 'territory', 'lines']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($salesRepId = $request->input('sales_rep_id')) {
            $query->where('sales_rep_id', $salesRepId);
        }

        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        if ($request->filled('date_from')) {
            $query->whereDate($request->input('date_field', 'created_at'), '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate($request->input('date_field', 'created_at'), '<=', $request->input('date_to'));
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (! in_array($user->role, ['sales_rep', 'manager', 'admin'], true)) {
            abort(403, 'Not allowed to create field orders');
        }

        $validated = $request->validate([
            'sales_rep_id' => ['required', 'integer', 'exists:sales_reps,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'sales_territory_id' => ['nullable', 'integer', 'exists:sales_territories,id'],
            'requested_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
        ]);

        if ($user->role === 'sales_rep') {
            $salesRep = SalesRep::where('user_id', $user->id)->first();

            if (! $salesRep || (int) $validated['sales_rep_id'] !== $salesRep->id) {
                abort(403, 'Not allowed to create field orders');
            }
        }

        $order = FieldOrder::create([
            'status' => 'submitted',
            'sales_rep_id' => $validated['sales_rep_id'],
            'customer_id' => $validated['customer_id'],
            'sales_territory_id' => $validated['sales_territory_id'] ?? null,
            'requested_date' => $validated['requested_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $orderTotal = 0;

        foreach ($validated['items'] as $item) {
            $lineTotal = $item['qty'] * $item['unit_price'];
            $orderTotal += $lineTotal;

            $line = new FieldOrderLine([
                'field_order_id' => $order->id,
                'product_variant_id' => $item['product_variant_id'],
            ]);

            $line->qty = $item['qty'];
            $line->unit_price = $item['unit_price'];
            $line->line_total = $lineTotal;
            $line->save();
        }

        $order->load('lines');

        return response()->json([
            'order' => $order,
            'total' => $orderTotal,
        ], 201);
    }

    public function approve(FieldOrder $order, Request $request)
    {
        $user = auth()->user();

        if (! in_array($user->role, ['manager', 'admin'], true)) {
            abort(403, 'Not allowed to approve field orders');
        }

        if ($order->status !== 'submitted') {
            return response()->json(['message' => 'Only submitted orders can be approved.'], 422);
        }

        $validated = $request->validate([
            'assigned_clerk_id' => ['nullable', 'integer', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $order->status = 'approved';
        $order->manager_id = $user->id;
        $order->approved_at = now();
        $order->assigned_clerk_id = $validated['assigned_clerk_id'] ?? null;
        $order->notes = $validated['notes'] ?? $order->notes;
        $order->save();

        return response()->json($order);
    }

    public function dispatch(FieldOrder $order, Request $request)
    {
        $user = auth()->user();

        if (! in_array($user->role, ['clerk', 'admin'], true)) {
            abort(403, 'Not allowed to dispatch field orders');
        }

        if ($order->status !== 'approved') {
            return response()->json(['message' => 'Only approved orders can be dispatched.'], 422);
        }

        if ($order->assigned_clerk_id && $user->role !== 'admin' && $user->id !== $order->assigned_clerk_id) {
            return response()->json(['message' => 'Order not assigned to you.'], 403);
        }

        $order->load('lines');

        $branchId = $request->input('branch_id');
        $saleTotal = $order->lines->sum(function (FieldOrderLine $line) {
            return $line->line_total ?? ($line->qty * $line->unit_price);
        });

        $sale = Sale::create([
            'branch_id' => $branchId,
            'customer_id' => $order->customer_id,
            'date_time' => Carbon::now()->toDateTimeString(),
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $sale->total = $saleTotal;
        $sale->save();

        foreach ($order->lines as $line) {
            $saleLine = new SaleLine([
                'sale_id' => $sale->id,
                'product_variant_id' => $line->product_variant_id,
            ]);

            $saleLine->qty = $line->qty;
            $saleLine->unit_price = $line->unit_price;
            $saleLine->line_total = $line->line_total ?? ($line->qty * $line->unit_price);
            $saleLine->save();

            $variant = ProductVariant::find($line->product_variant_id);

            $stockMovement = new StockMovement([
                'product_variant_id' => $line->product_variant_id,
                'branch_id' => $branchId,
                'type' => 'sale',
                'reference' => 'FIELD-ORDER-' . $order->id,
                'user_id' => $user->id,
            ]);

            $stockMovement->qty_change = -1 * ($line->qty ?? 0);
            $stockMovement->unit_cost = $variant?->cost;
            $stockMovement->save();
        }

        SalePayment::create([
            'sale_id' => $sale->id,
            'method' => 'credit',
            'amount' => $saleTotal,
        ]);

        $order->sale_id = $sale->id;
        $order->status = 'dispatched';
        $order->dispatched_by = $user->id;
        $order->dispatched_at = now();
        $order->save();

        // AccountingService::postSale($sale);

        return response()->json([
            'order' => $order->fresh('lines'),
            'sale' => $sale->load(['lines', 'payments']),
        ]);
    }
}
