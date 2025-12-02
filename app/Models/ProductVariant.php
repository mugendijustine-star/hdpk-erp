<?php

namespace App\Models;

class ProductVariant extends BaseModel
{
    public function setCost(float $value): void
    {
        $this->attributes['cost'] = $value;
use App\Traits\SecuresNumericAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ProductVariant extends Model
{
    use HasFactory;
    use SecuresNumericAttributes;

    protected $fillable = [
        'product_id',
        'size',
        'colour',
        'sku',
        'barcode',
        'cost_enc',
        'selling_price_enc',
        'stock_qty_enc',
        'reorder_level_enc',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->retrieveNumeric($this->cost_enc),
            set: fn ($value) => ['cost_enc' => $this->storeNumeric($value)],
        );
    }

    protected function sellingPrice(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->retrieveNumeric($this->selling_price_enc),
            set: fn ($value) => ['selling_price_enc' => $this->storeNumeric($value)],
        );
    }

    protected function stockQty(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->retrieveNumeric($this->stock_qty_enc),
            set: fn ($value) => ['stock_qty_enc' => $this->storeNumeric($value)],
        );
    }

    protected function reorderLevel(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->retrieveNumeric($this->reorder_level_enc),
            set: fn ($value) => ['reorder_level_enc' => $this->storeNumeric($value)],
        );
    }
}
