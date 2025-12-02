<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the product variants.
     */
    public function index(Request $request)
    {
        $query = ProductVariant::query()->with('product');

        if ($search = $request->input('search')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($productQuery) use ($search) {
                        $productQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $variants = $query->get()->map(function (ProductVariant $variant) {
            return [
                'id' => $variant->id,
                'product_id' => $variant->product_id,
                'product_name' => optional($variant->product)->name,
                'size' => $variant->size,
                'colour' => $variant->colour,
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'selling_price' => $variant->selling_price_decoded ?? $variant->selling_price,
                'stock_qty' => $variant->stock_qty_decoded ?? $variant->stock_qty,
            ];
        });

        return response()->json($variants);
    }

    /**
     * Display the specified product variant.
     */
    public function show(ProductVariant $variant)
    {
        $variant->load('product');

        $payload = $variant->toArray();
        $payload['product_name'] = optional($variant->product)->name;
        $payload['selling_price'] = $variant->selling_price_decoded ?? $variant->selling_price;
        $payload['stock_qty'] = $variant->stock_qty_decoded ?? $variant->stock_qty;

        return response()->json($payload);
    }
}
